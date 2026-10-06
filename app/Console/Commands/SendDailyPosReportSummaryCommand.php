<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Domain\Report\Pos\PosReportingService;
use App\Domain\WhatsApp\WhatsAppService;
use App\Models\Business;
use App\Models\User;
use App\Notifications\PosDailySalesSummaryNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendDailyPosReportSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pos:send-daily-summary
                            {--business= : ID spesifik bisnis yang ingin diproses}
                            {--date= : Tanggal evaluasi laporan (YYYY-MM-DD, default hari ini)}
                            {--channel=all : Saluran notifikasi (all, database, mail, whatsapp)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim ringkasan eksekutif harian penjualan POS dan saluran digital ke In-App, Email, dan WhatsApp Pemilik Bisnis';

    /**
     * Execute the console command.
     */
    public function handle(PosReportingService $reportingService, WhatsAppService $whatsAppService): int
    {
        $this->info('Memulai pemrosesan pengiriman ringkasan harian penjualan POS...');

        $businessId = $this->option('business');
        $dateOption = $this->option('date');
        $channel = (string) ($this->option('channel') ?? 'all');

        $asOfDate = $dateOption ? Carbon::parse($dateOption)->startOfDay() : Carbon::today()->startOfDay();
        $this->line("Tanggal Evaluasi: {$asOfDate->toDateString()}");
        $this->line("Saluran Dipilih: {$channel}");

        $businessesQuery = Business::query()->where('is_active', true);
        if ($businessId) {
            $businessesQuery->where('id', $businessId);
        }

        $businesses = $businessesQuery->get();
        if ($businesses->isEmpty()) {
            $this->warn('Tidak ada bisnis aktif yang ditemukan untuk diproses.');
            return self::SUCCESS;
        }

        $totalProcessed = 0;
        $totalNotificationsSent = 0;
        $totalWhatsAppSent = 0;

        foreach ($businesses as $business) {
            $filter = new PosReportFilterDTO(
                businessId: $business->id,
                startDate: $asOfDate->copy()->startOfDay(),
                endDate: $asOfDate->copy()->endOfDay()
            );

            $kpi = $reportingService->getKpiSummary($filter);

            // Skip jika tidak ada transaksi pada hari itu kecuali jika diminta secara spesifik
            if ($kpi->totalOrders === 0 && ! $businessId) {
                continue;
            }

            $channels = $reportingService->getSalesChannelBreakdown($filter);
            $topProducts = $reportingService->getTopSellingProducts($filter, 5);

            // Ambil seluruh users pemilik / manajer bisnis
            $recipients = $business->users()
                ->wherePivotIn('role', ['owner', 'admin', 'manager'])
                ->get();

            if ($recipients->isEmpty()) {
                $this->warn("Bisnis {$business->name} ({$business->id}) tidak memiliki penerima dengan role owner/admin.");
                continue;
            }

            $notification = new PosDailySalesSummaryNotification(
                business: $business,
                filter: $filter,
                kpi: $kpi,
                channels: $channels,
                topProducts: $topProducts
            );

            // 1. Dispatch In-App & Email Notification
            if (in_array($channel, ['all', 'database', 'mail'], true)) {
                Notification::send($recipients, $notification);
                $totalNotificationsSent += $recipients->count();
            }

            // 2. Dispatch WhatsApp Notification
            if (in_array($channel, ['all', 'whatsapp'], true)) {
                $waMessage = $notification->toWhatsAppMessage($business->name);
                
                // Kirim ke nomor telepon bisnis atau nomor owner
                $recipientPhones = [];
                if (! empty($business->phone)) {
                    $recipientPhones[] = $business->phone;
                }
                foreach ($recipients as $recipient) {
                    if (! empty($recipient->phone)) {
                        $recipientPhones[] = $recipient->phone;
                    }
                }

                $recipientPhones = array_unique(array_filter($recipientPhones));
                foreach ($recipientPhones as $phone) {
                    $sent = $whatsAppService->sendDailyPosSummary($business, $phone, $waMessage);
                    if ($sent) {
                        $totalWhatsAppSent++;
                    }
                }
            }

            $totalProcessed++;
            $this->line("✔ [{$business->name}] Diproses: {$kpi->totalOrders} order, Net: Rp " . number_format((float) $kpi->netSales, 0, ',', '.'));
        }

        $this->info("Pengiriman Ringkasan Selesai:");
        $this->line("• Total Bisnis Diproses: {$totalProcessed}");
        $this->line("• Notifikasi In-App/Email Terkirim: {$totalNotificationsSent}");
        $this->line("• Pesan WhatsApp Terkirim: {$totalWhatsAppSent}");

        return self::SUCCESS;
    }
}
