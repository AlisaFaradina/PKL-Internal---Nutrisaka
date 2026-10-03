<?php

namespace App\Traits;

use App\Models\AppSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

class SimulationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        try {
            if (Schema::hasTable('app_settings')) {
                if (AppSetting::isRealMode()) {
                    $builder->where($model->getTable() . '.is_simulation', false);
                } else {
                    $builder->where($model->getTable() . '.is_simulation', true);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika database dalam proses migrasi awal
        }
    }
}

trait HasSimulationMode
{
    public static function bootHasSimulationMode(): void
    {
        static::addGlobalScope(new SimulationScope());

        static::creating(function ($model) {
            if (!isset($model->attributes['is_simulation'])) {
                try {
                    $model->is_simulation = AppSetting::isDemoMode();
                } catch (\Throwable $e) {
                    $model->is_simulation = false;
                }
            }
        });
    }

    public function scopeWithoutSimulationScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope(SimulationScope::class);
    }

    public function scopeOnlySimulation(Builder $query): Builder
    {
        return $query->withoutGlobalScope(SimulationScope::class)->where($this->getTable() . '.is_simulation', true);
    }

    public function scopeOnlyReal(Builder $query): Builder
    {
        return $query->withoutGlobalScope(SimulationScope::class)->where($this->getTable() . '.is_simulation', false);
    }
}
