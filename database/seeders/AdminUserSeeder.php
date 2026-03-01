<?php
// database/seeders/AdminUserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear o obtener el rol de administrador
        $adminRole = Role::firstOrCreate(
            ['name' => 'administrador'],
            [
                'display_name' => 'Administrador del Sistema',
                'description' => 'Control total del sistema sin restricciones',
                'default_scope' => 'global' // Ámbito global = puede hacer todo
            ]
        );

        // 2. asignacion de TODOS los permisos
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        $admins = [
            [
                'name' => 'Administrador',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Fabiola Morales',
                'email' => 'moralessfaby.dev@gmail.com',
                'password' => Hash::make('password123'),
            ]
        ];

        foreach ($admins as $adminData) {
            $user = User::firstOrCreate(
                ['email' => $adminData['email']],
                [
                    'name' => $adminData['name'],
                    'password' => $adminData['password'],
                ]
            );
            $user->roles()->syncWithoutDetaching([
                $adminRole->id => [
                    'scope' => 'global',
                    'scope_id' => null,
                    'scope_type' => null
                ]
            ]);
            $this->command->info("✅ Usuario admin configurado: {$user->email} (ámbito GLOBAL)");
        }

        $this->command->info('🎉 Administradores creados exitosamente');
    }
}