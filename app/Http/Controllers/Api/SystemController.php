<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SystemController extends Controller
{
    public function verificarEstado()
    {
        $hayAdmin = User::where('rol', 'admin')->exists();
        return response()->json([
            'success' => true,
            'hayAdmin' => $hayAdmin,
            'necesitaBootstrap' => !$hayAdmin,
        ]);
    }

    public function crearPrimerAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'nombre' => 'required|string',
            'clave' => 'required|string|min:8',
        ]);

        if (User::where('rol', 'admin')->exists()) {
            return response()->json(['success' => false, 'error' => 'Ya existe administrador'], 400);
        }

        $admin = User::create([
            'email' => $request->email,
            'nombre' => $request->nombre,
            'password' => Hash::make($request->clave),
            'rol' => 'admin',
            'fechaCreacion' => now(),
            'activo' => true,
            'puedeEliminar' => true,
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Administrador creado']);
    }
}
