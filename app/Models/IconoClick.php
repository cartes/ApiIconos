<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IconoClick extends Model
{
    protected $table = 'icon_clicks';

    protected $fillable = [
        'user_email',
        'icono_id',
        'tenant_id',
    ];

    public function icono()
    {
        return $this->belongsTo(Icono::class, 'icono_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_email', 'email');
    }
}
