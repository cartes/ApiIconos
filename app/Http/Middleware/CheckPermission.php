<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();

        // El Administrador siempre tiene permisos para todo
        if ($user && $user->rol === 'admin') {
            return $next($request);
        }

        // Si la ruta requiere permiso de "eliminar", chequeamos en la base de datos
        if ($permission === 'eliminar' && $user && $user->puedeEliminar === false) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes permiso para eliminar'
            ], 403);
        }

        // Si la ruta requiere permiso de "editar", chequeamos el mismo atributo por ahora
        if ($permission === 'editar' && $user && $user->puedeEliminar === false) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes permiso para editar'
            ], 403);
        }

        return $next($request);
    }
}
