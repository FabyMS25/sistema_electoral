<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 1. CREAR TODOS LOS PERMISOS CON ÁMBITO =====
        $allPermissions = [
            // Permisos globales
            ['name' => 'view_users', 'display_name' => 'Ver Usuarios', 'group' => 'Usuarios', 'scope' => 'global'],
            ['name' => 'create_users', 'display_name' => 'Crear Usuarios', 'group' => 'Usuarios', 'scope' => 'global'],
            ['name' => 'assign_roles', 'display_name' => 'Asignar Roles', 'group' => 'Usuarios', 'scope' => 'global'],
            
            // Permisos con ámbito de recinto
            ['name' => 'view_recinto', 'display_name' => 'Ver Recinto', 'group' => 'Recintos', 'scope' => 'recinto'],
            ['name' => 'manage_recinto', 'display_name' => 'Gestionar Recinto', 'group' => 'Recintos', 'scope' => 'recinto'],
            
            // Permisos con ámbito de mesa
            ['name' => 'view_mesa', 'display_name' => 'Ver Mesa', 'group' => 'Mesas', 'scope' => 'mesa'],
            ['name' => 'register_votes', 'display_name' => 'Registrar Votos', 'group' => 'Votos', 'scope' => 'mesa'],
            ['name' => 'upload_acta', 'display_name' => 'Subir Acta', 'group' => 'Actas', 'scope' => 'mesa'],
            
            // Permisos mixtos (funcionan en múltiples ámbitos)
            ['name' => 'view_votes', 'display_name' => 'Ver Votos', 'group' => 'Votos', 'scope' => 'global'], // Puede tener scope específico en asignación
        ];

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // ===== 2. CREAR ROLES CON ÁMBITO POR DEFECTO =====
        $roles = [
            [
                'name' => 'administrador',
                'display_name' => 'Administrador del Sistema',
                'default_scope' => 'global',
                'permission_names' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'delegado_recinto',
                'display_name' => 'Delegado de Recinto',
                'default_scope' => 'recinto',
                'permission_names' => [
                    'view_recinto', 'manage_recinto', 'view_mesa',
                    'register_votes', 'view_votes', 'upload_acta'
                ]
            ],
            [
                'name' => 'delegado_mesa',
                'display_name' => 'Delegado de Mesa',
                'default_scope' => 'mesa',
                'permission_names' => [
                    'view_mesa', 'register_votes', 'view_votes', 'upload_acta'
                ]
            ],
            [
                'name' => 'revisor',
                'display_name' => 'Revisor',
                'default_scope' => 'recinto',
                'permission_names' => [
                    'view_mesa', 'view_votes', 'view_recinto'
                ]
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'] ?? '',
                    'default_scope' => $roleData['default_scope']
                ]
            );
            
            $permissionIds = Permission::whereIn('name', $roleData['permission_names'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}