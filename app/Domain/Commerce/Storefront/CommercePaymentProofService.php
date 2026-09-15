<?php

declare(strict_types=1);

namespace App\Domain\Commerce\Storefront;

use App\Domain\Inventory\StockService;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\CommerceOrder;
use App\Models\CommercePaymentProof;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class CommercePaymentProofService
{
    public function __construct(
        private readonly StockService $stockService = new StockService(),
        private readonly WhatsAppGatewayService $waGateway = new WhatsAppGatewayService()
    ) {}

    /**
     * Submit payment transfer receipt proof from customer tracking screen.
     */
    public function submitProof(
        CommerceOrder $order,
        UploadedFile $file,
        ?string $senderBank = null,
        ?string $senderAccountName = null
    ): CommercePaymentProof {
        if (! $order->canSubmitProof()) {
            throw new DomainException('Pesanan ini saat ini tidak menerima unggahan bukti transfer.');
        }

        // Validate file
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $mime = $file->getMimeType();
        if (! in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException('Format berkas harus berupa gambar (JPG, PNG, WEBP) atau PDF.');
        }

        $sizeKb = (int) ceil($file->getSize() / 1024);
        if ($sizeKb > 5120) {
            throw new InvalidArgumentException('Ukuran berkas bukti maksimal 5 MB.');
        }

        // Store privately: storage/app/private/commerce_proofs/{biz}/{order}/{hash}.ext
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $randomName = Str::random(40) . '.' . $ext;
        $relativePath = "commerce_proofs/{$order->business_id}/{$order->id}/{$randomName}";

        Storage::disk('local')->putFileAs(
            "commerce_proofs/{$order->business_id}/{$order->id}",
            $file,
            $randomName
        );

        return DB::transaction(function () use ($order, $relativePath, $sizeKb, $mime, $senderBank, $senderAccountName) {
            $proof = CommercePaymentProof::create([
                'business_id' => $order->business_id,
                'commerce_order_id' => $order->id,
                'file_path' => $relativePath,
                'file_size_kb' => $sizeKb,
                'mime_type' => $mime,
                'sender_bank' => $senderBank ? trim($senderBank) : null,
                'sender_account_name' => $senderAccountName ? trim($senderAccountName) : null,
                'status' => CommercePaymentProof::STATUS_PENDING,
            ]);

            $order->update([
                'status' => CommerceOrder::STATUS_PROOF_SUBMITTED,
                'payment_status' => CommerceOrder::PAYMENT_VERIFYING,
                'rejection_reason' => null,
            ]);

            return $proof;
        });
    }

    /**
     * Merchant approves payment proof and commits reserved stock.
     */
    public function verifyProof(CommercePaymentProof $proof, User $verifier): void
    {
        $order = $proof->order;
        if (! $order || $order->isPaid()) {
            return;
        }

        DB::transaction(function () use ($proof, $order, $verifier) {
            $proof->update([
                'status' => CommercePaymentProof::STATUS_VERIFIED,
                'verified_by' => $verifier->id,
                'verified_at' => Carbon::now(),
                'rejection_reason' => null,
            ]);

            $order->update([
                'status' => CommerceOrder::STATUS_PAID,
                'payment_status' => CommerceOrder::PAYMENT_PAID,
                'paid_at' => Carbon::now(),
            ]);

            // Commit reserved stock for each physical item
            foreach ($order->items as $item) {
                if ($item->product && $item->product->isGoods()) {
                    $this->stockService->commitProductReservedStock(
                        businessId: $order->business_id,
                        locationId: $order->location_id,
                        product: $item->product,
                        productQuantity: (float) $item->quantity,
                        unitCost: (float) $item->product->base_cost,
                        referenceId: $order->id,
                        referenceNumber: $order->order_number,
                        userId: $verifier->id
                    );
                }
            }

            // Send notification to customer
            $this->sendPaymentVerifiedNotification($order);
        });
    }

    /**
     * Merchant rejects payment proof and asks customer to re-upload.
     */
    public function rejectProof(CommercePaymentProof $proof, User $verifier, string $rejectionReason): void
    {
        $order = $proof->order;
        if (! $order || $order->isPaid()) {
            return;
        }

        $reason = trim($rejectionReason);
        if ($reason === '') {
            throw new InvalidArgumentException('Alasan penolakan bukti transfer wajib diisi.');
        }

        DB::transaction(function () use ($proof, $order, $verifier, $reason) {
            $proof->update([
                'status' => CommercePaymentProof::STATUS_REJECTED,
                'verified_by' => $verifier->id,
                'verified_at' => Carbon::now(),
                'rejection_reason' => $reason,
            ]);

            $order->update([
                'status' => CommerceOrder::STATUS_PAYMENT_REJECTED,
                'payment_status' => CommerceOrder::PAYMENT_FAILED,
                'rejection_reason' => $reason,
            ]);

            // Send rejection notice to customer
            $this->sendPaymentRejectedNotification($order, $reason);
        });
    }

    private function sendPaymentVerifiedNotification(CommerceOrder $order): void
    {
        try {
            $business = $order->business;
            $trackingUrl = url("/b/{$business->slug}/order/{$order->tracking_token}");

            $msg = "✅ Pembayaran Dikonfirmasi!\n\n";
            $msg .= "Halo *{$order->customer_name}*, pembayaran untuk pesanan #{$order->order_number} telah diverifikasi oleh *{$business->name}*.\n";
            $msg .= "Pesanan Anda sekarang sedang diproses.\n\n";
            $msg .= "Pantau status pesanan Anda di sini:\n👉 {$trackingUrl}\n\nTerima kasih!";

            $this->waGateway->sendMessage($business, $order->customer_phone, $msg);
        } catch (\Throwable $e) {
            Log::warning("[CommercePaymentProofService] WA notif failed: " . $e->getMessage());
        }
    }

    private function sendPaymentRejectedNotification(CommerceOrder $order, string $reason): void
    {
        try {
            $business = $order->business;
            $trackingUrl = url("/b/{$business->slug}/order/{$order->tracking_token}");

            $msg = "⚠️ Verifikasi Pembayaran Tertolak\n\n";
            $msg .= "Halo *{$order->customer_name}*, bukti transfer untuk pesanan #{$order->order_number} belum dapat diverifikasi.\n";
            $msg .= "*Alasan:* {$reason}\n\n";
            $msg .= "Silakan unggah ulang bukti transfer yang benar melalui tautan berikut:\n👉 {$trackingUrl}\n\nTerima kasih!";

            $this->waGateway->sendMessage($business, $order->customer_phone, $msg);
        } catch (\Throwable $e) {
            Log::warning("[CommercePaymentProofService] WA notif failed: " . $e->getMessage());
        }
    }
}
