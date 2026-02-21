<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarpetaController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\IconoController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas Públicas de Sistema
Route::get('/estado', [SystemController::class, 'verificarEstado']);
Route::post('/primer-admin', [SystemController::class, 'crearPrimerAdmin']);

// Login Público
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Auth endpoints
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/cambiar-clave', [AuthController::class, 'cambiarClave']);

    // Rutas Exclusivas para el ADMINISTRADOR
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('empresas', EmpresaController::class)->except(['show', 'update']);
        Route::apiResource('usuarios', UsuarioController::class)->except(['show']);
    });

    // Rutas Compartidas (Administrador y Usuarios)
    // Rutas de Iconos
    Route::put('iconos/reorder', [IconoController::class, 'reorder']);
    Route::apiResource('iconos', IconoController::class)->only(['index', 'store']);
    Route::put('iconos/{icono}', [IconoController::class, 'update'])
        ->middleware('permission:editar');
    Route::delete('iconos/{icono}', [IconoController::class, 'destroy'])
        ->middleware('permission:eliminar');

    // Rutas de Carpetas
    Route::put('carpetas/reorder', [CarpetaController::class, 'reorder']);
    Route::apiResource('carpetas', CarpetaController::class)->except(['show']);
});
