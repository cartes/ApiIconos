<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'clave' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Usuario no existe'], 401);
        }

        $autenticado = false;

        // 1. Intentar con Bcrypt (Laravel estándar)
        $info = Hash::info($user->hash);
        if ($info['algoName'] === 'bcrypt') {
            $autenticado = Hash::check($request->clave, $user->hash);
        }

        // 2. Si no es bcrypt o falló, intentar con Legacy Hash
        if (!$autenticado) {
            // Apps script sent base64 of sha256 bytes string
            $hashAttempt1 = base64_encode(hash('sha256', $request->clave, true));
            // Sometimes it was hex
            $hashAttempt2 = hash('sha256', $request->clave);

            if ($user->hash === $hashAttempt1 || $user->hash === $hashAttempt2 || $user->hash === $request->clave) {
                $autenticado = true;

                // Optimizacion: Actualizar automáticamente a Bcrypt para el futuro
                $user->hash = Hash::make($request->clave);
                $user->save();
            }
        }

        if (!$autenticado) {
            return response()->json(['success' => false, 'error' => 'Contraseña incorrecta'], 401);
        }

        // Delete previous tokens to keep only one active session per device if preferred
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'usuario' => [
                'email' => $user->email,
                'nombre' => $user->nombre,
                'rol' => $user->rol,
                'empresaId' => $user->empresaId,
                'empresaNombre' => $user->empresaNombre,
                'puedeEliminar' => $user->puedeEliminar !== false,
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'success' => true,
            'usuario' => [
                'email' => $user->email,
                'nombre' => $user->nombre,
                'rol' => $user->rol,
                'empresaId' => $user->empresaId,
                'empresaNombre' => $user->empresaNombre,
                'puedeEliminar' => $user->puedeEliminar !== false,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'mensaje' => 'Sesión cerrada exitosamente']);
    }

    public function cambiarClave(Request $request)
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

        if (!$autenticado) {
            $legacyHash = base64_encode(hash('sha256', $request->clave, true));
            if ($legacyHash === $user->hash) {
                $autenticado = true;
            }
        }

        if (!$autenticado) {
            return response()->json(['success' => false, 'error' => 'Contraseña actual incorrecta'], 400);
        }

        $user->hash = Hash::make($request->nuevaClave);
        $user->save();

        return response()->json(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
    }
}
