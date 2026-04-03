<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;

class SuperAdminController extends Controller
{
    /**
     * Lista todos los tenants con su dominio principal y datos virtuales.
     */
    public function indexTenants()
    {
        $tenants = Tenant::with('domains')->get()->map(function ($tenant) {
            return [
                'id'         => $tenant->id,
                'nombre'     => $tenant->nombre,
                'dominio'    => $tenant->domains->first()?->domain,
                'direccion'  => $tenant->direccion,
                'email'      => $tenant->email,
                'telefono'   => $tenant->telefono,
                'estado'     => $tenant->estado ?? 'activo',
                'created_at' => $tenant->created_at,
            ];
        });

        return response()->json($tenants);
    }

    /**
     * Crea un nuevo tenant con dominio y metadatos.
     */
    public function storeTenant(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:255',
            'dominio'    => 'required|string|max:100|unique:domains,domain',
            'direccion'  => 'nullable|string|max:255',
            'email'      => 'nullable|email|max:255',
            'telefono'   => 'nullable|string|max:20',
        ]);

        // Generar un ID legible desde el nombre (slug)
        $id = \Illuminate\Support\Str::slug($request->nombre, '-');

        // Asegurar unicidad del ID
        $base = $id;
        $i = 1;
        while (Tenant::find($id)) {
            $id = $base . '-' . $i++;
        }

        // Crear tenant usando atributos virtuales (VirtualColumn trait)
        $tenant = new Tenant();
        $tenant->id = $id;
        $tenant->nombre = $request->nombre;
        $tenant->direccion = $request->direccion;
        $tenant->email = $request->email;
        $tenant->telefono = $request->telefono;
        $tenant->estado = 'activo';
        $tenant->save();

        $tenant->domains()->create(['domain' => $request->dominio]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Agencia registrada correctamente',
            'tenant'  => [
                'id'         => $tenant->id,
                'nombre'     => $tenant->nombre,
                'dominio'    => $request->dominio,
                'direccion'  => $tenant->direccion,
                'email'      => $tenant->email,
                'telefono'   => $tenant->telefono,
                'estado'     => 'activo',
            ],
        ]);
    }

    /**
     * Actualiza el nombre y datos de un tenant, y opcionalmente su dominio.
     */
    public function updateTenant(Request $request, $id)
    {
        $request->validate([
            'nombre'    => 'sometimes|required|string|max:255',
            'direccion' => 'sometimes|nullable|string|max:255',
            'email'     => 'sometimes|nullable|email|max:255',
            'telefono'  => 'sometimes|nullable|string|max:20',
            'dominio'   => 'sometimes|required|string|max:100|unique:domains,domain,' . $id . ',tenant_id',
        ]);

        $tenant = Tenant::findOrFail($id);
        
        // Actualizar atributos virtuales directamente
        if ($request->has('nombre')) {
            $tenant->nombre = $request->nombre;
        }
        if ($request->has('direccion')) {
            $tenant->direccion = $request->direccion;
        }
        if ($request->has('email')) {
            $tenant->email = $request->email;
        }
        if ($request->has('telefono')) {
            $tenant->telefono = $request->telefono;
        }
        
        $tenant->save();

        // Actualizar dominio si se proporciona
        if ($request->has('dominio')) {
            $tenant->domains()->delete();
            $tenant->domains()->create(['domain' => $request->dominio]);
        }

        $dominio = $tenant->domains->first()?->domain;

        return response()->json([
            'success' => true,
            'mensaje' => 'Agencia actualizada correctamente',
            'tenant'  => [
                'id'        => $tenant->id,
                'nombre'    => $tenant->nombre,
                'dominio'   => $dominio,
                'direccion' => $tenant->direccion,
                'email'     => $tenant->email,
                'telefono'  => $tenant->telefono,
                'estado'    => $tenant->estado,
            ],
        ]);
    }

    /**
     * Elimina un tenant y sus dominios asociados.
     */
    public function deleteTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        
        // Eliminar dominios asociados
        $tenant->domains()->delete();
        
        // Eliminar el tenant
        $tenant->delete();

        return response()->json(['success' => true, 'mensaje' => 'Agencia eliminada correctamente']);
    }

    /**
     * Suspende un tenant actualizando su campo estado.
     */
    public function suspenderTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->estado = 'suspendido';
        $tenant->save();

        return response()->json(['success' => true, 'mensaje' => 'Agencia suspendida correctamente']);
    }

    /**
     * Reactiva un tenant suspendido.
     */
    public function activarTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->estado = 'activo';
        $tenant->save();

        return response()->json(['success' => true, 'mensaje' => 'Agencia activada correctamente']);
    }

    /**
     * Lista todos los usuarios del sistema (sin filtro de tenant, pero excluyendo super-admin).
     */
    public function indexUsuarios()
    {
        $usuarios = User::withoutGlobalScopes()->where('rol', '!=', 'super-admin')->get();
        return response()->json($usuarios);
    }
}
