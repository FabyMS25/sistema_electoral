<?php
// database/seeders/AssignAdminPermissionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

class AssignAdminPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar usuarios admin
        $users = User::whereIn('email', [
            'usuario1@gmail.com',
            'moralessfaby.dev@gmail.com'
        ])->get();

        if ($users->isEmpty()) {
            $this->command->error('No se encontraron usuarios admin');
            return;
        }

        // Obtener TODOS los permisos
        $allPermissions = Permission::all()->pluck('id')->toArray();
        
        // Obtener rol de administrador
        $adminRole = Role::where('name', 'administrador')->first();

        foreach ($users as $user) {
            // Asignar TODOS los permisos directamente
            $user->permissions()->sync($allPermissions);
            
            // Opcional: Asignar también el rol
            if ($adminRole) {
                $user->roles()->sync([$adminRole->id]);
            }
            
            $this->command->info("Permisos asignados a: {$user->email}");
        }
    }
}