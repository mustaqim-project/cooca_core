<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BusinessTypeTemplate;
use Illuminate\Database\Seeder;

final class BusinessTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'fnb_resto',
                'name' => 'Restoran / Rumah Makan',
                'industry_category' => 'fnb',
                'description' => 'Template HPP F&B Restoran dengan Recipe/BOM multi-porsi, bumbu masak, waste dapur, dan alokasi sewa.',
                'recommended_costing_method' => 'recipe_bom',
                'default_cost_components' => [
                    ['name' => 'Bahan Baku Makanan', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Bumbu & Pelengkap', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Kemasan & Packaging', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Upah Koki & Cook', 'category' => 'Direct Labor', 'behavior' => 'fixed', 'traceability' => 'direct'],
                    ['name' => 'Gas & Listrik Dapur', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'indirect'],
                    ['name' => 'Sewa Outlet & Ruko', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
                'default_allocation_rules' => [
                    ['pool_name' => 'Sewa & Kebersihan', 'driver_type' => 'revenue_pct'],
                ],
            ],
            [
                'code' => 'fnb_cafe',
                'name' => 'Coffee Shop & Cafe',
                'industry_category' => 'fnb',
                'description' => 'Template Coffee Shop dengan biji kopi (yield extraction), susu, cup takeaway, dan upah barista.',
                'recommended_costing_method' => 'recipe_bom',
                'default_cost_components' => [
                    ['name' => 'Biji Kopi (Beans)', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Fresh Milk & Sirup', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Paper Cup & Sedotan', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Upah Barista', 'category' => 'Direct Labor', 'behavior' => 'fixed', 'traceability' => 'direct'],
                    ['name' => 'Penyusutan Mesin Espresso', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'fnb_bakery',
                'name' => 'Bakery & Cake Shop',
                'industry_category' => 'fnb',
                'description' => 'Template toko roti dengan formula adonan batch, topping, baking oven hours, dan waste sisa display.',
                'recommended_costing_method' => 'recipe_bom',
                'default_cost_components' => [
                    ['name' => 'Tepung & Bahan Adonan', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Topping, Cokelat, Keju', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Baker Hours', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Listrik Oven Deck', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'fnb_cloud_kitchen',
                'name' => 'Cloud Kitchen & Delivery Only',
                'industry_category' => 'fnb',
                'description' => 'Model biaya F&B delivery dengan packaging premium dan marketplace price deductions.',
                'recommended_costing_method' => 'recipe_bom',
                'default_cost_components' => [
                    ['name' => 'Bahan Baku Porsi', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Box Delivery & Seal Sealant', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Jasa Masak Chef', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'fnb_catering',
                'name' => 'Catering & Prasmanan',
                'industry_category' => 'fnb',
                'description' => 'Kalkulasi HPP paket porsi besar (buffet/kotak) dengan tenaga kerja freelance harian.',
                'recommended_costing_method' => 'job',
                'default_cost_components' => [
                    ['name' => 'Bahan Menu Utama & Pembuka', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Tenaga Masak & Pelayan Harian', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Sewa Alat Prasmanan', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'fnb_frozen_food',
                'name' => 'Frozen Food Manufacturing',
                'industry_category' => 'fnb',
                'description' => 'Produksi olahan beku dengan blast freezing machine hour dan kemasan vacuum.',
                'recommended_costing_method' => 'process',
                'default_cost_components' => [
                    ['name' => 'Daging & Rempah Campuran', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Plastik Vacuum & Nitrogen', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Listrik Cold Storage', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'mfg_garment',
                'name' => 'Konveksi & Garment Manufacturing',
                'industry_category' => 'manufacturing',
                'description' => 'Kalkulasi pakaian (kain meter/kg, benang, kancing, cutting, jahit per potong, sablon).',
                'recommended_costing_method' => 'recipe_bom',
                'default_cost_components' => [
                    ['name' => 'Kain Utama & Furing', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Aksesoris (Kancing, Zipper, Label)', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Ongkos Jahit & Potong (CMT)', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Penyusutan Mesin Jahit', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'mfg_precision',
                'name' => 'Pabrik Plastik & Metal Presisi',
                'industry_category' => 'manufacturing',
                'description' => 'Injeksi molding & stamping presisi dengan cycle time CNC, mold depreciation, dan regrind yield.',
                'recommended_costing_method' => 'process',
                'default_cost_components' => [
                    ['name' => 'Resin / Biji Plastik / Plat Metal', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Operator CNC / Injection', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Depresiasi Mesin & Mold Tooling', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'mfg_furniture',
                'name' => 'Furniture & Woodworking',
                'industry_category' => 'manufacturing',
                'description' => 'Pembuatan mebel kayu (kayu solid, plywood, lem, amplas, finishing melamine/duco, tukang kayu).',
                'recommended_costing_method' => 'job',
                'default_cost_components' => [
                    ['name' => 'Kayu & Plywood', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Cat, Thinner & Finishing', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Tukang Kayu & Finishing', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'mfg_craft',
                'name' => 'Kerajinan Tangan & Handmade Craft',
                'industry_category' => 'creative',
                'description' => 'Produk seni & craft kustom dengan ketelitian tangan tinggi dan packaging artistik.',
                'recommended_costing_method' => 'simple',
                'default_cost_components' => [
                    ['name' => 'Bahan Baku Kerajinan', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Upah Pengrajin', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Hardbox & Pita Gift', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'mfg_printing',
                'name' => 'Percetakan & Digital Printing',
                'industry_category' => 'manufacturing',
                'description' => 'Percetakan offset/digital dengan kertas rim, tinta cmyk, plat film, dan machine run hours.',
                'recommended_costing_method' => 'process',
                'default_cost_components' => [
                    ['name' => 'Kertas / Banner Media', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Tinta & Toner Cetak', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Operator Mesin Offset', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Depresiasi Mesin Digital Printing', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'retail_reseller',
                'name' => 'Reseller & Toko Retail',
                'industry_category' => 'retail',
                'description' => 'Model HPP murni landed cost produk jadi dari supplier (beli + ongkir masuk - diskon beli).',
                'recommended_costing_method' => 'retail',
                'default_cost_components' => [
                    ['name' => 'Harga Beli Produk Jadi', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Ongkos Kirim Masuk (Freight-in)', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Plastik Belanja / Tas Kantong', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'retail_pharmacy',
                'name' => 'Apotek & Toko Obat',
                'industry_category' => 'retail',
                'description' => 'Retail obat & suplemen dengan expired date shrinkage buffer dan embalase racikan.',
                'recommended_costing_method' => 'retail',
                'default_cost_components' => [
                    ['name' => 'HPP Pembelian Obat PBF', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Kertas Puyer / Klip Obat', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Jasa Apoteker & Asisten', 'category' => 'Direct Labor', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'service_agency',
                'name' => 'Digital Creative Agency / IT Software',
                'industry_category' => 'service',
                'description' => 'Model biaya jasa profesional (Developer/Designer hourly rate, software subscription, PM overhead).',
                'recommended_costing_method' => 'service',
                'default_cost_components' => [
                    ['name' => 'Man-Hours Developer / Designer', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Lisensi Software & Cloud Hosting', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Account Manager / PM Time', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'service_workshop',
                'name' => 'Bengkel Mobil & Motor',
                'industry_category' => 'service',
                'description' => 'Kombinasi sparepart (retail/material) + jasa mekanik per jam / per task.',
                'recommended_costing_method' => 'job',
                'default_cost_components' => [
                    ['name' => 'Oli & Sparepart Diganti', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Jasa Mekanik (Flat Rate Time)', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Penyusutan Lift & Tools Bengkel', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'service_barbershop',
                'name' => 'Barbershop & Salon Kecantikan',
                'industry_category' => 'service',
                'description' => 'Jasa potong rambut/treatment + bahan habis pakai (shampoo, pomade, obat creambath).',
                'recommended_costing_method' => 'service',
                'default_cost_components' => [
                    ['name' => 'Krim, Shampoo, Cat Rambut', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Komisi / Upah Kapster', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Sewa Kursi & Listrik AC', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'service_laundry',
                'name' => 'Laundry Kiloan & Satuan',
                'industry_category' => 'service',
                'description' => 'Jasa cuci kiloan/satuan dengan detergen, softener, plastik packing, gas dryer, dan operator cuci/setrika.',
                'recommended_costing_method' => 'per_unit',
                'default_cost_components' => [
                    ['name' => 'Detergen, Parfum, Softener', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Gas Pengering & Listrik Mesin', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Upah Setrika & Cuci', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'service_contractor',
                'name' => 'Kontraktor & Renovasi Bangunan',
                'industry_category' => 'service',
                'description' => 'Job order proyek fisik (semen, pasir, bata, mandor harian, tukang borongan, sewa scaffolding).',
                'recommended_costing_method' => 'job',
                'default_cost_components' => [
                    ['name' => 'Material Bangunan Fisik', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Tukang & Mandor Borongan', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Sewa Alat Berat & Scaffolding', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'service_event',
                'name' => 'Event Organizer & Wedding Organizer',
                'industry_category' => 'service',
                'description' => 'Paket event WO (dekorasi, dokumentasi, sound system, crew event harian).',
                'recommended_costing_method' => 'job',
                'default_cost_components' => [
                    ['name' => 'Bunga & Bahan Dekorasi', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Honor Crew & MC Event', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Vendor Sound & Lighting', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'agri_farming',
                'name' => 'Peternakan & Pertanian',
                'industry_category' => 'agriculture',
                'description' => 'Pertanian/peternakan (bibit/DOC, pakan harian FCR, vitamin, panen yield mortality).',
                'recommended_costing_method' => 'process',
                'default_cost_components' => [
                    ['name' => 'Bibit / DOC Ayam / Benih', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Pakan Konsentrat & Vitamin', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Tenaga Rawat & Panen', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'mfg_cosmetics',
                'name' => 'Kosmetik & Skincare Production',
                'industry_category' => 'manufacturing',
                'description' => 'Produksi sabun kecantikan, serum, lotion dengan botol pump, stiker label BPOM, dan uji lab batch.',
                'recommended_costing_method' => 'process',
                'default_cost_components' => [
                    ['name' => 'Bahan Aktif & Carrier Oil', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Botol Pipet, Jar & Stiker BPOM', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Operator Homogenizer & Mixing', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Uji Lab & Sertifikasi Batch', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'distributor_fmcg',
                'name' => 'Distributor & Trading FMCG',
                'industry_category' => 'trading',
                'description' => 'Distribusi barang grosir kartonan dengan ongkos armada truk, sopir/kernet, dan handling gudang.',
                'recommended_costing_method' => 'retail',
                'default_cost_components' => [
                    ['name' => 'HPP Kartonan Pabrik', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'BBM & Tol Armada Truk', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Insentif Sopir & Helper', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Sewa Gudang Distribusi', 'category' => 'Fixed Overhead', 'behavior' => 'fixed', 'traceability' => 'indirect'],
                ],
            ],
            [
                'code' => 'service_autodetailing',
                'name' => 'Auto Detailing & Cuci Mobil',
                'industry_category' => 'service',
                'description' => 'Jasa coating, salon mobil, cuci hidrolik dengan obat poles compound, shampoo pH balance, dan teknisi.',
                'recommended_costing_method' => 'service',
                'default_cost_components' => [
                    ['name' => 'Nano Ceramic & Compound Poles', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Shampoo Mobil & Microfiber', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Komisi Detailer Mobil', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'fnb_catering_diet',
                'name' => 'Diet & Healthy Catering',
                'industry_category' => 'fnb',
                'description' => 'Katering harian berbasis hitungan kalori dengan kemasan microwave-safe dan kurir harian.',
                'recommended_costing_method' => 'recipe_bom',
                'default_cost_components' => [
                    ['name' => 'Bahan Baku Organik & Protein', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Box Microwave-Safe & Cutlery', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Jasa Konsultasi Ahli Gizi & Koki', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Kurir Pengantaran Harian', 'category' => 'Variable Overhead', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
            [
                'code' => 'mfg_tailor_custom',
                'name' => 'Penjahit Jas & Kebaya Custom',
                'industry_category' => 'manufacturing',
                'description' => 'Pembuatan pakaian adibusana kustom satuan dengan pola tailor, fitting berkala, dan furing sutra.',
                'recommended_costing_method' => 'job',
                'default_cost_components' => [
                    ['name' => 'Kain Wool / Brokat & Furing', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Aksesoris Payet & Kancing Mewah', 'category' => 'Direct Material', 'behavior' => 'variable', 'traceability' => 'direct'],
                    ['name' => 'Ongkos Master Tailor (Pola & Fitting)', 'category' => 'Direct Labor', 'behavior' => 'variable', 'traceability' => 'direct'],
                ],
            ],
        ];

        foreach ($templates as $data) {
            BusinessTypeTemplate::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
