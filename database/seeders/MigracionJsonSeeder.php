<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Icono;
use App\Models\Empresa;
use App\Models\Carpeta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MigracionJsonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Deshabilitar restricciones de FK para pgsql (Railway) si es necesario, 
        // aunque el orden correcto debería evitar problemas.

        // 1. MIGRAR EMPRESAS
        $pathEmpresas = storage_path('app/public/empresas.json');
        if (File::exists($pathEmpresas)) {
            $jsonEmpresas = File::get($pathEmpresas);
            $empresas = json_decode($jsonEmpresas, true);
            foreach ($empresas as $data) {
                Empresa::updateOrCreate(
                    ['id' => $data['id']],
                    [
                        'nombre' => $data['nombre'],
                        'fechaCreacion' => Carbon::parse($data['fechaCreacion']),
                    ]
                );
            }
            $this->command->info('Empresas migradas.');
        }

        // 2. MIGRAR USUARIOS
        $pathUsuarios = storage_path('app/public/usuarios.json');
        if (File::exists($pathUsuarios)) {
            $jsonUsuarios = File::get($pathUsuarios);
            $usuarios = json_decode($jsonUsuarios, true);
            foreach ($usuarios as $email => $data) {
                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'nombre' => $data['nombre'],
                        'hash' => $data['hash'], // Usamos el hash original tal cual
                        'rol' => $data['rol'],
                        'empresaId' => $data['empresaId'] ?? null,
                        'empresaNombre' => $data['empresaNombre'] ?? null,
                        'fechaCreacion' => Carbon::parse($data['fechaCreacion']),
                        'activo' => $data['activo'] ?? true,
                        'puedeEliminar' => $data['puedeEliminar'] ?? true,
                    ]
                );
            }
            $this->command->info('Usuarios migrados.');
        }

        // 3. MIGRAR CARPETAS
        $pathCarpetas = storage_path('app/public/carpetas.json');
        if (File::exists($pathCarpetas)) {
            $jsonCarpetas = File::get($pathCarpetas);
            $carpetas = json_decode($jsonCarpetas, true);
            foreach ($carpetas as $data) {
                Carpeta::updateOrCreate(
                    ['id' => $data['id']],
                    [
                        'nombre' => $data['nombre'],
                        'empresaId' => $data['empresaId'],
                        'creadoPor' => $data['creadoPor']
                    ]
                );
            }
            $this->command->info('Carpetas migradas.');
        }

        // 4. MIGRAR ICONOS
        $pathIconos = storage_path('app/public/iconos.json');
        if (File::exists($pathIconos)) {
            $jsonIconos = File::get($pathIconos);
            $iconos = json_decode($jsonIconos, true);
            foreach ($iconos as $item) {
                Icono::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'url' => $item['url'],
                        'carpetaId' => $item['carpetaId'] ?? null,
                        'empresaId' => $item['empresaId'],
                        'subidoPor' => $item['subidoPor'],
                        'fechaSubida' => Carbon::parse($item['fechaSubida']),
                        'etiqueta' => $item['etiqueta'] ?? null,
                        'orden' => $item['orden'] ?? 0
                    ]
                );
            }
            $this->command->info('Iconos migrados.');
        }
    }
}
