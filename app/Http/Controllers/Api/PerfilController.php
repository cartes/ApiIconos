<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function actualizarDatos(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$request->user()->id,
        ]);

        $user = $request->user();
        $user->nombre = $request->nombre;
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Datos actualizados correctamente',
            'usuario' => [
                'email' => $user->email,
                'nombre' => $user->nombre,
                'rol' => $user->rol,
                'empresaId' => $user->empresaId,
                'empresaNombre' => $user->empresaNombre,
                'puedeEliminar' => $user->puedeEliminar !== false,
            ],
        ]);
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'clave' => 'required',
            'nuevaClave' => 'required|min:8',
        ]);

        $user = $request->user();
        $autenticado = false;

        $info = Hash::info($user->hash);
        if ($info['algoName'] === 'bcrypt') {
            $autenticado = Hash::check($request->clave, $user->hash);
        }

        if (! $autenticado) {
            $legacyHash = base64_encode(hash('sha256', $request->clave, true));
            if ($legacyHash === $user->hash) {
                $autenticado = true;
            }
        }

        if (! $autenticado) {
            return response()->json(['success' => false, 'error' => 'Contraseña actual incorrecta'], 400);
        }

        $user->hash = Hash::make($request->nuevaClave);
        $user->save();

        return response()->json(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
    }

    public function listarSesiones(Request $request)
    {
        $currentTokenId = $request->user()->currentAccessToken()->id;

        $sesiones = $request->user()->tokens()
            ->orderByDesc('last_used_at')
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'nombre' => $token->name,
                'creado_en' => $token->created_at->toIso8601String(),
                'ultimo_uso' => $token->last_used_at?->toIso8601String(),
                'es_actual' => $token->id === $currentTokenId,
            ]);

        return response()->json(['success' => true, 'sesiones' => $sesiones]);
    }

    public function revocarSesion(Request $request, int $tokenId)
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (! $token) {
            return response()->json(['success' => false, 'error' => 'Sesión no encontrada'], 404);
        }

        $token->delete();

        return response()->json(['success' => true, 'mensaje' => 'Sesión cerrada correctamente']);
    }

    public function revocarOtrasSesiones(Request $request)
    {
        $currentTokenId = $request->user()->currentAccessToken()->id;

        $request->user()->tokens()
            ->where('id', '!=', $currentTokenId)
            ->delete();

        return response()->json(['success' => true, 'mensaje' => 'Otras sesiones cerradas correctamente']);
    }
}
