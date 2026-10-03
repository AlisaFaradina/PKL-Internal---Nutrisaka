<?php

namespace App\Models;

use App\Traits\HasSimulationMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasSimulationMode;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'before_stock',
        'after_stock',
        'reference_type',
        'reference_id',
        'notes',
        'is_simulation',
    ];

    protected $casts = [
        'quantity' => 'float',
        'before_stock' => 'float',
        'after_stock' => 'float',
        'is_simulation' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'in' => 'Masuk (+)',
            'out' => 'Keluar (-)',
            'adjustment' => 'Penyesuaian (Opname)',
            default => ucfirst($this->type),
        };
    }
}
