<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi\Templates;

use App\Models\PosOrder;

/**
 * Class PosReceiptTemplate
 *
 * Mengonstruksi parameter template resmi WhatsApp Meta untuk Struk Kasir POS.
 *
 * Contoh Teks Template di Meta Business Manager:
 * Header: [Media Gambar Struk]
 * Body:
 * Halo *{{1}}*! Terima kasih telah berbelanja di *{{5}}*.
 * Rincian transaksi Anda:
 * • No. Pesanan: *{{2}}*
 * • Tanggal: *{{4}}*
 * • Total Bayar: *{{3}}*
 *
 * Tombol URL: "Buka Struk Digital" -> https://umkm.cooca.id/r/{{1}}
 */
class PosReceiptTemplate
{
    public const DEFAULT_TEMPLATE_NAME = 'cooca_pos_receipt';

    public static function build(
        PosOrder $order,
        ?string $receiptImageUrl = null,
        string $templateName = self::DEFAULT_TEMPLATE_NAME,
        string $languageCode = 'id'
    ): WhatsAppTemplateBuilder {
        $business     = $order->business;
        $customerName = $order->customer?->name ?? $order->customer_name_guest ?? 'Pelanggan';
        $orderNumber  = (string) $order->order_number;
        $totalAmount  = (float) ($order->final_amount ?? $order->total_amount ?? 0);
        $orderDate    = $order->order_date ?? $order->created_at ?? now();
        $storeName    = (string) ($business?->name ?? 'Toko Kami');

        $builder = WhatsAppTemplateBuilder::make($templateName, $languageCode);

        // Header Gambar Struk Digital (jika ada)
        if (! empty($receiptImageUrl)) {
            $builder->setHeaderImage($receiptImageUrl);
        }

        // Parameter Body: {{1}} Nama, {{2}} Nomor Nota, {{3}} Total Bayar, {{4}} Waktu, {{5}} Nama Toko
        $builder->addBodyText($customerName)
            ->addBodyText($orderNumber)
            ->addBodyCurrency($totalAmount, $business?->currency ?? 'IDR')
            ->addBodyDateTime($orderDate, 'd/m/Y H:i')
            ->addBodyText($storeName);

        // Tombol URL menuju Struk Digital Publik COOCA
        $builder->addButtonUrl(0, (string) $order->id);

        return $builder;
    }
}
