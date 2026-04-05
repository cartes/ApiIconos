<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    use HasApiTokens, HasDomains;

    public function suscripcion()
    {
        return $this->hasOne(Suscripcion::class, 'tenant_id');
    }
}
