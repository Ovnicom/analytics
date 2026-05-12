<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Admin obtiene todos los módulos definidos en config/modules.php automáticamente
        $todosLosModulos = array_keys(config('modules'));

        $roles = [
            [
                'nombre'      => 'Administrador',
                'slug'        => 'admin',
                'descripcion' => 'Acceso total al sistema',
                'modulos'     => $todosLosModulos,
            ],
            [
                'nombre'      => 'Editor',
                'slug'        => 'editor',
                'descripcion' => 'Acceso a reportes y contenido',
                'modulos'     => ['msp_reports', 'meta2'],
            ],
            [
                'nombre'      => 'Ventas',
                'slug'        => 'ventas',
                'descripcion' => 'Acceso al módulo de ventas',
                'modulos'     => ['sales'],
            ],
        ];

        foreach ($roles as $data) {
            Role::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
