<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = ['nombre', 'precio_mensual', 'max_usuarios', 'max_iconos', 'activo'];

    protected $casts = ['activo' => 'boolean', 'precio_mensual' => 'decimal:2'];

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class);
    }
}
