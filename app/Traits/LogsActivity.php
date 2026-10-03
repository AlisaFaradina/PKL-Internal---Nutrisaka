<?php

namespace App\Traits;

use App\Models\ActivityLog;

/**
 * Trait untuk otomatis mencatat perubahan data (created/updated/deleted) ke activity_logs.
 * Gunakan: `use LogsActivity;` di Model yang ingin di-audit.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            ActivityLog::record(
                'created',
                static::class,
                $model->id,
                class_basename(static::class) . ' baru dibuat: ' . ($model->name ?? $model->invoice_number ?? $model->order_number ?? '#' . $model->id),
                null,
                $model->getAttributes()
            );
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = collect($model->getOriginal())->only(array_keys($changes))->toArray();

            // Jangan log perubahan timestamps saja
            $meaningfulChanges = collect($changes)->except(['updated_at'])->toArray();
            if (empty($meaningfulChanges)) {
                return;
            }

            ActivityLog::record(
                'updated',
                static::class,
                $model->id,
                class_basename(static::class) . ' diperbarui: #' . $model->id,
                $original,
                $meaningfulChanges
            );
        });

        static::deleted(function ($model) {
            ActivityLog::record(
                'deleted',
                static::class,
                $model->id,
                class_basename(static::class) . ' dihapus: ' . ($model->name ?? $model->invoice_number ?? '#' . $model->id),
                $model->getAttributes(),
                null
            );
        });
    }
}
