<?php
// database/seeders/PermissionRoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            // Usuarios
            ['name' => 'view_users', 'display_name' => 'Ver Usuarios', 'group' => 'Usuarios'],
            ['name' => 'create_users', 'display_name' => 'Crear Usuarios', 'group' => 'Usuarios'],
            ['name' => 'edit_users', 'display_name' => 'Editar Usuarios', 'group' => 'Usuarios'],
            ['name' => 'delete_users', 'display_name' => 'Eliminar Usuarios', 'group' => 'Usuarios'],
            ['name' => 'assign_roles', 'display_name' => 'Asignar Roles', 'group' => 'Usuarios'],
            ['name' => 'assign_permissions', 'display_name' => 'Asignar Permisos', 'group' => 'Usuarios'],
            
            // Recintos
            ['name' => 'view_recintos', 'display_name' => 'Ver Recintos', 'group' => 'Recintos'],
            ['name' => 'create_recintos', 'display_name' => 'Crear Recintos', 'group' => 'Recintos'],
            ['name' => 'edit_recintos', 'display_name' => 'Editar Recintos', 'group' => 'Recintos'],
            ['name' => 'delete_recintos', 'display_name' => 'Eliminar Recintos', 'group' => 'Recintos'],
            ['name' => 'assign_recinto_delegates', 'display_name' => 'Asignar Delegados de Recinto', 'group' => 'Recintos'],
            
            // Mesas
            ['name' => 'view_mesas', 'display_name' => 'Ver Mesas', 'group' => 'Mesas'],
            ['name' => 'create_mesas', 'display_name' => 'Crear Mesas', 'group' => 'Mesas'],
            ['name' => 'edit_mesas', 'display_name' => 'Editar Mesas', 'group' => 'Mesas'],
            ['name' => 'delete_mesas', 'display_name' => 'Eliminar Mesas', 'group' => 'Mesas'],
            ['name' => 'assign_table_delegates', 'display_name' => 'Asignar Delegados de Mesa', 'group' => 'Mesas'],
            
            // Votos
            ['name' => 'register_votes', 'display_name' => 'Registrar Votos', 'group' => 'Votos'],
            ['name' => 'view_votes', 'display_name' => 'Ver Votos', 'group' => 'Votos'],
            ['name' => 'review_votes', 'display_name' => 'Revisar Votos', 'group' => 'Votos'],
            ['name' => 'create_observations', 'display_name' => 'Crear Observaciones', 'group' => 'Votos'],
            ['name' => 'correct_votes', 'display_name' => 'Corregir Votos', 'group' => 'Votos'],
            ['name' => 'resolve_observations', 'display_name' => 'Resolver Observaciones', 'group' => 'Votos'],
            
            // Candidatos y elecciones
            ['name' => 'manage_candidates', 'display_name' => 'Gestionar Candidatos', 'group' => 'Elecciones'],
            ['name' => 'manage_election_types', 'display_name' => 'Gestionar Tipos de Elección', 'group' => 'Elecciones'],
            
            // Reportes
            ['name' => 'view_reports', 'display_name' => 'Ver Reportes', 'group' => 'Reportes'],
            ['name' => 'export_data', 'display_name' => 'Exportar Datos', 'group' => 'Reportes'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                $perm
            );
        }

        // Crear roles
        $roles = [
            [
                'name' => 'administrador',
                'display_name' => 'Administrador del Sistema',
                'description' => 'Control total del sistema',
                'permissions' => Permission::all()->pluck('id')->toArray()
            ],
            [
                'name' => 'delegado_recinto',
                'display_name' => 'Delegado de Recinto',
                'description' => 'Gestiona un recinto específico',
                'permissions' => Permission::whereIn('name', [
                    'view_recintos', 'view_mesas', 'assign_table_delegates',
                    'register_votes', 'view_votes', 'view_reports'
                ])->pluck('id')->toArray()
            ],
            [
                'name' => 'delegado_mesa',
                'display_name' => 'Delegado de Mesa',
                'description' => 'Registra votos de una mesa específica',
                'permissions' => Permission::whereIn('name', [
                    'view_mesas', 'register_votes', 'view_votes'
                ])->pluck('id')->toArray()
            ],
            [
                'name' => 'revisor',
                'display_name' => 'Revisor',
                'description' => 'Revisa y observa registros de votos',
                'permissions' => Permission::whereIn('name', [
                    'view_mesas', 'view_votes', 'review_votes', 
                    'create_observations', 'view_reports'
                ])->pluck('id')->toArray()
            ],
            [
                'name' => 'modificador',
                'display_name' => 'Modificador',
                'description' => 'Corrige votos observados',
                'permissions' => Permission::whereIn('name', [
                    'view_mesas', 'view_votes', 'correct_votes',
                    'resolve_observations', 'view_reports'
                ])->pluck('id')->toArray()
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            
            $role->permissions()->sync($permissions);
        }
    }
}