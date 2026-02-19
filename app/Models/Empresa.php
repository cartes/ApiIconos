<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Empresa extends Model
{
    protected $primaryKey = 'id';

    public $incrementing = false; // Usamos UUID

    protected $keyType = 'string';

    protected $fillable = ['id', 'nombre', 'fechaCreacion'];

    protected static function booted()
    {
        static::creating(fn ($model) => $model->id = (string) Str::uuid());
    }
}
