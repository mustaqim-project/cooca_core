<?php

declare(strict_types=1);

namespace App\Domain\Finance;

use App\Models\Business;
use App\Models\MerchantPayoutBankAccount;
use DomainException;
use Illuminate\Support\Facades\DB;

final class MerchantPayoutAccountService
{
    /**
     * Daftarkan rekening bank penarikan baru dengan validasi ketat kecocokan nama pemilik (Anti-Third-Party Payout).
     *
     * @param array{
     *     bank_code: string,
     *     bank_name: string,
     *     account_number: string,
     *     account_holder_name: string,
     *     location_id?: string|null,
     *     is_primary?: bool
     * } $data
     */
    public function registerAccount(Business $business, array $data, ?string $userId = null): MerchantPayoutBankAccount
    {
        $owner = $business->owner;
        $ownerName = trim((string) ($owner?->name ?? $business->name));
        $inputHolderName = trim((string) ($data['account_holder_name'] ?? ''));

        if (empty($inputHolderName)) {
            throw new DomainException('Nama pemilik rekening wajib diisi.');
        }

        // Normalisasi nama (hilangkan karakter khusus, spasi ganda, dan case-insensitive)
        $cleanOwner = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ownerName));
        $cleanHolder = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $inputHolderName));

        // Strict Owner Identity Matching:
        // Nama pada rekening bank penarikan wajib identik atau mengandung nama pemilik akun bisnis terdaftar
        $isMatch = ($cleanOwner === $cleanHolder)
            || (strlen($cleanOwner) >= 4 && str_contains($cleanHolder, $cleanOwner))
            || (strlen($cleanHolder) >= 4 && str_contains($cleanOwner, $cleanHolder));

        if (! $isMatch) {
            throw new DomainException(
                "Keamanan Penarikan: Nama pemilik rekening ({$inputHolderName}) tidak sesuai dengan nama pemilik usaha terdaftar ({$ownerName}). Demi mencegah fraud, pencairan dana hanya dapat dilakukan ke rekening atas nama pemilik bisnis resmi."
            );
        }

        return DB::transaction(function () use ($business, $data, $inputHolderName, $userId): MerchantPayoutBankAccount {
            $isPrimary = (bool) ($data['is_primary'] ?? false);

            // Jika rekening pertama, otomatis jadikan primary
            $existingCount = MerchantPayoutBankAccount::where('business_id', $business->id)->count();
            if ($existingCount === 0) {
                $isPrimary = true;
            } elseif ($isPrimary) {
                // Reset primary akun lainnya
                MerchantPayoutBankAccount::where('business_id', $business->id)->update(['is_primary' => false]);
            }

            return MerchantPayoutBankAccount::create([
                'business_id' => $business->id,
                'location_id' => $data['location_id'] ?? null,
                'bank_code' => strtoupper(trim((string) $data['bank_code'])),
                'bank_name' => trim((string) $data['bank_name']),
                'account_number' => preg_replace('/[^0-9]/', '', (string) $data['account_number']),
                'account_holder_name' => strtoupper($inputHolderName),
                'is_primary' => $isPrimary,
                'is_verified' => true,
                'verified_at' => now(),
                'created_by' => $userId,
            ]);
        });
    }

    /**
     * Jadikan rekening tertentu sebagai rekening utama penarikan.
     */
    public function setPrimary(MerchantPayoutBankAccount $account): MerchantPayoutBankAccount
    {
        return DB::transaction(function () use ($account): MerchantPayoutBankAccount {
            MerchantPayoutBankAccount::where('business_id', $account->business_id)
                ->where('id', '!=', $account->id)
                ->update(['is_primary' => false]);

            $account->update(['is_primary' => true]);

            return $account->fresh();
        });
    }

    /**
     * Hapus rekening penarikan.
     */
    public function deleteAccount(MerchantPayoutBankAccount $account): void
    {
        DB::transaction(function () use ($account): void {
            $wasPrimary = $account->is_primary;
            $bizId = $account->business_id;

            $account->delete();

            // Jika yang dihapus adalah primary, jadikan akun tertua yang tersisa sebagai primary baru
            if ($wasPrimary) {
                $next = MerchantPayoutBankAccount::where('business_id', $bizId)->oldest()->first();
                $next?->update(['is_primary' => true]);
            }
        });
    }
}
