<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class AccountRecoveryRequest extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const ISSUE_PHONE_LOST = 'phone_lost';
    public const ISSUE_EMAIL_INACCESSIBLE = 'email_inaccessible';
    public const ISSUE_BOTH = 'both';

    protected $fillable = [
        'ticket_number',
        'user_id',
        'business_id',
        'business_name',
        'applicant_name',
        'old_email',
        'old_phone',
        'new_email',
        'new_phone',
        'issue_type',
        'reason_description',
        'identity_card_path',
        'business_proof_path',
        'selfie_proof_path',
        'status',
        'admin_notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'ip_address',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Business, $this>
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /**
     * @return BelongsTo<Admin, $this>
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'rejected_by');
    }

    public function getIssueTypeLabel(): string
    {
        return match ($this->issue_type) {
            self::ISSUE_PHONE_LOST => 'HP Hilang / Nomor WhatsApp Hangus',
            self::ISSUE_EMAIL_INACCESSIBLE => 'Email Terkunci / Tidak Dapat Diakses',
            default => 'HP Hilang & Email Terkunci (Keduanya)',
        };
    }

    public function getIdentityCardUrl(): ?string
    {
        if (! $this->identity_card_path) {
            return null;
        }

        return route('admin.account-recoveries.document', ['recovery' => $this->id, 'type' => 'identity']);
    }

    public function getBusinessProofUrl(): ?string
    {
        if (! $this->business_proof_path) {
            return null;
        }

        return route('admin.account-recoveries.document', ['recovery' => $this->id, 'type' => 'business']);
    }

    public function getSelfieProofUrl(): ?string
    {
        if (! $this->selfie_proof_path) {
            return null;
        }

        return route('admin.account-recoveries.document', ['recovery' => $this->id, 'type' => 'selfie']);
    }

    public function getMaskedNewEmail(): string
    {
        $parts = explode('@', $this->new_email);
        if (count($parts) < 2) {
            return '***';
        }
        $name = $parts[0];
        $domain = $parts[1];
        $visibleLen = max(1, min(3, (int) floor(mb_strlen($name) / 2)));
        $visible = mb_substr($name, 0, $visibleLen);

        return $visible . '***@' . $domain;
    }

    public function getMaskedNewPhone(): string
    {
        $phone = $this->new_phone;
        if (strlen($phone) < 7) {
            return '****';
        }

        return substr($phone, 0, 4) . '****' . substr($phone, -3);
    }
}
