<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Carpeta extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    // Mantenemos los nombres de tu code.gs
    protected $fillable = ['id', 'nombre', 'empresaId', 'creadoPor', 'orden'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
