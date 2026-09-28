<?php

declare(strict_types=1);

namespace App\Domain\Security;

use App\Domain\WhatsApp\AdminWhatsAppService;
use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AntiFraudService
{
    public function __construct(
        protected ?AdminWhatsAppService $adminWhatsAppService = null,
        protected ?WhatsAppGatewayService $whatsAppGatewayService = null
    ) {
        $this->adminWhatsAppService = $adminWhatsAppService ?? app(AdminWhatsAppService::class);
        $this->whatsAppGatewayService = $whatsAppGatewayService ?? app(WhatsAppGatewayService::class);
    }

    /**
     * Evaluate model action and attribute changes against fraud and risk rules.
     *
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     * @return array{risk_level: string, risk_reason: ?string, notes: ?string}
     */
    public function evaluateRisk(
        Model $model,
        string $action,
        ?array $oldValues,
        ?array $newValues,
        ?string $customNotes = null
    ): array {
        // 1. RULE: Pembatalan Pesanan Kasir (Void Order / Refunded) -> HIGH RISK
        if ($model instanceof PosOrder) {
            $isVoided = ($newValues['status'] ?? null) === PosOrder::STATUS_VOIDED
                || (isset($newValues['status']) && in_array($newValues['status'], [PosOrder::STATUS_VOIDED, PosOrder::STATUS_REFUNDED, PosOrder::STATUS_REJECTED], true))
                || ! empty($newValues['void_reason'])
                || ($action === 'deleted');

            if ($isVoided) {
                $reason = $newValues['void_reason'] ?? $newValues['refund_reason'] ?? $newValues['rejection_reason'] ?? $model->void_reason ?? $customNotes;

                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Pembatalan Pesanan Kasir (Void Order)',
                    'notes' => $reason ? (string) $reason : 'Pesanan kasir dibatalkan/void tanpa alasan tertulis',
                ];
            }

            // RULE: Pemberian Diskon Kasir di atas 20% -> HIGH RISK
            $discountPct = (float) ($newValues['discount_percentage'] ?? $model->discount_percentage ?? 0);
            $subtotal = (float) ($newValues['subtotal'] ?? $model->subtotal ?? 0);
            $discountAmount = (float) ($newValues['discount_amount'] ?? $model->discount_amount ?? 0);

            if ($discountPct > 20.0 || ($subtotal > 0 && ($discountAmount / $subtotal) > 0.20)) {
                $effectivePct = $discountPct > 0 ? $discountPct : round(($discountAmount / $subtotal) * 100, 1);

                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Pemberian Diskon Kasir di atas 20%',
                    'notes' => "Diskon kasir {$effectivePct}% (Rp " . number_format($discountAmount, 0, ',', '.') . ') melampaui batas wajar',
                ];
            }
        }

        // 2. RULE: Perubahan Nomor Rekening Bank Supplier -> HIGH RISK
        if ($model instanceof Supplier) {
            $bankFieldsChanged = isset($newValues['bank_account_number'])
                || isset($newValues['bank_name'])
                || isset($newValues['bank_account_holder']);

            if ($action === 'updated' && $bankFieldsChanged) {
                $oldAcc = $oldValues['bank_account_number'] ?? '-';
                $newAcc = $newValues['bank_account_number'] ?? '-';
                $bankName = $newValues['bank_name'] ?? $oldValues['bank_name'] ?? 'Bank';

                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Perubahan Nomor Rekening Bank Supplier',
                    'notes' => "Rekening {$bankName} diubah dari {$oldAcc} ke {$newAcc}",
                ];
            }

            if ($action === 'deleted') {
                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Penghapusan Data Pemasok (Supplier)',
                    'notes' => "Pemasok {$model->name} dihapus dari sistem",
                ];
            }
        }

        // 3. RULE: Perubahan Hak Akses / Role Pengguna -> HIGH RISK
        if ($model instanceof BusinessMembership) {
            if ($action === 'updated' && (isset($newValues['role']) || isset($newValues['role_id']))) {
                $oldRole = $oldValues['role'] ?? 'anggota';
                $newRole = $newValues['role'] ?? 'anggota';

                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Perubahan Hak Akses / Role Pengguna',
                    'notes' => "Hak akses diubah dari '{$oldRole}' menjadi '{$newRole}'",
                ];
            }
        }

        // 4. RULE: Penghapusan Transaksi Jurnal Manual -> HIGH RISK
        if ($model instanceof JournalEntry && $action === 'deleted') {
            $refNumber = $oldValues['reference_number'] ?? $model->reference_number ?? 'JURNAL-MANUAL';

            return [
                'risk_level' => AuditLog::RISK_HIGH,
                'risk_reason' => 'Penghapusan Transaksi Jurnal Manual',
                'notes' => "Transaksi jurnal {$refNumber} dihapus permanen",
            ];
        }

        // 5. RULE: Perubahan Harga Jual Produk -> MEDIUM RISK
        if ($model instanceof Product && $action === 'updated') {
            if (isset($newValues['price']) || isset($newValues['selling_price'])) {
                $oldPrice = (float) ($oldValues['price'] ?? $oldValues['selling_price'] ?? 0);
                $newPrice = (float) ($newValues['price'] ?? $newValues['selling_price'] ?? 0);

                return [
                    'risk_level' => AuditLog::RISK_MEDIUM,
                    'risk_reason' => 'Perubahan Harga Jual Produk',
                    'notes' => 'Harga produk diubah dari Rp ' . number_format($oldPrice, 0, ',', '.') . ' menjadi Rp ' . number_format($newPrice, 0, ',', '.'),
                ];
            }
        }

        // 6. RULE: Pengeditan Data Pelanggan CRM -> MEDIUM RISK
        if ($model instanceof Customer && $action === 'updated') {
            return [
                'risk_level' => AuditLog::RISK_MEDIUM,
                'risk_reason' => 'Pengeditan Data Pelanggan CRM',
                'notes' => "Informasi profil pelanggan {$model->name} diperbarui",
            ];
        }

        // 7. RULE: Penonaktifan atau Penghapusan Gudang / Lokasi Operasional -> HIGH RISK (§FR-05)
        if ($model instanceof Location) {
            if ($action === 'updated' && isset($newValues['is_active']) && $newValues['is_active'] === false) {
                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Penonaktifan Gudang/Lokasi Operasional',
                    'notes' => "Lokasi operasional {$model->name} dinonaktifkan dari sistem",
                ];
            }

            if ($action === 'deleted') {
                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Penghapusan Gudang/Lokasi',
                    'notes' => "Lokasi {$model->name} dihapus permanen",
                ];
            }
        }

        // 8. RULE: Penyesuaian Kerugian Stok Bernilai Tinggi -> HIGH RISK
        if ($model instanceof StockAdjustment) {
            $lossCost = (float) ($newValues['total_loss_cost'] ?? $model->total_loss_cost ?? 0);
            if ($lossCost > 100000) {
                return [
                    'risk_level' => AuditLog::RISK_HIGH,
                    'risk_reason' => 'Penyesuaian Kerugian Stok Bernilai Tinggi',
                    'notes' => "Penyesuaian kerugian stok #{$model->adjustment_number} senilai Rp " . number_format($lossCost, 0, ',', '.'),
                ];
            }
        }

        // 9. DEFAULT: LOW RISK
        return [
            'risk_level' => AuditLog::RISK_LOW,
            'risk_reason' => null,
            'notes' => $customNotes,
        ];
    }

    /**
     * Send real-time WhatsApp Security Alert to the Business Owner.
     */
    public function sendFraudWhatsAppAlert(AuditLog $auditLog, Business $business): array
    {
        // 1. Temukan nomor WhatsApp Owner
        $owner = $this->getBusinessOwner($business);
        $phone = $owner?->phone ?: $business->phone;

        if (empty($phone)) {
            Log::warning("[AntiFraud] Gagal mengirim alert WA: Nomor telepon Owner/Bisnis {$business->name} ({$business->id}) tidak ditemukan.");

            return [
                'success' => false,
                'error' => 'Nomor WhatsApp Owner tidak ditemukan',
            ];
        }

        // 2. Susun data forensik dokumen
        $docDetails = $this->resolveDocumentDetails($auditLog);
        $performerName = $auditLog->user?->name ?? 'Kasir / Staf Sistem';
        $locationName = $docDetails['location_name'] ?? $business->city ?? $business->name;
        $formattedTime = Carbon::parse($auditLog->created_at ?? now())->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB';
        $auditLogsUrl = config('app.url', 'http://127.0.0.1:8000') . '/settings/audit-logs';

        // 3. Format pesan resmi PRD-05
        $message = "🚨 [PERINGATAN KEAMANAN COOCA] 🚨\n\n"
            . "Halo Bapak/Ibu Owner,\n"
            . "Sistem mendeteksi aktivitas berisiko tinggi pada bisnis Anda:\n\n"
            . "• Aktivitas: " . ($auditLog->risk_reason ?: 'Aktivitas Berisiko Tinggi') . "\n"
            . "• Dokumen: {$docDetails['document_ref']}\n"
            . "• Nilai Transaksi: {$docDetails['amount']}\n"
            . "• Dilakukan Oleh: {$performerName}\n"
            . "• Waktu: {$formattedTime}\n"
            . "• Lokasi: {$locationName} (IP: " . ($auditLog->ip_address ?: '127.0.0.1') . ")\n"
            . "• Alasan Diinput: \"" . ($auditLog->notes ?: 'Tidak ada alasan dicantumkan') . "\"\n\n"
            . "Silakan periksa detail jejak audit di:\n"
            . "{$auditLogsUrl}";

        // 4. Kirim via Admin WhatsApp Platform atau Gateway
        try {
            $result = $this->adminWhatsAppService->sendMessage($phone, $message);

            if (! ($result['success'] ?? false)) {
                // Fallback via Business Gateway
                $result = $this->whatsAppGatewayService->sendMessage($business, $phone, $message);
            }

            // Update log alert sent timestamp
            $auditLog->update([
                'alert_sent_at' => now(),
                'alert_recipient' => $phone,
            ]);

            return [
                'success' => true,
                'phone' => $phone,
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            Log::error("[AntiFraud] Exception saat mengirim WA alert: " . $e->getMessage(), [
                'audit_log_id' => $auditLog->id,
                'business_id' => $business->id,
            ]);

            // Tetap catat penerima yang dituju
            $auditLog->update([
                'alert_sent_at' => now(),
                'alert_recipient' => $phone,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve the business primary owner user.
     */
    protected function getBusinessOwner(Business $business): ?User
    {
        $owner = $business->owner;
        if ($owner) {
            return $owner;
        }

        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('role', 'owner')
            ->first();

        return $membership ? User::find($membership->user_id) : null;
    }

    /**
     * Resolve document reference, value, and location from auditable data.
     *
     * @return array{document_ref: string, amount: string, location_name: string}
     */
    public function resolveDocumentDetails(AuditLog $auditLog): array
    {
        $docRef = 'Dokumen #' . substr((string) $auditLog->auditable_id, 0, 8);
        $amount = '-';
        $locationName = 'Pusat';

        $values = $auditLog->new_values ?: ($auditLog->old_values ?: []);

        if ($auditLog->auditable_type && str_contains($auditLog->auditable_type, 'PosOrder')) {
            $orderNumber = $values['order_number'] ?? null;
            $docRef = $orderNumber ? 'Struk #' . $orderNumber : $docRef;

            $total = (float) ($values['total_amount'] ?? 0);
            $amount = $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : '-';
        } elseif ($auditLog->auditable_type && str_contains($auditLog->auditable_type, 'Supplier')) {
            $supplierName = $values['name'] ?? null;
            $docRef = $supplierName ? 'Pemasok: ' . $supplierName : $docRef;
        } elseif ($auditLog->auditable_type && str_contains($auditLog->auditable_type, 'JournalEntry')) {
            $refNumber = $values['reference_number'] ?? null;
            $docRef = $refNumber ? 'Jurnal #' . $refNumber : $docRef;
            $total = (float) ($values['total_debit'] ?? 0);
            $amount = $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : '-';
        }

        return [
            'document_ref' => $docRef,
            'amount' => $amount,
            'location_name' => $locationName,
        ];
    }
}
