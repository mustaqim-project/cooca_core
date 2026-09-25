<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\BelongsToBusiness;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPrintJob extends Model
{
    use BelongsToBusiness, HasFactory, HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PRINTED = 'printed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_KITCHEN_ORDER = 'kitchen_order';
    public const TYPE_BAR_ORDER = 'bar_order';
    public const TYPE_SHIFT_REPORT = 'shift_report';
    public const TYPE_TEST_PRINT = 'test_print';
    public const TYPE_CASH_DRAWER_PULSE = 'cash_drawer_pulse';

    protected $fillable = [
        'business_id',
        'location_id',
        'printer_id',
        'document_type',
        'document_id',
        'payload_raw',
        'status',
        'attempts',
        'error_message',
        'printed_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'printed_at' => 'datetime',
        ];
    }

    /**
     * Printer relationship.
     *
     * @return BelongsTo<PosPrinter, $this>
     */
    public function printer(): BelongsTo
    {
        return $this->belongsTo(PosPrinter::class, 'printer_id');
    }

    /**
     * Location relationship.
     *
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * User who initiated the print job.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mark job as printed.
     */
    public function markAsPrinted(): void
    {
        $this->update([
            'status' => self::STATUS_PRINTED,
            'printed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark job as failed with error.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'attempts' => $this->attempts + 1,
            'error_message' => $errorMessage,
        ]);
    }
}
