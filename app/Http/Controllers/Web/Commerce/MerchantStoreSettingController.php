<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Commerce;

use App\Domain\Finance\PaymentSettlementService;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\CommercePaymentMethod;
use App\Models\CommerceStoreSetting;
use App\Models\PaymentSettlement;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\StorageFile;
use App\Support\Context;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MerchantStoreSettingController extends Controller
{
    public function __construct(
        private readonly PaymentSettlementService $settlementService = new PaymentSettlementService,
    ) {}

    /**
     * Display storefront operational and payment settings.
     */
    public function index(): View
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.manage'), 403);

        $setting = CommerceStoreSetting::firstOrCreate(
            ['business_id' => $business->id],
            [
                'is_storefront_enabled' => true,
                'is_discoverable' => true,
                'allow_pickup' => true,
                'allow_delivery' => true,
                'order_auto_cancel_minutes' => 60,
            ]
        );

        $paymentMethods = CommercePaymentMethod::where('business_id', $business->id)
            ->orderBy('sort_order')
            ->get();

        $unsettledData = $this->settlementService->getUnsettledPayments($business);

        $recentSettlements = PaymentSettlement::where('business_id', $business->id)
            ->latest('settlement_date')
            ->take(5)
            ->get();

        $bankAccounts = CashAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $tripayApiKey = (string) (SystemSetting::get('tripay_api_key') ?: config('services.tripay.api_key', env('TRIPAY_API_KEY', '')));
        $tripayMerchantCode = (string) (SystemSetting::get('tripay_merchant_code') ?: config('services.tripay.merchant_code', env('TRIPAY_MERCHANT_CODE', '')));
        $isGatewayConfigured = ! empty($tripayApiKey) && ! empty($tripayMerchantCode);

        return view('app.storefront.settings', compact(
            'business',
            'setting',
            'paymentMethods',
            'unsettledData',
            'recentSettlements',
            'bankAccounts',
            'isGatewayConfigured'
        ));
    }

    /**
     * Update store operational settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.manage'), 403);

        $validated = $request->validate([
            'is_storefront_enabled' => ['boolean'],
            'is_discoverable' => ['boolean'],
            'allow_pickup' => ['boolean'],
            'allow_delivery' => ['boolean'],
            'allow_request_order' => ['boolean'],
            'allow_scheduled_order' => ['boolean'],
            'allow_customer_po' => ['boolean'],
            'allow_reservation' => ['boolean'],
            'allow_custom_date' => ['boolean'],
            'quota_metric' => ['nullable', 'string', 'in:orders,quantity'],
            'preorder_quota_unit' => ['nullable', 'string', 'max:30'],
            'batch_dates_mode' => ['nullable', 'string', 'in:operating_days,custom_dates'],
            'custom_batch_dates' => ['nullable'],
            'lead_time_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'cut_off_time' => ['nullable', 'string', 'max:8'],
            'daily_order_quota' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'order_auto_cancel_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'operating_days' => ['nullable'],
            'available_slots' => ['nullable'],
            'announcement_text' => ['nullable', 'string', 'max:500'],
        ]);

        $operatingDays = $request->input('operating_days');
        if (is_array($operatingDays)) {
            $operatingDays = array_values(array_filter($operatingDays));
        }

        $availableSlotsInput = $request->input('available_slots');
        $availableSlots = is_array($availableSlotsInput)
            ? array_values(array_filter($availableSlotsInput))
            : array_values(array_filter(array_map('trim', explode("\n", (string) $availableSlotsInput))));

        $customBatchDatesInput = $request->input('custom_batch_dates');
        $customBatchDates = null;
        if (is_array($customBatchDatesInput)) {
            $customBatchDates = array_values(array_filter($customBatchDatesInput, fn ($item): bool => ! empty($item['date'])));
        } elseif (is_string($customBatchDatesInput) && trim($customBatchDatesInput) !== '') {
            $lines = explode("\n", $customBatchDatesInput);
            $parsed = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = preg_split('/[:|]/', $line);
                $date = trim($parts[0] ?? '');
                if ($date !== '') {
                    $parsed[] = [
                        'date' => $date,
                        'quota' => isset($parts[1]) && is_numeric(trim($parts[1])) ? (int) trim($parts[1]) : null,
                        'note' => isset($parts[2]) ? trim($parts[2]) : null,
                    ];
                }
            }
            if (! empty($parsed)) {
                $customBatchDates = $parsed;
            }
        }

        $setting = CommerceStoreSetting::firstOrCreate(['business_id' => $business->id]);
        $setting->update([
            'is_storefront_enabled' => $request->boolean('is_storefront_enabled'),
            'is_discoverable' => $request->boolean('is_discoverable'),
            'allow_pickup' => $request->boolean('allow_pickup'),
            'allow_delivery' => $request->boolean('allow_delivery'),
            'allow_request_order' => $request->boolean('allow_request_order'),
            'allow_scheduled_order' => $request->boolean('allow_scheduled_order'),
            'allow_customer_po' => $request->boolean('allow_customer_po'),
            'allow_reservation' => $request->boolean('allow_reservation'),
            'allow_custom_date' => $request->has('allow_custom_date') ? $request->boolean('allow_custom_date') : true,
            'quota_metric' => $validated['quota_metric'] ?? 'orders',
            'preorder_quota_unit' => ! empty($validated['preorder_quota_unit']) ? trim($validated['preorder_quota_unit']) : 'PCS',
            'batch_dates_mode' => $validated['batch_dates_mode'] ?? 'operating_days',
            'custom_batch_dates' => $customBatchDates,
            'operating_days' => ! empty($operatingDays) ? $operatingDays : null,
            'available_slots' => ! empty($availableSlots) ? $availableSlots : null,
            'lead_time_hours' => (int) ($validated['lead_time_hours'] ?? 0),
            'cut_off_time' => ! empty($validated['cut_off_time']) ? $validated['cut_off_time'] : null,
            'daily_order_quota' => (int) ($validated['daily_order_quota'] ?? 0),
            'min_order_amount' => (float) $validated['min_order_amount'],
            'order_auto_cancel_minutes' => (int) $validated['order_auto_cancel_minutes'],
            'announcement_text' => $validated['announcement_text'] ?? null,
        ]);

        return back()->with('success', 'Pengaturan toko online berhasil disimpan.');
    }

    /**
     * Add new manual bank account or QRIS payment method for merchant store.
     */
    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business, 404);
        abort_unless(Context::hasPermission('storefront.manage'), 403);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:bank_transfer,qris'],
            'bank_name' => ['required', 'string', 'max:100'],
            'account_number' => ['nullable', 'required_if:type,bank_transfer', 'string', 'max:64'],
            'account_holder' => ['nullable', 'required_if:type,bank_transfer', 'string', 'max:150'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'qris_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $qrisPath = null;
        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $trackingService = app(StorageTrackingService::class);
            $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
            if ($owner) {
                $trackingService->assertCanUpload($owner, (int) $file->getSize(), 'qris_image');
            }

            $dir = TenantStorage::publicDir($business, TenantStorage::FOLDER_QRIS);
            $qrisPath = $file->store($dir, 'public');

            if ($owner) {
                $trackingService->recordUpload(
                    file: $file,
                    filePath: $qrisPath,
                    category: StorageFile::CATEGORY_QRIS,
                    module: 'commerce',
                    owner: $owner,
                    business: $business,
                    uploader: $request->user()
                );
            }
        }

        CommercePaymentMethod::create([
            'business_id' => $business->id,
            'type' => $validated['type'],
            'bank_name' => $validated['bank_name'],
            'account_number' => $validated['account_number'] ?? null,
            'account_holder' => $validated['account_holder'] ?? null,
            'instructions' => $validated['instructions'] ?? null,
            'qris_image_path' => $qrisPath,
            'is_active' => true,
        ]);

        return back()->with('success', 'Metode pembayaran toko berhasil ditambahkan.');
    }

    /**
     * Toggle active status of a payment method.
     */
    public function togglePaymentMethod(CommercePaymentMethod $paymentMethod): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $paymentMethod->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.manage'), 403);

        $paymentMethod->update(['is_active' => ! $paymentMethod->is_active]);

        return back()->with('success', "Status metode pembayaran {$paymentMethod->bank_name} berhasil diperbarui.");
    }

    /**
     * Delete payment method.
     */
    public function deletePaymentMethod(CommercePaymentMethod $paymentMethod): RedirectResponse
    {
        $business = Context::business();
        abort_unless($business && $paymentMethod->business_id === $business->id, 403);
        abort_unless(Context::hasPermission('storefront.manage'), 403);

        if ($paymentMethod->qris_image_path) {
            app(StorageTrackingService::class)->deleteFile($paymentMethod->qris_image_path, 'public');
        }

        $paymentMethod->delete();

        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }
}
