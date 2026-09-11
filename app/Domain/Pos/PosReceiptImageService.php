<?php

declare(strict_types=1);

namespace App\Domain\Pos;

use App\Models\PosOrder;
use Illuminate\Support\Facades\File;

class PosReceiptImageService
{
    protected int $width = 560; // 80mm thermal mobile standard width

    /**
     * Generate PNG binary bytes for a PosOrder receipt.
     */
    public function generate(PosOrder $order): string
    {
        $order->loadMissing(['items', 'payments', 'customer', 'user', 'location', 'business']);
        $business = $order->business;

        // Collect lines to calculate dynamic height
        $receiptData = $this->buildReceiptStructure($order, $business);

        return $this->renderImage($receiptData);
    }

    /**
     * Generate and store receipt image into public storage directory.
     * Returns the relative storage path (e.g. 'receipts/receipt_{id}.png').
     */
    public function generateAndStore(PosOrder $order): string
    {
        $png = $this->generate($order);
        $directory = storage_path('app/public/receipts');

        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = "receipt_{$order->id}.png";
        $filePath = "{$directory}/{$filename}";
        File::put($filePath, $png);

        return "receipts/{$filename}";
    }

    /**
     * Build structured sections for the receipt.
     */
    protected function buildReceiptStructure(PosOrder $order, $business): array
    {
        $items = [];
        foreach ($order->items as $item) {
            $qty = rtrim(rtrim((string) $item->quantity, '0'), '.');
            $items[] = [
                'name' => $item->product_name,
                'detail' => "{$qty} x " . number_format((float) $item->unit_price, 0, ',', '.'),
                'total' => number_format((float) $item->total_price, 0, ',', '.'),
                'notes' => $item->notes ?? '',
            ];
        }

        $totals = [];
        $totals[] = ['label' => 'Subtotal', 'val' => number_format((float) $order->subtotal, 0, ',', '.')];

        $totalDiscount = (float) $order->discount_amount + (float) $order->voucher_discount_amount;
        if ($totalDiscount > 0) {
            $totals[] = ['label' => 'Diskon Promo', 'val' => '-' . number_format($totalDiscount, 0, ',', '.')];
        }

        if ((float) $order->points_discount_amount > 0) {
            $totals[] = ['label' => 'Tukar Poin', 'val' => '-' . number_format((float) $order->points_discount_amount, 0, ',', '.')];
        }

        if ((float) $order->tax_amount > 0) {
            $taxPct = $order->tax_percentage ?? 11;
            $totals[] = ['label' => "PPN ({$taxPct}%)", 'val' => number_format((float) $order->tax_amount, 0, ',', '.')];
        }

        if ((float) $order->service_charge_amount > 0) {
            $totals[] = ['label' => 'Service Charge', 'val' => number_format((float) $order->service_charge_amount, 0, ',', '.')];
        }

        if ((float) $order->rounding_amount != 0) {
            $totals[] = ['label' => 'Pembulatan', 'val' => number_format((float) $order->rounding_amount, 0, ',', '.')];
        }

        $payments = [];
        foreach ($order->payments as $payment) {
            $method = strtoupper((string) $payment->payment_method);
            $payments[] = [
                'label' => "Bayar ({$method})",
                'val' => 'Rp ' . number_format((float) $payment->amount, 0, ',', '.'),
            ];
        }

        return [
            'merchant_name' => strtoupper($business?->name ?? 'COOCA POS'),
            'location' => $order->location?->name ?? '',
            'address' => $business?->address ?? '',
            'phone' => $business?->phone ? 'Telp: ' . $business->phone : '',
            'order_number' => '#' . $order->order_number,
            'date' => $order->order_date ? $order->order_date->format('d/m/Y H:i') : now()->format('d/m/Y H:i'),
            'cashier' => $order->user?->name ?? 'Kasir',
            'customer' => $order->customer?->name ?? $order->customer_name_guest ?? null,
            'table_ref' => $order->table_or_reference ?? null,
            'order_type' => strtoupper((string) ($order->order_type ?? 'dine_in')),
            'items' => $items,
            'totals' => $totals,
            'grand_total' => 'Rp ' . number_format((float) $order->total_amount, 0, ',', '.'),
            'payments' => $payments,
            'change' => 'Rp ' . number_format((float) $order->change_amount, 0, ',', '.'),
            'points_earned' => (int) ($order->points_earned ?? 0),
            'customer_points' => $order->customer?->points_balance ?? null,
            'footer_note' => $business?->pos_receipt_footer_note ?? 'Terima Kasih Atas Kunjungan Anda!',
        ];
    }

    /**
     * Render the thermal receipt image using GD.
     */
    protected function renderImage(array $data): string
    {
        $fontFile = $this->resolveFontFile();
        $useTtf = $fontFile !== null;

        // Estimate canvas height dynamically
        $lineHeight = 24;
        $itemsCount = count($data['items']);
        $totalsCount = count($data['totals']);
        $paymentsCount = count($data['payments']);

        $estimatedHeight = 360 + ($itemsCount * 46) + ($totalsCount * 26) + ($paymentsCount * 26) + 140;
        $height = max(520, $estimatedHeight);

        $im = imagecreatetruecolor($this->width, $height);

        // Palette
        $bgPaper = imagecolorallocate($im, 255, 255, 255);
        $textDark = imagecolorallocate($im, 18, 18, 18);
        $textMuted = imagecolorallocate($im, 110, 110, 115);
        $lineColor = imagecolorallocate($im, 210, 210, 215);
        $badgeBg = imagecolorallocate($im, 240, 242, 245);
        $brandBlue = imagecolorallocate($im, 0, 122, 255);
        $brandGreen = imagecolorallocate($im, 40, 167, 69);

        imagefilledrectangle($im, 0, 0, $this->width, $height, $bgPaper);

        // Top decorative bar
        imagefilledrectangle($im, 0, 0, $this->width, 8, $brandBlue);

        $y = 42;
        $padding = 34;
        $contentWidth = $this->width - ($padding * 2);

        // 1. Merchant Header (Center)
        $this->drawText($im, $data['merchant_name'], $this->width / 2, $y, $textDark, 16, true, 'center', $fontFile);
        $y += 24;

        if (!empty($data['location'])) {
            $this->drawText($im, $data['location'], $this->width / 2, $y, $textMuted, 10, false, 'center', $fontFile);
            $y += 18;
        }

        if (!empty($data['address'])) {
            $this->drawText($im, $data['address'], $this->width / 2, $y, $textMuted, 9, false, 'center', $fontFile);
            $y += 16;
        }

        if (!empty($data['phone'])) {
            $this->drawText($im, $data['phone'], $this->width / 2, $y, $textMuted, 9, false, 'center', $fontFile);
            $y += 18;
        }

        $y += 8;
        $this->drawDashedLine($im, $padding, $y, $this->width - $padding, $y, $lineColor);
        $y += 22;

        // 2. Order Metadata
        $this->drawRow($im, 'No. Transaksi', $data['order_number'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, true, $fontFile);
        $y += 20;

        $this->drawRow($im, 'Tanggal', $data['date'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
        $y += 20;

        $this->drawRow($im, 'Kasir', $data['cashier'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
        $y += 20;

        if (!empty($data['customer'])) {
            $this->drawRow($im, 'Pelanggan', $data['customer'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
            $y += 20;
        }

        if (!empty($data['table_ref'])) {
            $this->drawRow($im, 'Meja / Ref', $data['table_ref'], $padding, $contentWidth, $y, $textMuted, $brandBlue, 10, true, $fontFile);
            $y += 20;
        }

        $this->drawRow($im, 'Tipe Pesanan', $data['order_type'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
        $y += 24;

        $this->drawDashedLine($im, $padding, $y, $this->width - $padding, $y, $lineColor);
        $y += 22;

        // 3. Items List
        foreach ($data['items'] as $item) {
            $this->drawText($im, $item['name'], $padding, $y, $textDark, 11, true, 'left', $fontFile);
            $y += 19;

            $this->drawRow($im, $item['detail'], $item['total'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
            $y += 18;

            if (!empty($item['notes'])) {
                $this->drawText($im, "* " . $item['notes'], $padding + 10, $y, $textMuted, 9, false, 'left', $fontFile);
                $y += 16;
            }
            $y += 6;
        }

        $this->drawDashedLine($im, $padding, $y, $this->width - $padding, $y, $lineColor);
        $y += 22;

        // 4. Financial Totals
        foreach ($data['totals'] as $row) {
            $this->drawRow($im, $row['label'], $row['val'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
            $y += 22;
        }

        $y += 4;
        $this->drawSolidLine($im, $padding, $y, $this->width - $padding, $y, $textDark, 2);
        $y += 24;

        // Grand Total (Big bold)
        $this->drawRow($im, 'TOTAL TAGIHAN', $data['grand_total'], $padding, $contentWidth, $y, $textDark, $brandBlue, 14, true, $fontFile);
        $y += 28;

        $this->drawDashedLine($im, $padding, $y, $this->width - $padding, $y, $lineColor);
        $y += 20;

        // 5. Payments & Change
        foreach ($data['payments'] as $pay) {
            $this->drawRow($im, $pay['label'], $pay['val'], $padding, $contentWidth, $y, $textMuted, $textDark, 10, false, $fontFile);
            $y += 20;
        }

        $this->drawRow($im, 'Kembalian', $data['change'], $padding, $contentWidth, $y, $brandGreen, $brandGreen, 11, true, $fontFile);
        $y += 26;

        // 6. Loyalty Points (if any)
        if ($data['points_earned'] > 0 || $data['customer_points'] !== null) {
            imagefilledrectangle($im, $padding, $y - 6, $this->width - $padding, $y + 36, $badgeBg);
            $y += 12;
            if ($data['points_earned'] > 0) {
                $this->drawText($im, "★ Poin Diperoleh: +{$data['points_earned']} Poin", $this->width / 2, $y, $brandBlue, 9, true, 'center', $fontFile);
                $y += 16;
            }
            if ($data['customer_points'] !== null) {
                $this->drawText($im, "Total Saldo Poin: {$data['customer_points']} Poin", $this->width / 2, $y, $textMuted, 9, false, 'center', $fontFile);
                $y += 18;
            }
            $y += 10;
        }

        // 7. Footer
        $y += 10;
        $this->drawText($im, $data['footer_note'], $this->width / 2, $y, $textDark, 10, true, 'center', $fontFile);
        $y += 18;
        $this->drawText($im, 'Struk Digital Resmi • POS Cooca (cooca.id)', $this->width / 2, $y, $textMuted, 8, false, 'center', $fontFile);
        $y += 26;

        // Crop image to actual drawn height
        $finalHeight = min($height, $y);
        $finalCanvas = imagecreatetruecolor($this->width, $finalHeight);
        imagecopy($finalCanvas, $im, 0, 0, 0, 0, $this->width, $finalHeight);
        imagedestroy($im);

        ob_start();
        imagepng($finalCanvas);
        $pngData = (string) ob_get_clean();
        imagedestroy($finalCanvas);

        return $pngData;
    }

    /**
     * Draw text with TrueType font or fallback to built-in GD font.
     */
    protected function drawText($im, string $text, float $x, float $y, int $color, int $size = 10, bool $bold = false, string $align = 'left', ?string $fontFile = null): void
    {
        if ($fontFile && function_exists('imagettftext')) {
            $bbox = imagettfbbox($size, 0, $fontFile, $text);
            $textWidth = abs($bbox[4] - $bbox[0]);

            $posX = $x;
            if ($align === 'center') {
                $posX = $x - ($textWidth / 2);
            } elseif ($align === 'right') {
                $posX = $x - $textWidth;
            }

            imagettftext($im, $size, 0, (int) $posX, (int) $y, $color, $fontFile, $text);
            if ($bold) {
                imagettftext($im, $size, 0, (int) ($posX + 1), (int) $y, $color, $fontFile, $text);
            }
        } else {
            // Built-in font fallback (1 to 5)
            $gdFont = $size >= 14 ? 5 : ($size >= 11 ? 4 : ($size >= 9 ? 3 : 2));
            $charWidth = imagefontwidth($gdFont);
            $textWidth = strlen($text) * $charWidth;

            $posX = $x;
            if ($align === 'center') {
                $posX = $x - ($textWidth / 2);
            } elseif ($align === 'right') {
                $posX = $x - $textWidth;
            }

            imagestring($im, $gdFont, (int) $posX, (int) ($y - 12), $text, $color);
            if ($bold) {
                imagestring($im, $gdFont, (int) ($posX + 1), (int) ($y - 12), $text, $color);
            }
        }
    }

    /**
     * Draw a 2-column key-value row (Left text and Right value).
     */
    protected function drawRow($im, string $label, string $val, int $startX, int $width, int $y, int $labelColor, int $valColor, int $size = 10, bool $bold = false, ?string $fontFile = null): void
    {
        $this->drawText($im, $label, $startX, $y, $labelColor, $size, $bold, 'left', $fontFile);
        $this->drawText($im, $val, $startX + $width, $y, $valColor, $size, $bold, 'right', $fontFile);
    }

    /**
     * Draw a dashed line across the receipt.
     */
    protected function drawDashedLine($im, int $x1, int $y1, int $x2, int $y2, int $color): void
    {
        $style = [$color, $color, $color, $color, IMG_COLOR_TRANSPARENT, IMG_COLOR_TRANSPARENT];
        imagesetstyle($im, $style);
        imageline($im, $x1, $y1, $x2, $y2, IMG_COLOR_STYLED);
    }

    /**
     * Draw a solid separator line.
     */
    protected function drawSolidLine($im, int $x1, int $y1, int $x2, int $y2, int $color, int $thickness = 1): void
    {
        imagesetthickness($im, $thickness);
        imageline($im, $x1, $y1, $x2, $y2, $color);
        imagesetthickness($im, 1);
    }

    /**
     * Locate a clean monospace or sans-serif TTF font on the host.
     */
    protected function resolveFontFile(): ?string
    {
        $candidates = [
            'C:/Windows/Fonts/consola.ttf',
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/calibri.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $path) {
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
