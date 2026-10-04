<?php

declare(strict_types=1);

namespace App\Domain\Ai\Rag\Sources;

use App\Domain\Ai\Rag\Contracts\KnowledgeSourceInterface;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashTransaction;
use App\Models\Invoice;
use App\Models\PosOrder;
use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

final class TenantOperationalHistoryKnowledgeSource implements KnowledgeSourceInterface
{
    public function getSourceId(): string
    {
        return 'tenant_operational_history';
    }

    public function getLabel(): string
    {
        return 'Riwayat Transaksi & Operasional (Penjualan, Faktur, PO, Kas & Audit)';
    }

    public function retrieve(Business $business, ?User $user, string $query, array $options = []): array
    {
        $limit = $options['limit'] ?? 8;
        $chunks = [];
        $lowerQuery = mb_strtolower($query);

        // 1. Invoices / Piutang
        if (Str::contains($lowerQuery, ['faktur', 'invoice', 'piutang', 'tagihan', 'tempo', 'jatuh tempo', 'unpaid'])) {
            $invoices = Invoice::where('business_id', $business->id)
                ->latest()
                ->take($limit)
                ->get();

            foreach ($invoices as $inv) {
                $total = 'Rp ' . number_format((float) $inv->total_amount, 0, ',', '.');
                $due = $inv->due_date ? Carbon::parse($inv->due_date)->format('d M Y') : 'N/A';
                $customerName = $inv->customer?->name ?? 'Pelanggan Umum';

                $chunks[] = [
                    'content' => "Faktur Tagihan: #{$inv->invoice_number} | Klien: {$customerName} | Total: {$total} | Status: {$inv->status} | Jatuh Tempo: {$due}",
                    'citation' => "[Faktur Tagihan #{$inv->invoice_number}]",
                    'score' => 0.90,
                    'metadata' => [
                        'entity' => 'invoice',
                        'id' => $inv->id,
                        'status' => $inv->status,
                    ],
                ];
            }
        }

        // 2. Recent POS Orders / Penjualan Terkini
        if (Str::contains($lowerQuery, ['penjualan', 'omzet', 'transaksi', 'kasir', 'pos', 'struk', 'order', 'hari ini'])) {
            $recentOrders = PosOrder::where('business_id', $business->id)
                ->where('status', PosOrder::STATUS_COMPLETED)
                ->latest()
                ->take($limit)
                ->get();

            foreach ($recentOrders as $ord) {
                $total = 'Rp ' . number_format((float) $ord->total_amount, 0, ',', '.');
                $profit = 'Rp ' . number_format((float) $ord->total_gross_profit, 0, ',', '.');
                $date = Carbon::parse($ord->order_date ?? $ord->created_at)->format('d M Y H:i');
                $cust = $ord->customer_name ?? 'Pelanggan Langsung';

                $chunks[] = [
                    'content' => "Pesanan POS: #{$ord->order_number} | Waktu: {$date} | Pelanggan: {$cust} | Nominal: {$total} | Laba Kotor: {$profit} | Metode: {$ord->payment_method}",
                    'citation' => "[Transaksi POS #{$ord->order_number}]",
                    'score' => 0.88,
                    'metadata' => [
                        'entity' => 'pos_order',
                        'id' => $ord->id,
                    ],
                ];
            }
        }

        // 3. Purchase Orders / Pengadaan Barang
        if (Str::contains($lowerQuery, ['po', 'purchase', 'pembelian', 'pengadaan', 'supplier', 'pesan barang'])) {
            $pos = PurchaseOrder::where('business_id', $business->id)
                ->latest()
                ->take($limit)
                ->get();

            foreach ($pos as $po) {
                $total = 'Rp ' . number_format((float) $po->total_amount, 0, ',', '.');
                $supp = $po->supplier?->name ?? 'Supplier';
                $date = Carbon::parse($po->order_date ?? $po->created_at)->format('d M Y');

                $chunks[] = [
                    'content' => "Purchase Order (PO): #{$po->po_number} | Supplier: {$supp} | Tanggal: {$date} | Nilai PO: {$total} | Status: {$po->status}",
                    'citation' => "[Purchase Order #{$po->po_number}]",
                    'score' => 0.89,
                    'metadata' => [
                        'entity' => 'purchase_order',
                        'id' => $po->id,
                        'status' => $po->status,
                    ],
                ];
            }
        }

        // 4. Cash Transactions / Arus Kas
        if (Str::contains($lowerQuery, ['kas', 'pengeluaran', 'pemasukan', 'biaya', 'operasional', 'arus kas', 'cashflow'])) {
            $cashTx = CashTransaction::where('business_id', $business->id)
                ->latest()
                ->take($limit)
                ->get();

            foreach ($cashTx as $tx) {
                $amount = 'Rp ' . number_format((float) $tx->amount, 0, ',', '.');
                $type = strtoupper($tx->type ?? 'MUTASI');
                $chunks[] = [
                    'content' => "Mutasi Kas: {$type} sejumlah {$amount} | Kategori: {$tx->category} | Deskripsi: {$tx->description} | Tanggal: {$tx->transaction_date}",
                    'citation' => "[Mutasi Kas: {$tx->transaction_number}]",
                    'score' => 0.87,
                    'metadata' => [
                        'entity' => 'cash_transaction',
                        'id' => $tx->id,
                        'type' => $type,
                    ],
                ];
            }
        }

        // 5. Critical Audit Logs / Anomali Keamanan
        if (Str::contains($lowerQuery, ['anomali', 'fraud', 'keamanan', 'void', 'refund', 'selisih', 'curiga', 'audit'])) {
            $logs = AuditLog::where('business_id', $business->id)
                ->whereIn('risk_level', ['HIGH', 'CRITICAL'])
                ->latest()
                ->take(5)
                ->get();

            foreach ($logs as $log) {
                $time = Carbon::parse($log->created_at)->diffForHumans();
                $chunks[] = [
                    'content' => "Alert Keamanan & Audit: [Risk: {$log->risk_level}] Aksi: {$log->action} oleh User ID {$log->user_id} ({$time}) | Catatan: {$log->notes}",
                    'citation' => "[Audit Log #{$log->id}]",
                    'score' => 0.95,
                    'metadata' => [
                        'entity' => 'audit_log',
                        'risk' => $log->risk_level,
                    ],
                ];
            }
        }

        return $chunks;
    }
}
