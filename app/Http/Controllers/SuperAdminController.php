<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Stancl\Tenancy\Database\Models\Tenant;

class SuperAdminController extends Controller
{
    /**
     * Lista todos los tenants con su dominio principal y datos del JSON.
     */
    public function indexTenants()
    {
        $tenants = Tenant::with('domains')->get()->map(function ($tenant) {
            $data = is_array($tenant->data) ? $tenant->data : (json_decode($tenant->data, true) ?? []);
            return [
                'id'       => $tenant->id,
                'nombre'   => $data['nombre'] ?? null,
                'dominio'  => $tenant->domains->first()?->domain ?? null,
                'estado'   => $data['estado'] ?? 'activo',
                'created_at' => $tenant->created_at,
            ];
        });

        return response()->json($tenants);
    }

    /**
     * Crea un nuevo tenant con dominio y opcionalmente un admin inicial.
     */
    public function storeTenant(Request $request)
    {
        $request->validate([
            'nombre'  => 'required|string|max:255',
            'dominio' => 'required|string|max:100|unique:domains,domain',
        ]);

        // Generar un ID legible desde el nombre (slug)
        $id = \Illuminate\Support\Str::slug($request->nombre, '-');

        // Asegurar unicidad del ID
        $base = $id;
        $i = 1;
        while (Tenant::find($id)) {
            $id = $base . '-' . $i++;
        }

        $tenant = Tenant::create([
            'id'   => $id,
            'data' => json_encode(['nombre' => $request->nombre, 'estado' => 'activo']),
        ]);

        $tenant->domains()->create(['domain' => $request->dominio]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Agencia registrada correctamente',
            'tenant'  => [
                'id'      => $tenant->id,
                'nombre'  => $request->nombre,
                'dominio' => $request->dominio,
                'estado'  => 'activo',
            ],
        ]);
    }

    /**
     * Suspende un tenant actualizando su campo estado en data JSON.
     */
    public function suspenderTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $data = is_array($tenant->data) ? $tenant->data : (json_decode($tenant->data, true) ?? []);
        $data['estado'] = 'suspendido';
        $tenant->update(['data' => json_encode($data)]);

        return response()->json(['success' => true, 'mensaje' => 'Agencia suspendida correctamente']);
    }

    /**
     * Reactiva un tenant suspendido.
     */
    public function activarTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $data = is_array($tenant->data) ? $tenant->data : (json_decode($tenant->data, true) ?? []);
        $data['estado'] = 'activo';
        $tenant->update(['data' => json_encode($data)]);

        return response()->json(['success' => true, 'mensaje' => 'Agencia activada correctamente']);
    }

    /**
     * Lista todos los usuarios del sistema (sin filtro de tenant).
     */
    public function indexUsuarios()
    {
        $usuarios = User::withoutGlobalScopes()->get();
        return response()->json($usuarios);
    }
}
