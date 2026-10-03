<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\HasSimulationMode;

class Category extends Model
{
    use HasSimulationMode;

    protected $fillable = [
        'name',
        'icon',
        'description',
        'is_simulation',
    ];

    protected $casts = [
        'is_simulation' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
