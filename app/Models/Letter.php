<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letter extends Model
{
    use Auditable, HasUuid, SoftDeletes;
    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const TYPES = [self::TYPE_INTERNAL, self::TYPE_EXTERNAL];
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_ARCHIVED,
    ];
    public const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    protected $fillable = [
        'user_id',
        'department_id',
        'type',
        'subject',
        'body',
        'priority',
        'due_date',
        'status',
        'reference_number',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(LetterReferral::class, 'letter_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'letter_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'letter_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendingReferralsTo($query, int $userId)
    {
        return $query->whereHas('referrals', function ($q) use ($userId) {
            $q->where('to_user_id', $userId)->where('status', LetterReferral::STATUS_PENDING);
        });
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT)
            ->orWhere(function ($q) {
                $q->where('priority', self::PRIORITY_HIGH)
                    ->where('due_date', '<=', now()->addDays(3));
            });
    }

    public function scopeNotDraft($query)
    {
        return $query->where('status', '!=', self::STATUS_DRAFT);
    }
}
