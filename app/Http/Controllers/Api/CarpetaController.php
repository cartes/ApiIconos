<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carpeta;
use App\Models\Icono;
use Illuminate\Http\Request;

class CarpetaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $contextEmpresaId = ($user->rol === 'admin' && $request->has('targetEmpresaId'))
            ? $request->targetEmpresaId
            : $user->empresaId;

        $carpetas = Carpeta::where('empresaId', $contextEmpresaId)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'carpetas' => $carpetas,
            'puedeEliminar' => $user->puedeEliminar !== false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
        ]);

        $user = $request->user();

        $contextEmpresaId = ($user->rol === 'admin' && $request->has('targetEmpresaId'))
            ? $request->targetEmpresaId
            : $user->empresaId;

        if (!$contextEmpresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes empresa asignada'], 400);
        }

        if (Carpeta::where('nombre', $request->nombre)->where('empresaId', $contextEmpresaId)->exists()) {
            return response()->json(['success' => false, 'error' => 'La carpeta ya existe'], 400);
        }

        $carpeta = Carpeta::create([
            'nombre' => $request->nombre,
            'empresaId' => $contextEmpresaId,
            'creadoPor' => $user->email,
            'orden' => Carpeta::where('empresaId', $contextEmpresaId)->max('orden') + 1,
        ]);

        return response()->json(['success' => true, 'carpeta' => $carpeta], 201);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'carpetas' => 'required|array',
            'carpetas.*.id' => 'required|exists:carpetas,id',
            'carpetas.*.orden' => 'required|integer',
        ]);

        $user = $request->user();

        if ($user->puedeEliminar === false && $user->rol !== 'admin') {
            return response()->json(['success' => false, 'error' => 'No tienes permisos para reordenar'], 403);
        }

        foreach ($request->carpetas as $carpetaData) {
            Carpeta::where('id', $carpetaData['id'])->update(['orden' => $carpetaData['orden']]);
        }

        return response()->json(['success' => true, 'mensaje' => 'Orden de carpetas actualizado']);
    }

    public function update(Request $request, Carpeta $carpeta)
    {
        $request->validate([
            'nombre' => 'required|string',
        ]);

        $user = $request->user();

        if ($user->rol !== 'admin' && $carpeta->empresaId !== $user->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos'], 403);
        }

        if (Carpeta::where('nombre', $request->nombre)->where('empresaId', $carpeta->empresaId)->where('id', '!=', $carpeta->id)->exists()) {
            return response()->json(['success' => false, 'error' => 'Ya existe una carpeta con ese nombre'], 400);
        }

        $carpeta->nombre = $request->nombre;
        $carpeta->save();

        return response()->json(['success' => true, 'mensaje' => 'Carpeta renombrada']);
    }

    public function destroy(Request $request, Carpeta $carpeta)
    {
        $user = $request->user();

        if ($user->rol !== 'admin' && $carpeta->empresaId !== $user->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos'], 403);
        }

        if (Icono::where('carpetaId', $carpeta->id)->exists()) {
            return response()->json(['success' => false, 'error' => 'La carpeta no está vacía. Elimina los iconos primero.'], 400);
        }

        $carpeta->delete();

        return response()->json(['success' => true, 'mensaje' => 'Carpeta eliminada']);
    }
}
