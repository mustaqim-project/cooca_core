<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Finance;

use App\Domain\Accounting\AccountingReportService;
use App\Domain\Accounting\AutoJournalService;
use App\Domain\Accounting\BankReconciliationService;
use App\Http\Controllers\Controller;
use App\Models\BankStatement;
use App\Models\BankStatementLine;
use App\Models\CashAccount;
use App\Models\ChartOfAccount;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class AccountingWebController extends Controller
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService,
        private readonly AccountingReportService $reportService = new AccountingReportService,
        private readonly BankReconciliationService $reconciliationService = new BankReconciliationService
    ) {}

    /**
     * Tampilkan Pengelola Bagan Akun Kustom (COA Tree Builder).
     */
    public function coaIndex(Request $request): View
    {
        $business = Context::requireBusiness();
        $this->journalService->ensureStandardAccounts($business);

        $query = ChartOfAccount::where('business_id', $business->id)
            ->with(['parent', 'children'])
            ->orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('search')) {
            $search = '%' . trim($request->string('search')->toString()) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', $search)
                    ->orWhere('name', 'like', $search);
            });
        }

        $accounts = $query->get();
        $parentCandidates = ChartOfAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $stats = [
            'total' => $accounts->count(),
            'system' => $accounts->where('is_system', true)->count(),
            'custom' => $accounts->where('is_system', false)->count(),
            'active' => $accounts->where('is_active', true)->count(),
        ];

        return view('app.finance.accounting.coa', compact('business', 'accounts', 'parentCandidates', 'stats'));
    }

    /**
     * Simpan Sub-Akun Kustom Baru.
     */
    public function coaStore(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('chart_of_accounts')->where('business_id', $business->id),
            ],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', Rule::in([
                ChartOfAccount::TYPE_ASSET,
                ChartOfAccount::TYPE_LIABILITY,
                ChartOfAccount::TYPE_EQUITY,
                ChartOfAccount::TYPE_REVENUE,
                ChartOfAccount::TYPE_COGS,
                ChartOfAccount::TYPE_EXPENSE,
            ])],
            'normal_balance' => ['required', 'string', Rule::in(['debit', 'credit'])],
            'parent_id' => ['nullable', 'uuid', Rule::exists('chart_of_accounts', 'id')->where('business_id', $business->id)],
        ]);

        ChartOfAccount::create([
            'business_id' => $business->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'code' => trim($validated['code']),
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'normal_balance' => $validated['normal_balance'],
            'is_system' => false,
            'is_active' => true,
        ]);

        return redirect()->route('finance.coa.index')->with('success', "Akun {$validated['code']} - {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Perbarui Akun COA.
     */
    public function coaUpdate(Request $request, ChartOfAccount $account): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($account->business_id !== $business->id) {
            abort(403);
        }

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (! $account->is_system) {
            $descendantIds = $account->getAllDescendantIds();
            $invalidParentIds = array_merge([$account->id], $descendantIds);

            $rules['code'] = [
                'required',
                'string',
                'max:30',
                Rule::unique('chart_of_accounts')->where('business_id', $business->id)->ignore($account->id),
            ];
            $rules['parent_id'] = [
                'nullable',
                'uuid',
                Rule::notIn($invalidParentIds),
                Rule::exists('chart_of_accounts', 'id')->where('business_id', $business->id),
            ];
        }

        $validated = $request->validate($rules, [
            'parent_id.not_in' => 'Akun induk tidak boleh diarahkan ke diri sendiri atau sub-akun turunan.',
        ]);

        $payload = [
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active', true),
        ];

        if (! $account->is_system) {
            $payload['code'] = trim($validated['code']);
            $payload['parent_id'] = $validated['parent_id'] ?? null;
        }

        $account->update($payload);

        return redirect()->route('finance.coa.index')->with('success', "Akun {$account->code} berhasil diperbarui.");
    }

    /**
     * Hapus Akun Kustom.
     */
    public function coaDestroy(ChartOfAccount $account): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($account->business_id !== $business->id) {
            abort(403);
        }

        if (! $account->canBeDeleted()) {
            $reason = 'Akun tidak dapat dihapus.';
            if ($account->is_system) {
                $reason = 'Akun sistem standar akuntansi terlindungi dan tidak dapat dihapus.';
            } elseif ($account->children()->count() > 0) {
                $count = $account->children()->count();
                $reason = "Akun ini memiliki {$count} sub-akun. Silakan hapus atau pindahkan sub-akun tersebut terlebih dahulu.";
            } elseif ($account->lines()->count() > 0) {
                $reason = 'Akun ini telah memiliki riwayat transaksi mutasi jurnal akuntansi.';
            }

            return back()->with('error', $reason);
        }

        $code = $account->code;
        $account->delete();

        return redirect()->route('finance.coa.index')->with('success', "Akun {$code} berhasil dihapus.");
    }

    /**
     * Tampilkan Laporan Neraca Keuangan resmi (Balance Sheet).
     */
    public function balanceSheet(Request $request): View
    {
        $business = Context::requireBusiness();
        $asOfDate = $request->filled('as_of_date') ? Carbon::parse($request->string('as_of_date')->toString()) : now();

        $sheet = $this->reportService->getBalanceSheet($business, $asOfDate);

        return view('app.finance.accounting.balance-sheet', compact('business', 'sheet', 'asOfDate'));
    }

    /**
     * Tampilkan Laporan Neraca Saldo (Trial Balance).
     */
    public function trialBalance(Request $request): View
    {
        $business = Context::requireBusiness();

        $startDate = $request->filled('start_date') ? Carbon::parse($request->string('start_date')->toString()) : now()->startOfYear();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->string('end_date')->toString()) : now();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $trial = $this->reportService->getTrialBalance($business, $startDate, $endDate);

        return view('app.finance.accounting.trial-balance', compact('business', 'trial', 'startDate', 'endDate'));
    }

    /**
     * Tampilkan Buku Besar Umum (General Ledger Drilldown).
     */
    public function generalLedger(Request $request): View
    {
        $business = Context::requireBusiness();

        $accountId = $request->filled('account_id') ? $request->string('account_id')->toString() : null;
        $startDate = $request->filled('start_date') ? Carbon::parse($request->string('start_date')->toString()) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->string('end_date')->toString()) : now();

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $ledger = $this->reportService->getGeneralLedger($business, $accountId, $startDate, $endDate);

        return view('app.finance.accounting.general-ledger', compact('business', 'ledger', 'startDate', 'endDate', 'accountId'));
    }

    /**
     * Tampilkan Daftar & Lembar Rekonsiliasi Bank.
     */
    public function reconciliationIndex(Request $request): View
    {
        $business = Context::requireBusiness();

        $cashAccounts = CashAccount::where('business_id', $business->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $statements = BankStatement::where('business_id', $business->id)
            ->with(['cashAccount', 'importer'])
            ->latest('statement_date')
            ->get();

        $selectedStatement = null;
        if ($request->filled('statement_id')) {
            $selectedStatement = $statements->firstWhere('id', $request->string('statement_id')->toString());
        }
        if (! $selectedStatement && $statements->isNotEmpty()) {
            $selectedStatement = $statements->first();
        }

        $lines = $selectedStatement
            ? $selectedStatement->lines()->orderBy('transaction_date')->orderBy('created_at')->get()
            : collect();

        return view('app.finance.accounting.reconciliation', compact('business', 'cashAccounts', 'statements', 'selectedStatement', 'lines'));
    }

    /**
     * Unggah Rekening Koran (Format CSV atau Baris Data).
     */
    public function reconciliationUpload(Request $request): RedirectResponse
    {
        $business = Context::requireBusiness();

        $validated = $request->validate([
            'cash_account_id' => ['required', 'uuid', Rule::exists('cash_accounts', 'id')->where('business_id', $business->id)],
            'statement_date' => ['required', 'date'],
            'statement_file' => ['nullable', 'required_without:manual_entries', 'file', 'mimes:csv,txt', 'max:5120'],
            'manual_entries' => ['nullable', 'required_without:statement_file', 'string'],
        ], [
            'statement_file.required_without' => 'Silakan unggah berkas rekening koran (CSV) atau masukkan data mutasi manual.',
            'manual_entries.required_without' => 'Silakan unggah berkas rekening koran (CSV) atau masukkan data mutasi manual.',
        ]);

        $cashAccount = CashAccount::where('business_id', $business->id)->findOrFail($validated['cash_account_id']);
        $statementDate = Carbon::parse($validated['statement_date']);
        $rows = [];
        $filename = 'Input Manual Mutasi';

        if ($request->hasFile('statement_file')) {
            $file = $request->file('statement_file');
            $filename = $file->getClientOriginalName();
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                $header = fgetcsv($handle);
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 3) {
                        // format: Date, Description, Amount, Type (optional debit/credit)
                        $rows[] = [
                            'date' => trim($data[0]),
                            'description' => trim($data[1]),
                            'amount' => $this->parseCurrencyAmount(trim($data[2])),
                            'type' => isset($data[3]) && strtolower(trim($data[3])) === 'debit' ? 'debit' : 'credit',
                            'reference_number' => $data[4] ?? null,
                        ];
                    }
                }
                fclose($handle);
            }
        } elseif ($request->filled('manual_entries')) {
            $lines = explode("\n", $validated['manual_entries']);
            foreach ($lines as $line) {
                $parts = explode(',', trim($line));
                if (count($parts) >= 3) {
                    $rows[] = [
                        'date' => trim($parts[0]),
                        'description' => trim($parts[1]),
                        'amount' => $this->parseCurrencyAmount(trim($parts[2])),
                        'type' => isset($parts[3]) && strtolower(trim($parts[3])) === 'debit' ? 'debit' : 'credit',
                        'reference_number' => isset($parts[4]) ? trim($parts[4]) : null,
                    ];
                }
            }
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ada data mutasi yang valid untuk diimpor. Pastikan format CSV: Tanggal, Keterangan, Nominal, Tipe (debit/credit).');
        }

        $statement = $this->reconciliationService->importStatement(
            $business,
            $cashAccount,
            $filename,
            $statementDate,
            $rows,
            auth()->user()
        );

        return redirect()->route('finance.reconciliations.index', ['statement_id' => $statement->id])
            ->with('success', "Mutasi rekening koran berhasil diimpor ({$statement->total_lines} baris). Sistem telah mencocokkan transaksi otomatis.");
    }

    /**
     * Konfirmasi Rekonsiliasi Baris.
     */
    public function reconciliationMatch(Request $request, BankStatementLine $line): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($line->business_id !== $business->id) {
            abort(403);
        }

        $notes = $request->string('notes')->toString();
        $this->reconciliationService->reconcileLine($line, auth()->user(), $notes);

        return back()->with('success', 'Baris mutasi berhasil direkonsiliasi.');
    }

    /**
     * Batalkan Rekonsiliasi Baris.
     */
    public function reconciliationUnmatch(Request $request, BankStatementLine $line): RedirectResponse
    {
        $business = Context::requireBusiness();
        if ($line->business_id !== $business->id) {
            abort(403);
        }

        $this->reconciliationService->unmatchLine($line);

        return back()->with('success', 'Status rekonsiliasi dibatalkan.');
    }

    /**
     * Parse nominal rupiah cerdas (mendukung format BCA/Mandiri, titik ribuan, desimal koma).
     */
    private function parseCurrencyAmount(string $raw): float
    {
        $cleaned = trim($raw);
        $cleaned = preg_replace('/[^\d,.\-]/', '', $cleaned);
        if (empty($cleaned)) {
            return 0.0;
        }

        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            if (strrpos($cleaned, ',') > strrpos($cleaned, '.')) {
                // contoh: 1.500.000,50 (format Indonesia)
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                // contoh: 1,500,000.50 (format US)
                $cleaned = str_replace(',', '', $cleaned);
            }
        } elseif (str_contains($cleaned, ',')) {
            $commaCount = substr_count($cleaned, ',');
            if ($commaCount === 1 && strlen(substr($cleaned, strrpos($cleaned, ',') + 1)) <= 2) {
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                $cleaned = str_replace(',', '', $cleaned);
            }
        } elseif (str_contains($cleaned, '.')) {
            $dotCount = substr_count($cleaned, '.');
            if ($dotCount === 1 && strlen(substr($cleaned, strrpos($cleaned, '.') + 1)) <= 2) {
                // desimal murni: 1500.50
            } else {
                // ribuan: 1.500.000
                $cleaned = str_replace('.', '', $cleaned);
            }
        }

        return (float) $cleaned;
    }
}
