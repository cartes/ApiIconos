<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Icono extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    // Exactamente igual a tu JSON de Iconos
    protected $fillable = [
        'id',
        'url',
        'carpetaId',
        'empresaId',
        'subidoPor',
        'fechaSubida',
        'etiqueta',
        'orden',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
