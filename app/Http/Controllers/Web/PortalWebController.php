<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BusinessMembership;
use App\Models\Location;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PortalWebController extends Controller
{
    /**
     * Display the employee personal portal & attendance center.
     */
    public function index(Request $request): View
    {
        $business = Context::requireBusiness();
        $user = auth()->user();

        // 1. Current user membership & role details
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->with(['roleModel', 'location'])
            ->first();

        // 2. Today's attendance record (WIB context)
        $today = Carbon::now('Asia/Jakarta')->toDateString();
        $todayAttendance = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 3. Past 7 days attendance history (including today)
        $sevenDaysAgo = Carbon::now('Asia/Jakarta')->subDays(6)->toDateString();
        $recentAttendances = Attendance::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->whereDate('date', '>=', $sevenDaysAgo)
            ->orderByDesc('date')
            ->get();

        // 4. Seven days summary metrics
        $presentDaysCount = $recentAttendances->whereNotNull('clock_in_at')->count();
        $onTimeDaysCount = $recentAttendances->where('clock_in_status', Attendance::CLOCK_IN_ON_TIME)->count();
        $lateDaysCount = $recentAttendances->filter(function (Attendance $att) {
            return $att->clock_in_status === Attendance::CLOCK_IN_LATE || $att->status === Attendance::STATUS_LATE;
        })->count();
        $totalWorkMinutes = (int) $recentAttendances->sum('work_duration_minutes');
        $totalWorkHours = round($totalWorkMinutes / 60, 1);

        // 5. Office location / branch for geofencing & weather
        $location = $membership?->location
            ?? Location::where('business_id', $business->id)->where('is_primary', true)->first()
            ?? Location::where('business_id', $business->id)->first();

        $defaultLat = $location?->latitude ? (float) $location->latitude : -6.2088;
        $defaultLng = $location?->longitude ? (float) $location->longitude : 106.8456;
        $cityName = $location?->city ?? $business->city ?? 'Jakarta';

        // 6. Authorized quick-access modules for this user
        $quickModules = [];

        if (Context::isOwner() || Context::hasPermission('pos.terminal')) {
            $quickModules[] = [
                'name' => 'Mesin Kasir (POS)',
                'route' => route('pos.terminal'),
                'icon' => 'calculator',
                'color' => '#007AFF',
                'desc' => 'Buka terminal transaksi kasir toko',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('pos.orders')) {
            $quickModules[] = [
                'name' => 'Pesanan Kasir',
                'route' => route('pos.orders.index'),
                'icon' => 'receipt',
                'color' => '#5856D6',
                'desc' => 'Daftar transaksi kasir & shift kas',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('pos.kitchen')) {
            $quickModules[] = [
                'name' => 'Dapur KDS',
                'route' => route('pos.kitchen.index'),
                'icon' => 'utensils',
                'color' => '#FF9500',
                'desc' => 'Layar pesanan dapur & barista',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('pos.tables')) {
            $quickModules[] = [
                'name' => 'Denah Meja',
                'route' => route('pos.tables.index'),
                'icon' => 'layout-grid',
                'color' => '#34C759',
                'desc' => 'Status meja & nomor reservasi',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('inventory.view') || Context::hasPermission('warehouse.view')) {
            $quickModules[] = [
                'name' => 'Gudang & Stok',
                'route' => route('warehouse.index'),
                'icon' => 'warehouse',
                'color' => '#AF52DE',
                'desc' => 'Cek mutasi stok & penerimaan barang',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('products.view')) {
            $quickModules[] = [
                'name' => 'Katalog Produk',
                'route' => route('products.index'),
                'icon' => 'package',
                'color' => '#0A84FF',
                'desc' => 'Daftar produk, menu & resep',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('materials.view')) {
            $quickModules[] = [
                'name' => 'Bahan Baku',
                'route' => route('materials.index'),
                'icon' => 'boxes',
                'color' => '#30B0C7',
                'desc' => 'Daftar bahan baku & harga beli',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('customers.view')) {
            $quickModules[] = [
                'name' => 'Data Pelanggan',
                'route' => route('customers.index'),
                'icon' => 'users',
                'color' => '#32D74B',
                'desc' => 'Kontak pelanggan & keanggotaan',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('finance.cash_bank')) {
            $quickModules[] = [
                'name' => 'Kas & Bank',
                'route' => route('finance.cash-bank.index'),
                'icon' => 'wallet',
                'color' => '#30D158',
                'desc' => 'Mutasi kas masuk dan kas keluar',
            ];
        }

        if (Context::isOwner() || Context::hasPermission('ai.access')) {
            $quickModules[] = [
                'name' => 'Asisten AI',
                'route' => route('pos.ai.index'),
                'icon' => 'sparkles',
                'color' => '#BF5AF2',
                'desc' => 'Konsultasi pintar & analisis',
            ];
        }

        return view('app.portal.index', compact(
            'business',
            'user',
            'membership',
            'todayAttendance',
            'recentAttendances',
            'presentDaysCount',
            'onTimeDaysCount',
            'lateDaysCount',
            'totalWorkMinutes',
            'totalWorkHours',
            'location',
            'defaultLat',
            'defaultLng',
            'cityName',
            'quickModules'
        ));
    }
}
