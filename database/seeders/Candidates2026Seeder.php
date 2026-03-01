<?php
// database/seeders/Candidates2026Seeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use Illuminate\Support\Facades\DB;

class Candidates2026Seeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('CARGANDO CANDIDATOS A ALCALDE 2026');
        $this->command->info('========================================');

        // Buscar el tipo de elección (Municipales 2026)
        $electionType = ElectionType::where('name', 'LIKE', '%Municipales 2026%')
            ->orWhere('id', 2)
            ->first();

        if (!$electionType) {
            $this->command->error('❌ No se encontró el tipo de elección "Elecciones Municipales 2026"');
            return;
        }

        $this->command->info("✅ Tipo de elección: {$electionType->name} (ID: {$electionType->id})");

        // Buscar la categoría "Alcalde"
        $alcaldeCategory = ElectionCategory::where('code', 'ALC')
            ->orWhere('name', 'Alcalde')
            ->first();

        if (!$alcaldeCategory) {
            $this->command->error('❌ No se encontró la categoría "Alcalde"');
            return;
        }

        $this->command->info("✅ Categoría: {$alcaldeCategory->name} (ID: {$alcaldeCategory->id})");

        // Buscar o crear la relación en election_type_categories
        $electionTypeCategory = ElectionTypeCategory::firstOrCreate([
            'election_type_id' => $electionType->id,
            'election_category_id' => $alcaldeCategory->id,
        ], [
            'votes_per_person' => 1,
            'has_blank_vote' => true,
            'has_null_vote' => true,
        ]);

        $this->command->info("✅ Relación Tipo-Categoría ID: {$electionTypeCategory->id}");

        // Datos de candidatos (usando election_type_category_id)
        $candidatos = [
            [
                'name' => 'Hector Cartagena',
                'party' => 'UNE',
                'party_full_name' => 'Unidad Nacional de Esperanza',
                'party_logo' => 'candidates/party-logos/s5K2f3KYMeeieGDHLFuV6ZPUvTM2vr11feDDOHYJ.jpg',
                'photo' => 'candidates/photos/AyCQ6dYldUranHMee6P1dqN9Op8QmJacyrPLK1Ql.jpg',
                'color' => '#18bcfa',
            ],
            [
                'name' => 'Jaime Aduana',
                'party' => 'FAP',
                'party_full_name' => 'Jaime Aduana',
                'party_logo' => 'candidates/party-logos/Wx2HLoLtHCLOwkbxO4UZeq9sq2AXOgskBXLzHnow.png',
                'photo' => 'candidates/photos/ChSlVBAYENwBShkaSsNopCG4LGi2kcIr8BuukJ8u.jpg',
                'color' => '#000000',
            ],
            [
                'name' => 'Marcos Cabrera',
                'party' => 'LIBRE',
                'party_full_name' => 'Libre Alianza',
                'party_logo' => 'candidates/party-logos/Z2PajBJFP7iRXfuWkGqGQxnXyuasdtlkg4IVbIFx.jpg',
                'photo' => 'candidates/photos/GR9QMJeT9iSTFpFfMw4l0UplTcrc6DyY9zM80hHg.jpg',
                'color' => '#ff0000',
            ],
            [
                'name' => 'Ariel Ramallo',
                'party' => 'MTS',
                'party_full_name' => 'Movimiento Tercer Sistema',
                'party_logo' => 'candidates/party-logos/mGvbRDLlUq31sPgBuxzTXqYcLbk7qA5hcQNuXksR.jpg',
                'photo' => 'candidates/photos/BwJcB0mEG3Z5K97BhAPrrny0e8upm5g9W7HzqvP1.png',
                'color' => '#008040',
            ],
            [
                'name' => 'Carlos López',
                'party' => 'NGP',
                'party_full_name' => 'Nueva Generación Patriotica',
                'party_logo' => 'candidates/party-logos/0m9Lrc7AzD9tEfB7XNMGtv6iRwK22ykNpR9jIquF.jpg',
                'photo' => 'candidates/photos/o072D1sKNjmNxDltzlm81WpRnXfT7d3LXSdWkfgd.jpg',
                'color' => '#f1b603',
            ],
            [
                'name' => 'Charles Becerra',
                'party' => 'Soluciones con Todos',
                'party_full_name' => 'Soluciones con Todos',
                'party_logo' => 'candidates/party-logos/cbnbf4fh7jifDeAyOOiajqeaj9pP9Fa5nZpff0c0.jpg',
                'photo' => 'candidates/photos/lbL3p8ccQyD0fs31YzOxNviQqNPMNrUKLrhw8lxB.png',
                'color' => '#ff00ff',
            ],
            [
                'name' => 'Roberto Sarabia',
                'party' => 'Fuerza Social',
                'party_full_name' => 'Fuerza Social',
                'party_logo' => 'candidates/party-logos/JTETflAx6zWBBzXD3XuuIaLMvhsQvna7efKj0WIe.jpg',
                'photo' => 'candidates/photos/Whro1HmOpGksYfkRKwzY0ckVe3kzoZfyUEcpNQg5.png',
                'color' => '#80ff00',
            ],
            [
                'name' => 'Edwin Vargas',
                'party' => 'PDC',
                'party_full_name' => 'Partido Político Demócrata Cristiano',
                'party_logo' => 'candidates/party-logos/LKlnoLdU3ZTjacKHIXpH6OKcjraRcZolgMtbNqEd.png',
                'photo' => 'candidates/photos/m9Cb5OiDC0TQbO5hJA30OsuNSoreSTs1vq5zIVGg.png',
                'color' => '#408080',
            ],
            [
                'name' => 'Percy Rosas',
                'party' => 'APB-SUMATE',
                'party_full_name' => 'APB-SUMATE',
                'party_logo' => 'candidates/party-logos/MYYFy7cRnN0nMVg9IUETDJ3iB6rEfkSDcv0PpKy2.png',
                'photo' => 'candidates/photos/7jSx7LImPNB3599Fc5O3bgEyR1Pi1bPU8WiG8bXg.png',
                'color' => '#59017e',
            ],
            [
                'name' => 'Eduardo Mérida (UN)',
                'party' => 'UN',
                'party_full_name' => 'UN',
                'party_logo' => 'candidates/party-logos/Zy6qsq9S8SAfcac0GnIPnTtlRjh5tFUejklru0dx.png',
                'photo' => 'candidates/photos/E9cq7HddtgmbnflUct0K4acczaP51etvCoiNshO4.jpg',
                'color' => '#005555',
            ],
            [
                'name' => 'Sergio Vasquez',
                'party' => 'UNIDOS',
                'party_full_name' => 'UNIDOS',
                'party_logo' => 'candidates/party-logos/gcOMersu7GD753uCIGaj4FkPWlFcRxipOgQ2UAjV.jpg',
                'photo' => 'candidates/photos/LmyvL3DePFtzy9QYDuuTH9lym8ElTihtrJXqjpuw.jpg',
                'color' => '#ff1313',
            ],
            [
                'name' => 'Oscar Claros',
                'party' => 'Alianza Patria',
                'party_full_name' => 'Alianza Patria',
                'party_logo' => 'candidates/party-logos/v5g6GMqvVIOfzOs3dcB79bymsaXLRRy5n7o5gxK0.jpg',
                'photo' => 'candidates/photos/pz9g5sk8h0grMm0CyoQbXN7nM0TVZOmhUDBcW9s8.png',
                'color' => '#ff8000',
            ],
            [
                'name' => 'Luis Santa Cruz',
                'party' => 'FRI',
                'party_full_name' => 'Frente Revolucionario de la Izquierda',
                'party_logo' => 'candidates/party-logos/3t4NCcgyxMrYg4YylYu8tXOM4ZaK5HD7AiQT68St.png',
                'photo' => 'candidates/photos/vHDsPIQQWHUuhtlTWYKywOt70Xj8HS0W5dCBoArV.png',
                'color' => '#0000ff',
            ],
            [
                'name' => 'Monica Alvis',
                'party' => 'A-UPP',
                'party_full_name' => 'Alianza por los Pueblos',
                'party_logo' => 'candidates/party-logos/xvW28ABf5JU8NBrcsu2noXO1H3V9cSzWrBfMJt9j.jpg',
                'photo' => 'candidates/photos/Roa01nLRyrQDZsCH1rtF5PusB54jLHdR3WvzlnZf.jpg',
                'color' => '#ffff80',
            ],
        ];

        DB::beginTransaction();

        try {
            $contador = 0;
            $actualizados = 0;

            foreach ($candidatos as $data) {
                // Verificar si ya existe
                $candidato = Candidate::where('name', $data['name'])
                    ->where('party', $data['party'])
                    ->where('election_type_category_id', $electionTypeCategory->id)
                    ->first();

                $candidateData = [
                    'name' => $data['name'],
                    'party' => $data['party'],
                    'party_full_name' => $data['party_full_name'],
                    'party_logo' => $data['party_logo'],
                    'photo' => $data['photo'],
                    'color' => $data['color'],
                    'election_type_category_id' => $electionTypeCategory->id, // ✅ usando la tabla pivot
                    'type' => 'candidato',
                    'active' => true,
                ];

                if ($candidato) {
                    $candidato->update($candidateData);
                    $actualizados++;
                } else {
                    Candidate::create($candidateData);
                    $contador++;
                }
            }

            DB::commit();

            $this->command->info('========================================');
            $this->command->info('✅ CARGA COMPLETADA');
            $this->command->info('========================================');
            $this->command->info("📊 Nuevos candidatos creados: {$contador}");
            $this->command->info("📝 Candidatos actualizados: {$actualizados}");
            $this->command->info("🎉 Total: " . ($contador + $actualizados));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }
}