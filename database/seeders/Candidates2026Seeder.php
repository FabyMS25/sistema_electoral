<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Support\Facades\DB;

class Candidates2026Seeder extends Seeder
{
    public function run(): void
    {
        $quillacollo = $this->getQuillacolloMunicipality();
        if (!$quillacollo) {
            $this->command->error('❌ No se encontró el municipio de Quillacollo');
            return;
        }
        $electionType = ElectionType::where('name', 'LIKE', '%Municipal%2026%')->first();
        if (!$electionType) {
            $this->command->error('❌ No se encontró el tipo de elección Municipal 2026');
            return;
        }
        $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
        if (!$alcaldeCategory) {
            $this->command->error('❌ No se encontró la categoría "Alcalde"');
            return;
        }
        $electionTypeCategory = ElectionTypeCategory::where([
            'election_type_id'    => $electionType->id,
            'election_category_id' => $alcaldeCategory->id,
        ])->first();

        if (!$electionTypeCategory) {
            $this->command->error('❌ No se encontró el election_type_category para ALC');
            return;
        }

        $ordenFranjas = [
            'UNE' => 1, 'FAP' => 2, 'LIBRE' => 3, 'MTS' => 4, 'NGP' => 5,
            'Soluciones con Todos' => 6, 'Fuerza Social' => 7, 'PDC' => 8,
            'APB-SUMATE' => 9, 'UN' => 10, 'UNIDOS' => 11, 'Alianza Patria' => 12,
            'A-UPP' => 13, 'FRI' => 14,
        ];

        $candidatos = [
            ['name' => 'Hector Cartagena',  'party' => 'UNE', 'party_full_name' => 'Unidad Nacional de Esperanza',
             'party_logo' => 'candidates/party-logos/s5K2f3KYMeeieGDHLFuV6ZPUvTM2vr11feDDOHYJ.jpg',
             'photo' => 'candidates/photos/AyCQ6dYldUranHMee6P1dqN9Op8QmJacyrPLK1Ql.jpg', 'color' => '#18bcfa'],
            ['name' => 'Jaime Aduana',      'party' => 'FAP', 'party_full_name' => 'Frente Amplio Popular',
             'party_logo' => 'candidates/party-logos/Wx2HLoLtHCLOwkbxO4UZeq9sq2AXOgskBXLzHnow.png',
             'photo' => 'candidates/photos/ChSlVBAYENwBShkaSsNopCG4LGi2kcIr8BuukJ8u.jpg', 'color' => '#000000'],
            ['name' => 'Marcos Cabrera',    'party' => 'LIBRE', 'party_full_name' => 'Libre Alianza',
             'party_logo' => 'candidates/party-logos/Z2PajBJFP7iRXfuWkGqGQxnXyuasdtlkg4IVbIFx.jpg',
             'photo' => 'candidates/photos/GR9QMJeT9iSTFpFfMw4l0UplTcrc6DyY9zM80hHg.jpg', 'color' => '#ff0000'],
            ['name' => 'Ariel Ramallo',     'party' => 'MTS', 'party_full_name' => 'Movimiento Tercer Sistema',
             'party_logo' => 'candidates/party-logos/mGvbRDLlUq31sPgBuxzTXqYcLbk7qA5hcQNuXksR.jpg',
             'photo' => 'candidates/photos/BwJcB0mEG3Z5K97BhAPrrny0e8upm5g9W7HzqvP1.png', 'color' => '#008040'],
            ['name' => 'Carlos López',      'party' => 'NGP', 'party_full_name' => 'Nueva Generación Patriotica',
             'party_logo' => 'candidates/party-logos/0m9Lrc7AzD9tEfB7XNMGtv6iRwK22ykNpR9jIquF.jpg',
             'photo' => 'candidates/photos/o072D1sKNjmNxDltzlm81WpRnXfT7d3LXSdWkfgd.jpg', 'color' => '#f1b603'],
            ['name' => 'Charles Becerra',   'party' => 'Soluciones con Todos', 'party_full_name' => 'Soluciones con Todos',
             'party_logo' => 'candidates/party-logos/cbnbf4fh7jifDeAyOOiajqeaj9pP9Fa5nZpff0c0.jpg',
             'photo' => 'candidates/photos/lbL3p8ccQyD0fs31YzOxNviQqNPMNrUKLrhw8lxB.png', 'color' => '#ff00ff'],
            ['name' => 'Roberto Sarabia',   'party' => 'Fuerza Social', 'party_full_name' => 'Fuerza Social',
             'party_logo' => 'candidates/party-logos/JTETflAx6zWBBzXD3XuuIaLMvhsQvna7efKj0WIe.jpg',
             'photo' => 'candidates/photos/Whro1HmOpGksYfkRKwzY0ckVe3kzoZfyUEcpNQg5.png', 'color' => '#80ff00'],
            ['name' => 'Edwin Vargas',      'party' => 'PDC', 'party_full_name' => 'Partido Político Demócrata Cristiano',
             'party_logo' => 'candidates/party-logos/LKlnoLdU3ZTjacKHIXpH6OKcjraRcZolgMtbNqEd.png',
             'photo' => 'candidates/photos/m9Cb5OiDC0TQbO5hJA30OsuNSoreSTs1vq5zIVGg.png', 'color' => '#408080'],
            ['name' => 'Percy Rosas',       'party' => 'APB-SUMATE', 'party_full_name' => 'APB-SUMATE',
             'party_logo' => 'candidates/party-logos/MYYFy7cRnN0nMVg9IUETDJ3iB6rEfkSDcv0PpKy2.png',
             'photo' => 'candidates/photos/7jSx7LImPNB3599Fc5O3bgEyR1Pi1bPU8WiG8bXg.png', 'color' => '#59017e'],
            ['name' => 'Eduardo Mérida (UN)', 'party' => 'UN', 'party_full_name' => 'Unidad Nacional',
             'party_logo' => 'candidates/party-logos/Zy6qsq9S8SAfcac0GnIPnTtlRjh5tFUejklru0dx.png',
             'photo' => 'candidates/photos/E9cq7HddtgmbnflUct0K4acczaP51etvCoiNshO4.jpg', 'color' => '#005555'],
            ['name' => 'Sergio Vasquez',    'party' => 'UNIDOS', 'party_full_name' => 'UNIDOS',
             'party_logo' => 'candidates/party-logos/gcOMersu7GD753uCIGaj4FkPWlFcRxipOgQ2UAjV.jpg',
             'photo' => 'candidates/photos/LmyvL3DePFtzy9QYDuuTH9lym8ElTihtrJXqjpuw.jpg', 'color' => '#ff1313'],
            ['name' => 'Oscar Claros',      'party' => 'Alianza Patria', 'party_full_name' => 'Alianza Patria',
             'party_logo' => 'candidates/party-logos/v5g6GMqvVIOfzOs3dcB79bymsaXLRRy5n7o5gxK0.jpg',
             'photo' => 'candidates/photos/pz9g5sk8h0grMm0CyoQbXN7nM0TVZOmhUDBcW9s8.png', 'color' => '#ff8000'],
            ['name' => 'Luis Santa Cruz',   'party' => 'FRI', 'party_full_name' => 'Frente Revolucionario de la Izquierda',
             'party_logo' => 'candidates/party-logos/3t4NCcgyxMrYg4YylYu8tXOM4ZaK5HD7AiQT68St.png',
             'photo' => 'candidates/photos/vHDsPIQQWHUuhtlTWYKywOt70Xj8HS0W5dCBoArV.png', 'color' => '#0000ff'],
            ['name' => 'Monica Alvis',      'party' => 'A-UPP', 'party_full_name' => 'Alianza por los Pueblos',
             'party_logo' => 'candidates/party-logos/xvW28ABf5JU8NBrcsu2noXO1H3V9cSzWrBfMJt9j.jpg',
             'photo' => 'candidates/photos/Roa01nLRyrQDZsCH1rtF5PusB54jLHdR3WvzlnZf.jpg', 'color' => '#ffff80'],
        ];

        DB::beginTransaction();
        try {
            foreach ($candidatos as $data) {
                Candidate::updateOrCreate(
                    [
                        'name'  => $data['name'],
                        'party' => $data['party'],
                        'election_type_category_id' => $electionTypeCategory->id,
                    ],
                    [
                        'party_full_name'           => $data['party_full_name'],
                        'party_logo'                => $data['party_logo'],
                        'photo'                     => $data['photo'],
                        'color'                     => $data['color'],
                        'active'                    => true,
                        'list_name'                 => 'ALCALDES',
                        'list_order'                => $ordenFranjas[$data['party']] ?? null,
                        'election_type_category_id' => $electionTypeCategory->id,
                        'municipality_id'           => $quillacollo->id,
                        'province_id'               => $quillacollo->province_id,
                        'department_id'             => $quillacollo->province->department_id ?? null,
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getQuillacolloMunicipality()
    {
        return Municipality::where('name', 'Quillacollo')->first()
            ?? Municipality::whereHas('province', fn($q) => $q->where('name', 'Quillacollo'))
                ->where('name', 'Quillacollo')->first();
    }
}
