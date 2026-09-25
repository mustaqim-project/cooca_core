<?php

declare(strict_types=1);

namespace App\Domain\Printer;

use App\Models\PosOrder;
use App\Models\PosPrinter;
use App\Models\PosShift;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Mike42\Escpos\Printer;
use Throwable;

class EscposFormatter
{
    /**
     * Format a complete POS Order receipt into raw ESC/POS binary stream.
     *
     * @param array{
     *     open_drawer?: bool,
     *     is_reprint?: bool,
     *     reprint_count?: int
     * } $options
     */
    public function formatReceipt(PosOrder $order, PosPrinter $printerModel, array $options = []): string
    {
        $order->loadMissing(['items.modifiers', 'payments', 'customer', 'user', 'location', 'business', 'technician']);
        $business = $order->business;

        $connector = new DummyPrintConnector();
        $profile = CapabilityProfile::load("default");
        $printer = new Printer($connector, $profile);

        $maxCols = $printerModel->getMaxColumns();
        $is58mm = $printerModel->paper_width === '58mm';

        try {
            // 1. Initialize
            $printer->initialize();

            // 2. Pulse Cash Drawer if requested and supported
            if (!empty($options['open_drawer']) && $printerModel->hasCapability(PosPrinter::CAP_CASH_DRAWER)) {
                $printer->pulse(0, 25, 250);
            }

            // 3. Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_EMPHASIZED);
            $printer->text(strtoupper($business->name ?? 'COOCA POS') . "\n");
            $printer->selectPrintMode();

            if ($order->location) {
                $printer->text($order->location->name . "\n");
            }
            if (!empty($business->address)) {
                $printer->text($this->wordWrapCenter($business->address, $maxCols) . "\n");
            }
            if (!empty($business->phone)) {
                $printer->text("Telp: " . $business->phone . "\n");
            }

            $printer->text($this->separator($maxCols, '=') . "\n");

            // 4. Reprint Notice
            $reprintCount = $options['reprint_count'] ?? (int) $order->print_count;
            if (!empty($options['is_reprint']) || $reprintCount > 1) {
                $printer->setEmphasis(true);
                $printer->text("*** SALINAN (CETAKAN KE-{$reprintCount}) ***\n");
                $printer->setEmphasis(false);
                $printer->text($this->separator($maxCols, '-') . "\n");
            }

            // 5. Order Meta
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($this->formatTwoColumn("No. Order", "#" . $order->order_number, $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Tanggal", $order->order_date ? $order->order_date->format('d/m/Y H:i') : now()->format('d/m/Y H:i'), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Kasir", $order->user?->name ?? 'Kasir', $maxCols) . "\n");

            if ($order->customer) {
                $tier = strtoupper((string) ($order->customer->membership_tier ?? 'Member'));
                $printer->text($this->formatTwoColumn("Pelanggan", $order->customer->name . " ({$tier})", $maxCols) . "\n");
            } elseif (!empty($order->customer_name_guest)) {
                $printer->text($this->formatTwoColumn("Pelanggan", $order->customer_name_guest, $maxCols) . "\n");
            }

            if (!empty($order->table_or_reference)) {
                $printer->text($this->formatTwoColumn("Meja/Ref", $order->table_or_reference, $maxCols) . "\n");
            }

            $printer->text($this->formatTwoColumn("Tipe", strtoupper((string) ($order->order_type ?? 'Takeaway')), $maxCols) . "\n");

            // Industry Specific Metadata
            if (!empty($order->vehicle_license_plate)) {
                $plate = $order->vehicle_license_plate . ($order->vehicle_model ? " ({$order->vehicle_model})" : "");
                $printer->text($this->formatTwoColumn("No. Polisi", $plate, $maxCols) . "\n");
            }
            if (!empty($order->vehicle_mileage)) {
                $printer->text($this->formatTwoColumn("Odometer", number_format($order->vehicle_mileage, 0, ',', '.') . " KM", $maxCols) . "\n");
            }
            if ($order->technician) {
                $printer->text($this->formatTwoColumn("Mekanik", $order->technician->name, $maxCols) . "\n");
            }
            if (!empty($order->laundry_weight_kg)) {
                $printer->text($this->formatTwoColumn("Berat", number_format((float) $order->laundry_weight_kg, 2, ',', '.') . " kg", $maxCols) . "\n");
            }
            if (!empty($order->rack_location)) {
                $printer->text($this->formatTwoColumn("Loker/Rak", $order->rack_location, $maxCols) . "\n");
            }

            $printer->text($this->separator($maxCols, '-') . "\n");

            // 6. Items
            foreach ($order->items as $item) {
                $printer->setEmphasis(true);
                $printer->text($item->product_name . "\n");
                $printer->setEmphasis(false);

                $qty = rtrim(rtrim((string) $item->quantity, '0'), '.');
                $priceStr = number_format((float) $item->unit_price, 0, ',', '.');
                $totalStr = number_format((float) $item->total_price, 0, ',', '.');
                $leftCol = " {$qty} x {$priceStr}";

                $printer->text($this->formatTwoColumn($leftCol, $totalStr, $maxCols) . "\n");

                // Modifiers
                if ($item->modifiers && $item->modifiers->isNotEmpty()) {
                    foreach ($item->modifiers as $mod) {
                        $printer->text("  + " . $mod->option_name . "\n");
                    }
                }

                // Notes & Pharmacy info
                if (!empty($item->notes)) {
                    $printer->text("  * " . $item->notes . "\n");
                }
                if (!empty($item->dosage_instructions)) {
                    $printer->text("  Dosis: " . $item->dosage_instructions . "\n");
                }
            }

            $printer->text($this->separator($maxCols, '-') . "\n");

            // 7. Totals
            $printer->text($this->formatTwoColumn("Subtotal", number_format((float) $order->subtotal, 0, ',', '.'), $maxCols) . "\n");

            $totalDiscount = (float) $order->discount_amount + (float) $order->voucher_discount_amount;
            if ($totalDiscount > 0) {
                $printer->text($this->formatTwoColumn("Diskon Promo", "-" . number_format($totalDiscount, 0, ',', '.'), $maxCols) . "\n");
            }
            if ((float) $order->points_discount_amount > 0) {
                $printer->text($this->formatTwoColumn("Tukar Poin", "-" . number_format((float) $order->points_discount_amount, 0, ',', '.'), $maxCols) . "\n");
            }
            if ((float) $order->tax_amount > 0) {
                $taxPct = $order->tax_percentage ?? 11;
                $printer->text($this->formatTwoColumn("PPN ({$taxPct}%)", number_format((float) $order->tax_amount, 0, ',', '.'), $maxCols) . "\n");
            }
            if ((float) $order->service_charge_amount > 0) {
                $printer->text($this->formatTwoColumn("Service Charge", number_format((float) $order->service_charge_amount, 0, ',', '.'), $maxCols) . "\n");
            }
            if ((float) $order->rounding_amount != 0) {
                $printer->text($this->formatTwoColumn("Pembulatan", number_format((float) $order->rounding_amount, 0, ',', '.'), $maxCols) . "\n");
            }

            $printer->text($this->separator($maxCols, '=') . "\n");

            // Grand Total
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $grandTotalStr = "Rp " . number_format((float) $order->total_amount, 0, ',', '.');
            $printer->text($this->formatTwoColumn("TOTAL TAGIHAN", $grandTotalStr, $maxCols) . "\n");
            $printer->selectPrintMode();

            $printer->text($this->separator($maxCols, '-') . "\n");

            // 8. Payments
            foreach ($order->payments as $payment) {
                $method = strtoupper((string) $payment->payment_method);
                $printer->text($this->formatTwoColumn("Bayar ({$method})", number_format((float) $payment->amount, 0, ',', '.'), $maxCols) . "\n");
            }
            $printer->text($this->formatTwoColumn("Kembalian", "Rp " . number_format((float) $order->change_amount, 0, ',', '.'), $maxCols) . "\n");

            // 9. Loyalty Points
            if ((int) $order->points_earned > 0 || ($order->customer && $order->customer->points_balance !== null)) {
                $printer->text($this->separator($maxCols, '-') . "\n");
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                if ((int) $order->points_earned > 0) {
                    $printer->text("Poin Didapat: +" . $order->points_earned . " Poin\n");
                }
                if ($order->customer) {
                    $printer->text("Saldo Poin Member: " . $order->customer->points_balance . " Poin\n");
                }
            }

            // 10. QR Code
            if ($printerModel->hasCapability(PosPrinter::CAP_QR_CODE)) {
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->feed(1);
                $publicReceiptUrl = route('public.receipt', $order->id);
                try {
                    $printer->qrCode($publicReceiptUrl, Printer::QR_ECLEVEL_M, $is58mm ? 4 : 5);
                } catch (Throwable) {
                    // Fallback to text if native QR is unsupported
                }
            }

            // 11. Footer
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->feed(1);
            $footerNote = $business->pos_receipt_footer_note ?? 'Terima Kasih Atas Kunjungan Anda!';
            $printer->text($footerNote . "\n");
            $printer->text("Struk Digital: cooca.id\n");
            $printer->feed(2);

            // 12. Cut Paper
            if ($printerModel->hasCapability(PosPrinter::CAP_CUT)) {
                $printer->cut(Printer::CUT_PARTIAL);
            }

            $data = $connector->getData();
            $printer->close();
            return $data;
        } catch (Throwable $e) {
            $printer->close();
            throw $e;
        }
    }

    /**
     * Format a Kitchen Order Ticket (KOT) for kitchen / bar station.
     */
    public function formatKitchenOrder(PosOrder $order, PosPrinter $printerModel, array $items = [], string $stationName = 'DAPUR'): string
    {
        $connector = new DummyPrintConnector();
        $profile = CapabilityProfile::load("default");
        $printer = new Printer($connector, $profile);

        $maxCols = $printerModel->getMaxColumns();

        try {
            $printer->initialize();

            // Sound buzzer if supported
            if ($printerModel->hasCapability(PosPrinter::CAP_BEEP)) {
                $printer->text("\x1B\x42\x02\x02"); // ESC B (Beep 2 times)
            }

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_EMPHASIZED);
            $printer->text("TIKET " . strtoupper($stationName) . "\n");
            $printer->selectPrintMode();

            $printer->text($this->separator($maxCols, '=') . "\n");

            // Table / Order Info (Big Bold)
            $tableOrType = $order->posTable ? "MEJA " . $order->posTable->table_number : ($order->table_or_reference ?: strtoupper((string) $order->order_type));
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_EMPHASIZED);
            $printer->text($tableOrType . "\n");
            $printer->selectPrintMode();

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($this->formatTwoColumn("No. Order", "#" . $order->order_number, $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Waktu", now()->format('H:i:s (d/m)'), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Kasir/Pramusaji", $order->user?->name ?? 'Kasir', $maxCols) . "\n");

            if (!empty($order->customer_name_guest)) {
                $printer->text($this->formatTwoColumn("Tamu", $order->customer_name_guest, $maxCols) . "\n");
            }

            $printer->text($this->separator($maxCols, '=') . "\n");

            // Items to prepare
            $itemsList = !empty($items) ? $items : $order->items;
            $totalQty = 0;

            foreach ($itemsList as $item) {
                $qty = is_array($item) ? ($item['quantity'] ?? 1) : $item->quantity;
                $name = is_array($item) ? ($item['product_name'] ?? $item['name'] ?? 'Item') : $item->product_name;
                $notes = is_array($item) ? ($item['notes'] ?? null) : $item->notes;
                $modifiers = is_array($item) ? ($item['modifiers'] ?? []) : ($item->modifiers ? $item->modifiers->pluck('option_name')->toArray() : []);

                $totalQty += (float) $qty;
                $qtyFormatted = rtrim(rtrim((string) $qty, '0'), '.');

                $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
                $printer->text("[ {$qtyFormatted}x ] " . $name . "\n");
                $printer->selectPrintMode();

                if (!empty($modifiers)) {
                    foreach ($modifiers as $mod) {
                        $printer->text("    * " . (is_string($mod) ? $mod : ($mod['option_name'] ?? '')) . "\n");
                    }
                }

                if (!empty($notes)) {
                    $printer->setEmphasis(true);
                    $printer->text("    CATATAN: " . $notes . "\n");
                    $printer->setEmphasis(false);
                }

                $printer->text($this->separator($maxCols, '-') . "\n");
            }

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text("TOTAL ITEM: " . $totalQty . "\n");
            $printer->selectPrintMode();
            $printer->feed(2);

            if ($printerModel->hasCapability(PosPrinter::CAP_CUT)) {
                $printer->cut(Printer::CUT_PARTIAL);
            }

            $data = $connector->getData();
            $printer->close();
            return $data;
        } catch (Throwable $e) {
            $printer->close();
            throw $e;
        }
    }

    /**
     * Format Cashier Shift Summary (Blind Cash Count Report).
     */
    public function formatCashierShiftReport(PosShift $shift, PosPrinter $printerModel): string
    {
        $shift->loadMissing(['user', 'location', 'business']);
        $business = $shift->business;

        $connector = new DummyPrintConnector();
        $profile = CapabilityProfile::load("default");
        $printer = new Printer($connector, $profile);

        $maxCols = $printerModel->getMaxColumns();

        try {
            $printer->initialize();

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_EMPHASIZED);
            $printer->text(strtoupper($business->name ?? 'COOCA') . "\n");
            $printer->selectPrintMode();
            $printer->text("LAPORAN TUTUP KASIR / SHIFT\n");
            $printer->text($this->separator($maxCols, '=') . "\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($this->formatTwoColumn("Kasir", $shift->user?->name ?? 'Kasir', $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Outlet", $shift->location?->name ?? 'Semua', $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Buka Shift", $shift->opened_at ? $shift->opened_at->format('d/m/Y H:i') : '-', $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Tutup Shift", $shift->closed_at ? $shift->closed_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i'), $maxCols) . "\n");

            $printer->text($this->separator($maxCols, '-') . "\n");

            $printer->text($this->formatTwoColumn("Modal Awal Kas", "Rp " . number_format((float) $shift->opening_cash, 0, ',', '.'), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Total Sales Tunai", "Rp " . number_format((float) $shift->total_cash_sales, 0, ',', '.'), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Total Sales Non-Tunai", "Rp " . number_format((float) $shift->total_non_cash_sales, 0, ',', '.'), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Kas Masuk (In)", "Rp " . number_format((float) $shift->total_cash_in, 0, ',', '.'), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Kas Keluar (Out)", "Rp " . number_format((float) $shift->total_cash_out, 0, ',', '.'), $maxCols) . "\n");

            $printer->text($this->separator($maxCols, '=') . "\n");

            $expected = (float) ($shift->closing_cash_expected ?? ($shift->opening_cash + $shift->total_cash_sales + $shift->total_cash_in - $shift->total_cash_out));
            $actual = (float) ($shift->closing_cash_actual ?? 0);
            $diff = (float) ($shift->cash_difference ?? ($actual - $expected));

            $printer->text($this->formatTwoColumn("Ekspektasi Kas Laci", "Rp " . number_format($expected, 0, ',', '.'), $maxCols) . "\n");
            $printer->selectPrintMode(Printer::MODE_EMPHASIZED);
            $printer->text($this->formatTwoColumn("Fisik Kas Dihitung", "Rp " . number_format($actual, 0, ',', '.'), $maxCols) . "\n");
            $printer->selectPrintMode();

            $diffLabel = $diff >= 0 ? "+Rp " . number_format($diff, 0, ',', '.') . " (Pas/Lebih)" : "-Rp " . number_format(abs($diff), 0, ',', '.') . " (Kurang)";
            $printer->text($this->formatTwoColumn("Selisih Kas Fisik", $diffLabel, $maxCols) . "\n");

            if (!empty($shift->notes)) {
                $printer->text($this->separator($maxCols, '-') . "\n");
                $printer->text("Catatan: " . $shift->notes . "\n");
            }

            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Tanda Tangan Kasir\n\n\n");
            $printer->text("( " . ($shift->user?->name ?? 'Kasir') . " )\n");
            $printer->feed(2);

            if ($printerModel->hasCapability(PosPrinter::CAP_CUT)) {
                $printer->cut(Printer::CUT_PARTIAL);
            }

            $data = $connector->getData();
            $printer->close();
            return $data;
        } catch (Throwable $e) {
            $printer->close();
            throw $e;
        }
    }

    /**
     * Format a self-test diagnostic print receipt.
     */
    public function formatTestPrint(PosPrinter $printerModel): string
    {
        $connector = new DummyPrintConnector();
        $profile = CapabilityProfile::load("default");
        $printer = new Printer($connector, $profile);

        $maxCols = $printerModel->getMaxColumns();

        try {
            $printer->initialize();

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH | Printer::MODE_EMPHASIZED);
            $printer->text("COOCA POS PRINTER TEST\n");
            $printer->selectPrintMode();
            $printer->text("Hardware Diagnostic Receipt\n");
            $printer->text($this->separator($maxCols, '=') . "\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($this->formatTwoColumn("Nama Printer", $printerModel->name, $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Tipe Koneksi", strtoupper($printerModel->connection_type), $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Alamat / IP", $printerModel->interface_address, $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Port Jaringan", (string) $printerModel->port, $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Lebar Kertas", $printerModel->paper_width, $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Kapasitas Kolom", "{$maxCols} Karakter/Baris", $maxCols) . "\n");
            $printer->text($this->formatTwoColumn("Waktu Pengujian", now()->format('d/m/Y H:i:s'), $maxCols) . "\n");

            $printer->text($this->separator($maxCols, '-') . "\n");
            $printer->text("Pengujian Fitur Hardware:\n");
            $printer->text(" [OK] Cetak Teks Rata Kiri & Kanan\n");
            $printer->text(" [OK] Huruf Tebal & Font Skala\n");

            if ($printerModel->hasCapability(PosPrinter::CAP_QR_CODE)) {
                $printer->text(" [OK] Cetak 2D QR Code Native\n");
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->feed(1);
                $printer->qrCode("https://cooca.id/hardware-verified", Printer::QR_ECLEVEL_M, 4);
            }

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->feed(1);
            $printer->text("Status: TERHUBUNG & SIAP OPERASIONAL\n");
            $printer->feed(2);

            if ($printerModel->hasCapability(PosPrinter::CAP_CUT)) {
                $printer->cut(Printer::CUT_PARTIAL);
            }

            $data = $connector->getData();
            $printer->close();
            return $data;
        } catch (Throwable $e) {
            $printer->close();
            throw $e;
        }
    }

    /**
     * Generate standalone cash drawer pulse bytes.
     */
    public function formatCashDrawerPulse(?string $pin = null): string
    {
        if ($pin === 'pin5') {
            return "\x1B\x70\x01\x19\xFA";
        }
        if ($pin === 'pin2') {
            return "\x1B\x70\x00\x19\xFA";
        }
        // Standard ESC/POS kick pulse: ESC p 0 25 250 (Pin 2) + ESC p 1 25 250 (Pin 5)
        return "\x1B\x70\x00\x19\xFA\x1B\x70\x01\x19\xFA";
    }

    /**
     * Alias for formatCashDrawerPulse.
     */
    public function formatDrawerPulse(?string $pin = null): string
    {
        return $this->formatCashDrawerPulse($pin);
    }

    /**
     * Helper to format two aligned columns (Left Label and Right Value).
     */
    protected function formatTwoColumn(string $left, string $right, int $maxCols): string
    {
        $leftLen = strlen($left);
        $rightLen = strlen($right);

        if ($leftLen + $rightLen >= $maxCols) {
            $availLeft = $maxCols - $rightLen - 1;
            if ($availLeft > 3) {
                $left = substr($left, 0, $availLeft);
                $leftLen = strlen($left);
            }
        }

        $spacesCount = max(1, $maxCols - $leftLen - $rightLen);
        return $left . str_repeat(' ', $spacesCount) . $right;
    }

    /**
     * Helper to generate horizontal separator.
     */
    protected function separator(int $maxCols, string $char = '-'): string
    {
        return str_repeat($char, $maxCols);
    }

    /**
     * Helper to wrap text centered.
     */
    protected function wordWrapCenter(string $text, int $maxCols): string
    {
        $lines = explode("\n", wordwrap($text, $maxCols, "\n", true));
        return implode("\n", array_map('trim', $lines));
    }
}
