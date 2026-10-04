<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Crm\CustomerPaymentTermReminderService;
use App\Models\Business;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendCustomerPaymentTermRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:send-term-reminders 
                            {--business= : ID spesifik bisnis yang ingin diproses} 
                            {--date= : Tanggal simulasi evaluasi (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim otomatis notifikasi pengingat termin pembayaran faktur ke WhatsApp dan Email pelanggan jika belum bayar (H-3, Hari H, Overdue)';

    /**
     * Execute the console command.
     */
    public function handle(CustomerPaymentTermReminderService $reminderService): int
    {
        $this->info('Memulai pemindaian termin pembayaran pelanggan yang belum lunas...');

        $businessId = $this->option('business');
        $specificBusiness = $businessId ? Business::find($businessId) : null;
        if ($businessId && ! $specificBusiness) {
            $this->error("Bisnis dengan ID {$businessId} tidak ditemukan.");
            return self::FAILURE;
        }

        $dateOption = $this->option('date');
        $asOfDate = $dateOption ? Carbon::parse($dateOption) : Carbon::today();

        $this->line("Tanggal Evaluasi: {$asOfDate->toDateString()}");

        $stats = $reminderService->sendScheduledDueReminders($specificBusiness, $asOfDate);

        $this->info("Pemindaian Selesai:");
        $this->line("• Total Faktur Diperiksa: {$stats['total_checked']}");
        $this->line("• Berhasil Terkirim: {$stats['sent']}");
        $this->line("• Dilewati (Sudah Diingatkan Hari Ini): {$stats['skipped']}");
        $this->line("• Gagal Kirim: {$stats['failed']}");

        return self::SUCCESS;
    }
}
