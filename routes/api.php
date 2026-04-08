<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarpetaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\IconoController;
use App\Http\Controllers\Api\InvitationController;
use App\Http\Controllers\Api\PerfilController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
// use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain; // Alternativa: identificación por subdominio (ej. agencia.miapp.com)

// ==========================================
// RUTAS CENTRALES (No requieren contexto de tenant)
// ==========================================

Route::get('/estado', [SystemController::class, 'verificarEstado']);
Route::post('/primer-admin', [SystemController::class, 'crearPrimerAdmin']);
Route::post('/login', [AuthController::class, 'login']);

// ── Invitaciones públicas (sin contexto de tenant) ───────────────────────
// Deben registrarse ANTES del grupo Route::prefix('{tenant}') para tener
// precedencia y evitar que "invitar" sea interpretado como un slug de tenant.
Route::get('/invitar/{token}', [InvitationController::class, 'show']);
Route::post('/invitar/{token}/aceptar', [InvitationController::class, 'accept']);

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
// LÓGICA DE RUTAS TENANT (Compartida entre métodos de identificación)
// ==========================================

$registrarRutasTenant = function () {
    // Rutas de invitado dentro del tenant
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/estado', [SystemController::class, 'verificarEstado']);

    // Rutas protegidas dentro del tenant
    Route::middleware('auth:sanctum')->group(function () {

        // Sesión del usuario en contexto tenant
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/cambiar-clave', [AuthController::class, 'cambiarClave']);

        // Perfil del usuario en contexto tenant
        Route::put('/perfil', [PerfilController::class, 'actualizarDatos']);
        Route::put('/perfil/password', [PerfilController::class, 'cambiarPassword']);
        Route::get('/perfil/sesiones', [PerfilController::class, 'listarSesiones']);
        Route::delete('/perfil/sesiones', [PerfilController::class, 'revocarOtrasSesiones']);
        Route::delete('/perfil/sesiones/{tokenId}', [PerfilController::class, 'revocarSesion']);

        // Información del Tenant actual
        Route::get('/tenant-info', function () {
            $tenant = tenant();

            return response()->json([
                'success' => true,
                'nombre' => $tenant->nombre,
                'slug' => $tenant->slug,
                'id' => $tenant->id,
            ]);
        });

        // Gestión de Usuarios y Empresas del Tenant (solo admin)
        Route::middleware('role:admin')->name('tenant.')->group(function () {
            Route::apiResource('usuarios', UsuarioController::class)->except(['show']);
            Route::apiResource('empresas', EmpresaController::class)->except(['show', 'update']);

            // Gestión de Invitaciones del Tenant
            Route::get('invitaciones', [InvitationController::class, 'index']);
            Route::post('invitaciones', [InvitationController::class, 'store']);
            Route::delete('invitaciones/{invitacion}', [InvitationController::class, 'destroy']);
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
};

// ==========================================
// MÉTODOS DE IDENTIFICACIÓN
// ==========================================

// 1. Identificación por RUTA (ej. /api/agencia-slug/...)
// Usado principalmente por el nuevo frontend iconos_comercial
Route::prefix('{tenant}')->name('path.')->middleware([
    InitializeTenancyByPath::class,
])->group($registrarRutasTenant);

// 2. Identificación por HEADER (X-Tenant)
// Para compatibilidad con el repositorio original 'iconos'
Route::middleware([
    \Stancl\Tenancy\Middleware\InitializeTenancyByRequestData::class,
])->group($registrarRutasTenant);

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
