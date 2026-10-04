<?php

declare(strict_types=1);

namespace App\Domain\Crm;

use App\Domain\WhatsApp\WhatsAppGatewayService;
use App\Domain\WhatsApp\WhatsAppService;
use App\Mail\CustomerPaymentTermReminderMail;
use App\Models\Business;
use App\Models\Customer;
use App\Models\CustomerTermReminder;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CustomerPaymentTermReminderService
{
    public function __construct(
        protected ?WhatsAppGatewayService $waGateway = null,
        protected ?WhatsAppService $waLegacy = null
    ) {
        $this->waGateway = $waGateway ?? app(WhatsAppGatewayService::class);
        $this->waLegacy = $waLegacy ?? app(WhatsAppService::class);
    }

    /**
     * Send scheduled payment term reminders across businesses for due and overdue invoices.
     *
     * @return array{total_checked: int, sent: int, skipped: int, failed: int, details: list<array>}
     */
    public function sendScheduledDueReminders(?Business $specificBusiness = null, ?Carbon $date = null): array
    {
        $today = ($date ?? Carbon::today())->startOfDay();

        $businesses = $specificBusiness
            ? collect([$specificBusiness])
            : Business::query()->get();

        $stats = [
            'total_checked' => 0,
            'sent' => 0,
            'skipped' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($businesses as $business) {
            $invoices = Invoice::with(['customer', 'business'])
                ->where('business_id', $business->id)
                ->whereIn('status', [
                    Invoice::STATUS_SENT,
                    Invoice::STATUS_UNPAID,
                    Invoice::STATUS_PARTIALLY_PAID,
                    Invoice::STATUS_OVERDUE,
                ])
                ->where('balance_due', '>', 0)
                ->whereNotNull('due_date')
                ->get();

            foreach ($invoices as $invoice) {
                $stats['total_checked']++;

                $dueDate = Carbon::parse($invoice->due_date)->startOfDay();
                $diffDays = (int) round($today->diffInDays($dueDate, false)); // negative if past due

                $reminderType = null;
                if ($diffDays === 3) {
                    $reminderType = CustomerTermReminder::TYPE_UPCOMING_H3;
                } elseif ($diffDays === 0) {
                    $reminderType = CustomerTermReminder::TYPE_DUE_DATE;
                } elseif ($diffDays < 0) {
                    // Overdue intervals: 1st day overdue, 3rd day, 7th day, or every 7 days after
                    $daysOverdue = abs($diffDays);
                    if ($daysOverdue === 1 || $daysOverdue === 3 || $daysOverdue % 7 === 0) {
                        $reminderType = CustomerTermReminder::TYPE_OVERDUE;
                    }
                }

                if (! $reminderType) {
                    continue;
                }

                // Check daily deduplication anti-spam guard
                $alreadySent = CustomerTermReminder::where('business_id', $business->id)
                    ->where('invoice_id', $invoice->id)
                    ->where('reminder_type', $reminderType)
                    ->whereDate('sent_date', $today->toDateString())
                    ->exists();

                if ($alreadySent) {
                    $stats['skipped']++;
                    continue;
                }

                $result = $this->dispatchInvoiceReminder($invoice, $reminderType, CustomerTermReminder::CHANNEL_BOTH, null, $today);
                if ($result['success']) {
                    $stats['sent']++;
                } else {
                    $stats['failed']++;
                }

                $stats['details'][] = [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'reminder_type' => $reminderType,
                    'result' => $result,
                ];
            }
        }

        return $stats;
    }

    /**
     * Dispatch single invoice reminder via specified channel (whatsapp, email, or both).
     *
     * @return array{success: bool, wa_status: string, email_status: string, error?: string, wa_url?: string}
     */
    public function dispatchInvoiceReminder(
        Invoice $invoice,
        string $reminderType = CustomerTermReminder::TYPE_DUE_DATE,
        string $channel = CustomerTermReminder::CHANNEL_BOTH,
        ?string $customNotes = null,
        ?Carbon $date = null
    ): array {
        $customer = $invoice->customer;
        $business = $invoice->business ?? Business::find($invoice->business_id);

        if (! $customer || ! $business) {
            return [
                'success' => false,
                'wa_status' => CustomerTermReminder::STATUS_FAILED,
                'email_status' => CustomerTermReminder::STATUS_FAILED,
                'error' => 'Pelanggan atau profil bisnis tidak ditemukan.',
            ];
        }

        $today = ($date ?? Carbon::today())->startOfDay();
        $phone = $customer->phone;
        $email = $customer->email;

        $waStatus = CustomerTermReminder::STATUS_SKIPPED;
        $emailStatus = CustomerTermReminder::STATUS_SKIPPED;
        $errorMessage = null;

        $message = $this->buildWhatsAppMessage($invoice, $business, $customer, $reminderType, $customNotes);
        $waUrl = $phone ? $this->buildWhatsAppUrl($phone, $message) : null;

        // 1. Dispatch Email Channel
        if (in_array($channel, [CustomerTermReminder::CHANNEL_EMAIL, CustomerTermReminder::CHANNEL_BOTH], true)) {
            if ($email) {
                try {
                    Mail::to($email)->send(new CustomerPaymentTermReminderMail(
                        $invoice,
                        $customer,
                        $business,
                        $reminderType,
                        $customNotes
                    ));
                    $emailStatus = CustomerTermReminder::STATUS_SENT;
                } catch (\Throwable $e) {
                    $emailStatus = CustomerTermReminder::STATUS_FAILED;
                    $errorMessage = 'Email dispatch error: ' . $e->getMessage();
                    Log::error("CustomerPaymentTermReminder Email Error ({$invoice->invoice_number}): " . $e->getMessage());
                }
            } else {
                $emailStatus = CustomerTermReminder::STATUS_SKIPPED;
            }
        }

        // 2. Dispatch WhatsApp Channel
        if (in_array($channel, [CustomerTermReminder::CHANNEL_WHATSAPP, CustomerTermReminder::CHANNEL_BOTH], true)) {
            if ($phone) {
                try {
                    $waResult = $this->waGateway->sendMessage($business, $phone, $message);
                    if ($waResult['success'] ?? false) {
                        $waStatus = CustomerTermReminder::STATUS_SENT;
                    } else {
                        // Fallback to legacy/mock dispatcher or mark failed with direct WA url
                        $sentLegacy = $this->waLegacy->sendMessage($phone, $message);
                        if ($sentLegacy) {
                            $waStatus = CustomerTermReminder::STATUS_SENT;
                        } else {
                            $waStatus = CustomerTermReminder::STATUS_FAILED;
                            $errorMessage = ($errorMessage ? $errorMessage . ' | ' : '') . ($waResult['error'] ?? 'WhatsApp Gateway disconnected.');
                        }
                    }
                } catch (\Throwable $e) {
                    $waStatus = CustomerTermReminder::STATUS_FAILED;
                    $errorMessage = ($errorMessage ? $errorMessage . ' | ' : '') . 'WA error: ' . $e->getMessage();
                    Log::error("CustomerPaymentTermReminder WA Error ({$invoice->invoice_number}): " . $e->getMessage());
                }
            } else {
                $waStatus = CustomerTermReminder::STATUS_SKIPPED;
            }
        }

        $overallSuccess = ($emailStatus === CustomerTermReminder::STATUS_SENT || $waStatus === CustomerTermReminder::STATUS_SENT);

        // Record audit reminder log
        CustomerTermReminder::create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'reminder_type' => $reminderType,
            'channel' => $channel,
            'recipient_phone' => $phone,
            'recipient_email' => $email,
            'status' => $overallSuccess ? CustomerTermReminder::STATUS_SENT : CustomerTermReminder::STATUS_FAILED,
            'wa_status' => $waStatus,
            'email_status' => $emailStatus,
            'error_message' => $errorMessage,
            'sent_date' => $today->toDateString(),
            'sent_at' => now(),
        ]);

        return [
            'success' => $overallSuccess,
            'wa_status' => $waStatus,
            'email_status' => $emailStatus,
            'error' => $errorMessage,
            'wa_url' => $waUrl,
        ];
    }

    /**
     * Build structured, professional WhatsApp message text for the customer.
     */
    public function buildWhatsAppMessage(
        Invoice $invoice,
        Business $business,
        Customer $customer,
        string $reminderType,
        ?string $customNotes = null
    ): string {
        $balanceFormatted = 'Rp ' . number_format((float) $invoice->balance_due, 0, ',', '.');
        $totalFormatted = 'Rp ' . number_format((float) $invoice->total_amount, 0, ',', '.');
        $dueDateFormatted = $invoice->due_date ? Carbon::parse($invoice->due_date)->translatedFormat('d F Y') : '-';
        $invoiceUrl = route('invoices.show', $invoice->id);

        $greetingHeader = match ($reminderType) {
            CustomerTermReminder::TYPE_UPCOMING_H3 => "🔔 *Pengingat Jatuh Tempo Faktur (H-3)*",
            CustomerTermReminder::TYPE_DUE_DATE => "⚠️ *Pemberitahuan Jatuh Tempo Faktur Hari Ini*",
            CustomerTermReminder::TYPE_OVERDUE => "🚨 *Pemberitahuan Tagihan Faktur Melewati Jatuh Tempo*",
            default => "📄 *Rincian Tagihan Faktur*",
        };

        $msg = "{$greetingHeader}\n\n"
            . "Yth. *{$customer->name}*" . ($customer->company_name ? " ({$customer->company_name})" : '') . ",\n\n"
            . "Kami dari *{$business->name}* menginformasikan status tagihan faktur pembelian/layanan Anda:\n"
            . "• No. Faktur: *#{$invoice->invoice_number}*\n"
            . "• Termin Tempo: *" . ($invoice->payment_terms ?: "Net {$customer->payment_terms_days} Hari") . "*\n"
            . "• Tanggal Jatuh Tempo: *{$dueDateFormatted}*\n"
            . "• Total Faktur: *{$totalFormatted}*\n"
            . "• Sisa Tagihan Belum Lunas: *{$balanceFormatted}*\n\n";

        // Bank details snapshot
        $bankDetails = $invoice->bank_details_snapshot;
        if (! is_array($bankDetails)) {
            $bankDetails = json_decode($bankDetails ?: '[]', true) ?: [];
        }

        if (! empty($bankDetails)) {
            $msg .= "Pembayaran dapat ditransfer ke rekening resmi kami:\n";
            foreach ($bankDetails as $bank) {
                $bankName = $bank['bank_name'] ?? 'Bank';
                $accNo = $bank['account_number'] ?? '-';
                $accName = $bank['account_name'] ?? $business->name;
                $msg .= "👉 *{$bankName}*: `{$accNo}` a.n *{$accName}*\n";
            }
            $msg .= "\n";
        }

        if ($customNotes) {
            $msg .= "Catatan: _{$customNotes}_\n\n";
        }

        $msg .= "Lihat atau unduh salinan faktur resmi:\n{$invoiceUrl}\n\n"
            . "Terima kasih atas kerja sama dan kemitraan Anda.\n"
            . "_{$business->name}_";

        return $msg;
    }

    /**
     * Generate direct WhatsApp Web click-to-chat URL with pre-filled message text.
     */
    public function buildWhatsAppUrl(string $phone, string $message): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleaned, '0')) {
            $cleaned = '62' . substr($cleaned, 1);
        }

        return 'https://wa.me/' . $cleaned . '?text=' . rawurlencode($message);
    }
}
