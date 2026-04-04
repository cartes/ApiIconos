<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\Suscripcion;

class SuperAdminController extends Controller
{
    /**
     * Lista todos los tenants con su dominio principal, datos virtuales y suscripción activa.
     */
    public function indexTenants()
    {
        $tenants = Tenant::with(['domains', 'suscripcion.plan'])->get()->map(function ($tenant) {
            $sub = $tenant->suscripcion;
            return [
                'id'          => $tenant->id,
                'nombre'      => $tenant->nombre,
                'dominio'     => $tenant->domains->first()?->domain,
                'direccion'   => $tenant->direccion,
                'email'       => $tenant->email,
                'telefono'    => $tenant->telefono,
                'estado'      => $tenant->estado ?? 'activo',
                'created_at'  => $tenant->created_at,
                'suscripcion' => $sub ? [
                    'id'               => $sub->id,
                    'plan_id'          => $sub->plan_id,
                    'estado'           => $sub->estado,
                    'fecha_inicio'     => $sub->fecha_inicio?->toDateString(),
                    'fecha_vencimiento'=> $sub->fecha_vencimiento?->toDateString(),
                    'notas'            => $sub->notas,
                    'plan'             => $sub->plan ? [
                        'id'             => $sub->plan->id,
                        'nombre'         => $sub->plan->nombre,
                        'precio_mensual' => $sub->plan->precio_mensual,
                    ] : null,
                ] : null,
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

        $id = \Illuminate\Support\Str::slug($request->nombre, '-');
        $base = $id;
        $i = 1;
        while (Tenant::find($id)) {
            $id = $base . '-' . $i++;
        }

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
                'suscripcion'=> null,
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

        if ($request->has('nombre')) $tenant->nombre = $request->nombre;
        if ($request->has('direccion')) $tenant->direccion = $request->direccion;
        if ($request->has('email')) $tenant->email = $request->email;
        if ($request->has('telefono')) $tenant->telefono = $request->telefono;
        $tenant->save();

        if ($request->has('dominio')) {
            $tenant->domains()->delete();
            $tenant->domains()->create(['domain' => $request->dominio]);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Agencia actualizada correctamente',
            'tenant'  => [
                'id'        => $tenant->id,
                'nombre'    => $tenant->nombre,
                'dominio'   => $tenant->domains()->first()?->domain,
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
        $tenant->domains()->delete();
        $tenant->delete();

        return response()->json(['success' => true, 'mensaje' => 'Agencia eliminada correctamente']);
    }

    /**
     * Suspende un tenant.
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
     * Lista todos los usuarios del sistema.
     */
    public function indexUsuarios()
    {
        $usuarios = User::withoutGlobalScopes()->where('rol', '!=', 'super-admin')->get();
        return response()->json($usuarios);
    }

    // ─── PLANES ────────────────────────────────────────────────────────────────

    public function indexPlanes()
    {
        return response()->json(['planes' => Plan::orderBy('precio_mensual')->get()]);
    }

    public function storePlan(Request $request)
    {
        $request->validate([
            'nombre'         => 'required|string|max:100',
            'precio_mensual' => 'required|numeric|min:0',
            'max_usuarios'   => 'nullable|integer|min:1',
            'max_iconos'     => 'nullable|integer|min:1',
            'activo'         => 'boolean',
        ]);

        $plan = Plan::create($request->only('nombre', 'precio_mensual', 'max_usuarios', 'max_iconos', 'activo'));

        return response()->json(['success' => true, 'id' => $plan->id, 'plan' => $plan]);
    }

    public function updatePlan(Request $request, $id)
    {
        $request->validate([
            'nombre'         => 'sometimes|required|string|max:100',
            'precio_mensual' => 'sometimes|required|numeric|min:0',
            'max_usuarios'   => 'sometimes|nullable|integer|min:1',
            'max_iconos'     => 'sometimes|nullable|integer|min:1',
            'activo'         => 'sometimes|boolean',
        ]);

        $plan = Plan::findOrFail($id);
        $plan->update($request->only('nombre', 'precio_mensual', 'max_usuarios', 'max_iconos', 'activo'));

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function deletePlan($id)
    {
        Plan::findOrFail($id)->delete();
        return response()->json(['success' => true, 'mensaje' => 'Plan eliminado']);
    }

    // ─── SUSCRIPCIONES ─────────────────────────────────────────────────────────

    public function storeSuscripcion(Request $request)
    {
        $request->validate([
            'tenant_id'         => 'required|string|exists:tenants,id',
            'plan_id'           => 'required|integer|exists:planes,id',
            'estado'            => 'required|in:activa,vencida,cancelada,trial',
            'fecha_inicio'      => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_inicio',
            'notas'             => 'nullable|string',
        ]);

        $sub = Suscripcion::updateOrCreate(
            ['tenant_id' => $request->tenant_id],
            $request->only('plan_id', 'estado', 'fecha_inicio', 'fecha_vencimiento', 'notas')
        );

        $sub->load('plan');

        return response()->json(['success' => true, 'suscripcion' => $sub]);
    }

    public function updateSuscripcion(Request $request, $id)
    {
        $request->validate([
            'plan_id'           => 'sometimes|integer|exists:planes,id',
            'estado'            => 'sometimes|in:activa,vencida,cancelada,trial',
            'fecha_inicio'      => 'sometimes|date',
            'fecha_vencimiento' => 'sometimes|nullable|date',
            'notas'             => 'sometimes|nullable|string',
        ]);

        $sub = Suscripcion::findOrFail($id);
        $sub->update($request->only('plan_id', 'estado', 'fecha_inicio', 'fecha_vencimiento', 'notas'));
        $sub->load('plan');

        return response()->json(['success' => true, 'suscripcion' => $sub]);
    }
}
