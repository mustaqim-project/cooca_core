<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

final class DraftInvoiceProposalTool extends BaseAiTool
{
    public function getName(): string
    {
        return 'DraftInvoiceProposal';
    }

    public function getDescription(): string
    {
        return 'Menyusun usulan penerbitan draf faktur penjualan (invoice) resmi untuk pelanggan tertentu.';
    }

    public function getCategory(): string
    {
        return 'WRITE';
    }

    public function getRiskLevel(): string
    {
        return 'HIGH';
    }

    public function requiresHumanApproval(): bool
    {
        return true;
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'customer_id' => ['type' => 'string', 'description' => 'UUID Pelanggan'],
                'product_id' => ['type' => 'string', 'description' => 'UUID Produk'],
                'quantity' => ['type' => 'number', 'description' => 'Kuantitas barang'],
                'unit_price' => ['type' => 'number', 'description' => 'Harga satuan custom (opsional)'],
            ],
            'required' => ['product_id'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $productId = (string) ($arguments['product_id'] ?? '');
        $product = Product::where('business_id', $business->id)->where('id', $productId)->first()
            ?? Product::where('business_id', $business->id)->where('is_active', true)->first();

        if (! $product) {
            return [
                'success' => false,
                'message' => 'Tidak ditemukan produk aktif untuk penerbitan faktur.',
            ];
        }

        $customerId = (string) ($arguments['customer_id'] ?? '');
        $customer = Customer::where('business_id', $business->id)->where('id', $customerId)->first()
            ?? Customer::where('business_id', $business->id)->first();

        $custName = $customer ? $customer->name : 'Pelanggan Walk-In';
        $qty = max(1, (float) ($arguments['quantity'] ?? 1));
        $price = isset($arguments['unit_price']) && is_numeric($arguments['unit_price'])
            ? (float) $arguments['unit_price']
            : (float) $product->selling_price;

        $total = $qty * $price;

        return [
            'action_type' => 'create_invoice',
            'title' => "Terbitkan Faktur Penjualan: {$custName}",
            'description' => "Penerbitan faktur untuk {$qty}x {$product->name} dengan total nilai Rp " . number_format($total, 0, ',', '.'),
            'reason' => 'Kebutuhan transaksi penjualan baru yang disiapkan oleh AI Sales Agent.',
            'estimated_cost' => $total,
            'payload' => [
                'customer_id' => $customer?->id,
                'customer_name' => $custName,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $qty,
                'unit_price' => $price,
                'total_amount' => $total,
            ],
        ];
    }
}
