<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Illuminate\Console\Command;

class SendWhatsAppSubscriptionRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-wa-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat WhatsApp ke Bisnis Owner untuk masa patungan/langganan yang akan habis (H-7, H-3, H-1, Hari H)';

    /**
     * Execute the console command.
     */
    public function handle(AdminWhatsAppService $adminWaService): int
    {
        $this->info('Memeriksa langganan jatuh tempo (H-7, H-3, H-1, Hari H)...');

        $status = $adminWaService->getStatus();
        if (($status['status'] ?? '') !== 'connected') {
            $this->warn('WhatsApp Admin belum terhubung. Harap scan QR di panel Admin WhatsApp terlebih dahulu.');
            return self::FAILURE;
        }

        $result = $adminWaService->sendAllDueReminders();

        $this->info("Pengiriman selesai: {$result['sent']} terkirim, {$result['failed']} gagal, {$result['skipped']} dilewati (sudah dikirim hari ini).");

        return self::SUCCESS;
    }
}
