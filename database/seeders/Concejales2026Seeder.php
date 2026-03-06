<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Candidate;
use App\Models\ElectionType;
use App\Models\ElectionCategory;
use App\Models\ElectionTypeCategory;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;

class Concejales2026Seeder extends Seeder
{
    public function run(): void
    {
        $quillacollo = Municipality::where('name', 'Quillacollo')->first();
        if (!$quillacollo) {
            $this->command->error('❌ No se encontró el municipio de Quillacollo');
            return;
        }
        $electionType = ElectionType::where('name', 'LIKE', '%Municipal%2026%')->first();
        if (!$electionType) {
            $this->command->error('❌ No se encontró el tipo de elección Municipal 2026');
            return;
        }
        $concejalCategory = ElectionCategory::where('code', 'CON')->first();
        if (!$concejalCategory) {
            $this->command->error('❌ No se encontró la categoría CON');
            return;
        }

        $electionTypeCategory = ElectionTypeCategory::where([
            'election_type_id'    => $electionType->id,
            'election_category_id' => $concejalCategory->id,
        ])->first();

        if (!$electionTypeCategory) {
            $this->command->error('❌ No se encontró election_type_category para CON. Ejecutar ElectionTypeSeeder primero.');
            return;
        }
        $alcaldeCategory    = ElectionCategory::where('code', 'ALC')->first();
        $alcaldeTypeCategory = ElectionTypeCategory::where([
            'election_type_id'    => $electionType->id,
            'election_category_id' => $alcaldeCategory->id,
        ])->first();

        $alcaldesLogos = [];
        if ($alcaldeTypeCategory) {
            Candidate::where('election_type_category_id', $alcaldeTypeCategory->id)
                ->where('municipality_id', $quillacollo->id)
                ->get()
                ->each(function ($a) use (&$alcaldesLogos) {
                    $alcaldesLogos[$a->party] = [
                        'party_full_name' => $a->party_full_name,
                        'party_logo'      => $a->party_logo,
                        'color'           => $a->color,
                    ];
                });
        }
        $logoFor = function (string $partyKey, string $fallbackName, string $fallbackLogo, string $fallbackColor) use ($alcaldesLogos): array {
            $data = $alcaldesLogos[$partyKey] ?? null;
            return [
                'party_full_name' => $data['party_full_name'] ?? $fallbackName,
                'party_logo'      => $data['party_logo']      ?? $fallbackLogo,
                'color'           => $data['color']           ?? $fallbackColor,
            ];
        };

        $ordenFranjas = [
            'UNE' => 1, 'FAP' => 2, 'LIBRE' => 3, 'MTS' => 4, 'NGP' => 5,
            'Solucion con todos' => 6, 'FUERZA SOCIAL' => 7, 'PDC' => 8,
            'APB-SUMATE' => 9, 'UN' => 10, 'UNIDOS' => 11, 'ALIANZA PATRIA' => 12,
            'A-UPP' => 13, 'FRI' => 14,
        ];
        $concejales = [
            ['name' => 'JOHNNY ROQUE OCHOA', 'party' => 'MTS',
             ...$logoFor('MTS', 'Movimiento Tercer Sistema', 'candidates/party-logos/mGvbRDLlUq31sPgBuxzTXqYcLbk7qA5hcQNuXksR.jpg', '#008040')],
            ['name' => 'KARINA AMPARO RICO MUÑOZ', 'party' => 'NGP',
             ...$logoFor('NGP', 'Nueva Generación Patriotica', 'candidates/party-logos/0m9Lrc7AzD9tEfB7XNMGtv6iRwK22ykNpR9jIquF.jpg', '#f1b603')],
            ['name' => 'CASTO RODRIGUEZ CHOQUE', 'party' => 'Solucion con todos',
             ...$logoFor('Soluciones con Todos', 'Soluciones con Todos', 'candidates/party-logos/cbnbf4fh7jifDeAyOOiajqeaj9pP9Fa5nZpff0c0.jpg', '#ff00ff')],
            ['name' => 'DARIO JOSE ANTEZANA VARGAS', 'party' => 'UN',
             ...$logoFor('UN', 'Unidad Nacional', 'candidates/party-logos/Zy6qsq9S8SAfcac0GnIPnTtlRjh5tFUejklru0dx.png', '#005555')],
            ['name' => 'ESTEFANIA TORREZ SAN MIGUEL', 'party' => 'PDC',
             ...$logoFor('PDC', 'Partido Político Demócrata Cristiano', 'candidates/party-logos/LKlnoLdU3ZTjacKHIXpH6OKcjraRcZolgMtbNqEd.png', '#408080')],
            ['name' => 'BLADIMIR VARGAS ROJAS', 'party' => 'UNE',
             ...$logoFor('UNE', 'Unidad Nacional de Esperanza', 'candidates/party-logos/s5K2f3KYMeeieGDHLFuV6ZPUvTM2vr11feDDOHYJ.jpg', '#18bcfa')],
            ['name' => 'HEEDY GIOVANNA FLORES RODRIGUEZ', 'party' => 'FAP',
             ...$logoFor('FAP', 'Frente Amplio Popular', 'candidates/party-logos/Wx2HLoLtHCLOwkbxO4UZeq9sq2AXOgskBXLzHnow.png', '#000000')],
            ['name' => 'DENIS ALBARO CABERO RIOS', 'party' => 'FRI',
             ...$logoFor('FRI', 'Frente Revolucionario de la Izquierda', 'candidates/party-logos/3t4NCcgyxMrYg4YylYu8tXOM4ZaK5HD7AiQT68St.png', '#0000ff')],
            ['name' => 'LIVIO JHASMANY TERAN ZENTENO', 'party' => 'LIBRE',
             ...$logoFor('LIBRE', 'Libre Alianza', 'candidates/party-logos/Z2PajBJFP7iRXfuWkGqGQxnXyuasdtlkg4IVbIFx.jpg', '#ff0000')],
            ['name' => 'REYNALDO LAFUENTE TERRAZAS', 'party' => 'A-UPP',
             ...$logoFor('A-UPP', 'Alianza por los Pueblos', 'candidates/party-logos/xvW28ABf5JU8NBrcsu2noXO1H3V9cSzWrBfMJt9j.jpg', '#ffff80')],
            ['name' => 'ROSA MAVEL PADILLA MENDIETA', 'party' => 'ALIANZA PATRIA',
             ...$logoFor('Alianza Patria', 'Alianza Patria', 'candidates/party-logos/v5g6GMqvVIOfzOs3dcB79bymsaXLRRy5n7o5gxK0.jpg', '#ff8000')],
            ['name' => 'LEYDI CARLA SANTOS ESCALERA', 'party' => 'APB-SUMATE',
             ...$logoFor('APB-SUMATE', 'APB-SUMATE', 'candidates/party-logos/MYYFy7cRnN0nMVg9IUETDJ3iB6rEfkSDcv0PpKy2.png', '#59017e')],
            ['name' => 'JOSE LUIS FERNANDEZ QUINTANA', 'party' => 'FUERZA SOCIAL',
             ...$logoFor('Fuerza Social', 'Fuerza Social', 'candidates/party-logos/JTETflAx6zWBBzXD3XuuIaLMvhsQvna7efKj0WIe.jpg', '#80ff00')],
            ['name' => 'ALVARO LIMA LOPEZ', 'party' => 'UNIDOS',
             ...$logoFor('UNIDOS', 'UNIDOS', 'candidates/party-logos/gcOMersu7GD753uCIGaj4FkPWlFcRxipOgQ2UAjV.jpg', '#ff1313')],
        ];

        DB::beginTransaction();
        try {
            foreach ($concejales as $data) {
                Candidate::updateOrCreate(
                    [
                        'name'                    => $data['name'],
                        'party'                   => $data['party'],
                        'election_type_category_id' => $electionTypeCategory->id,
                    ],
                    [
                        'party_full_name'           => $data['party_full_name'],
                        'party_logo'                => $data['party_logo'],
                        'photo'                     => null,
                        'color'                     => $data['color'],
                        'active'                    => true,
                        'list_name'                 => 'CONCEJALES',
                        'list_order'                => $ordenFranjas[$data['party']] ?? null,
                        'election_type_category_id' => $electionTypeCategory->id,
                        'municipality_id'           => $quillacollo->id,
                        'province_id'               => $quillacollo->province_id,
                        'department_id'             => $quillacollo->province->department_id ?? null,
                    ]
                );
                $this->command->info("  ✅ {$data['name']} ({$data['party']}) - Franja " . ($ordenFranjas[$data['party']] ?? '?'));
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
