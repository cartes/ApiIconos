<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'name',
        'description',
        'price',
        'storage_limit_gb',
        'mp_plan_id',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'storage_limit_gb' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function legacyTenantOneDefaults(): array
    {
        return [
            'name' => 'Plan Medio',
            'description' => 'Compatibilidad heredada para Tenant 1: publica iconos mediante URLs externas y no consume almacenamiento fisico.',
            'price' => 0,
            'storage_limit_gb' => 0,
            'mp_plan_id' => null,
            'is_active' => true,
        ];
    }
}
