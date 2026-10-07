<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Domain\Report\Pos\DTOs\PosKpiSummaryDTO;
use App\Domain\Report\Pos\DTOs\PosReportFilterDTO;
use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class PosDailySalesSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Collection<int, object> $channels
     * @param Collection<int, object> $topProducts
     */
    public function __construct(
        public readonly Business $business,
        public readonly PosReportFilterDTO $filter,
        public readonly PosKpiSummaryDTO $kpi,
        public readonly Collection $channels,
        public readonly Collection $topProducts
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the array representation of the notification for in-app database center.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'type' => 'pos_daily_sales_summary',
            'business_id' => $this->business->id,
            'title' => 'Laporan Penjualan POS (' . $this->filter->startDate->format('d M Y') . ')',
            'message' => 'Total Omzet: Rp ' . number_format((float) $this->kpi->netSales, 0, ',', '.') . ' (' . $this->kpi->totalOrders . ' transaksi, Laba: Rp ' . number_format((float) $this->kpi->grossProfit, 0, ',', '.') . ')',
            'net_sales' => $this->kpi->netSales,
            'gross_sales' => $this->kpi->grossSales,
            'total_orders' => $this->kpi->totalOrders,
            'gross_profit' => $this->kpi->grossProfit,
            'margin_percent' => $this->kpi->grossMarginPercent,
            'total_hpp' => $this->kpi->totalHpp,
            'channels_count' => $this->channels->count(),
            'start_date' => $this->filter->startDate->toDateString(),
            'end_date' => $this->filter->endDate->toDateString(),
            'action_url' => route('pos.reports.index', [
                'tab' => 'overview',
                'start_date' => $this->filter->startDate->toDateString(),
                'end_date' => $this->filter->endDate->toDateString(),
            ]),
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $recipientName = $notifiable->name ?? 'Pemilik Usaha';
        $dateFormatted = $this->filter->startDate->translatedFormat('d F Y');
        $dashboardUrl = route('pos.reports.index', [
            'tab' => 'overview',
            'start_date' => $this->filter->startDate->toDateString(),
            'end_date' => $this->filter->endDate->toDateString(),
        ]);

        return (new MailMessage)
            ->subject("📊 Ringkasan Penjualan POS ({$dateFormatted}) - {$this->business->name}")
            ->view('emails.pos_daily_sales_summary', [
                'business' => $this->business,
                'recipientName' => $recipientName,
                'dateFormatted' => $dateFormatted,
                'kpi' => $this->kpi,
                'channels' => $this->channels,
                'topProducts' => $this->topProducts,
                'dashboardUrl' => $dashboardUrl,
            ]);
    }

    /**
     * Build standard WhatsApp format message text.
     */
    public function toWhatsAppMessage(string $recipientName = 'Bapak/Ibu'): string
    {
        $dateFormatted = $this->filter->startDate->format('d/m/Y');
        $netSalesFormatted = 'Rp ' . number_format((float) $this->kpi->netSales, 0, ',', '.');
        $grossSalesFormatted = 'Rp ' . number_format((float) $this->kpi->grossSales, 0, ',', '.');
        $profitFormatted = 'Rp ' . number_format((float) $this->kpi->grossProfit, 0, ',', '.');
        $hppFormatted = 'Rp ' . number_format((float) $this->kpi->totalHpp, 0, ',', '.');
        $marginFormatted = number_format((float) $this->kpi->grossMarginPercent, 1) . '%';
        $reportUrl = route('pos.reports.index', [
            'start_date' => $this->filter->startDate->toDateString(),
            'end_date' => $this->filter->endDate->toDateString(),
        ]);

        $lines = [
            "📊 *RINGKASAN PENJUALAN POS & SALURAN DIGITAL*",
            "Bisnis: *{$this->business->name}*",
            "Tanggal: *{$dateFormatted}*",
            "Yth. *{$recipientName}*",
            "",
            "📈 *METRIK UTAMA:*",
            "• Omzet Bersih: *{$netSalesFormatted}* (Bruto: {$grossSalesFormatted})",
            "• Total Transaksi: *{$this->kpi->totalOrders} Pesanan*",
            "• Total Modal/HPP: *{$hppFormatted}*",
            "• Laba Kotor Riil: *{$profitFormatted}*",
            "• Margin Laba: *{$marginFormatted}*",
        ];

        if ($this->channels->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "🛵 *PERFORMA SALURAN JUAL & OJOL:*";
            foreach ($this->channels as $chan) {
                $chanName = $chan->channel_label ?? $chan->channel;
                $chanGross = 'Rp ' . number_format((float) $chan->gross_sales, 0, ',', '.');
                $chanNet = 'Rp ' . number_format((float) $chan->net_merchant_payout, 0, ',', '.');
                $lines[] = "• *{$chanName}*: {$chan->order_count} order | Omzet: {$chanGross} (Net Payout: *{$chanNet}*)";
            }
        }

        if ($this->topProducts->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "🏆 *TOP 3 PRODUK TERLARIS:*";
            foreach ($this->topProducts->take(3) as $idx => $prod) {
                $rank = $idx + 1;
                $salesFormatted = 'Rp ' . number_format((float) $prod->total_sales, 0, ',', '.');
                $lines[] = "{$rank}. {$prod->product_name} ({$prod->total_qty}x) - {$salesFormatted}";
            }
        }

        $lines[] = "";
        $lines[] = "🔗 *Buka Laporan POS Lengkap:*";
        $lines[] = $reportUrl;
        $lines[] = "";
        $lines[] = "_Dihasilkan otomatis oleh COOCA ID System_";

        return implode("\n", $lines);
    }
}
