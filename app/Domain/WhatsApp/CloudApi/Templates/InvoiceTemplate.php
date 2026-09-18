<?php

declare(strict_types=1);

namespace App\Domain\WhatsApp\CloudApi\Templates;

use App\Models\Business;

/**
 * Class InvoiceTemplate
 *
 * Mengonstruksi parameter template resmi WhatsApp Meta untuk Faktur / Invoice Tagihan.
 *
 * Contoh Teks Template di Meta Business Manager:
 * Header: [Media Dokumen PDF Invoice]
 * Body:
 * Halo *{{1}}*, berikut faktur tagihan dari *{{5}}*:
 * • No. Invoice: *{{2}}*
 * • Jatuh Tempo: *{{3}}*
 * • Total Tagihan: *{{4}}*
 *
 * Tombol URL: "Lihat / Bayar Invoice" -> https://cooca.id/inv/{{1}}
 */
class InvoiceTemplate
{
    public const DEFAULT_TEMPLATE_NAME = 'cooca_sales_invoice';

    public static function build(
        string $customerName,
        string $invoiceNumber,
        \DateTimeInterface $dueDate,
        float|int $totalAmount,
        string $storeName,
        ?string $invoicePdfUrl = null,
        ?string $invoiceUrlSuffix = null,
        string $currency = 'IDR',
        string $templateName = self::DEFAULT_TEMPLATE_NAME,
        string $languageCode = 'id'
    ): WhatsAppTemplateBuilder {
        $builder = WhatsAppTemplateBuilder::make($templateName, $languageCode);

        // Header Dokumen PDF (jika tersedia link publik)
        if (! empty($invoicePdfUrl)) {
            $builder->setHeaderDocument($invoicePdfUrl, "invoice-{$invoiceNumber}.pdf");
        }

        // Parameter Body: {{1}} Customer, {{2}} Invoice No, {{3}} Due Date, {{4}} Total, {{5}} Store Name
        $builder->addBodyText($customerName)
            ->addBodyText($invoiceNumber)
            ->addBodyDateTime($dueDate, 'd/m/Y')
            ->addBodyCurrency($totalAmount, $currency)
            ->addBodyText($storeName);

        // Tombol URL jika ada suffix invoice
        if (! empty($invoiceUrlSuffix)) {
            $builder->addButtonUrl(0, $invoiceUrlSuffix);
        }

        return $builder;
    }
}
