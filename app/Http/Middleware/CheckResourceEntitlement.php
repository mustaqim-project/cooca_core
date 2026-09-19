<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domain\Billing\EntitlementService;
use App\Support\Context;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckResourceEntitlement
{
    public function __construct(
        private readonly EntitlementService $entitlementService = new EntitlementService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $resourceType  'product' | 'recipe' | 'invoice' | 'ai'
     */
    public function handle(Request $request, Closure $next, string $resourceType): Response
    {
        $business = Context::business();
        if (!$business) {
            return $next($request);
        }

        $canProceed = match ($resourceType) {
            'product' => $this->entitlementService->canCreateProduct($business),
            'recipe' => $this->entitlementService->canCreateRecipe($business),
            'invoice' => $this->entitlementService->canCreateInvoiceThisMonth($business),
            'ai' => $this->entitlementService->canAccessAi($business),
            'material' => $this->entitlementService->canCreateMaterial($business),
            'customer' => $this->entitlementService->canCreateCustomer($business),
            'supplier' => $this->entitlementService->canCreateSupplier($business),
            'outlet' => $this->entitlementService->canCreateLocation($business, 'outlet'),
            'warehouse' => $this->entitlementService->canCreateLocation($business, 'warehouse'),
            'purchase_order', 'po' => $this->entitlementService->canCreatePurchaseOrderThisMonth($business),
            'pos' => $this->entitlementService->canCreatePosTransactionThisMonth($business),
            'table' => $this->entitlementService->canCreateTable($business),
            'transfer_stock' => $this->entitlementService->canTransferStock($business),
            'kds' => $business->subscription?->hasTier(\App\Models\BusinessSubscription::TIER_PREMIUM) ?? false,
            'branch_pricing' => $this->entitlementService->canSetBranchPrices($business),
            'commission' => $this->entitlementService->canCalculateCommissions($business),
            'loan' => $this->entitlementService->canManageEmployeeLoans($business),
            'daily_worker' => $this->entitlementService->canManageDailyWorkers($business),
            'bpjs' => $this->entitlementService->canCalculateBPJS($business),
            'thr' => $this->entitlementService->canCalculateTHR($business),
            'pph21' => $this->entitlementService->canCalculatePPh21($business),
            'social_post' => $this->entitlementService->canScheduleSocialPostThisMonth($business),
            'whatsapp' => $this->entitlementService->canSendWhatsAppThisMonth($business),
            'import' => $this->entitlementService->canImportData($business),
            'export' => $this->entitlementService->canExportData($business),
            'member', 'user' => $this->entitlementService->canAddMember($business),
            default => true,
        };

        if (!$canProceed) {
            $labels = [
                'product' => 'Katalog Produk',
                'recipe' => 'Resep HPP / BOM (Fitur Paket Premium)',
                'material' => 'Bahan Baku',
                'customer' => 'Pelanggan / CRM',
                'supplier' => 'Pemasok / Supplier',
                'outlet' => 'Outlet / Cabang',
                'warehouse' => 'Gudang / Central Kitchen',
                'invoice' => 'Faktur Penjualan Bulanan',
                'purchase_order' => 'Purchase Order Bulanan',
                'po' => 'Purchase Order Bulanan',
                'pos' => 'Transaksi POS Kasir Bulanan',
                'table' => 'Meja Kasir Dine-in',
                'transfer_stock' => 'Transfer Stok Antar-Cabang (Fitur Paket Premium)',
                'kds' => 'Kitchen Display System (KDS) (Fitur Paket Premium)',
                'branch_pricing' => 'Multi-Harga per Cabang (Fitur Paket Premium)',
                'social_post' => 'Jadwal Postingan Media Sosial Bulanan',
                'whatsapp' => 'Pesan WhatsApp Gateway Bulanan',
                'ai' => 'Fitur Asisten & Prediksi AI',
                'import' => 'Fitur Import Data Excel/CSV',
                'export' => 'Fitur Export Data Excel/CSV',
                'member' => 'Tambah Karyawan / Pengguna',
                'user' => 'Tambah Karyawan / Pengguna',
                'commission' => 'Komisi Karyawan (Fitur Paket Premium)',
                'loan' => 'Kasbon & Pinjaman Karyawan (Fitur Paket Premium)',
                'daily_worker' => 'Manajemen Pekerja Harian Lepas (Fitur Paket Premium)',
                'bpjs' => 'Kalkulasi BPJS Ketenagakerjaan & Kesehatan (Fitur Paket Premium)',
                'thr' => 'Kalkulasi THR Prorata (Fitur Paket Premium)',
                'pph21' => 'Kalkulasi PPh 21 TER PP 58/2023 (Fitur Paket Prestige)',
            ];
            $label = $labels[$resourceType] ?? $resourceType;

            $upgradeFee = match ($resourceType) {
                'pph21', 'payroll_wa' => 'Prestige (Rp199.000/bln)',
                'recipe', 'transfer_stock', 'kds', 'branch_pricing', 'commission', 'loan', 'daily_worker', 'bpjs', 'thr', 'outlet', 'warehouse' => 'Premium (Rp89.000/bln)',
                'pos', 'table', 'invoice', 'purchase_order', 'po', 'product', 'material', 'customer', 'supplier', 'member', 'user' => 'Standard (Rp29.000/bln) atau Premium (Rp89.000/bln)',
                default => 'paket yang lebih tinggi',
            };

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'RESOURCE_LIMIT_EXCEEDED',
                    'message' => "Batas kuota {$label} telah tercapai. Tingkatkan paket ke {$upgradeFee} untuk akses tanpa batas. Data lama Anda tetap aman (No Data Punishment).",
                    'upgrade_url' => route('billing.limits'),
                ], 403);
            }

            return redirect()->route('billing.limits')
                ->with('error', "Batas kuota {$label} telah tercapai. Data lama Anda tetap aman (No Data Punishment). Silakan tingkatkan paket Anda ke {$upgradeFee} untuk menambah data baru.");
        }

        return $next($request);
    }
}
