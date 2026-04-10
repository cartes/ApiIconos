<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TenantRegistrationController extends Controller
{
    /**
     * Registra una nueva agencia (Tenant) junto con su usuario administrador.
     *
     * Endpoint público: POST /api/registrar-agencia
     * No requiere autenticación.
     *
     * @todo Aplicar rate limiting (p.ej. ThrottleRequests) en producción.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'   => [
                'required',
                'string',
                'max:255',
                // 'nombre' lives in the `data` JSON column (VirtualColumn).
                // A standard `unique:tenants,nombre` rule queries the column directly and
                // fails because there is no real `nombre` column in PostgreSQL.
                // We query via JSON path instead.
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Tenant::where('data->nombre', $value)->exists()) {
                        $fail('El nombre de agencia ya está en uso.');
                    }
                },
            ],
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:8',
            'telefono' => 'nullable|string|max:20',
        ]);

        $password = $request->filled('password')
            ? $request->input('password')
            : Str::password(8);

        $slug = $this->generateUniqueSlug($request->input('nombre'));

        $tenant = Tenant::create([
            'nombre'        => $request->input('nombre'),
            'slug'          => $slug,
            'email'         => $request->input('email'),
            'telefono'      => $request->input('telefono'),
            'estado'        => 'activo',
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'nombre'        => $request->input('nombre'),
            'email'         => $request->input('email'),
            'hash'          => Hash::make($password),
            'rol'           => 'admin',
            'tenant_id'     => $tenant->id,
            'fechaCreacion' => now(),
            'activo'        => true,
            'puedeEliminar' => true,
        ]);

        $this->assignAdminRole($user, $tenant->id);

        return response()->json([
            'success' => true,
            'mensaje' => 'Agencia registrada correctamente. Guarde su contraseña en un lugar seguro.',
            'agencia' => [
                'id'            => $tenant->id,
                'nombre'        => $tenant->nombre,
                'slug'          => $tenant->slug,
                'email'         => $tenant->email,
                'trial_ends_at' => $tenant->trial_ends_at?->toIso8601String(),
            ],
            'usuario' => [
                'nombre' => $user->nombre,
                'email'  => $user->email,
                'rol'    => $user->rol,
            ],
            'password' => $password,
        ], 201);
    }

    /**
     * Genera un slug único a partir del nombre de la agencia.
     * Si el slug ya existe, agrega un sufijo numérico incremental.
     *
     * Nota: `slug` se almacena en la columna JSON `data` (VirtualColumn),
     * por lo que se consulta via JSON path para evitar un error de columna
     * inexistente en PostgreSQL.
     */
    private function generateUniqueSlug(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $counter = 2;

        while (Tenant::where('data->slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Crea (si no existe) el rol 'admin' en el contexto del tenant dado
     * y lo asigna al usuario.
     */
    private function assignAdminRole(User $user, string $tenantId): void
    {
        setPermissionsTeamId($tenantId);

        $role = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web', 'tenant_id' => $tenantId]
        );

        $user->assignRole($role);
    }
}
