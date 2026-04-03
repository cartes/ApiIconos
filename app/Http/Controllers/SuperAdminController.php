<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Empresa;

class SuperAdminController extends Controller
{
    public function indexEmpresas()
    {
        $empresas = Empresa::withoutGlobalScopes()->withCount('users')->get();
        return response()->json($empresas);
    }

    public function indexUsuarios()
    {
        $usuarios = User::withoutGlobalScopes()->get();
        return response()->json($usuarios);
    }

    public function suspenderEmpresa($id)
    {
        $empresa = Empresa::withoutGlobalScopes()->findOrFail($id);
        $empresa->update([
            'estado' => 'suspendida'
        ]);
        return response()->json([
            'message' => 'Empresa suspendida correctamente'
        ]);
    }

    public function activarEmpresa($id)
    {
        $empresa = Empresa::withoutGlobalScopes()->findOrFail($id);
        $empresa->update([
            'estado' => 'activo'
        ]);
        return response()->json([
            'message' => 'Empresa activada correctamente'
        ]);
    }

}
