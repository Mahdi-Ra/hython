<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (self $model) {
            $model->auditLog('created', null, $model->getAttributes());
        });

        static::updated(function (self $model) {
            $model->auditLog('updated', $model->getRawOriginal(), $model->getAttributes());
        });

        static::deleted(function (self $model) {
            $model->auditLog('deleted', $model->getAttributes(), null);
        });

        static::restored(function (self $model) {
            $model->auditLog('restored', null, $model->getAttributes());
        });
    }

    protected function auditLog(string $event, ?array $oldValues, ?array $newValues): void
    {
        $exclude = $this->getAuditExcludeKeys();
        if ($oldValues !== null) {
            $oldValues = array_diff_key($oldValues, array_flip($exclude));
        }
        if ($newValues !== null) {
            $newValues = array_diff_key($newValues, array_flip($exclude));
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * کلیدهایی که در لاگ audit ذخیره نشوند (امنیت).
     */
    protected function getAuditExcludeKeys(): array
    {
        return ['password', 'remember_token'];
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject');
    }
}
