<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRule extends Model
{
    public const EVENT_LETTER_CREATED = 'letter_created';
    public const EVENT_TASK_CREATED = 'task_created';
    public const EVENT_LETTER_OVERDUE = 'letter_overdue';
    public const EVENT_TASK_OVERDUE = 'task_overdue';

    public const ACTION_NOTIFY_USER = 'notify_user';
    public const ACTION_NOTIFY_ROLE = 'notify_role';
    public const ACTION_CREATE_TASK = 'create_task';

    public const EVENTS = [
        self::EVENT_LETTER_CREATED,
        self::EVENT_TASK_CREATED,
        self::EVENT_LETTER_OVERDUE,
        self::EVENT_TASK_OVERDUE,
    ];

    public const ACTIONS = [
        self::ACTION_NOTIFY_USER,
        self::ACTION_NOTIFY_ROLE,
        self::ACTION_CREATE_TASK,
    ];

    public const EVENT_LABELS = [
        self::EVENT_LETTER_CREATED => 'ثبت نامه',
        self::EVENT_TASK_CREATED => 'ایجاد وظیفه',
        self::EVENT_LETTER_OVERDUE => 'معوق شدن نامه',
        self::EVENT_TASK_OVERDUE => 'معوق شدن وظیفه',
    ];

    public const ACTION_LABELS = [
        self::ACTION_NOTIFY_USER => 'اعلان به کاربر',
        self::ACTION_NOTIFY_ROLE => 'اعلان به نقش',
        self::ACTION_CREATE_TASK => 'ایجاد وظیفه',
    ];

    protected $fillable = [
        'name',
        'event',
        'department_id',
        'priority',
        'action',
        'target_role',
        'target_user_id',
        'task_title_template',
        'task_description_template',
        'task_priority',
        'due_in_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'due_in_days' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function eventLabel(?string $event): string
    {
        return self::EVENT_LABELS[$event] ?? ($event ?: '—');
    }

    public static function actionLabel(?string $action): string
    {
        return self::ACTION_LABELS[$action] ?? ($action ?: '—');
    }
}
