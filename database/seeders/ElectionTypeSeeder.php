<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use App\Models\Department;
use App\Models\Municipality;

class ElectionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'             => 'Alcalde',
                'code'             => 'ALC',
                'description'      => 'Elección de Alcalde Municipal',
                'default_order'    => 1,
                'geographic_scope' => 'municipal',
                'allows_list'      => false,
                'active'           => true,
            ],
            [
                'name'             => 'Concejal',
                'code'             => 'CON',
                'description'      => 'Elección de Concejales Municipales',
                'default_order'    => 2,
                'geographic_scope' => 'municipal',
                'allows_list'      => true,
                'active'           => true,
            ],
            [
                'name'             => 'Gobernador',
                'code'             => 'GOB',
                'description'      => 'Elección de Gobernador Departamental',
                'default_order'    => 1,
                'geographic_scope' => 'departamental',
                'allows_list'      => false,
                'active'           => true,
            ],
            [
                'name'             => 'Asambleísta por Territorio',
                'code'             => 'AST',
                'description'      => 'Elección de Asambleístas Departamentales por Territorio',
                'default_order'    => 2,
                'geographic_scope' => 'provincial',
                'allows_list'      => true,
                'active'           => true,
            ],
            [
                'name'             => 'Asambleísta por Población',
                'code'             => 'ASP',
                'description'      => 'Elección de Asambleístas Departamentales por Población',
                'default_order'    => 3,
                'geographic_scope' => 'departamental',
                'allows_list'      => true,
                'active'           => true,
            ],
        ];

        $createdCategories = [];
        foreach ($categories as $catData) {
            $category = ElectionCategory::updateOrCreate(
                ['code' => $catData['code']],
                $catData
            );
            $createdCategories[$catData['code']] = $category->id;
        }

        $Cbba          = Department::where('name', 'Cochabamba')->first();
        $QlloMunicipio = Municipality::where('name', 'Quillacollo')->first();
        if (!$Cbba) {
            $this->command->warn('⚠️  Departamento Cochabamba no encontrado — geographic_scope_id será null');
        }
        if (!$QlloMunicipio) {
            $this->command->warn('⚠️  Municipio Quillacollo no encontrado — geographic_scope_id será null');
        }
        $electionTypes = [
            [
                'name'                  => 'Elecciones Departamentales Cochabamba 2026',
                'short_name'            => 'Departamental 2026',
                'level'                 => 'departamental',
                'geographic_scope_type' => $Cbba ? get_class($Cbba) : null,
                'geographic_scope_id'   => $Cbba?->id,
                'election_date'         => '2026-03-22',
                'start_time'            => '08:00:00',
                'end_time'              => '17:00:00',
                'active'                => true,
                'description'           => 'Elecciones Subnacionales 2026 - Papeleta Departamental (3 franjas)',
                'categories'            => ['GOB', 'AST', 'ASP'],
            ],
            [
                'name'                  => 'Elecciones Municipales Quillacollo 2026',
                'short_name'            => 'Municipal 2026',
                'level'                 => 'municipal',
                'geographic_scope_type' => $QlloMunicipio ? get_class($QlloMunicipio) : null,
                'geographic_scope_id'   => $QlloMunicipio?->id,
                'election_date'         => '2026-03-22',
                'start_time'            => '08:00:00',
                'end_time'              => '17:00:00',
                'active'                => true,
                'description'           => 'Elecciones Subnacionales 2026 - Papeleta Municipal (2 franjas)',
                'categories'            => ['ALC', 'CON'],
            ],
        ];

        foreach ($electionTypes as $typeData) {
            $categoryCodes = $typeData['categories'];
            unset($typeData['categories']);
            $electionType = ElectionType::updateOrCreate(
                ['name' => $typeData['name']],
                $typeData
            );
            foreach ($categoryCodes as $ballotOrder => $code) {
                $ballotOrder = $ballotOrder + 1;
                $categoryId = $createdCategories[$code] ?? null;
                if (!$categoryId) {
                    $this->command->warn("     ⚠️  Categoría {$code} no encontrada — saltando");
                    continue;
                }
                ElectionTypeCategory::updateOrCreate(
                    [
                        'election_type_id'     => $electionType->id,
                        'election_category_id' => $categoryId,
                    ],
                    [
                        'ballot_order'    => $ballotOrder,
                        'votes_per_person' => 1,
                        'has_blank_vote'  => true,
                        'has_null_vote'   => true,
                    ]
                );
            }
        }
    }
}
