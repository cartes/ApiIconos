<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationController extends Controller
{
    /** Admin: list all invitations for the current tenant. */
    public function index()
    {
        $invitations = Invitation::with('invitedBy')
            ->latest()
            ->get()
            ->map(fn (Invitation $i) => [
                'id'         => $i->id,
                'email'      => $i->email,
                'rol'        => $i->rol,
                'status'     => $i->status,
                'expires_at' => $i->expires_at?->toISOString(),
                'created_at' => $i->created_at?->toISOString(),
                'invited_by' => $i->invitedBy?->email,
            ]);

        return response()->json(['success' => true, 'invitaciones' => $invitations]);
    }

    /** Admin: send a new invitation by email. */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'rol'   => ['required', Rule::in(['admin', 'usuario', 'editor'])],
        ]);

        $currentTenant = tenant();

        // Reject if email already belongs to this tenant
        $alreadyMember = User::where('email', $request->email)
            ->where('tenant_id', $currentTenant->id)
            ->exists();

        if ($alreadyMember) {
            return response()->json([
                'success' => false,
                'error'   => 'Este correo ya pertenece a un miembro de esta agencia.',
            ], 409);
        }

        // Reject if a valid pending invitation already exists
        $pendingExists = Invitation::where('email', $request->email)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->exists();

        if ($pendingExists) {
            return response()->json([
                'success' => false,
                'error'   => 'Ya existe una invitación pendiente para este correo.',
            ], 409);
        }

        $invitation = Invitation::create([
            'email'      => $request->email,
            'rol'        => $request->rol,
            'token'      => Str::random(64),
            'tenant_id'  => $currentTenant->id,
            'invited_by' => $request->user()->id,
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        try {
            Mail::to($request->email)->send(new InvitationMail($invitation, $currentTenant));
        } catch (\Exception $e) {
            $invitation->delete();

            return response()->json([
                'success' => false,
                'error'   => 'No se pudo enviar el correo de invitación. Verifica la configuración de correo del servidor.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'mensaje' => "Invitación enviada a {$request->email}.",
        ], 201);
    }

    /** Public: verify a token without initializing any tenant context. */
    public function show(string $token)
    {
        $invitation = Invitation::withoutGlobalScopes()
            ->where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (! $invitation) {
            return response()->json([
                'success' => false,
                'error'   => 'La invitación no es válida o ha expirado.',
            ], 404);
        }

        $tenantModel = Tenant::find($invitation->tenant_id);

        $userExists = User::withoutGlobalScopes()
            ->where('email', $invitation->email)
            ->exists();

        return response()->json([
            'success'     => true,
            'invitacion'  => [
                'email'         => $invitation->email,
                'rol'           => $invitation->rol,
                'tenant_nombre' => $tenantModel?->nombre ?? $invitation->tenant_id,
                'tenant_slug'   => $tenantModel?->slug  ?? $invitation->tenant_id,
                'expires_at'    => $invitation->expires_at?->toISOString(),
                'usuario_existe' => $userExists,
            ],
        ]);
    }

    /** Public: accept an invitation, registering or re-assigning the user. */
    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::withoutGlobalScopes()
            ->where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if (! $invitation) {
            return response()->json([
                'success' => false,
                'error'   => 'La invitación no es válida o ha expirado.',
            ], 404);
        }

        $existingUser = User::withoutGlobalScopes()
            ->where('email', $invitation->email)
            ->first();

        if ($existingUser) {
            // User already has an account: associate to this tenant and update role.
            $existingUser->tenant_id = $invitation->tenant_id;
            $existingUser->rol       = $invitation->rol;
            $existingUser->save();
            $existingUser->syncRoles([$invitation->rol]);
        } else {
            // New user: require name and password.
            $request->validate([
                'nombre'           => 'required|string|max:100',
                'clave'            => 'required|string|min:8|confirmed',
                'clave_confirmation' => 'required',
            ]);

            $user = User::create([
                'nombre'        => $request->nombre,
                'email'         => $invitation->email,
                'hash'          => Hash::make($request->clave),
                'rol'           => $invitation->rol,
                'tenant_id'     => $invitation->tenant_id,
                'activo'        => true,
                'puedeEliminar' => true,
                'fechaCreacion' => now(),
            ]);

            $user->syncRoles([$invitation->rol]);
        }

        $invitation->update(['status' => 'accepted']);

        return response()->json([
            'success' => true,
            'mensaje' => '¡Bienvenido! Ya puedes iniciar sesión.',
        ]);
    }

    /** Admin: cancel (delete) a pending invitation. */
    public function destroy(Invitation $invitacion)
    {
        $invitacion->delete();

        return response()->json(['success' => true, 'mensaje' => 'Invitación cancelada.']);
    }
}
