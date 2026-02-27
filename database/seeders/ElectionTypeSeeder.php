<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElectionType;
use Carbon\Carbon;

class ElectionTypeSeeder extends Seeder
{
    public function run()
    {
        $electionTypes = [
            [
                'name' => 'Elecciones Presidenciales 2025',
                'type' => 'presidente',
                'election_date' => '2025-10-19',
                'active' => false,
            ],
            [
                'name' => 'Elecciones Municipales 2026',
                'type' => 'alcalde',
                'election_date' => '2026-03-22',
                'active' => true,
            ],
            [
                'name' => 'Elecciones Senadores 2025',
                'type' => 'senador',
                'election_date' => '2025-10-19',
                'active' => true,
            ],
            [
                'name' => 'Elecciones Diputados 2025',
                'type' => 'diputado', 
                'election_date' => '2025-10-19',
                'active' => true,
            ],
            [
                'name' => 'Elecciones Concejales 2025', 
                'type' => 'concejal',
                'election_date' => '2025-03-09',
                'active' => false,
            ],
        ];

        foreach ($electionTypes as $electionType) {
            ElectionType::create($electionType);
        }
    }
}