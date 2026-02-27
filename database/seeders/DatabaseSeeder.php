<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionRoleSeeder::class,           
            DepartmentSeeder::class,               
            ProvincesMunicipalitiesSeeder::class,
            LocalitiesSeeder::class,
            ElectionTypeSeeder::class,             
            AssignAdminPermissionsSeeder::class,   
            
            QuillacolloInstitutionsSeeder::class, 
            QuillacolloTablesSeeder::class
        ]);
    }
}