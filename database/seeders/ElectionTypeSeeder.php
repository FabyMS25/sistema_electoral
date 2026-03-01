<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use Carbon\Carbon;

class ElectionTypeSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('========================================');
        $this->command->info('CREANDO TIPOS DE ELECCIÓN Y CATEGORÍAS');
        $this->command->info('========================================');

        // ===== 1. CREAR CATEGORÍAS ELECTORALES =====
        $this->command->info('Creando categorías electorales...');
        
        $categories = [
            [
                'name' => 'Presidente',
                'code' => 'PRE',
                'description' => 'Elección de Presidente y Vicepresidente',
                'order' => 1,
                'ballot_position' => 'unica',
                'active' => true,
            ],
            [
                'name' => 'Senador',
                'code' => 'SEN',
                'description' => 'Elección de Senadores',
                'order' => 2,
                'ballot_position' => 'unica',
                'active' => true,
            ],
            [
                'name' => 'Diputado',
                'code' => 'DIP',
                'description' => 'Elección de Diputados',
                'order' => 3,
                'ballot_position' => 'unica',
                'active' => true,
            ],
            [
                'name' => 'Alcalde',
                'code' => 'ALC',
                'description' => 'Elección de Alcaldes Municipales',
                'order' => 4,
                'ballot_position' => 'superior',
                'active' => true,
            ],
            [
                'name' => 'Concejal',
                'code' => 'CON',
                'description' => 'Elección de Concejales Municipales',
                'order' => 5,
                'ballot_position' => 'inferior',
                'active' => true,
            ],
        ];

        $categoriasCreadas = [];
        foreach ($categories as $catData) {
            $category = ElectionCategory::firstOrCreate(
                ['code' => $catData['code']],
                $catData
            );
            $categoriasCreadas[$catData['code']] = $category->id;
            $this->command->info("  ✅ Categoría: {$catData['name']} ({$catData['code']})");
        }

        // ===== 2. CREAR TIPOS DE ELECCIÓN =====
        $this->command->info('');
        $this->command->info('Creando tipos de elección...');

        $electionTypes = [
            [
                'name' => 'Elecciones Presidenciales 2025',
                'election_date' => '2025-10-19',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'finalizado',
                'active' => false,
                'categories' => ['PRE', 'SEN', 'DIP'], // Presidente, Senadores, Diputados
            ],
            [
                'name' => 'Elecciones Municipales 2026',
                'election_date' => '2026-03-22',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'status' => 'preparacion',
                'active' => true,
                'categories' => ['ALC', 'CON'], // Alcalde y Concejal (una sola papeleta con dos franjas)
            ],
        ];

        foreach ($electionTypes as $typeData) {
            $categories = $typeData['categories'];
            unset($typeData['categories']);

            // Crear o actualizar el tipo de elección
            $electionType = ElectionType::updateOrCreate(
                ['name' => $typeData['name']],
                $typeData
            );

            $this->command->info("  📊 Tipo: {$electionType->name}");

            // ===== 3. ASOCIAR CATEGORÍAS AL TIPO DE ELECCIÓN =====
            $this->command->info("     Asignando categorías:");
            
            foreach ($categories as $catCode) {
                if (isset($categoriasCreadas[$catCode])) {
                    // Verificar si ya existe la relación
                    $exists = ElectionTypeCategory::where([
                        'election_type_id' => $electionType->id,
                        'election_category_id' => $categoriasCreadas[$catCode],
                    ])->exists();

                    if (!$exists) {
                        ElectionTypeCategory::create([
                            'election_type_id' => $electionType->id,
                            'election_category_id' => $categoriasCreadas[$catCode],
                            'votes_per_person' => 1,
                            'has_blank_vote' => true,
                            'has_null_vote' => true,
                        ]);
                        $this->command->info("       ✅ {$catCode}");
                    } else {
                        $this->command->info("       ⏭️ {$catCode} (ya existía)");
                    }
                }
            }
        }

        // ===== 4. MOSTRAR RESUMEN =====
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('✅ RESUMEN FINAL');
        $this->command->info('========================================');
        
        $totalCategorias = ElectionCategory::count();
        $totalTipos = ElectionType::count();
        $totalRelaciones = ElectionTypeCategory::count();
        
        $this->command->info("📊 Categorías electorales: {$totalCategorias}");
        $this->command->info("📅 Tipos de elección: {$totalTipos}");
        $this->command->info("🔗 Relaciones creadas: {$totalRelaciones}");
        
        // Mostrar tipo activo
        $activo = ElectionType::where('active', true)->first();
        if ($activo) {
            $this->command->info('');
            $this->command->info("🎯 Tipo activo actual: {$activo->name}");
        }
    }
}