<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Icono;
use Illuminate\Http\Request;

class IconoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $contextEmpresaId = ($user->rol === 'admin' && $request->has('targetEmpresaId'))
            ? $request->targetEmpresaId
            : $user->empresaId;

        $iconos = Icono::where('empresaId', $contextEmpresaId)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'iconos' => $iconos,
            'puedeEliminar' => $user->puedeEliminar !== false,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'carpetaId' => 'required|exists:carpetas,id',
            'nombre' => 'nullable|string',
            'url' => 'required|url',
        ]);

        $user = $request->user();

        $contextEmpresaId = ($user->rol === 'admin' && $request->has('targetEmpresaId'))
            ? $request->targetEmpresaId
            : $user->empresaId;

        $icono = Icono::create([
            'url' => $request->url,
            'carpetaId' => $request->carpetaId,
            'etiqueta' => $request->nombre ?? '',
            'empresaId' => $contextEmpresaId,
            'subidoPor' => $user->email,
            'fechaSubida' => now(),
            'extension' => 'url', // Se coloca genérico
            'orden' => Icono::where('carpetaId', $request->carpetaId)->max('orden') + 1,
        ]);

        return response()->json(['success' => true, 'icono' => $icono], 201);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'iconos' => 'required|array',
            'iconos.*.id' => 'required|exists:iconos,id',
            'iconos.*.orden' => 'required|integer',
        ]);

        $user = $request->user();

        if ($user->puedeEliminar === false && $user->rol !== 'admin') {
            return response()->json(['success' => false, 'error' => 'No tienes permisos para reordenar'], 403);
        }

        foreach ($request->iconos as $iconoData) {
            Icono::where('id', $iconoData['id'])->update(['orden' => $iconoData['orden']]);
        }

        return response()->json(['success' => true, 'mensaje' => 'Orden de iconos actualizado']);
    }

    public function update(Request $request, Icono $icono)
    {
        $request->validate([
            'nuevaEtiqueta' => 'required|string'
        ]);

        $user = $request->user();

        if ($user->rol !== 'admin' && $icono->empresaId !== $user->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos'], 403);
        }

        $icono->etiqueta = $request->nuevaEtiqueta;
        $icono->save();

        return response()->json(['success' => true, 'icono' => $icono]);
    }

    public function destroy(Request $request, Icono $icono)
    {
        $user = $request->user();

        if ($user->rol !== 'admin' && $icono->empresaId !== $user->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos'], 403);
        }

        $icono->delete();

        return response()->json(['success' => true, 'mensaje' => 'Icono eliminado']);
    }
}
