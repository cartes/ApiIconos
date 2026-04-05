<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Icono extends Model
{
    use BelongsToTenant;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'url',
        'carpetaId',
        'empresaId',
        'subidoPor',
        'fechaSubida',
        'etiqueta',
        'orden',
        'clicks',
        'tenant_id',
    ];

    public function historialClicks()
    {
        return $this->hasMany(IconoClick::class, 'icono_id');
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
