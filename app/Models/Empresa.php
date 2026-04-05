<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Empresa extends Model
{
    use BelongsToTenant;

    protected $primaryKey = 'id';

    public $incrementing = false; // Usamos UUID

    protected $keyType = 'string';

    protected $fillable = ['id', 'nombre', 'fechaCreacion', 'tenant_id'];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
