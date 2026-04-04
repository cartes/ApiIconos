<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarpetaController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\IconoController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;

use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

// ==========================================
// RUTAS CENTRALES (No requieren X-Tenant)
// ==========================================

Route::get('/estado', [SystemController::class, 'verificarEstado']);
Route::post('/primer-admin', [SystemController::class, 'crearPrimerAdmin']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas globales (Administración Maestra)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/cambiar-clave', [AuthController::class, 'cambiarClave']);

    // Perfil del usuario autenticado
    Route::put('/perfil', [PerfilController::class, 'actualizarDatos']);
    Route::put('/perfil/password', [PerfilController::class, 'cambiarPassword']);
    Route::get('/perfil/sesiones', [PerfilController::class, 'listarSesiones']);
    Route::delete('/perfil/sesiones', [PerfilController::class, 'revocarOtrasSesiones']);
    Route::delete('/perfil/sesiones/{tokenId}', [PerfilController::class, 'revocarSesion']);

    // Rutas exclusivas para el SUPER ADMINISTRADOR
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('empresas', EmpresaController::class)->except(['show', 'update']);
    });
});

// ==========================================
// RUTAS TENANT (Requieren X-Tenant Header)
// ==========================================

Route::middleware([
    'auth:sanctum',
    InitializeTenancyByRequestData::class,
])->group(function () {

    // Información del Tenant actual
    Route::get('/tenant-info', function () {
        $tenant = tenant();
        return response()->json([
            'success' => true,
            'nombre' => $tenant->nombre,
            'id' => $tenant->id
        ]);
    });

    // Gestión de Usuarios del Tenant
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class)->except(['show']);
    });

    // Gestión de Iconos
    Route::put('iconos/reorder', [IconoController::class, 'reorder']);
    Route::apiResource('iconos', IconoController::class)->only(['index', 'store']);
    Route::put('iconos/{icono}', [IconoController::class, 'update'])->middleware('permission:editar');
    Route::delete('iconos/{icono}', [IconoController::class, 'destroy'])->middleware('permission:eliminar');
    Route::post('iconos/{icono}/click', [IconoController::class, 'registerClick']);

    // Dashboard de métricas (admin)
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Gestión de Carpetas
    Route::put('carpetas/reorder', [CarpetaController::class, 'reorder']);
    Route::apiResource('carpetas', CarpetaController::class)->except(['show']);
});

// Rutas para el SUPERADMINISTRADOR
Route::middleware(['auth:sanctum', 'role:super-admin'])->prefix('super-admin')->group(function () {
    Route::get('/tenants', [SuperAdminController::class, 'indexTenants']);
    Route::post('/tenants', [SuperAdminController::class, 'storeTenant']);
    Route::put('/tenants/{id}', [SuperAdminController::class, 'updateTenant']);
    Route::delete('/tenants/{id}', [SuperAdminController::class, 'deleteTenant']);
    Route::post('/tenants/{id}/suspender', [SuperAdminController::class, 'suspenderTenant']);
    Route::post('/tenants/{id}/activar', [SuperAdminController::class, 'activarTenant']);
    Route::get('/usuarios', [SuperAdminController::class, 'indexUsuarios']);

    // Planes
    Route::get('/planes', [SuperAdminController::class, 'indexPlanes']);
    Route::post('/planes', [SuperAdminController::class, 'storePlan']);
    Route::put('/planes/{id}', [SuperAdminController::class, 'updatePlan']);
    Route::delete('/planes/{id}', [SuperAdminController::class, 'deletePlan']);

    // Suscripciones
    Route::post('/suscripciones', [SuperAdminController::class, 'storeSuscripcion']);
    Route::put('/suscripciones/{id}', [SuperAdminController::class, 'updateSuscripcion']);
});
