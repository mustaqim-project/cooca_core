<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi\Templates;

use App\Models\PosOrder;

/**
 * Class PosReceiptTemplate
 *
 * Mengonstruksi parameter template resmi WhatsApp Meta untuk Struk Kasir POS.
 * Template standar resmi Meta ('cooca_pos_receipt') menggunakan Header Teks statis
 * (tanpa lampiran media gambar di chat untuk menghemat kuota/biaya dan kecepatan kirim),
 * dan menyertakan tombol aksi URL interaktif "Buka Struk Digital" menuju halaman e-struk.
 *
 * Struktur Template di Meta Business Manager:
 * Header: Struk Pembelian Kasir (Format: TEXT)
 * Body:
 * Halo *{{1}}*! Terima kasih telah berbelanja di *{{2}}*.
 * Rincian transaksi Anda:
 * • No. Struk: *{{3}}*
 * • Waktu: *{{4}}*
 * • Total Bayar: *{{5}}*
 *
 * Struk digital transaksi Anda tersimpan aman di sistem kami.
 * Footer: Layanan Kasir Resmi Cooca POS
 * Tombol URL: "Buka Struk Digital" -> https://cooca.id/r/{{1}}
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

        // Header: Template resmi cooca_pos_receipt menggunakan header teks statis Meta,
        // tidak melampirkan media gambar di dalam chat demi efisiensi biaya (kategori Utility Teks).
        // Gambar struk digital diakses pelanggan melalui tombol URL "Buka Struk Digital".
        if (! empty($receiptImageUrl) && $templateName !== self::DEFAULT_TEMPLATE_NAME) {
            $builder->setHeaderImage($receiptImageUrl);
        }

        // Parameter Body:
        // {{1}} Nama Pelanggan, {{2}} Nama Toko, {{3}} Nomor Struk, {{4}} Waktu Transaksi, {{5}} Total Bayar
        if ($templateName === self::DEFAULT_TEMPLATE_NAME) {
            $builder->addBodyText($customerName)
                ->addBodyText($storeName)
                ->addBodyText($orderNumber)
                ->addBodyDateTime($orderDate, 'd/m/Y H:i')
                ->addBodyCurrency($totalAmount, $business?->currency ?? 'IDR');
        } else {
            $builder->addBodyText($customerName)
                ->addBodyText($orderNumber)
                ->addBodyCurrency($totalAmount, $business?->currency ?? 'IDR')
                ->addBodyDateTime($orderDate, 'd/m/Y H:i')
                ->addBodyText($storeName);
        }

        // Tombol URL menuju Struk Digital Publik COOCA (membuka halaman dengan gambar struk lengkap)
        $builder->addButtonUrl(0, (string) ($order->order_number ?: $order->id));

        return $builder;
    }
}
