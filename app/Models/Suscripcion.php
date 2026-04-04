<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    protected $fillable = ['tenant_id', 'plan_id', 'estado', 'fecha_inicio', 'fecha_vencimiento', 'notas'];

    protected $casts = ['fecha_inicio' => 'date', 'fecha_vencimiento' => 'date'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
