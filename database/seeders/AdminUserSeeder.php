<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $adminRole = Role::firstOrCreate(
                ['name' => 'administrador'],
                [
                    'display_name' => 'Administrador del Sistema',
                    'description' => 'Control total del sistema sin restricciones',
                    'default_scope' => 'global'
                ]
            );
            $allPermissions = Permission::all();
            if ($allPermissions->isNotEmpty()) {
                $adminRole->permissions()->sync($allPermissions->pluck('id'));
            }

            $admins = [
                [
                    'name' => 'Admin',
                    'last_name' => 'User',
                    'email' => 'admin@gmail.com',
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                    'avatar' => 'avatar-6.jpg',
                    'is_active' => true,
                ],
                [
                    'name' => 'Faby',
                    'last_name' => 'Morales',
                    'email' => 'moralessfaby.dev@gmail.com',
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                    'avatar' => 'avatar-6.jpg',
                    'is_active' => true,
                ]
            ];

            foreach ($admins as $adminData) {
                $user = User::updateOrCreate(
                    ['email' => $adminData['email']],
                    array_merge($adminData, [
                        'created_by' => null,
                    ])
                );
                $user->roles()->syncWithoutDetaching([
                    $adminRole->id => [
                        'scope' => 'global',
                        'institution_id' => null,
                        'voting_table_id' => null,
                        'election_type_id' => null,
                        'scope_settings' => json_encode(['full_access' => true]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }

        });
    }
}
