<?php

namespace Database\Seeders;

use App\Models\Carpeta;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;

class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // 1. Empresas
        $empresas = [
            [
                'id' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'nombre' => 'Provida',
                'fechaCreacion' => '2026-01-28T12:28:55.842Z',
            ],
            [
                'id' => 'c792c0cd-1d41-4205-8261-7b8454ef4856',
                'nombre' => 'Metlife',
                'fechaCreacion' => '2026-01-28T12:28:55.842Z', // Placeholder
            ],
        ];

        foreach ($empresas as $empData) {
            Empresa::updateOrCreate(['id' => $empData['id']], $empData);
        }

        // 2. Usuarios
        $usuarios = [
            'cristian.cartes@content360.cl' => [
                'nombre' => 'Cristian Cartes',
                'rol' => 'admin',
                'empresaId' => null,
                'empresaNombre' => null,
                'fechaCreacion' => '2026-01-19T16:31:57.041Z',
                'activo' => true,
            ],
            'constanza.calderon@content360.cl' => [
                'nombre' => 'Constanza Calderon',
                'rol' => 'usuario',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'empresaNombre' => 'Provida',
                'fechaCreacion' => '2026-01-28T12:30:17.593Z',
                'activo' => true,
            ],
            'david.caldera@content360.cl' => [
                'nombre' => 'David Caldera',
                'rol' => 'usuario',
                'empresaId' => 'c792c0cd-1d41-4205-8261-7b8454ef4856',
                'empresaNombre' => 'Metlife',
                'fechaCreacion' => '2026-01-28T12:41:51.185Z',
                'activo' => true,
            ],
            'carmari.barreto@content360.cl' => [
                'nombre' => 'Carmarí Barreto',
                'rol' => 'usuario',
                'empresaId' => 'c792c0cd-1d41-4205-8261-7b8454ef4856',
                'empresaNombre' => 'Metlife',
                'fechaCreacion' => '2026-02-05T20:16:06.673Z',
                'activo' => true,
            ],
            'macarena.collao@content360.cl' => [
                'nombre' => 'Macarena Collao',
                'rol' => 'admin',
                'empresaId' => null,
                'empresaNombre' => null,
                'fechaCreacion' => '2026-02-06T19:12:17.152Z',
                'activo' => true,
            ],
            'ariel.gonzalez@content360.cl' => [
                'nombre' => 'Ariel Gonzalez',
                'rol' => 'admin',
                'empresaId' => null,
                'empresaNombre' => null,
                'fechaCreacion' => '2026-02-06T19:13:15.307Z',
                'activo' => true,
            ],
            'tamara.vega@content360.cl' => [
                'nombre' => 'Tamara Vega',
                'rol' => 'usuario',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'empresaNombre' => 'Provida',
                'fechaCreacion' => '2026-02-19T18:51:39.649Z',
                'activo' => true,
                'puedeEliminar' => false,
            ],
            'gonzalo.astudillo@content360.cl' => [
                'nombre' => 'Gonzalo Astudillo',
                'rol' => 'usuario',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'empresaNombre' => 'Provida',
                'fechaCreacion' => '2026-02-19T18:52:25.737Z',
                'activo' => true,
                'puedeEliminar' => false,
            ],
            'Catalina.herrera@content360.cl' => [
                'nombre' => 'Catalina Herrera',
                'rol' => 'usuario',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'empresaNombre' => 'Provida',
                'fechaCreacion' => '2026-02-19T18:53:11.307Z',
                'activo' => true,
                'puedeEliminar' => false,
            ],
            'alex.maulen@content360.cl' => [
                'nombre' => 'Alex Maulen',
                'rol' => 'usuario',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'empresaNombre' => 'Provida',
                'fechaCreacion' => '2026-02-19T18:54:00.153Z',
                'activo' => true,
                'puedeEliminar' => false,
            ],
        ];

        foreach ($usuarios as $email => $data) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'nombre' => $data['nombre'],
                    'hash' => 'password', // Default password for local testing
                    'rol' => $data['rol'],
                    'empresaId' => $data['empresaId'],
                    'empresaNombre' => $data['empresaNombre'],
                    'fechaCreacion' => $data['fechaCreacion'],
                    'activo' => $data['activo'],
                    'puedeEliminar' => $data['puedeEliminar'] ?? true,
                ]
            );
        }

        // 3. Carpetas
        $carpetas = [
            [
                'id' => 'c5588a3b-48d4-47ed-bbc5-642e3c3c8259',
                'nombre' => 'Headers sin luneta',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'creadoPor' => 'constanza.calderon@content360.cl',
            ],
            [
                'id' => '233d0883-3df8-4931-b1a9-4ffa9f14d7b6',
                'nombre' => 'Iconos',
                'empresaId' => 'c8789b47-fe25-4ca9-b57b-3cc2981e1317',
                'creadoPor' => 'constanza.calderon@content360.cl',
            ],
            [
                'id' => '5cf66187-1f22-451c-a7f2-8ccc31c286af',
                'nombre' => 'Iconos',
                'empresaId' => 'c792c0cd-1d41-4205-8261-7b8454ef4856',
                'creadoPor' => 'david.caldera@content360.cl',
            ],
        ];

        foreach ($carpetas as $carpetaData) {
            Carpeta::updateOrCreate(['id' => $carpetaData['id']], $carpetaData);
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
}
