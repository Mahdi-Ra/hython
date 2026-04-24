<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_EMPLOYEE = 'employee';

    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_MANAGER,
        self::ROLE_EMPLOYEE,
    ];

    public const PERMISSION_MANAGEMENT_ACCESS = 'management.access';
    public const PERMISSION_DEPARTMENTS_MANAGE = 'departments.manage';
    public const PERMISSION_PERMISSIONS_MANAGE = 'permissions.manage';
    public const PERMISSION_AUTOMATIONS_MANAGE = 'automations.manage';
    public const PERMISSION_AUDIT_VIEW = 'audit.view';
    public const PERMISSION_EMPLOYEES_MANAGE = 'employees.manage';
    public const PERMISSION_WORKLOAD_VIEW = 'workload.view';
    public const PERMISSION_LETTERS_CREATE = 'letters.create';
    public const PERMISSION_LETTERS_VIEW_DEPARTMENT = 'letters.view_department';
    public const PERMISSION_LETTERS_VIEW_ALL = 'letters.view_all';
    public const PERMISSION_LETTERS_MANAGE_STATUS = 'letters.manage_status';
    public const PERMISSION_LETTERS_REFER = 'letters.refer';
    public const PERMISSION_LETTERS_REQUEST_APPROVAL = 'letters.request_approval';
    public const PERMISSION_LETTERS_APPROVE = 'letters.approve';
    public const PERMISSION_TASKS_CREATE = 'tasks.create';
    public const PERMISSION_TASKS_VIEW_DEPARTMENT = 'tasks.view_department';
    public const PERMISSION_TASKS_VIEW_ALL = 'tasks.view_all';
    public const PERMISSION_TASKS_MANAGE_DEPARTMENT = 'tasks.manage_department';
    public const PERMISSION_TASKS_MANAGE_ALL = 'tasks.manage_all';
    public const PERMISSION_REPORTS_VIEW = 'reports.view';
    public const PERMISSION_KPIS_VIEW = 'kpis.view';

    public const PERMISSION_LABELS = [
        self::PERMISSION_MANAGEMENT_ACCESS => 'دسترسی به پنل مدیریت',
        self::PERMISSION_DEPARTMENTS_MANAGE => 'مدیریت دپارتمان‌ها',
        self::PERMISSION_PERMISSIONS_MANAGE => 'مدیریت نقش و دسترسی',
        self::PERMISSION_AUTOMATIONS_MANAGE => 'مدیریت اتوماسیون‌ها',
        self::PERMISSION_AUDIT_VIEW => 'مشاهده گزارش فعالیت‌ها',
        self::PERMISSION_EMPLOYEES_MANAGE => 'مدیریت کارمندان',
        self::PERMISSION_WORKLOAD_VIEW => 'مشاهده Workload',
        self::PERMISSION_LETTERS_CREATE => 'ایجاد نامه',
        self::PERMISSION_LETTERS_VIEW_DEPARTMENT => 'مشاهده نامه‌های دپارتمان',
        self::PERMISSION_LETTERS_VIEW_ALL => 'مشاهده همه نامه‌ها',
        self::PERMISSION_LETTERS_MANAGE_STATUS => 'تغییر وضعیت نامه',
        self::PERMISSION_LETTERS_REFER => 'ارجاع نامه',
        self::PERMISSION_LETTERS_REQUEST_APPROVAL => 'درخواست تایید نامه',
        self::PERMISSION_LETTERS_APPROVE => 'تایید یا رد نامه',
        self::PERMISSION_TASKS_CREATE => 'ایجاد وظیفه',
        self::PERMISSION_TASKS_VIEW_DEPARTMENT => 'مشاهده وظایف دپارتمان',
        self::PERMISSION_TASKS_VIEW_ALL => 'مشاهده همه وظایف',
        self::PERMISSION_TASKS_MANAGE_DEPARTMENT => 'مدیریت وظایف دپارتمان',
        self::PERMISSION_TASKS_MANAGE_ALL => 'مدیریت همه وظایف',
        self::PERMISSION_REPORTS_VIEW => 'مشاهده گزارش‌ها',
        self::PERMISSION_KPIS_VIEW => 'مشاهده KPI',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class);
    }

    public function referralsFrom(): HasMany
    {
        return $this->hasMany(LetterReferral::class, 'from_user_id');
    }

    public function referralsTo(): HasMany
    {
        return $this->hasMany(LetterReferral::class, 'to_user_id');
    }

    public function referralsAssignedBy(): HasMany
    {
        return $this->hasMany(LetterReferral::class, 'assigned_by_user_id');
    }

    public function tasksAssigned(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
    }

    public function tasksCreated(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by_user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(LetterApproval::class, 'requested_by_user_id');
    }

    public function approvalsToReview(): HasMany
    {
        return $this->hasMany(LetterApproval::class, 'approver_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'receiver_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function canAccessManagementPanel(): bool
    {
        return $this->isAdmin() || $this->hasPermission(self::PERMISSION_MANAGEMENT_ACCESS);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return in_array($permission, $this->effectivePermissions(), true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function effectivePermissions(): array
    {
        $defaults = self::defaultPermissionsForRole($this->role);
        $custom = array_values(array_filter($this->permissions ?? []));

        return array_values(array_unique(array_merge($defaults, $custom)));
    }

    public static function defaultPermissionsForRole(?string $role): array
    {
        return match ($role) {
            self::ROLE_MANAGER => [
                self::PERMISSION_LETTERS_CREATE,
                self::PERMISSION_LETTERS_VIEW_DEPARTMENT,
                self::PERMISSION_LETTERS_MANAGE_STATUS,
                self::PERMISSION_LETTERS_REFER,
                self::PERMISSION_LETTERS_REQUEST_APPROVAL,
                self::PERMISSION_LETTERS_APPROVE,
                self::PERMISSION_TASKS_CREATE,
                self::PERMISSION_TASKS_VIEW_DEPARTMENT,
                self::PERMISSION_TASKS_MANAGE_DEPARTMENT,
                self::PERMISSION_REPORTS_VIEW,
                self::PERMISSION_KPIS_VIEW,
                self::PERMISSION_WORKLOAD_VIEW,
            ],
            self::ROLE_EMPLOYEE => [
                self::PERMISSION_LETTERS_CREATE,
                self::PERMISSION_LETTERS_REQUEST_APPROVAL,
            ],
            default => [],
        };
    }

    public static function permissionGroups(): array
    {
        return [
            'پنل مدیریت' => [
                self::PERMISSION_MANAGEMENT_ACCESS,
                self::PERMISSION_DEPARTMENTS_MANAGE,
                self::PERMISSION_PERMISSIONS_MANAGE,
                self::PERMISSION_AUTOMATIONS_MANAGE,
                self::PERMISSION_AUDIT_VIEW,
                self::PERMISSION_EMPLOYEES_MANAGE,
                self::PERMISSION_WORKLOAD_VIEW,
            ],
            'نامه‌ها' => [
                self::PERMISSION_LETTERS_CREATE,
                self::PERMISSION_LETTERS_VIEW_DEPARTMENT,
                self::PERMISSION_LETTERS_VIEW_ALL,
                self::PERMISSION_LETTERS_MANAGE_STATUS,
                self::PERMISSION_LETTERS_REFER,
                self::PERMISSION_LETTERS_REQUEST_APPROVAL,
                self::PERMISSION_LETTERS_APPROVE,
            ],
            'وظایف' => [
                self::PERMISSION_TASKS_CREATE,
                self::PERMISSION_TASKS_VIEW_DEPARTMENT,
                self::PERMISSION_TASKS_VIEW_ALL,
                self::PERMISSION_TASKS_MANAGE_DEPARTMENT,
                self::PERMISSION_TASKS_MANAGE_ALL,
            ],
            'تحلیل و گزارش' => [
                self::PERMISSION_REPORTS_VIEW,
                self::PERMISSION_KPIS_VIEW,
            ],
        ];
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeManagers($query)
    {
        return $query->where('role', self::ROLE_MANAGER);
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', self::ROLE_EMPLOYEE);
    }
}
