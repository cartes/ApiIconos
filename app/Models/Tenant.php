<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant
{
    use HasDomains;

    public function suscripcion()
    {
        return $this->hasOne(Suscripcion::class, 'tenant_id');
    }
}
