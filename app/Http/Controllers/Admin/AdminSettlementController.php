<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Finance\PaymentSettlementService;
use App\Domain\Storage\AdminStorage;
use App\Http\Controllers\Controller;
use App\Models\PaymentSettlement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

final class AdminSettlementController extends Controller
{
    public function __construct(
        private readonly PaymentSettlementService $settlementService = new PaymentSettlementService
    ) {}

    /**
     * Display listing of merchant payment settlement requests for platform superadmin.
     */
    public function index(Request $request): View|JsonResponse
    {
        $status = (string) $request->get('status', 'all');
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = PaymentSettlement::with(['business', 'allocations', 'admin', 'reconciler'])
            ->latest('settlement_date');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('settlement_number', 'like', "%{$search}%")
                    ->orWhere('destination_bank', 'like', "%{$search}%")
                    ->orWhereHas('business', function ($bq) use ($search) {
                        $bq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($dateFrom)) {
            $query->whereDate('settlement_date', '>=', $dateFrom);
        }

        if (! empty($dateTo)) {
            $query->whereDate('settlement_date', '<=', $dateTo);
        }

        $settlements = $query->paginate(15)->withQueryString();

        // High-level statistics
        $pendingCount = PaymentSettlement::where('status', PaymentSettlement::STATUS_PENDING)->count();
        $pendingAmount = (float) PaymentSettlement::where('status', PaymentSettlement::STATUS_PENDING)->sum('net_amount');

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $completedThisMonth = PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)
            ->whereDate('settlement_date', '>=', $startOfMonth);

        $completedAmountThisMonth = (float) (clone $completedThisMonth)->sum('net_amount');
        $completedCountThisMonth = (clone $completedThisMonth)->count();

        $totalFeeMdr = (float) PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)->sum('fee_amount');
        $totalGrossAllTime = (float) PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)->sum('gross_amount');

        $stats = [
            'pending_count'              => $pendingCount,
            'pending_amount'             => $pendingAmount,
            'completed_count_this_month' => $completedCountThisMonth,
            'completed_amount_this_month'=> $completedAmountThisMonth,
            'total_fee_mdr'              => $totalFeeMdr,
            'total_gross_all_time'       => $totalGrossAllTime,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'stats'       => $stats,
                'settlements' => $settlements,
            ]);
        }

        return view('admin.settlements.index', compact(
            'settlements',
            'stats',
            'status',
            'search',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Show detail of a merchant settlement payout request.
     */
    public function show(PaymentSettlement $settlement): View|JsonResponse
    {
        $settlement->load(['business', 'allocations', 'admin', 'reconciler']);

        if (request()->wantsJson()) {
            return response()->json([
                'success'    => true,
                'settlement' => $settlement,
            ]);
        }

        return view('admin.settlements.show', compact('settlement'));
    }

    /**
     * Approve payout request: upload payment proof image and complete settlement.
     */
    public function approve(Request $request, PaymentSettlement $settlement): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'proof_image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'proof_image.required' => 'Wajib melampirkan file foto/struk bukti transfer bank ke merchant.',
            'proof_image.image'    => 'File bukti transfer harus berupa format gambar valid (JPG, PNG, atau WEBP).',
            'proof_image.max'      => 'Ukuran file gambar bukti transfer maksimal 5 MB.',
        ]);

        try {
            $file = $request->file('proof_image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = AdminStorage::storePrivateFile($file, AdminStorage::FOLDER_SETTLEMENT_PROOFS, $filename);

            $admin = auth('admin')->user();
            $adminId = $admin?->id;

            $updatedSettlement = $this->settlementService->completePayout(
                settlement: $settlement,
                adminId: $adminId,
                proofImagePath: $path,
                adminNotes: $validated['admin_notes'] ?? null
            );

            $msg = "Pencairan saldo #{$updatedSettlement->settlement_number} sebesar Rp "
                . number_format($updatedSettlement->net_amount, 0, ',', '.')
                . " berhasil disetujui. Bukti transfer telah terbit dan dapat dilihat oleh pemilik bisnis.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => $msg,
                    'settlement' => $updatedSettlement,
                ]);
            }

            return redirect()->route('admin.settlements.show', $settlement->id)->with('success', $msg);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pencairan: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->route('admin.settlements.show', $settlement->id)->withInput()->with('error', 'Gagal memproses pencairan: ' . $e->getMessage());
        }
    }

    /**
     * Reject a payout request and unlock allocated order payments back to merchant.
     */
    public function reject(Request $request, PaymentSettlement $settlement): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ], [
            'rejection_reason.required' => 'Wajib mengisi alasan penolakan pencairan saldo merchant.',
        ]);

        try {
            $admin = auth('admin')->user();
            $adminId = $admin?->id;

            $rejectedSettlement = $this->settlementService->rejectPayout(
                settlement: $settlement,
                adminId: $adminId,
                reason: $validated['rejection_reason']
            );

            $msg = "Pengajuan pencairan #{$rejectedSettlement->settlement_number} telah ditolak. Transaksi pesanan telah dikembalikan ke saldo mengendap merchant.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => $msg,
                    'settlement' => $rejectedSettlement,
                ]);
            }

            return redirect()->route('admin.settlements.show', $settlement->id)->with('success', $msg);
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menolak pengajuan: ' . $e->getMessage(),
                ], 422);
            }

            return redirect()->route('admin.settlements.show', $settlement->id)->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }
}
