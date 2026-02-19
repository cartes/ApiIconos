<?php

namespace App\Http\Controllers;

use App\Models\Carpeta;
use App\Models\Empresa;
use App\Models\Icono;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiLegacyController extends Controller
{
    public function handle(Request $request)
    {
        $params = $request->all();
        $accion = $params['accion'] ?? null;

        if (! $accion) {
            return response()->json(['success' => false, 'error' => 'Accion desconocida']);
        }

        try {
            switch ($accion) {
                // SISTEMA
                case 'verificarEstado':
                    return response()->json([
                        'hayAdmin' => User::where('rol', 'admin')->exists(),
                        'necesitaBootstrap' => ! User::where('rol', 'admin')->exists(),
                    ]);

                case 'crearPrimerAdmin':
                    if (User::where('rol', 'admin')->exists()) {
                        return response()->json(['success' => false, 'error' => 'Ya existe administrador']);
                    }
                    $admin = User::create([
                        'email' => $params['email'],
                        'nombre' => $params['nombre'],
                        'password' => $params['clave'], // Laravel will hash it via cast
                        'rol' => 'admin',
                        'fechaCreacion' => now(),
                        'activo' => true,
                    ]);

                    return response()->json(['success' => true]);

                case 'login':
                    return $this->verificarLogin($params['email'], $params['clave']);

                case 'cambiarClave':
                    return $this->cambiarClave($params['email'], $params['clave'], $params['nuevaClave']);

                    // EMPRESAS
                case 'crearEmpresa':
                    return $this->crearEmpresa($params);

                case 'listarEmpresas':
                    return $this->listarEmpresas($params);

                case 'eliminarEmpresa':
                    return $this->eliminarEmpresa($params);

                    // USUARIOS
                case 'crearUsuario':
                    return $this->crearUsuario($params);

                case 'listarUsuarios':
                    return $this->listarUsuarios($params);

                case 'editarUsuario':
                    return $this->editarUsuario($params);

                case 'eliminarUsuario':
                    return $this->eliminarUsuario($params);

                    // CARPETAS
                case 'crearCarpeta':
                    return $this->crearCarpeta($params);

                case 'listarCarpetas':
                    return $this->listarCarpetas($params);

                case 'eliminarCarpeta':
                    return $this->eliminarCarpeta($params);

                case 'renombrarCarpeta':
                    return $this->renombrarCarpeta($params);

                    // ICONOS
                case 'subirIcono':
                    return $this->subirIcono($params);

                case 'listarIconos':
                    return $this->listarIconos($params);

                case 'editarIcono':
                    return $this->editarIcono($params);

                case 'eliminarIcono':
                    return $this->eliminarIcono($params);

                default:
                    return response()->json(['success' => false, 'error' => 'Accion desconocida']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Error servidor: '.$e->getMessage()]);
        }
    }

    private function verificarLogin($email, $clave)
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Usuario no existe']);
        }

        if (! Hash::check($clave, $user->password)) {
            return response()->json(['success' => false, 'error' => 'Contraseña incorrecta']);
        }

        return response()->json([
            'success' => true,
            'usuario' => $user->email,
            'nombre' => $user->nombre,
            'rol' => $user->rol,
            'empresaId' => $user->empresaId,
            'empresaNombre' => $user->empresaNombre,
            'puedeEliminar' => $user->puedeEliminar !== false,
        ]);
    }

    private function verificarAdmin($email, $clave)
    {
        $user = User::where('email', $email)->first();
        if (! $user || $user->rol !== 'admin') {
            return false;
        }

        return Hash::check($clave, $user->password);
    }

    private function crearEmpresa($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        if (Empresa::where('nombre', $params['nombreEmpresa'])->exists()) {
            return response()->json(['success' => false, 'error' => 'Ya existe una empresa con este nombre']);
        }

        $empresa = Empresa::create([
            'nombre' => $params['nombreEmpresa'],
            'fechaCreacion' => now(),
        ]);

        return response()->json(['success' => true, 'empresa' => $empresa]);
    }

    private function listarEmpresas($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        return response()->json(['success' => true, 'empresas' => Empresa::all()]);
    }

    private function eliminarEmpresa($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        Empresa::where('id', $params['idEmpresa'])->delete();

        return response()->json(['success' => true]);
    }

    private function crearUsuario($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        if (User::where('email', $params['nuevoEmail'])->exists()) {
            return response()->json(['success' => false, 'error' => 'El usuario ya existe']);
        }

        $empresaNombre = null;
        if ($params['empresaId'] ?? null) {
            $emp = Empresa::find($params['empresaId']);
            if ($emp) {
                $empresaNombre = $emp->nombre;
            }
        }

        User::create([
            'nombre' => $params['nuevoNombre'],
            'email' => $params['nuevoEmail'],
            'password' => $params['nuevaClave'],
            'rol' => ($params['esAdmin'] ?? false) ? 'admin' : 'usuario',
            'empresaId' => $params['empresaId'] ?? null,
            'empresaNombre' => $empresaNombre,
            'fechaCreacion' => now(),
            'activo' => true,
            'puedeEliminar' => true,
        ]);

        return response()->json(['success' => true, 'mensaje' => 'Usuario creado']);
    }

    private function listarUsuarios($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        $usuarios = User::all()->map(function ($u) {
            return [
                'email' => $u->email,
                'nombre' => $u->nombre,
                'rol' => $u->rol,
                'empresaId' => $u->empresaId,
                'empresaNombre' => $u->empresaNombre,
                'puedeEliminar' => $u->puedeEliminar !== false,
            ];
        });

        return response()->json(['success' => true, 'usuarios' => $usuarios]);
    }

    private function editarUsuario($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        $user = User::where('email', $params['targetEmail'])->first();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Usuario no existe']);
        }

        $datos = $params['datos'];
        if (isset($datos['nombre'])) {
            $user->nombre = $datos['nombre'];
        }
        if (isset($datos['empresaId'])) {
            $user->empresaId = $datos['empresaId'];
            $emp = Empresa::find($datos['empresaId']);
            $user->empresaNombre = $emp ? $emp->nombre : null;
        }
        if (isset($datos['puedeEliminar'])) {
            $user->puedeEliminar = $datos['puedeEliminar'];
        }
        if (isset($datos['nuevaClave']) && strlen($datos['nuevaClave']) >= 8) {
            $user->password = $datos['nuevaClave'];
        }

        $user->save();

        return response()->json(['success' => true]);
    }

    private function eliminarUsuario($params)
    {
        if (! $this->verificarAdmin($params['email'], $params['clave'])) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos de administrador']);
        }

        if ($params['email'] === $params['usuarioTarget']) {
            return response()->json(['success' => false, 'error' => 'No puedes eliminarte a ti mismo']);
        }

        User::where('email', $params['usuarioTarget'])->delete();

        return response()->json(['success' => true]);
    }

    private function crearCarpeta($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        $contextEmpresaId = ($login->rol === 'admin' && ($params['targetEmpresaId'] ?? null))
            ? $params['targetEmpresaId']
            : $login->empresaId;

        if (! $contextEmpresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes empresa asignada']);
        }

        if (Carpeta::where('nombre', $params['nombreCarpeta'])->where('empresaId', $contextEmpresaId)->exists()) {
            return response()->json(['success' => false, 'error' => 'La carpeta ya existe']);
        }

        $carpeta = Carpeta::create([
            'nombre' => $params['nombreCarpeta'],
            'empresaId' => $contextEmpresaId,
            'creadoPor' => $params['email'],
        ]);

        return response()->json(['success' => true, 'carpeta' => $carpeta]);
    }

    private function listarCarpetas($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        $contextEmpresaId = ($login->rol === 'admin' && ($params['targetEmpresaId'] ?? null))
            ? $params['targetEmpresaId']
            : $login->empresaId;

        $carpetas = Carpeta::where('empresaId', $contextEmpresaId)->get();

        return response()->json([
            'success' => true,
            'carpetas' => $carpetas,
            'puedeEliminar' => $login->puedeEliminar !== false,
        ]);
    }

    private function eliminarCarpeta($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        if ($login->rol !== 'admin' && $login->puedeEliminar === false) {
            return response()->json(['success' => false, 'error' => 'No tienes permiso para eliminar']);
        }

        $carpeta = Carpeta::find($params['idCarpeta']);
        if (! $carpeta) {
            return response()->json(['success' => false, 'error' => 'Carpeta no encontrada']);
        }

        if ($login->rol !== 'admin' && $carpeta->empresaId !== $login->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos']);
        }

        if (Icono::where('carpetaId', $params['idCarpeta'])->exists()) {
            return response()->json(['success' => false, 'error' => 'La carpeta no está vacía. Elimina los iconos primero.']);
        }

        $carpeta->delete();

        return response()->json(['success' => true]);
    }

    private function renombrarCarpeta($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        $carpeta = Carpeta::find($params['idCarpeta']);
        if (! $carpeta) {
            return response()->json(['success' => false, 'error' => 'Carpeta no encontrada']);
        }

        if ($login->rol !== 'admin' && $carpeta->empresaId !== $login->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos']);
        }

        if (Carpeta::where('nombre', $params['nuevoNombre'])->where('empresaId', $carpeta->empresaId)->where('id', '!=', $params['idCarpeta'])->exists()) {
            return response()->json(['success' => false, 'error' => 'Ya existe una carpeta con ese nombre']);
        }

        $carpeta->nombre = $params['nuevoNombre'];
        $carpeta->save();

        return response()->json(['success' => true]);
    }

    private function subirIcono($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        $contextEmpresaId = ($login->rol === 'admin' && ($params['targetEmpresaId'] ?? null))
            ? $params['targetEmpresaId']
            : $login->empresaId;

        $icono = Icono::create([
            'url' => $params['url'],
            'carpetaId' => $params['carpetaId'],
            'etiqueta' => $params['etiqueta'] ?? '',
            'empresaId' => $contextEmpresaId,
            'subidoPor' => $params['email'],
            'fechaSubida' => now(),
        ]);

        return response()->json(['success' => true, 'icono' => $icono]);
    }

    private function listarIconos($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        $contextEmpresaId = ($login->rol === 'admin' && ($params['targetEmpresaId'] ?? null))
            ? $params['targetEmpresaId']
            : $login->empresaId;

        $iconos = Icono::where('empresaId', $contextEmpresaId)->get();

        return response()->json([
            'success' => true,
            'iconos' => $iconos,
            'puedeEliminar' => $login->puedeEliminar !== false,
        ]);
    }

    private function editarIcono($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        $icono = Icono::find($params['idIcono']);
        if (! $icono) {
            return response()->json(['success' => false, 'error' => 'Icono no encontrado']);
        }

        if ($login->rol !== 'admin' && $icono->empresaId !== $login->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos']);
        }

        $icono->etiqueta = $params['nuevaEtiqueta'];
        $icono->save();

        return response()->json(['success' => true]);
    }

    private function eliminarIcono($params)
    {
        $loginRes = $this->verificarLogin($params['email'], $params['clave']);
        $login = $loginRes->getData();
        if (! $login->success) {
            return $loginRes;
        }

        if ($login->rol !== 'admin' && $login->puedeEliminar === false) {
            return response()->json(['success' => false, 'error' => 'No tienes permiso para eliminar']);
        }

        $icono = Icono::find($params['idIcono']);
        if (! $icono) {
            return response()->json(['success' => false, 'error' => 'Icono no encontrado']);
        }

        if ($login->rol !== 'admin' && $icono->empresaId !== $login->empresaId) {
            return response()->json(['success' => false, 'error' => 'No tienes permisos']);
        }

        $icono->delete();

        return response()->json(['success' => true]);
    }

    private function cambiarClave($email, $claveActual, $nuevaClave)
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json(['success' => false, 'error' => 'Usuario no existe']);
        }

        if (! Hash::check($claveActual, $user->password)) {
            return response()->json(['success' => false, 'error' => 'Contraseña actual incorrecta']);
        }

        if (! $nuevaClave || strlen($nuevaClave) < 8) {
            return response()->json(['success' => false, 'error' => 'La nueva contraseña debe tener al menos 8 caracteres']);
        }

        $user->password = $nuevaClave; // Hashed via cast
        $user->save();

        return response()->json(['success' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
    }
}
