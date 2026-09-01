<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;

final class CommerceSeeder extends Seeder
{
    /**
     * Run the database seeds for commercial modules (Customers, Purchase Orders, Invoices, Payments).
     */
    public function run(): void
    {
        $biz1 = Business::where('slug', 'restoran-nusantara-rasa')->first();
        $biz2 = Business::where('slug', 'konveksi-mandiri-kreasi')->first();
        $biz3 = Business::where('slug', 'roti-prima-delima')->first();

        if ($biz1) {
            $this->seedFnbCommerce($biz1);
        }

        if ($biz2) {
            $this->seedApparelCommerce($biz2);
        }

        if ($biz3) {
            $this->seedBakeryCommerce($biz3);
        }
    }

    /**
     * Seed rich commerce data for F&B Restaurant & Catering.
     */
    private function seedFnbCommerce(Business $biz): void
    {
        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::first();
        $porsi = Unit::where('code', 'porsi')->first() ?? $pcs;
        $pack = Unit::where('code', 'pack')->first() ?? $pcs;
        $kg = Unit::where('code', 'kg')->first() ?? $pcs;

        // ─────────────────────────────────────────────────────────────
        // 1. Data Pelanggan Korporat & Retail (Customers)
        // ─────────────────────────────────────────────────────────────
        $custTelkom = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'pt-telkom-indonesia-persero-tbk'],
            [
                'name' => 'Bpk. Rendra Wijaya',
                'company_name' => 'PT Telkom Indonesia (Persero) Tbk',
                'phone' => '0811-2345-6789',
                'email' => 'procurement@telkom.co.id',
                'billing_address' => "Graha Merah Putih Lt. 8, Jl. Gatot Subroto No. 52\nJakarta Selatan 12710",
                'shipping_address' => "Telkom Landmark Tower Lt. 15, Jl. Jend. Gatot Subroto\nJakarta Selatan",
                'tax_identification_number' => '01.000.013.1-093.000',
                'payment_terms_days' => 30,
                'notes' => 'Klien korporat BUMN - Penyediaan catering rapat direksi dan event bulanan',
            ]
        );

        $custBca = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'bank-bca-kantor-wilayah-xii'],
            [
                'name' => 'Ibu Veronica Cindy',
                'company_name' => 'Bank Central Asia (BCA) Kanwil XII',
                'phone' => '0812-8877-6655',
                'email' => 'ga_kanwil12@bca.co.id',
                'billing_address' => "Menara BCA Grand Indonesia Lt. 18, Jl. M.H. Thamrin No. 1\nJakarta Pusat 10310",
                'shipping_address' => "Menara BCA Grand Indonesia Lt. 18\nJakarta Pusat",
                'tax_identification_number' => '01.308.449.0-054.000',
                'payment_terms_days' => 14,
                'notes' => 'Order rutin snack box dan bento lunch box VIP tiap Selasa & Kamis',
            ]
        );

        $custEo = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'eo-graha-kencana-nusantara'],
            [
                'name' => 'Mas Bagus Pratama',
                'company_name' => 'Graha Kencana Event Organizer',
                'phone' => '0813-1122-3344',
                'email' => 'info@grahakencana.id',
                'billing_address' => "Rukan Fatmawati Festival Blok C-12, Jl. RS Fatmawati\nJakarta Selatan",
                'shipping_address' => "Gedung Balai Kartini / SMESCO Convention Hall\nJakarta",
                'tax_identification_number' => '02.876.543.2-015.000',
                'payment_terms_days' => 30,
                'notes' => 'Partner vendor catering wedding and exhibition',
            ]
        );

        $custNotaris = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'kantor-notaris-danuarto-sh'],
            [
                'name' => 'Danuarto, S.H., M.Kn.',
                'company_name' => 'Kantor Notaris & PPAT Danuarto',
                'phone' => '0815-9988-7766',
                'email' => 'sekretariat@notarisdanuarto.com',
                'billing_address' => "Ruko Permata Senayan Blok D-15, Jl. Tentara Pelajar\nJakarta Selatan",
                'shipping_address' => "Ruko Permata Senayan Blok D-15\nJakarta Selatan",
                'tax_identification_number' => '07.123.456.7-012.000',
                'payment_terms_days' => 7,
                'notes' => 'Langganan makan siang karyawan harian',
            ]
        );

        $custVip = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'ibu-ratna-sari-vip'],
            [
                'name' => 'Ibu Ratna Sari',
                'company_name' => 'Private Residence',
                'phone' => '0818-0987-1234',
                'email' => 'ratna.sari@gmail.com',
                'billing_address' => "Pondok Indah Bukit Hijau VII No. 12\nJakarta Selatan",
                'shipping_address' => "Pondok Indah Bukit Hijau VII No. 12\nJakarta Selatan",
                'payment_terms_days' => 0,
                'notes' => 'Pelanggan VIP Personal - Pembayaran langsung / COD',
            ]
        );

        // Ambil produk F&B yang sudah dibuat oleh UserSeeder
        $prodNasiAyam = Product::where('business_id', $biz->id)->where('slug', 'nasi-goreng-ayam-spesial')->first();
        $prodNasiSapi = Product::where('business_id', $biz->id)->where('slug', 'nasi-goreng-sapi-wagyu-slice')->first();
        $prodKopi = Product::where('business_id', $biz->id)->where('slug', 'es-kopi-susu-aren-nusantara')->first();
        $prodJasa = Product::where('business_id', $biz->id)->where('slug', 'jasa-sewa-hall-sound-event-per-sesi')->first();

        $supPasar = Supplier::where('business_id', $biz->id)->first();

        // ─────────────────────────────────────────────────────────────
        // 2. Purchase Orders (Customer PO & Vendor PO)
        // ─────────────────────────────────────────────────────────────

        // PO-1: Telkom (Fully Invoiced)
        $poTelkom = PurchaseOrder::updateOrCreate(
            ['business_id' => $biz->id, 'po_number' => 'PO-202608-0001'],
            [
                'po_type' => PurchaseOrder::TYPE_CUSTOMER,
                'customer_id' => $custTelkom->id,
                'order_date' => now()->subDays(25),
                'expected_delivery_date' => now()->subDays(20),
                'reference_number' => 'TELKOM-PO-PRJ-2026/VIII/044',
                'status' => PurchaseOrder::STATUS_FULLY_INVOICED,
                'subtotal' => 18000000,
                'tax_percentage' => 11,
                'tax_amount' => 1980000,
                'total_amount' => 19980000,
                'notes' => 'Konsumsi seminar tahunan Telkom Group 300 peserta',
                'terms_and_conditions' => "1. Makanan dikirim pukul 11.30 WIB hangat.\n2. Wajib menggunakan packaging paper box eco-friendly ramah lingkungan.",
            ]
        );

        if ($prodNasiAyam && $prodKopi) {
            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poTelkom->id, 'item_name' => $prodNasiAyam->name],
                [
                    'product_id' => $prodNasiAyam->id,
                    'sku' => $prodNasiAyam->code,
                    'unit_id' => $prodNasiAyam->output_unit_id,
                    'quantity' => 300,
                    'unit_price' => 38000,
                    'cost_price_snapshot' => $prodNasiAyam->base_cost ?: 16500,
                    'subtotal' => 11400000,
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poTelkom->id, 'item_name' => $prodKopi->name],
                [
                    'product_id' => $prodKopi->id,
                    'sku' => $prodKopi->code,
                    'unit_id' => $prodKopi->output_unit_id,
                    'quantity' => 300,
                    'unit_price' => 22000,
                    'cost_price_snapshot' => $prodKopi->base_cost ?: 7200,
                    'subtotal' => 6600000,
                ]
            );
        }

        // PO-2: BCA (Confirmed - Siap Dikonversi ke Faktur)
        $poBca = PurchaseOrder::updateOrCreate(
            ['business_id' => $biz->id, 'po_number' => 'PO-202608-0002'],
            [
                'po_type' => PurchaseOrder::TYPE_CUSTOMER,
                'customer_id' => $custBca->id,
                'order_date' => now()->subDays(5),
                'expected_delivery_date' => now()->addDays(3),
                'reference_number' => 'BCA-GA-PO-88219',
                'status' => PurchaseOrder::STATUS_CONFIRMED,
                'subtotal' => 11000000,
                'tax_percentage' => 11,
                'tax_amount' => 1210000,
                'total_amount' => 12210000,
                'notes' => 'Paket Jamuan VIP Rapat Koordinasi Wilayah BCA',
                'terms_and_conditions' => 'Pengantaran tepat waktu di Menara BCA Lt. 18, security clearance jam 10.00.',
            ]
        );

        if ($prodNasiSapi && $prodKopi) {
            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poBca->id, 'item_name' => $prodNasiSapi->name],
                [
                    'product_id' => $prodNasiSapi->id,
                    'sku' => $prodNasiSapi->code,
                    'unit_id' => $prodNasiSapi->output_unit_id,
                    'quantity' => 200,
                    'unit_price' => 55000,
                    'cost_price_snapshot' => $prodNasiSapi->base_cost ?: 26000,
                    'subtotal' => 11000000,
                ]
            );
        }

        // PO-3: Vendor PO ke Supplier Pasar Induk
        if ($supPasar) {
            $poVendor = PurchaseOrder::updateOrCreate(
                ['business_id' => $biz->id, 'po_number' => 'PO-202608-0003'],
                [
                    'po_type' => PurchaseOrder::TYPE_SUPPLIER,
                    'supplier_id' => $supPasar->id,
                    'order_date' => now()->subDays(12),
                    'expected_delivery_date' => now()->subDays(10),
                    'status' => PurchaseOrder::STATUS_CONFIRMED,
                    'subtotal' => 8400000,
                    'tax_percentage' => 0,
                    'tax_amount' => 0,
                    'total_amount' => 8400000,
                    'notes' => 'Pengadaan bahan baku segar mingguan untuk resto & catering',
                    'terms_and_conditions' => 'Kualitas segar, daging dingin beku, reject bila layu/berbau.',
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poVendor->id, 'item_name' => 'Daging Ayam Fillet Segar'],
                [
                    'sku' => 'MAT-AYAM-01',
                    'unit_id' => $kg->id,
                    'quantity' => 100,
                    'unit_price' => 42000,
                    'subtotal' => 4200000,
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poVendor->id, 'item_name' => 'Daging Sapi Slice US Beef'],
                [
                    'sku' => 'MAT-SAPI-01',
                    'unit_id' => $kg->id,
                    'quantity' => 30,
                    'unit_price' => 110000,
                    'subtotal' => 3300000,
                ]
            );

            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poVendor->id, 'item_name' => 'Telur Ayam Negeri Fresh'],
                [
                    'sku' => 'MAT-TLR-01',
                    'unit_id' => $kg->id,
                    'quantity' => 32,
                    'unit_price' => 28000,
                    'subtotal' => 900000,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 3. Faktur Penjualan (Invoices) & Pembayaran
        // ─────────────────────────────────────────────────────────────

        // Faktur 1: Telkom - Status LUNAS (PAID)
        $inv1 = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202608-0001'],
            [
                'customer_id' => $custTelkom->id,
                'purchase_order_id' => $poTelkom->id,
                'invoice_date' => now()->subDays(20),
                'due_date' => now()->subDays(20)->addDays(30),
                'status' => Invoice::STATUS_PAID,
                'subtotal' => 18000000,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 11,
                'tax_amount' => 1980000,
                'shipping_cost' => 150000,
                'total_amount' => 20130000,
                'paid_amount' => 20130000,
                'balance_due' => 0,
                'total_hpp_cost' => 7110000,
                'total_gross_profit' => 10890000,
                'notes' => 'Terima kasih atas kerja sama pengadaan catering Telkom Group.',
            ]
        );

        if ($prodNasiAyam && $prodKopi) {
            $invItem1 = InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv1->id, 'product_id' => $prodNasiAyam->id],
                [
                    'item_name' => $prodNasiAyam->name,
                    'sku' => $prodNasiAyam->code,
                    'unit_id' => $prodNasiAyam->output_unit_id,
                    'quantity' => 300,
                    'unit_price' => 38000,
                    'unit_hpp' => $prodNasiAyam->base_cost ?: 16500,
                    'subtotal' => 11400000,
                ]
            );

            $invItem2 = InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv1->id, 'product_id' => $prodKopi->id],
                [
                    'item_name' => $prodKopi->name,
                    'sku' => $prodKopi->code,
                    'unit_id' => $prodKopi->output_unit_id,
                    'quantity' => 300,
                    'unit_price' => 22000,
                    'unit_hpp' => $prodKopi->base_cost ?: 7200,
                    'subtotal' => 6600000,
                ]
            );
        }

        // Catat Pembayaran Lunas Telkom
        InvoicePayment::updateOrCreate(
            ['invoice_id' => $inv1->id, 'payment_number' => 'PAY-202608-0001'],
            [
                'business_id' => $biz->id,
                'amount' => 20130000,
                'payment_date' => now()->subDays(10),
                'payment_method' => 'bank_transfer',
                'reference_number' => 'TRF/BCA/TELKOM/0821882',
                'notes' => 'Pelunasan faktur catering 300 pax via transfer BCA Perusahaan',
            ]
        );

        // Faktur 2: BCA Kanwil - Status PARTIALLY PAID (Cicilan Termin 1)
        $inv2 = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202608-0002'],
            [
                'customer_id' => $custBca->id,
                'invoice_date' => now()->subDays(8),
                'due_date' => now()->subDays(8)->addDays(14),
                'status' => Invoice::STATUS_PARTIALLY_PAID,
                'subtotal' => 11000000,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 11,
                'tax_amount' => 1210000,
                'total_amount' => 12210000,
                'paid_amount' => 6000000,
                'balance_due' => 6210000,
                'total_hpp_cost' => 5200000,
                'total_gross_profit' => 5800000,
                'notes' => 'Pembayaran termin pertama (DP 50%) telah diterima.',
            ]
        );

        if ($prodNasiSapi) {
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv2->id, 'product_id' => $prodNasiSapi->id],
                [
                    'item_name' => $prodNasiSapi->name,
                    'sku' => $prodNasiSapi->code,
                    'unit_id' => $prodNasiSapi->output_unit_id,
                    'quantity' => 200,
                    'unit_price' => 55000,
                    'unit_hpp' => $prodNasiSapi->base_cost ?: 26000,
                    'subtotal' => 11000000,
                ]
            );
        }

        InvoicePayment::updateOrCreate(
            ['invoice_id' => $inv2->id, 'payment_number' => 'PAY-202608-0002'],
            [
                'business_id' => $biz->id,
                'amount' => 6000000,
                'payment_date' => now()->subDays(6),
                'payment_method' => 'bank_transfer',
                'reference_number' => 'BCA-TERMIN1-DP50',
                'notes' => 'Uang muka / DP 50% catering VIP BCA',
            ]
        );

        // Faktur 3: Notaris Danuarto - Status UNPAID / SENT
        $inv3 = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202608-0003'],
            [
                'customer_id' => $custNotaris->id,
                'invoice_date' => now()->subDays(3),
                'due_date' => now()->addDays(4),
                'status' => Invoice::STATUS_SENT,
                'subtotal' => 3800000,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'total_amount' => 3800000,
                'paid_amount' => 0,
                'balance_due' => 3800000,
                'total_hpp_cost' => 1650000,
                'total_gross_profit' => 2150000,
                'notes' => 'Tagihan makan siang 100 porsi periode 1-15 Agustus 2026.',
            ]
        );

        if ($prodNasiAyam) {
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv3->id, 'product_id' => $prodNasiAyam->id],
                [
                    'item_name' => $prodNasiAyam->name,
                    'sku' => $prodNasiAyam->code,
                    'unit_id' => $prodNasiAyam->output_unit_id,
                    'quantity' => 100,
                    'unit_price' => 38000,
                    'unit_hpp' => $prodNasiAyam->base_cost ?: 16500,
                    'subtotal' => 3800000,
                ]
            );
        }

        // Faktur 4: Graha Kencana EO - Status OVERDUE (Jatuh Tempo Lampau)
        $inv4 = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202607-0004'],
            [
                'customer_id' => $custEo->id,
                'invoice_date' => now()->subDays(45),
                'due_date' => now()->subDays(15), // Lewat jatuh tempo
                'status' => Invoice::STATUS_OVERDUE,
                'subtotal' => 15000000,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 11,
                'tax_amount' => 1650000,
                'total_amount' => 16650000,
                'paid_amount' => 0,
                'balance_due' => 16650000,
                'total_hpp_cost' => 6600000,
                'total_gross_profit' => 8400000,
                'notes' => 'SURAT PENAGIHAN KE-2: Mohon segera menyelesaikan tagihan yang telah lewat jatuh tempo.',
            ]
        );

        if ($prodJasa) {
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv4->id, 'product_id' => $prodJasa->id],
                [
                    'item_name' => $prodJasa->name,
                    'sku' => $prodJasa->code,
                    'unit_id' => $prodJasa->output_unit_id,
                    'quantity' => 3,
                    'unit_price' => 5000000,
                    'unit_hpp' => $prodJasa->base_cost ?: 2200000,
                    'subtotal' => 15000000,
                ]
            );
        }

        // ─────────────────────────────────────────────────────────────
        // 4. Sample PO & Faktur dengan 24 Item Menu untuk Uji Multi-page Print / PDF
        // ─────────────────────────────────────────────────────────────
        $megaItems = [
            ['name' => 'Nasi Goreng Ayam Spesial Rempah Nusantara', 'sku' => 'FNB-NG-001', 'qty' => 300, 'unit_price' => 38000, 'unit_hpp' => 16500],
            ['name' => 'Nasi Goreng Sapi Wagyu Slice Meltique', 'sku' => 'FNB-NG-002', 'qty' => 150, 'unit_price' => 55000, 'unit_hpp' => 26000],
            ['name' => 'Ayam Bakar Bumbu Rujak Tradisional', 'sku' => 'FNB-AYM-001', 'qty' => 300, 'unit_price' => 28000, 'unit_hpp' => 13500],
            ['name' => 'Sate Ayam Madura Saus Kacang Gurih', 'sku' => 'FNB-STE-001', 'qty' => 300, 'unit_price' => 32000, 'unit_hpp' => 14000],
            ['name' => 'Rendang Daging Sapi Minang Asli 48 Jam', 'sku' => 'FNB-RDG-001', 'qty' => 200, 'unit_price' => 45000, 'unit_hpp' => 22000],
            ['name' => 'Sup Kimlo Jamur Kuping & Bakso Ikan', 'sku' => 'FNB-SUP-001', 'qty' => 300, 'unit_price' => 22000, 'unit_hpp' => 9500],
            ['name' => 'Ikan Gurame Goreng Saus Asam Manis', 'sku' => 'FNB-IKN-001', 'qty' => 100, 'unit_price' => 65000, 'unit_hpp' => 31000],
            ['name' => 'Udang Goreng Gandum Crispy Garlic Oat', 'sku' => 'FNB-UDG-001', 'qty' => 150, 'unit_price' => 48000, 'unit_hpp' => 24000],
            ['name' => 'Capcay Seafood Kuah Kental Jamur Enoki', 'sku' => 'FNB-SAY-001', 'qty' => 200, 'unit_price' => 25000, 'unit_hpp' => 11000],
            ['name' => 'Bakmi Goreng Jawa Ulang Tahun Telur Puyuh', 'sku' => 'FNB-MIE-001', 'qty' => 200, 'unit_price' => 28000, 'unit_hpp' => 12000],
            ['name' => 'Sambal Goreng Ati Ampela Kentang Balado', 'sku' => 'FNB-ATI-001', 'qty' => 150, 'unit_price' => 20000, 'unit_hpp' => 8500],
            ['name' => 'Kerupuk Udang Sidoarjo Renyah Premium', 'sku' => 'FNB-KRP-001', 'qty' => 300, 'unit_price' => 5000, 'unit_hpp' => 1800],
            ['name' => 'Acar Wortel Timun & Sambal Bajak Pedas', 'sku' => 'FNB-ACR-001', 'qty' => 300, 'unit_price' => 3500, 'unit_hpp' => 1200],
            ['name' => 'Es Kopi Susu Aren Nusantara Gula Organik', 'sku' => 'FNB-KPI-001', 'qty' => 300, 'unit_price' => 22000, 'unit_hpp' => 7200],
            ['name' => 'Es Campur Durian Legit Medan Spesial', 'sku' => 'FNB-MIN-001', 'qty' => 200, 'unit_price' => 26000, 'unit_hpp' => 11500],
            ['name' => 'Es Cincau Hijau Santan Murni Gula Kelapa', 'sku' => 'FNB-MIN-002', 'qty' => 200, 'unit_price' => 18000, 'unit_hpp' => 6500],
            ['name' => 'Puding Karamel Susu Kemasan Cup Sealing', 'sku' => 'FNB-PDG-001', 'qty' => 250, 'unit_price' => 15000, 'unit_hpp' => 5500],
            ['name' => 'Buah Potong Segar Tropis (Melon, Semangka, Nanas)', 'sku' => 'FNB-BUH-001', 'qty' => 300, 'unit_price' => 12000, 'unit_hpp' => 4500],
            ['name' => 'Snack Box: Pastel Ayam Sayur Telur Cincang', 'sku' => 'FNB-SNK-001', 'qty' => 200, 'unit_price' => 8000, 'unit_hpp' => 3200],
            ['name' => 'Snack Box: Risoles Mayo Smoked Beef Melted', 'sku' => 'FNB-SNK-002', 'qty' => 200, 'unit_price' => 9000, 'unit_hpp' => 3800],
            ['name' => 'Snack Box: Lemper Ayam Bakar Pulen Pandan', 'sku' => 'FNB-SNK-003', 'qty' => 200, 'unit_price' => 7500, 'unit_hpp' => 3000],
            ['name' => 'Snack Box: Kue Soes Vla Vanilla Rhum Asli', 'sku' => 'FNB-SNK-004', 'qty' => 200, 'unit_price' => 8500, 'unit_hpp' => 3400],
            ['name' => 'Air Mineral Botol 600ml Dingin Higienis', 'sku' => 'FNB-AIR-001', 'qty' => 400, 'unit_price' => 4000, 'unit_hpp' => 2000],
            ['name' => 'Paket Service Chafing Dish & Peralatan Prasmanan', 'sku' => 'FNB-SRV-001', 'qty' => 1, 'unit_price' => 2500000, 'unit_hpp' => 800000],
        ];

        $subtotalMega = 0;
        $totalHppMega = 0;
        foreach ($megaItems as $item) {
            $subtotalMega += $item['qty'] * $item['unit_price'];
            $totalHppMega += $item['qty'] * $item['unit_hpp'];
        }
        $taxMega = round($subtotalMega * 0.11);
        $totalAmountMega = $subtotalMega + $taxMega;
        $grossProfitMega = $subtotalMega - $totalHppMega;

        // Purchase Order 24 Item (PO-202608-0020)
        $poMega = PurchaseOrder::updateOrCreate(
            ['business_id' => $biz->id, 'po_number' => 'PO-202608-0020'],
            [
                'po_type' => PurchaseOrder::TYPE_CUSTOMER,
                'customer_id' => $custTelkom->id,
                'order_date' => now()->subDays(2),
                'expected_delivery_date' => now()->addDays(5),
                'reference_number' => 'TELKOM-GALA-DINNER-24MENU',
                'status' => PurchaseOrder::STATUS_CONFIRMED,
                'subtotal' => $subtotalMega,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 11,
                'tax_amount' => $taxMega,
                'total_amount' => $totalAmountMega,
                'notes' => 'Paket Jamuan Akbar Gala Dinner & Penghargaan Karyawan Telkom (24 Menu Varian Lengkap)',
                'terms_and_conditions' => "1. Wajib standby di Ballroom Lt. 2 Telkom Landmark Tower pukul 16.00 WIB.\n2. Seluruh hidangan panas disajikan dalam Chafing Dish roll-top dengan penghangat api.\n3. Peralatan makan (piring keramik, sendok garpu stainless steel) disediakan lengkap dan higienis.",
            ]
        );

        foreach ($megaItems as $idx => $mItem) {
            PurchaseOrderItem::updateOrCreate(
                ['purchase_order_id' => $poMega->id, 'item_name' => $mItem['name']],
                [
                    'sku' => $mItem['sku'],
                    'unit_id' => $pcs->id,
                    'quantity' => $mItem['qty'],
                    'unit_price' => $mItem['unit_price'],
                    'cost_price_snapshot' => $mItem['unit_hpp'],
                    'subtotal' => $mItem['qty'] * $mItem['unit_price'],
                ]
            );
        }

        // Faktur Penjualan 24 Item (INV-202608-0020)
        $invMega = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202608-0020'],
            [
                'customer_id' => $custTelkom->id,
                'purchase_order_id' => $poMega->id,
                'invoice_date' => now()->subDays(1),
                'due_date' => now()->addDays(29),
                'status' => Invoice::STATUS_SENT,
                'subtotal' => $subtotalMega,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 11,
                'tax_amount' => $taxMega,
                'shipping_cost' => 250000,
                'total_amount' => $totalAmountMega + 250000,
                'paid_amount' => 0,
                'balance_due' => $totalAmountMega + 250000,
                'total_hpp_cost' => $totalHppMega,
                'total_gross_profit' => $grossProfitMega,
                'payment_terms' => 'Net 30',
                'notes' => "Tagihan Paket Gala Dinner Telkom Indonesia 300 Pax (24 Menu Lengkap).\nHarap melampirkan salinan Berita Acara Serah Terima (BAST) saat proses verifikasi pembayaran.",
            ]
        );

        foreach ($megaItems as $idx => $mItem) {
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $invMega->id, 'item_name' => $mItem['name']],
                [
                    'sku' => $mItem['sku'],
                    'unit_id' => $pcs->id,
                    'quantity' => $mItem['qty'],
                    'unit_price' => $mItem['unit_price'],
                    'unit_hpp' => $mItem['unit_hpp'],
                    'subtotal' => $mItem['qty'] * $mItem['unit_price'],
                ]
            );
        }
    }

    /**
     * Seed commerce data for Apparel / Konveksi.
     */
    private function seedApparelCommerce(Business $biz): void
    {
        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::first();

        // Buat Klien Konveksi
        $custAstra = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'pt-astra-international-tbk'],
            [
                'name' => 'Bpk. Bambang Hermawan',
                'company_name' => 'PT Astra International Tbk',
                'phone' => '0812-7766-5544',
                'email' => 'seragam@astra.co.id',
                'billing_address' => "Menara Astra Lt. 25, Jl. Jend. Sudirman Kav. 5-6\nJakarta 10220",
                'tax_identification_number' => '01.000.012.3-051.000',
                'payment_terms_days' => 45,
            ]
        );

        $custDistro = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'distro-urban-wave-indonesia'],
            [
                'name' => 'Kevin Sanjaya',
                'company_name' => 'Urban Wave Apparel Brand',
                'phone' => '0817-2233-4455',
                'email' => 'production@urbanwave.id',
                'billing_address' => "Jl. Riau No. 88, Bandung",
                'payment_terms_days' => 30,
            ]
        );

        // Buat sample produk konveksi jika belum ada
        $catSeragam = \App\Models\ProductCategory::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'seragam-kerja-wearpack'],
            ['name' => 'Seragam Kerja & Wearpack Pabrik']
        );

        $prodSeragam = Product::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'kemeja-seragam-drill-astra'],
            [
                'name' => 'Kemeja Seragam Kerja American Drill Bordir',
                'code' => 'APP-SGM-001',
                'category_id' => $catSeragam->id,
                'output_unit_id' => $pcs->id,
                'base_cost' => 65000,
                'selling_price' => 115000,
                'min_stock' => 50,
                'is_active' => true,
            ]
        );

        // PO Astra
        $poAstra = PurchaseOrder::updateOrCreate(
            ['business_id' => $biz->id, 'po_number' => 'PO-202608-K001'],
            [
                'po_type' => PurchaseOrder::TYPE_CUSTOMER,
                'customer_id' => $custAstra->id,
                'order_date' => now()->subDays(15),
                'status' => PurchaseOrder::STATUS_CONFIRMED,
                'subtotal' => 57500000,
                'tax_percentage' => 11,
                'tax_amount' => 6325000,
                'total_amount' => 63825000,
                'notes' => 'Pengadaan 500 pcs kemeja seragam teknisi Astra batch Agustus',
            ]
        );

        PurchaseOrderItem::updateOrCreate(
            ['purchase_order_id' => $poAstra->id, 'item_name' => $prodSeragam->name],
            [
                'product_id' => $prodSeragam->id,
                'sku' => $prodSeragam->code,
                'unit_id' => $pcs->id,
                'quantity' => 500,
                'unit_price' => 115000,
                'cost_price_snapshot' => 65000,
                'subtotal' => 57500000,
            ]
        );

        // Faktur Lunas Distro
        $invDistro = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202608-K001'],
            [
                'customer_id' => $custDistro->id,
                'invoice_date' => now()->subDays(10),
                'due_date' => now()->addDays(20),
                'status' => Invoice::STATUS_PAID,
                'subtotal' => 23000000,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 0,
                'tax_amount' => 0,
                'total_amount' => 23000000,
                'paid_amount' => 23000000,
                'balance_due' => 0,
                'total_hpp_cost' => 13000000,
                'total_gross_profit' => 10000000,
                'notes' => 'Produksi 200 pcs kemeja custom Urban Wave telah selesai dan terkirim.',
            ]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $invDistro->id, 'product_id' => $prodSeragam->id],
            [
                'item_name' => $prodSeragam->name,
                'sku' => $prodSeragam->code,
                'unit_id' => $pcs->id,
                'quantity' => 200,
                'unit_price' => 115000,
                'unit_hpp' => 65000,
                'subtotal' => 23000000,
            ]
        );

        InvoicePayment::updateOrCreate(
            ['invoice_id' => $invDistro->id, 'payment_number' => 'PAY-202608-K001'],
            [
                'business_id' => $biz->id,
                'amount' => 23000000,
                'payment_date' => now()->subDays(7),
                'payment_method' => 'bank_transfer',
                'reference_number' => 'MANDIRI-TRF-URBANWAVE-88',
                'notes' => 'Pelunasan invoice konveksi via Bank Mandiri',
            ]
        );
    }

    /**
     * Seed commerce data for Bakery & Pastry.
     */
    private function seedBakeryCommerce(Business $biz): void
    {
        $pcs = Unit::where('code', 'pcs')->first() ?? Unit::first();

        $custHotel = Customer::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'hotel-grand-palace-semarang'],
            [
                'name' => 'Executive Chef Marco',
                'company_name' => 'Hotel Grand Palace Semarang',
                'phone' => '0824-3322-1100',
                'email' => 'fb@grandpalacehotel.com',
                'billing_address' => "Jl. Simpang Lima No. 1, Semarang",
                'tax_identification_number' => '02.111.222.3-501.000',
                'payment_terms_days' => 30,
            ]
        );

        $catPastry = \App\Models\ProductCategory::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'pastry-croissant-premium'],
            ['name' => 'Artisan Pastry & Croissant']
        );

        $prodCroissant = Product::updateOrCreate(
            ['business_id' => $biz->id, 'slug' => 'butter-croissant-french-style'],
            [
                'name' => 'Butter Croissant French Style Artisan',
                'code' => 'BKR-CRS-001',
                'category_id' => $catPastry->id,
                'output_unit_id' => $pcs->id,
                'base_cost' => 8500,
                'selling_price' => 18000,
                'min_stock' => 100,
                'is_active' => true,
            ]
        );

        // Faktur Rutin Hotel
        $invHotel = Invoice::updateOrCreate(
            ['business_id' => $biz->id, 'invoice_number' => 'INV-202608-B001'],
            [
                'customer_id' => $custHotel->id,
                'invoice_date' => now()->subDays(5),
                'due_date' => now()->addDays(25),
                'status' => Invoice::STATUS_SENT,
                'subtotal' => 9000000,
                'discount_type' => 'percentage',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_percentage' => 11,
                'tax_amount' => 990000,
                'total_amount' => 9990000,
                'paid_amount' => 0,
                'balance_due' => 9990000,
                'total_hpp_cost' => 4250000,
                'total_gross_profit' => 4750000,
                'notes' => 'Pasokan 500 pcs butter croissant untuk sarapan buffet hotel',
            ]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $invHotel->id, 'product_id' => $prodCroissant->id],
            [
                'item_name' => $prodCroissant->name,
                'sku' => $prodCroissant->code,
                'unit_id' => $pcs->id,
                'quantity' => 500,
                'unit_price' => 18000,
                'unit_hpp' => 8500,
                'subtotal' => 9000000,
            ]
        );
    }
}
