<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::all()->map(function ($u) {
            return [
                'email' => $u->email,
                'nombre' => $u->nombre,
                'rol' => $u->rol,
                'empresaId' => $u->empresaId,
                'empresaNombre' => $u->empresaNombre,
                'puedeEliminar' => $u->puedeEliminar !== false,
            ];
        });

        return response()->json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'clave' => 'required|string|min:8',
            'rol' => ['required', Rule::in(['admin', 'usuario'])],
            'empresaId' => 'nullable|exists:empresas,id'
        ]);

        $empresaNombre = null;
        if ($request->empresaId) {
            $emp = Empresa::find($request->empresaId);
            if ($emp) {
                $empresaNombre = $emp->nombre;
            }
        }

        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->clave),
            'rol' => $request->rol,
            'empresaId' => $request->empresaId,
            'empresaNombre' => $empresaNombre,
            'fechaCreacion' => now(),
            'activo' => true,
            'puedeEliminar' => true,
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Usuario creado'], 201);
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'nombre' => 'sometimes|string',
            'empresaId' => 'sometimes|nullable|exists:empresas,id',
            'puedeEliminar' => 'sometimes|boolean',
            'nuevaClave' => 'sometimes|string|min:8',
        ]);

        if ($request->has('nombre')) {
            $usuario->nombre = $request->nombre;
        }

        if ($request->has('empresaId')) {
            $usuario->empresaId = $request->empresaId;
            $emp = Empresa::find($request->empresaId);
            $usuario->empresaNombre = $emp ? $emp->nombre : null;
        }

        if ($request->has('puedeEliminar')) {
            $usuario->puedeEliminar = $request->boolean('puedeEliminar');
        }

        if ($request->filled('nuevaClave')) {
            $usuario->password = Hash::make($request->nuevaClave);
        }

        $usuario->save();

        return response()->json(['success' => true, 'mensaje' => 'Usuario actualizado']);
    }

    public function destroy(Request $request, User $usuario)
    {
        if ($request->user()->id === $usuario->id) {
            return response()->json(['success' => false, 'error' => 'No puedes eliminarte a ti mismo'], 400);
        }

        $usuario->delete();

        return response()->json(['success' => true, 'mensaje' => 'Usuario eliminado']);
    }
}
