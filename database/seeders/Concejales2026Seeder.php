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

class Concejales2026Seeder extends Seeder
{
    public function run(): void
    {
        $quillacollo = $this->getQuillacolloMunicipality();
        if (!$quillacollo) {
            $this->command->error('❌ No se encontró el municipio de Quillacollo');
            return;
        }
        $electionType = ElectionType::where('name', 'LIKE', '%Municipales 2026%')
            ->orWhere('id', 2)
            ->first();
        if (!$electionType) {
            $this->command->error('❌ No se encontró el tipo de elección "Elecciones Municipales 2026"');
            return;
        }
        $concejalCategory = ElectionCategory::where('code', 'CON')
            ->orWhere('name', 'Concejal')
            ->first();
        if (!$concejalCategory) {
            $this->command->error('❌ No se encontró la categoría "Concejal"');
            return;
        }

        $electionTypeCategory = ElectionTypeCategory::firstOrCreate([
            'election_type_id' => $electionType->id,
            'election_category_id' => $concejalCategory->id,
        ], [
            'votes_per_person' => 1,
            'has_blank_vote' => true,
            'has_null_vote' => true,
        ]);
        $alcaldeCategory = ElectionCategory::where('code', 'ALC')->first();
        $alcaldeTypeCategory = ElectionTypeCategory::where([
            'election_type_id' => $electionType->id,
            'election_category_id' => $alcaldeCategory->id,
        ])->first();
        $alcaldesLogos = [];
        if ($alcaldeTypeCategory) {
            $alcaldes = Candidate::where('election_type_category_id', $alcaldeTypeCategory->id)
                ->where('municipality_id', $quillacollo->id)
                ->get();
            foreach ($alcaldes as $alcalde) {
                $alcaldesLogos[$alcalde->party] = [
                    'party_full_name' => $alcalde->party_full_name,
                    'party_logo' => $alcalde->party_logo,
                    'color' => $alcalde->color,
                ];
            }
        }

        $ordenFranjas = [
            'UNE' => 1,
            'FAP' => 2,
            'LIBRE' => 3,
            'MTS' => 4,
            'NGP' => 5,
            'Solucion con todos' => 6,
            'FUERZA SOCIAL' => 7,
            'PDC' => 8,
            'APB-SUMATE' => 9,
            'UN' => 10,
            'UNIDOS' => 11,
            'ALIANZA PATRIA' => 12,
            'A-UPP' => 13,
            'FRI' => 14,
            'BLANCO' => 15,
            'NULO' => 16,
        ];

        $concejales = [
            [
                'name' => 'JOHNNY ROQUE OCHOA',
                'party' => 'MTS',
                'party_full_name' => $alcaldesLogos['MTS']['party_full_name'] ?? 'Movimiento Tercer Sistema',
                'party_logo' => $alcaldesLogos['MTS']['party_logo'] ?? 'candidates/party-logos/mGvbRDLlUq31sPgBuxzTXqYcLbk7qA5hcQNuXksR.jpg',
                'color' => $alcaldesLogos['MTS']['color'] ?? '#008040',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['MTS'] ?? 4,
                'type' => 'candidato',
            ],
            [
                'name' => 'KARINA AMPARO RICO MUÑOZ',
                'party' => 'NGP',
                'party_full_name' => $alcaldesLogos['NGP']['party_full_name'] ?? 'Nueva Generación Patriotica',
                'party_logo' => $alcaldesLogos['NGP']['party_logo'] ?? 'candidates/party-logos/0m9Lrc7AzD9tEfB7XNMGtv6iRwK22ykNpR9jIquF.jpg',
                'color' => $alcaldesLogos['NGP']['color'] ?? '#f1b603',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['NGP'] ?? 5,
                'type' => 'candidato',
            ],
            [
                'name' => 'CASTO RODRIGUEZ CHOQUE',
                'party' => 'Solucion con todos',
                'party_full_name' => $alcaldesLogos['Soluciones con Todos']['party_full_name'] ?? 'Soluciones con Todos',
                'party_logo' => $alcaldesLogos['Soluciones con Todos']['party_logo'] ?? 'candidates/party-logos/cbnbf4fh7jifDeAyOOiajqeaj9pP9Fa5nZpff0c0.jpg',
                'color' => $alcaldesLogos['Soluciones con Todos']['color'] ?? '#ff00ff',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['Solucion con todos'] ?? 6,
                'type' => 'candidato',
            ],
            [
                'name' => 'DARIO JOSE ANTEZANA VARGAS',
                'party' => 'UN',
                'party_full_name' => $alcaldesLogos['UN']['party_full_name'] ?? 'Unidad Nacional',
                'party_logo' => $alcaldesLogos['UN']['party_logo'] ?? 'candidates/party-logos/Zy6qsq9S8SAfcac0GnIPnTtlRjh5tFUejklru0dx.png',
                'color' => $alcaldesLogos['UN']['color'] ?? '#005555',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['UN'] ?? 10,
                'type' => 'candidato',
            ],
            [
                'name' => 'ESTEFANIA TORREZ SAN MIGUEL',
                'party' => 'PDC',
                'party_full_name' => $alcaldesLogos['PDC']['party_full_name'] ?? 'Partido Político Demócrata Cristiano',
                'party_logo' => $alcaldesLogos['PDC']['party_logo'] ?? 'candidates/party-logos/LKlnoLdU3ZTjacKHIXpH6OKcjraRcZolgMtbNqEd.png',
                'color' => $alcaldesLogos['PDC']['color'] ?? '#408080',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['PDC'] ?? 8,
                'type' => 'candidato',
            ],
            [
                'name' => 'BLADIMIR VARGAS ROJAS',
                'party' => 'UNE',
                'party_full_name' => $alcaldesLogos['UNE']['party_full_name'] ?? 'Unidad Nacional de Esperanza',
                'party_logo' => $alcaldesLogos['UNE']['party_logo'] ?? 'candidates/party-logos/s5K2f3KYMeeieGDHLFuV6ZPUvTM2vr11feDDOHYJ.jpg',
                'color' => $alcaldesLogos['UNE']['color'] ?? '#18bcfa',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['UNE'] ?? 1,
                'type' => 'candidato',
            ],
            [
                'name' => 'HEEDY GIOVANNA FLORES RODRIGUEZ',
                'party' => 'FAP',
                'party_full_name' => $alcaldesLogos['FAP']['party_full_name'] ?? 'Frente Amplio Popular',
                'party_logo' => $alcaldesLogos['FAP']['party_logo'] ?? 'candidates/party-logos/Wx2HLoLtHCLOwkbxO4UZeq9sq2AXOgskBXLzHnow.png',
                'color' => $alcaldesLogos['FAP']['color'] ?? '#000000',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['FAP'] ?? 2,
                'type' => 'candidato',
            ],
            [
                'name' => 'DENIS ALBARO CABERO RIOS',
                'party' => 'FRI',
                'party_full_name' => $alcaldesLogos['FRI']['party_full_name'] ?? 'Frente Revolucionario de la Izquierda',
                'party_logo' => $alcaldesLogos['FRI']['party_logo'] ?? 'candidates/party-logos/3t4NCcgyxMrYg4YylYu8tXOM4ZaK5HD7AiQT68St.png',
                'color' => $alcaldesLogos['FRI']['color'] ?? '#0000ff',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['FRI'] ?? 16,
                'type' => 'candidato',
            ],
            [
                'name' => 'LIVIO JHASMANY TERAN ZENTENO',
                'party' => 'LIBRE',
                'party_full_name' => $alcaldesLogos['LIBRE']['party_full_name'] ?? 'Libre Alianza',
                'party_logo' => $alcaldesLogos['LIBRE']['party_logo'] ?? 'candidates/party-logos/Z2PajBJFP7iRXfuWkGqGQxnXyuasdtlkg4IVbIFx.jpg',
                'color' => $alcaldesLogos['LIBRE']['color'] ?? '#ff0000',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['LIBRE'] ?? 3,
                'type' => 'candidato',
            ],
            [
                'name' => 'REYNALDO LAFUENTE TERRAZAS',
                'party' => 'A-UPP',
                'party_full_name' => $alcaldesLogos['A-UPP']['party_full_name'] ?? 'Alianza por los Pueblos',
                'party_logo' => $alcaldesLogos['A-UPP']['party_logo'] ?? 'candidates/party-logos/xvW28ABf5JU8NBrcsu2noXO1H3V9cSzWrBfMJt9j.jpg',
                'color' => $alcaldesLogos['A-UPP']['color'] ?? '#ffff80',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['A-UPP'] ?? 14,
                'type' => 'candidato',
            ],
            [
                'name' => 'ROSA MAVEL PADILLA MENDIETA',
                'party' => 'ALIANZA PATRIA',
                'party_full_name' => $alcaldesLogos['Alianza Patria']['party_full_name'] ?? 'Alianza Patria',
                'party_logo' => $alcaldesLogos['Alianza Patria']['party_logo'] ?? 'candidates/party-logos/v5g6GMqvVIOfzOs3dcB79bymsaXLRRy5n7o5gxK0.jpg',
                'color' => $alcaldesLogos['Alianza Patria']['color'] ?? '#ff8000',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['ALIANZA PATRIA'] ?? 12,
                'type' => 'candidato',
            ],
            [
                'name' => 'LEYDI CARLA SANTOS ESCALERA',
                'party' => 'APB-SUMATE',
                'party_full_name' => $alcaldesLogos['APB-SUMATE']['party_full_name'] ?? 'APB-SUMATE',
                'party_logo' => $alcaldesLogos['APB-SUMATE']['party_logo'] ?? 'candidates/party-logos/MYYFy7cRnN0nMVg9IUETDJ3iB6rEfkSDcv0PpKy2.png',
                'color' => $alcaldesLogos['APB-SUMATE']['color'] ?? '#59017e',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['APB-SUMATE'] ?? 9,
                'type' => 'candidato',
            ],
            [
                'name' => 'JOSE LUIS FERNANDEZ QUINTANA',
                'party' => 'FUERZA SOCIAL',
                'party_full_name' => $alcaldesLogos['Fuerza Social']['party_full_name'] ?? 'Fuerza Social',
                'party_logo' => $alcaldesLogos['Fuerza Social']['party_logo'] ?? 'candidates/party-logos/JTETflAx6zWBBzXD3XuuIaLMvhsQvna7efKj0WIe.jpg',
                'color' => $alcaldesLogos['Fuerza Social']['color'] ?? '#80ff00',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['FUERZA SOCIAL'] ?? 7,
                'type' => 'candidato',
            ],
            [
                'name' => 'ALVARO LIMA LOPEZ',
                'party' => 'UNIDOS',
                'party_full_name' => $alcaldesLogos['UNIDOS']['party_full_name'] ?? 'UNIDOS',
                'party_logo' => $alcaldesLogos['UNIDOS']['party_logo'] ?? 'candidates/party-logos/gcOMersu7GD753uCIGaj4FkPWlFcRxipOgQ2UAjV.jpg',
                'color' => $alcaldesLogos['UNIDOS']['color'] ?? '#ff1313',
                'photo' => null,
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['UNIDOS'] ?? 11,
                'type' => 'candidato',
            ],
            [
                'name' => 'BLANCO',
                'party' => '_',
                'party_full_name' => '',
                'party_logo' => 'candidates/party-logos/Qq0uzpiwVnIZEz7gTnKopZKkvWYd0ZbVynIChnNI.png',
                'photo' => 'candidates/party-logos/Qq0uzpiwVnIZEz7gTnKopZKkvWYd0ZbVynIChnNI.png',
                'color' => '#e4e4e4',
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['BLANCO'],
                'type' => 'blank_votes'
            ],
            [
                'name' => 'NULO',
                'party' => '_',
                'party_full_name' => '',
                'party_logo' => 'candidates/party-logos/dzrjldakTuKe514IPmLBYZbJ8U11VfdF0sLBsloh.png',
                'photo' => 'candidates/party-logos/dzrjldakTuKe514IPmLBYZbJ8U11VfdF0sLBsloh.png',
                'color' => '#ff0000',
                'list_name' => 'CONCEJALES',
                'list_order' => $ordenFranjas['NULO'],
                'type' => 'null_votes'
            ],
        ];

        DB::beginTransaction();
        try {
            $contador = 0;
            $actualizados = 0;

            foreach ($concejales as $data) {
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
                    'type' => $data['type'],
                    'active' => true,
                    'election_type_category_id' => $electionTypeCategory->id,
                    'municipality_id' => $quillacollo->id,
                    'province_id' => $quillacollo->province_id,
                    'department_id' => $quillacollo->province->department_id ?? null,
                    'list_name' => $data['list_name'],
                    'list_order' => $data['list_order'],
                ];

                if ($candidato) {
                    $candidato->update($candidateData);
                    $actualizados++;
                    $this->command->info("  🔄 Actualizado: {$data['name']} ({$data['party']}) - Franja {$data['list_order']}");
                } else {
                    Candidate::create($candidateData);
                    $contador++;
                    $this->command->info("  ✅ Creado: {$data['name']} ({$data['party']}) - Franja {$data['list_order']}");
                }
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
        $quillacollo = Municipality::where('name', 'Quillacollo')->first();
        if (!$quillacollo) {
            $province = Province::where('name', 'Quillacollo')->first();
            if ($province) {
                $quillacollo = Municipality::where('province_id', $province->id)
                    ->where('name', 'Quillacollo')
                    ->first();
            }
        }
        return $quillacollo;
    }
}
