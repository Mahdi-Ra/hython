<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterApproval extends Model
{
    use Auditable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'در انتظار تایید',
        self::STATUS_APPROVED => 'تایید شد',
        self::STATUS_REJECTED => 'رد شد',
    ];

    protected $fillable = [
        'letter_id',
        'requested_by_user_id',
        'approver_id',
        'status',
        'request_note',
        'decision_note',
        'requested_at',
        'decided_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: '—');
    }
}
