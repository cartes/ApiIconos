<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'empresas' => Empresa::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:empresas,nombre',
        ]);

        $empresa = Empresa::create([
            'nombre' => $request->nombre,
            'fechaCreacion' => now(),
        ]);

        return response()->json(['success' => true, 'empresa' => $empresa], 201);
    }

    public function destroy(Empresa $empresa)
    {
        $empresa->delete();
        return response()->json(['success' => true, 'mensaje' => 'Empresa eliminada']);
    }
}
