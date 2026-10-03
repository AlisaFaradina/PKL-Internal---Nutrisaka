<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
        'device_info',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Helper untuk mencatat aktivitas ke tabel log.
     */
    public static function record(
        string $action,
        string $modelType,
        ?int $modelId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): static {
        return static::create([
            'action' => $action,
            'model_type' => class_basename($modelType),
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'device_info' => gethostname() ?: php_uname('n'),
            'created_at' => now(),
        ]);
    }

    /**
     * Accessor: decode JSON old_values.
     */
    public function getOldValuesArrayAttribute(): ?array
    {
        return $this->old_values ? json_decode($this->old_values, true) : null;
    }

    /**
     * Accessor: decode JSON new_values.
     */
    public function getNewValuesArrayAttribute(): ?array
    {
        return $this->new_values ? json_decode($this->new_values, true) : null;
    }

    /**
     * Label aksi dalam bahasa Indonesia.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            'status_changed' => 'Status Diubah',
            default => ucfirst($this->action),
        };
    }
}
