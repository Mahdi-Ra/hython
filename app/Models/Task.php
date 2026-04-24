<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use Auditable, HasUuid, SoftDeletes;
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'done';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
    ];
    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'در انتظار',
        self::STATUS_IN_PROGRESS => 'در حال انجام',
        self::STATUS_COMPLETED => 'انجام‌شده',
    ];

    public const PRIORITY_LABELS = [
        self::PRIORITY_LOW => 'پایین',
        self::PRIORITY_NORMAL => 'عادی',
        self::PRIORITY_HIGH => 'بالا',
        self::PRIORITY_URGENT => 'فوری',
    ];

    protected $fillable = [
        'letter_id',
        'assigned_to',
        'assigned_to_user_id',
        'created_by_user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class);
    }

    public function assignedTo(): BelongsTo
    {
        $foreignKey = array_key_exists('assigned_to_user_id', $this->attributes)
            ? 'assigned_to_user_id'
            : 'assigned_to';

        return $this->belongsTo(User::class, $foreignKey);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function followers(): MorphMany
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_to_user_id', $userId)
                ->orWhere('assigned_to', $userId);
        });
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeUrgent($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_URGENT, self::PRIORITY_HIGH])
            ->whereNull('completed_at');
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public static function statusLabel(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: '—');
    }

    public static function priorityLabel(?string $priority): string
    {
        return self::PRIORITY_LABELS[$priority] ?? ($priority ?: '—');
    }

    public function isFollowedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->relationLoaded('followers')) {
            return $this->followers->contains(fn (Follow $follow) => (int) $follow->user_id === (int) $user->id);
        }

        return $this->followers()->where('user_id', $user->id)->exists();
    }
}
