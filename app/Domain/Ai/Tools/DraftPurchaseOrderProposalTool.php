<?php

declare(strict_types=1);

namespace App\Domain\Ai\Tools;

use App\Models\Business;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

final class DraftPurchaseOrderProposalTool extends BaseAiTool
{
    public function getName(): string
    {
        return 'DraftPurchaseOrderProposal';
    }

    public function getDescription(): string
    {
        return 'Menyusun usulan penerbitan draf Purchase Order (PO) pengadaan bahan/stok dari supplier berdasarkan rekomendasi AI Purchasing Agent.';
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

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $supplier = Supplier::where('business_id', $business->id)->first();
        $supplierName = $supplier ? $supplier->name : 'Supplier Rekanan';

        $product = Product::where('business_id', $business->id)->where('is_active', true)->first();
        $prodName = $product ? $product->name : 'Item Restock';
        $qty = max(1, (float) ($arguments['quantity'] ?? 20));
        $estUnitCost = $product ? (float) $product->base_cost : 15000;
        $totalCost = $qty * $estUnitCost;

        return [
            'action_type' => 'create_purchase_order',
            'title' => "Buat Purchase Order ke {$supplierName}",
            'description' => "Pengadaan restock untuk {$qty}x {$prodName} senilai estimasi Rp " . number_format($totalCost, 0, ',', '.'),
            'reason' => 'Proyeksi stok menipis dan mendekati batas Reorder Point (ROP).',
            'estimated_cost' => $totalCost,
            'payload' => [
                'supplier_id' => $supplier?->id,
                'supplier_name' => $supplierName,
                'items' => [
                    [
                        'product_id' => $product?->id,
                        'product_name' => $prodName,
                        'quantity' => $qty,
                        'unit_cost' => $estUnitCost,
                        'subtotal' => $totalCost,
                    ],
                ],
                'total_amount' => $totalCost,
            ],
        ];
    }
}
