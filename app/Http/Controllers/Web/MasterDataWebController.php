<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Support\Context;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MasterDataWebController extends Controller
{
    public function materialCategories(): View
    {
        $business = Context::requireBusiness();
        $items = MaterialCategory::latest()->get();

        return view('app.master-data.index', [
            'business' => $business,
            'items' => $items,
            'type' => 'material-categories',
            'title' => 'Kategori Bahan Baku',
            'subtitle' => 'Kelola klasifikasi bahan baku yang digunakan bisnis.',
            'icon' => 'layers',
            'nameLabel' => 'Nama Kategori Bahan',
            'emptyLabel' => 'Belum ada kategori bahan baku.',
        ]);
    }

    public function productCategories(): View
    {
        $business = Context::requireBusiness();
        $items = ProductCategory::latest()->get();

        return view('app.master-data.index', [
            'business' => $business,
            'items' => $items,
            'type' => 'product-categories',
            'title' => 'Kategori Produk',
            'subtitle' => 'Kelola klasifikasi produk dan menu penjualan bisnis.',
            'icon' => 'folder',
            'nameLabel' => 'Nama Kategori Produk',
            'emptyLabel' => 'Belum ada kategori produk.',
        ]);
    }

    public function units(): View
    {
        $business = Context::requireBusiness();
        $systemUnits = Unit::whereNull('business_id')->orderBy('name')->get();
        $businessUnits = Unit::where('business_id', $business->id)->latest()->get();
        $unitConversions = UnitConversion::where('business_id', $business->id)
            ->with(['fromUnit', 'toUnit'])
            ->latest()
            ->get();

        return view('app.master-data.index', [
            'business' => $business,
            'items' => $systemUnits->concat($businessUnits),
            'type' => 'units',
            'title' => 'Satuan Ukur',
            'subtitle' => 'Kelola satuan bahan baku, pembelian, produksi, dan penjualan.',
            'icon' => 'scale',
            'nameLabel' => 'Nama Satuan',
            'emptyLabel' => 'Belum ada satuan yang tersedia.',
            'unitConversions' => $unitConversions,
        ]);
    }
}
