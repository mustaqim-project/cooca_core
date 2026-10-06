<?php

declare(strict_types=1);

namespace App\Domain\Mcp\Tools;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Finance\CashLedgerService;
use App\Domain\Storage\OwnerStorageQuotaService;
use App\Domain\Storage\StorageTrackingService;
use App\Domain\Storage\TenantStorage;
use App\Models\AuditLog;
use App\Models\Business;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\StorageFile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class FinanceRecordExpenseTool implements McpToolInterface
{
    public function __construct(
        private readonly AutoJournalService $journalService = new AutoJournalService(),
        private readonly CashLedgerService $cashLedgerService = new CashLedgerService()
    ) {}

    public function getName(): string
    {
        return 'finance_record_expense';
    }

    public function getDescription(): string
    {
        return 'Mencatat bukti pengeluaran/beban operasional bisnis, menyimpan file nota ke vault tenant, dan otomatis membukukan jurnal akuntansi kas keluar.';
    }

    public function getRequiredAbility(): string
    {
        return 'mcp:expenses:write';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'expense_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Tanggal nota dalam format YYYY-MM-DD. Jika tidak tertera, gunakan tanggal hari ini.',
                ],
                'category' => [
                    'type' => 'string',
                    'description' => 'Kategori beban operasional (misal: Bahan Baku, Operasional, Transportasi & Bensin, Gaji & Upah, Sewa & Utilitas, Pemasaran, Lain-lain).',
                ],
                'amount' => [
                    'type' => 'number',
                    'minimum' => 1,
                    'description' => 'Nominal total pengeluaran dalam Rupiah (angka murni tanpa titik/koma).',
                ],
                'payment_method' => [
                    'type' => 'string',
                    'enum' => ['petty_cash', 'cash', 'bank_transfer'],
                    'default' => 'cash',
                    'description' => 'Metode pembayaran kasir atau rekening asal pembayaran.',
                ],
                'description' => [
                    'type' => 'string',
                    'maxLength' => 255,
                    'description' => 'Keterangan rincian barang/jasa yang dibeli beserta nama toko/merchant.',
                ],
                'receipt_image_base64' => [
                    'type' => 'string',
                    'description' => 'Optional: String base64 gambar nota (JPEG/PNG/WEBP/PDF) untuk diarsipkan sebagai bukti resmi.',
                ],
                'receipt_file_name' => [
                    'type' => 'string',
                    'default' => 'nota.jpg',
                    'description' => 'Nama file bukti nota.',
                ],
                'idempotency_key' => [
                    'type' => 'string',
                    'description' => 'Kunci unik (UUID) untuk mencegah pengulangan transaksi nota yang sama.',
                ],
            ],
            'required' => ['expense_date', 'category', 'amount', 'description'],
        ];
    }

    public function execute(Business $business, ?User $user, array $arguments = []): array
    {
        $amount = (float) ($arguments['amount'] ?? 0);
        if ($amount <= 0) {
            throw new InvalidArgumentException('Nominal pengeluaran harus lebih besar dari 0.');
        }

        $expenseDate = trim((string) ($arguments['expense_date'] ?? date('Y-m-d')));
        $category = trim((string) ($arguments['category'] ?? 'Operasional'));
        $description = trim((string) ($arguments['description'] ?? ''));
        $paymentMethod = in_array($arguments['payment_method'] ?? 'cash', ['cash', 'bank_transfer', 'petty_cash'], true)
            ? (string) $arguments['payment_method']
            : 'cash';

        $idempotencyKey = ! empty($arguments['idempotency_key']) ? trim((string) $arguments['idempotency_key']) : null;

        // Idempotency check: jika key sudah tercatat dalam 24 jam terakhir pada keterangan/audit log
        if ($idempotencyKey !== null) {
            $existing = Expense::where('business_id', $business->id)
                ->where('description', 'like', "%[idempotency:{$idempotencyKey}]%")
                ->first();

            if ($existing !== null) {
                return [
                    'status' => 'success',
                    'idempotent_duplicate' => true,
                    'expense_id' => $existing->id,
                    'expense_number' => $existing->expense_number,
                    'amount' => (float) $existing->amount,
                    'message' => "Transaksi nota sudah pernah dicatat sebelumnya dengan No. {$existing->expense_number}.",
                ];
            }
        }

        // Simpan lampiran nota base64 jika ada
        $receiptPath = null;
        if (! empty($arguments['receipt_image_base64'])) {
            $base64 = (string) $arguments['receipt_image_base64'];
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64)[1];
            }
            $binary = base64_decode($base64, true);

            if ($binary !== false && strlen($binary) > 0) {
                $ext = 'jpg';
                if (! empty($arguments['receipt_file_name'])) {
                    $ext = pathinfo((string) $arguments['receipt_file_name'], PATHINFO_EXTENSION) ?: 'jpg';
                }

                $dir = TenantStorage::privateDir($business, TenantStorage::FOLDER_EXPENSES);
                $fileName = Str::uuid()->toString() . '.' . $ext;
                $receiptPath = "{$dir}/{$fileName}";

                Storage::disk('local')->put($receiptPath, $binary);

                // Quota tracking
                try {
                    $owner = app(OwnerStorageQuotaService::class)->ownerForBusiness($business);
                    if ($owner !== null) {
                        app(StorageTrackingService::class)->recordUpload(
                            file: null,
                            filePath: $receiptPath,
                            category: StorageFile::CATEGORY_EXPENSE_RECEIPT,
                            module: 'finance',
                            owner: $owner,
                            business: $business,
                            uploader: $user,
                            disk: 'local',
                            fileSizeBytes: strlen($binary),
                            mimeType: 'image/jpeg',
                            originalFileName: $arguments['receipt_file_name'] ?? 'nota.jpg'
                        );
                    }
                } catch (\Throwable) {
                    // Fail gracefully on non-blocking storage quota logging
                }
            }
        }

        $finalDescription = $idempotencyKey !== null
            ? "{$description} [idempotency:{$idempotencyKey}]"
            : $description;

        $expense = DB::transaction(function () use ($business, $user, $expenseDate, $category, $amount, $paymentMethod, $finalDescription, $receiptPath) {
            $expenseNumber = 'EXP-' . date('Ymd') . '-' . rand(100, 999);

            $expense = Expense::create([
                'business_id' => $business->id,
                'expense_number' => $expenseNumber,
                'expense_date' => $expenseDate,
                'category' => $category,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'description' => $finalDescription,
                'receipt_image_path' => $receiptPath,
                'recorded_by' => $user?->id,
            ]);

            // Auto-Journaling
            $this->journalService->ensureStandardAccounts($business);
            $recorder = $user ?? $business->memberships()->with('user')->first()?->user ?? User::first();
            if ($recorder !== null) {
                $this->journalService->recordExpenseJournal($expense, $recorder);
            }

            // Mutasi Kas Toko / Petty Cash
            $cashAccount = CashAccount::where('business_id', $business->id)
                ->where('is_active', true)
                ->first();

            $this->cashLedgerService->recordOutflow(
                $business,
                $amount,
                'expense',
                $expense->id,
                "Pengeluaran #{$expense->expense_number}: {$category}",
                $paymentMethod,
                $user?->id,
                $cashAccount
            );

            // Audit Log
            AuditLog::create([
                'business_id' => $business->id,
                'user_id' => $user?->id,
                'action' => 'mcp.finance_record_expense',
                'auditable_type' => Expense::class,
                'auditable_id' => $expense->id,
                'old_values' => null,
                'new_values' => [
                    'expense_number' => $expense->expense_number,
                    'amount' => $expense->amount,
                    'category' => $expense->category,
                    'receipt_attached' => $receiptPath !== null,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent() ?: 'COOCA-MCP-Agent',
            ]);

            return $expense;
        });

        return [
            'status' => 'success',
            'expense_id' => $expense->id,
            'expense_number' => $expense->expense_number,
            'amount' => (float) $expense->amount,
            'category' => $expense->category,
            'expense_date' => (string) $expense->expense_date,
            'receipt_attached' => $receiptPath !== null,
            'message' => "Nota berhasil dibukukan dengan No. {$expense->expense_number} sebesar Rp " . number_format((float) $expense->amount, 0, ',', '.') . ". Jurnal akuntansi kas keluar sudah otomatis terbit.",
        ];
    }
}
