<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

class ApiKey extends PersonalAccessToken
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Property hook for getting and setting allowed domains seamlessly using PHP 8.4 syntax.
     */
    public array $domains {
        get => json_decode($this->attributes['allowed_domains'] ?? '[]', true) ?: [];
        set(array $value) => $this->attributes['allowed_domains'] = json_encode($value);
    }
}
