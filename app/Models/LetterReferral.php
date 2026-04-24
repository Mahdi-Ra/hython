<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LetterReferral extends Model
{
    use Auditable, HasUuid, SoftDeletes;
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'در انتظار پاسخ',
        self::STATUS_ACCEPTED => 'پذیرفته شده',
        self::STATUS_COMPLETED => 'انجام شده',
        self::STATUS_REJECTED => 'رد شده',
    ];

    protected $fillable = [
        'uuid',
        'letter_id',
        'from_user_id',
        'to_user_id',
        'assigned_by_user_id',
        'status',
        'note',
        'response_note',
        'referred_at',
        'responded_at',
    ];

    protected $casts = [
        'referred_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: '—');
    }
}
