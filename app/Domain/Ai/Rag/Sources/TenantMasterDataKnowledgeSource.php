<?php

declare(strict_types=1);

namespace App\Domain\Ai\Rag\Sources;

use App\Domain\Ai\Rag\Contracts\KnowledgeSourceInterface;
use App\Models\Business;
use App\Models\Customer;
use App\Models\PaymentAccount;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Str;

final class TenantMasterDataKnowledgeSource implements KnowledgeSourceInterface
{
    public function getSourceId(): string
    {
        return 'tenant_master_data';
    }

    public function getLabel(): string
    {
        return 'Master Data Bisnis (Produk, Pemasok, Pelanggan & Akun)';
    }

    public function retrieve(Business $business, ?User $user, string $query, array $options = []): array
    {
        $limit = $options['limit'] ?? 8;
        $chunks = [];
        $lowerQuery = mb_strtolower($query);
        $keywords = array_filter(explode(' ', $lowerQuery), fn($k) => mb_strlen($k) >= 3);

        // 1. Products / Items Search
        $productQuery = Product::where('business_id', $business->id)
            ->where('is_active', true)
            ->with(['stocks', 'outputUnit']);

        if (! empty($keywords)) {
            $productQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%{$kw}%")
                      ->orWhere('code', 'like', "%{$kw}%")
                      ->orWhere('description', 'like', "%{$kw}%");
                }
            });
        }

        $products = $productQuery->take($limit)->get();
        foreach ($products as $p) {
            $sku = $p->code ?? $p->sku ?? '';
            $price = $p->selling_price ?? $p->price ?? 0;
            $cost = $p->base_cost ?? $p->hpp_cost ?? 0;
            $priceFormatted = 'Rp ' . number_format((float) $price, 0, ',', '.');
            $costFormatted = 'Rp ' . number_format((float) $cost, 0, ',', '.');
            $stock = $p->stocks ? (float) $p->stocks->sum('quantity') : (float) ($p->stock_quantity ?? 0);
            $minAlert = (float) ($p->min_stock ?? $p->min_stock_alert ?? 5);
            $status = $stock <= $minAlert ? 'KRITIS/MENIPIS' : 'NORMAL';
            $unit = $p->outputUnit?->code ?? $p->unit_code ?? 'Pcs';

            $score = 0.75;
            if (mb_stripos($lowerQuery, mb_strtolower($p->name)) !== false) {
                $score = 0.95;
            } elseif (! empty($sku) && mb_stripos($lowerQuery, mb_strtolower($sku)) !== false) {
                $score = 0.98;
            }

            $chunks[] = [
                'content' => "Produk: {$p->name} (SKU: {$sku}) | Harga Jual: {$priceFormatted} | HPP: {$costFormatted} | Sisa Stok: {$stock} {$unit} | Status Stok: {$status}",
                'citation' => "[Katalog Produk: #{$sku} - {$p->name}]",
                'score' => $score,
                'metadata' => [
                    'entity' => 'product',
                    'id' => $p->id,
                    'sku' => $sku,
                    'stock' => $stock,
                ],
            ];
        }

        // 2. Suppliers Search
        $supplierQuery = Supplier::where('business_id', $business->id);
        if (! empty($keywords)) {
            $supplierQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%{$kw}%")
                      ->orWhere('contact_person', 'like', "%{$kw}%")
                      ->orWhere('phone', 'like', "%{$kw}%");
                }
            });
        }

        $suppliers = $supplierQuery->take(5)->get();
        foreach ($suppliers as $s) {
            $score = 0.70;
            if (mb_stripos($lowerQuery, mb_strtolower($s->name)) !== false) {
                $score = 0.95;
            }

            $chunks[] = [
                'content' => "Pemasok / Supplier: {$s->name} | Kontak: {$s->contact_person} ({$s->phone}) | Email: {$s->email} | Alamat: {$s->address}",
                'citation' => "[Direktori Supplier: {$s->name}]",
                'score' => $score,
                'metadata' => [
                    'entity' => 'supplier',
                    'id' => $s->id,
                ],
            ];
        }

        // 3. Customers Search
        $customerQuery = Customer::where('business_id', $business->id);
        if (! empty($keywords)) {
            $customerQuery->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%{$kw}%")
                      ->orWhere('phone', 'like', "%{$kw}%")
                      ->orWhere('email', 'like', "%{$kw}%");
                }
            });
        }

        $customers = $customerQuery->take(5)->get();
        foreach ($customers as $c) {
            $score = 0.65;
            if (mb_stripos($lowerQuery, mb_strtolower($c->name)) !== false) {
                $score = 0.95;
            }

            $spent = 'Rp ' . number_format((float) ($c->total_spend ?? 0), 0, ',', '.');
            $points = number_format((int) ($c->loyalty_points ?? 0), 0, ',', '.');

            $chunks[] = [
                'content' => "Pelanggan: {$c->name} | Phone/WA: {$c->phone} | Total Belanja: {$spent} | Poin Loyalitas: {$points} | Kategori Member: {$c->customer_group}",
                'citation' => "[Profil Pelanggan: {$c->name}]",
                'score' => $score,
                'metadata' => [
                    'entity' => 'customer',
                    'id' => $c->id,
                ],
            ];
        }

        // 4. Payment Accounts
        if (Str::contains($lowerQuery, ['kas', 'bank', 'rekening', 'saldo', 'pembayaran', 'uang'])) {
            $accounts = PaymentAccount::where('business_id', $business->id)->where('is_active', true)->get();
            foreach ($accounts as $acc) {
                $balance = 'Rp ' . number_format((float) ($acc->current_balance ?? 0), 0, ',', '.');
                $chunks[] = [
                    'content' => "Rekening Kas & Bank: {$acc->account_name} ({$acc->bank_name} - {$acc->account_number}) | Saldo Berjalan: {$balance}",
                    'citation' => "[Rekening Pembayaran: {$acc->account_name}]",
                    'score' => 0.88,
                    'metadata' => [
                        'entity' => 'payment_account',
                        'id' => $acc->id,
                    ],
                ];
            }
        }

        return $chunks;
    }
}
