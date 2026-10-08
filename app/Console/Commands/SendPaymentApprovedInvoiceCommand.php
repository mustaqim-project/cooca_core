<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Mail\DynamicMailConfig;
use App\Mail\PaymentApprovedInvoiceMail;
use App\Models\BillingPackage;
use App\Models\Business;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class SendPaymentApprovedInvoiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:send-payment-approved-invoice 
                            {payment_id? : UUID atau nomor order tagihan langganan} 
                            {--email= : Email penerima (default: email pemilik bisnis)}
                            {--save-path= : Path penyimpanan file PDF hasil generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirimkan [Kwitansi Resmi & Invoice] Pembayaran Terverifikasi Cooca lengkap dengan lampiran file PDF invoice';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        DynamicMailConfig::bootstrap();

        $paymentId = $this->argument('payment_id');
        $payment = null;

        if ($paymentId) {
            $payment = SubscriptionPayment::where('id', $paymentId)
                ->orWhere('order_number', $paymentId)
                ->first();

            if (! $payment) {
                $this->error("Tagihan pembayaran dengan ID atau No. Order '{$paymentId}' tidak ditemukan.");
                return self::FAILURE;
            }
        } else {
            $payment = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
                ->latest()
                ->first()
                ?? SubscriptionPayment::latest()->first();
        }

        // Jika belum ada data pembayaran sama sekali di database, siapkan contoh pembayaran resmi
        if (! $payment) {
            $this->info('Belum ada data transaksi pembayaran langganan. Menyiapkan data transaksi verifikasi resmi...');

            $user = User::first() ?? User::factory()->create([
                'name' => 'Pemilik Bisnis Cooca',
                'email' => 'owner@cooca.id',
            ]);

            $business = Business::first() ?? Business::create([
                'name' => 'Kedai Cooca Digital Nusantara',
                'currency' => 'IDR',
                'email' => $user->email,
                'phone' => '081298765432',
                'address' => 'Jl. Jenderal Sudirman Kav. 28, Jakarta Pusat',
            ]);

            if (! $business->users()->where('users.id', $user->id)->exists()) {
                $business->users()->attach($user->id, [
                    'id' => (string) Str::uuid(),
                    'role' => 'owner',
                ]);
            }

            $package = BillingPackage::where('code', 'pro')->first()
                ?? BillingPackage::create([
                    'type' => BillingPackage::TYPE_SUBSCRIPTION,
                    'code' => 'cooca_pro_monthly',
                    'name' => 'Layanan Cooca Pro',
                    'price' => 149000,
                    'duration_days' => 30,
                    'is_active' => true,
                ]);

            $payment = SubscriptionPayment::create([
                'business_id' => $business->id,
                'user_id' => $user->id,
                'order_number' => 'ORD-COOCA-' . date('Ymd') . '-001',
                'billing_package_id' => $package->id,
                'package_name_snapshot' => $package->name,
                'plan_code' => $package->code,
                'package_duration_days' => $package->duration_days,
                'payment_type' => BillingPackage::TYPE_SUBSCRIPTION,
                'amount' => 149000,
                'package_price' => 149000,
                'discount_amount' => 0,
                'gateway_fee' => 0,
                'payment_method' => SubscriptionPayment::METHOD_QRIS,
                'status' => SubscriptionPayment::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        }

        $payment->loadMissing(['business.users', 'billingPackage', 'user']);

        // Tentukan email tujuan
        $ownerUser = $payment->user
            ?? $payment->business?->users()->wherePivot('role', 'owner')->first()
            ?? $payment->business?->users()->first();

        $recipientEmail = $this->option('email')
            ?: ($ownerUser?->email ?? ($payment->business?->email ?? config('mail.from.address')));

        if (! $recipientEmail) {
            $this->error('Email penerima tidak ditemukan.');
            return self::FAILURE;
        }

        $invoiceNo = 'INV-' . strtoupper(substr($payment->id, 0, 8));
        $packageName = $payment->billingPackage?->name ?? ($payment->package_name ?? 'Layanan Cooca');

        $this->info("--------------------------------------------------");
        $this->info("Menyiapkan [Kwitansi Resmi & Invoice] Pembayaran Terverifikasi");
        $this->info("--------------------------------------------------");
        $this->line("<comment>Nama Bisnis   :</comment> " . ($payment->business?->name ?? '-'));
        $this->line("<comment>Layanan/Paket :</comment> {$packageName}");
        $this->line("<comment>No. Invoice   :</comment> {$invoiceNo}");
        $this->line("<comment>No. Pesanan   :</comment> {$payment->order_number}");
        $this->line("<comment>Total Bayar   :</comment> Rp " . number_format((float) $payment->amount, 0, ',', '.'));
        $this->line("<comment>Penerima      :</comment> {$recipientEmail}");

        // Inisialisasi mailable & generate PDF invoice
        $this->line("\n<info>[1/3]</info> Membuat file PDF Faktur & Kwitansi Resmi via DomPDF...");
        $mailable = new PaymentApprovedInvoiceMail($payment);
        $pdfBinary = $mailable->generatePdfContent($invoiceNo);

        $pdfBytes = strlen($pdfBinary);
        $pdfKb = round($pdfBytes / 1024, 2);
        $this->info("      File PDF berhasil dibuat ({$pdfKb} KB).");

        // Simpan salinan file PDF secara lokal
        $this->line("<info>[2/3]</info> Menyimpan salinan berkas PDF ke disk storage...");
        $fileName = "Invoice-Cooca-{$invoiceNo}.pdf";
        $defaultDir = storage_path('app/public/invoices');
        File::ensureDirectoryExists($defaultDir);
        $targetPath = $this->option('save-path') ?: ($defaultDir . DIRECTORY_SEPARATOR . $fileName);
        File::put($targetPath, $pdfBinary);
        $this->info("      Tersimpan di: {$targetPath}");

        // Kirim email dengan lampiran PDF
        $this->line("<info>[3/3]</info> Mengirimkan email resmi berserta lampiran PDF ke {$recipientEmail}...");
        Mail::to($recipientEmail)->send($mailable);

        $this->newLine();
        $this->info("==================================================");
        $this->info("SUCCESS: Email dan File PDF Berhasil Dikirimkan!");
        $this->info("==================================================");
        $this->line("Subjek Email : " . $mailable->envelope()->subject);
        $this->line("Lampiran PDF : {$fileName} ({$pdfKb} KB)");
        $this->line("Lokasi PDF   : {$targetPath}");

        return self::SUCCESS;
    }
}
