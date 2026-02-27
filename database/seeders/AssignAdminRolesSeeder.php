<?php
// database/seeders/AssignAdminRolesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AssignAdminRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar el rol de administrador
        $adminRole = Role::where('name', 'administrador')->first();
        
        if (!$adminRole) {
            $this->command->error('El rol "administrador" no existe. Ejecuta primero PermissionRoleSeeder');
            return;
        }

        // Buscar los usuarios por email
        $users = User::whereIn('email', [
            'usuario1@gmail.com',
            'moralessfaby.dev@gmail.com'
        ])->get();

        foreach ($users as $user) {
            // Asignar rol de administrador
            $user->roles()->syncWithoutDetaching([$adminRole->id]);
            $this->command->info("Rol administrador asignado a: {$user->email}");
        }
    }
}