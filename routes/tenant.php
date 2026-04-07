<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes — Identificación por dominio (DESACTIVADO)
|--------------------------------------------------------------------------
|
| Este archivo era el punto de entrada para rutas tenant por dominio completo.
| El enfoque ACTIVO en este proyecto es identificación por PATH en routes/api.php:
|
|   Route::prefix('{tenant}')->middleware([InitializeTenancyByPath::class])->group(...)
|
| Para reactivar la identificación por dominio:
|   1. Descomentar el bloque Route::middleware(...) de abajo.
|   2. En routes/api.php, reemplazar InitializeTenancyByPath por InitializeTenancyBySubdomain.
|   3. Actualizar config/tenancy.php → 'central_domains' con los dominios correctos.
|
*/

// Route::middleware([
//     'web',
//     InitializeTenancyByDomain::class,
//     PreventAccessFromCentralDomains::class,
// ])->group(function () {
//     Route::get('/', function () {
//         return 'This is your multi-tenant application. The id of the current tenant is '.tenant('id');
//     });
// });
