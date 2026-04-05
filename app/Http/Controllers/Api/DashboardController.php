<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Icono;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = tenant('id');

        $contextEmpresaId = ($user->rol === 'admin' && $request->has('targetEmpresaId'))
            ? $request->targetEmpresaId
            : $user->empresaId;

        // Últimos 10 íconos subidos (por fecha de subida descendente)
        $ultimosIconos = Icono::where('empresaId', $contextEmpresaId)
            ->orderByDesc('fechaSubida')
            ->take(10)
            ->get(['id', 'url', 'etiqueta', 'subidoPor', 'fechaSubida', 'clicks']);

        // Top 5 íconos más copiados
        $topIconos = Icono::where('empresaId', $contextEmpresaId)
            ->orderByDesc('clicks')
            ->where('clicks', '>', 0)
            ->take(5)
            ->get(['id', 'url', 'etiqueta', 'clicks']);

        // Top 5 usuarios más activos (por número de interacciones en este tenant)
        $topUsuarios = DB::table('icon_clicks')
            ->select('user_email', DB::raw('COUNT(*) as total_interacciones'))
            ->where('tenant_id', $tenantId)
            ->groupBy('user_email')
            ->orderByDesc('total_interacciones')
            ->take(5)
            ->get();

        // Enriquecer con nombre de usuario
        $emails = $topUsuarios->pluck('user_email');
        $usuariosMap = User::whereIn('email', $emails)
            ->pluck('nombre', 'email');

        $topUsuarios = $topUsuarios->map(function ($row) use ($usuariosMap) {
            $row->nombre = $usuariosMap[$row->user_email] ?? $row->user_email;

            return $row;
        });

        return response()->json([
            'success' => true,
            'ultimos_iconos' => $ultimosIconos,
            'top_iconos' => $topIconos,
            'top_usuarios' => $topUsuarios,
        ]);
    }
}
