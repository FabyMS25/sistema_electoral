<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ProvincesMunicipalitiesSeeder::class,
            LocalitiesSeeder::class,

            PermissionRoleSeeder::class,
            AdminUserSeeder::class,

            ElectionTypeSeeder::class,
            Candidates2026Seeder::class,
            Concejales2026Seeder::class,
            QuillacolloInstitutionsSeeder::class,
            QuillacolloTablesSeeder::class,
        ]);
    }
}
