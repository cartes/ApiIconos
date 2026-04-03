<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarpetaController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\IconoController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    // Gestión de Usuarios del Tenant
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('usuarios', UsuarioController::class)->except(['show']);
    });

    // Gestión de Iconos
    Route::put('iconos/reorder', [IconoController::class, 'reorder']);
    Route::apiResource('iconos', IconoController::class)->only(['index', 'store']);
    Route::put('iconos/{icono}', [IconoController::class, 'update'])->middleware('permission:editar');
    Route::delete('iconos/{icono}', [IconoController::class, 'destroy'])->middleware('permission:eliminar');

    // Gestión de Carpetas
    Route::put('carpetas/reorder', [CarpetaController::class, 'reorder']);
    Route::apiResource('carpetas', CarpetaController::class)->except(['show']);
});
