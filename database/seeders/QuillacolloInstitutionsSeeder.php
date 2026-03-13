<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Locality, Institution, Municipality, VotingTable, VotingTableElection, ElectionType};
use Illuminate\Support\Facades\DB;

class QuillacolloInstitutionsSeeder extends Seeder
{
    protected $recintos = [
        ['name' => 'UNIDAD EDUCATIVA NESTOR ADRIAZOLA', 'code' => '309010101', 'locality' => 'Quillacollo', 'mesas' => 31, 'last_mesa_voters' => 85, 'oep_inicio' => 303066],
        ['name' => 'ESCUELA SIMON BOLIVAR', 'code' => '309010102', 'locality' => 'Quillacollo', 'mesas' => 16, 'last_mesa_voters' => 21, 'oep_inicio' => 303139],
        ['name' => 'INSTITUTO PARTICULAR QUILLACOLLO', 'code' => '309010103', 'locality' => 'Quillacollo', 'mesas' => 14, 'last_mesa_voters' => 129, 'oep_inicio' => 303052],
        ['name' => 'UNIDAD EDUCATIVA VILLA MODERNA', 'code' => '309010104', 'locality' => 'Quillacollo', 'mesas' => 19, 'last_mesa_voters' => 167, 'oep_inicio' => 303098],
        ['name' => 'CARCEL PENAL DE SAN PABLO', 'code' => '309010105', 'locality' => 'Quillacollo', 'mesas' => 2, 'last_mesa_voters' => 177, 'oep_inicio' => 303027],
        ['name' => 'UNIDAD EDUCATIVA 1RO DE MAYO', 'code' => '309010106', 'locality' => 'Quillacollo', 'mesas' => 4, 'last_mesa_voters' => 120, 'oep_inicio' => 303023],
        ['name' => 'UNIDAD EDUCATIVA SAN MARTIN DE PORRES (TARDE)', 'code' => '309010107', 'locality' => 'Quillacollo', 'mesas' => 1, 'last_mesa_voters' => 250, 'oep_inicio' => 303218, 'suffix' => '-T'],
        ['name' => 'COLEGIO CRISTINA PRADO', 'code' => '309010108', 'locality' => 'Quillacollo', 'mesas' => 14, 'last_mesa_voters' => 192, 'oep_inicio' => 303182],
        ['name' => 'COLEGIO FRANZ TAMAYO', 'code' => '309010109', 'locality' => 'Quillacollo', 'mesas' => 22, 'last_mesa_voters' => 69, 'oep_inicio' => 303196],
        ['name' => 'LICEO AMERICA', 'code' => '309010110', 'locality' => 'Quillacollo', 'mesas' => 23, 'last_mesa_voters' => 130, 'oep_inicio' => 303029],
        ['name' => 'ESCUELA FIDELIA C. DE SANCHEZ', 'code' => '309010111', 'locality' => 'Quillacollo', 'mesas' => 12, 'last_mesa_voters' => 70, 'oep_inicio' => 303157],
        ['name' => 'UNIDAD EDUCATIVA HEROINAS', 'code' => '309010112', 'locality' => 'Quillacollo', 'mesas' => 13, 'last_mesa_voters' => 30, 'oep_inicio' => 303169],
        ['name' => 'TEOFILO VARGAS CANDIA B', 'code' => '309010113', 'locality' => 'Quillacollo', 'mesas' => 22, 'last_mesa_voters' => 185, 'oep_inicio' => 303117],
        ['name' => 'UNIDAD EDUCATIVA FLORA SALINAS HINOJOSA - AMALIA ECHALAR', 'code' => '309010114', 'locality' => 'Quillacollo', 'mesas' => 1, 'last_mesa_voters' => 166, 'oep_inicio' => 303218, 'suffix' => '-F'],
        ['name' => 'UNIDAD EDUCATIVA NUESTRA SEÑORA DE URCUPIÑA', 'code' => '309010115', 'locality' => 'Quillacollo', 'mesas' => 2, 'last_mesa_voters' => 234, 'oep_inicio' => 303155],
        ['name' => 'UNIDAD EDUCATIVA MILIVOY ETEROVIC MATENDA', 'code' => '309010116', 'locality' => 'Quillacollo', 'mesas' => 14, 'last_mesa_voters' => 168, 'oep_inicio' => 303001],
        ['name' => 'ESCUELA 12 DE SEPTIEMBRE', 'code' => '309010117', 'locality' => 'Quillacollo', 'mesas' => 16, 'last_mesa_voters' => 126, 'oep_inicio' => 303243],
        ['name' => 'ESCUELA TOMAS BATA', 'code' => '309010118', 'locality' => 'Quillacollo', 'mesas' => 13, 'last_mesa_voters' => 14, 'oep_inicio' => 303223],
        ['name' => 'UNIDAD EDUCATIVA 12 DE ENERO B', 'code' => '309010119', 'locality' => 'Quillacollo', 'mesas' => 5, 'last_mesa_voters' => 254, 'oep_inicio' => 303238],
        ['name' => 'UNIDAD EDUCATIVA VILLA ASUNCION', 'code' => '309010120', 'locality' => 'Quillacollo', 'mesas' => 4, 'last_mesa_voters' => 224, 'oep_inicio' => 303219],
        ['name' => 'UNIDAD EDUCATIVA SAN MARTIN DE PORRES', 'code' => '309010121', 'locality' => 'Quillacollo', 'mesas' => 2, 'last_mesa_voters' => 289, 'oep_inicio' => 303236],
        ['name' => 'COLEGIO NACIONAL CALAMA', 'code' => '309010122', 'locality' => 'Quillacollo', 'mesas' => 6, 'last_mesa_voters' => 238, 'oep_inicio' => 303457],
        ['name' => 'UNIDAD EDUCATIVA IRONCOLLO', 'code' => '309010123', 'locality' => 'Quillacollo', 'mesas' => 15, 'last_mesa_voters' => 267, 'oep_inicio' => 303281],
        ['name' => 'UNIDAD EDUCATIVA MARTIN CARDENAS', 'code' => '309010124', 'locality' => 'Quillacollo', 'mesas' => 11, 'last_mesa_voters' => 192, 'oep_inicio' => 303270],
        ['name' => 'UNIDAD EDUCATIVA TUNARI', 'code' => '309010125', 'locality' => 'Quillacollo', 'mesas' => 8, 'last_mesa_voters' => 107, 'oep_inicio' => 303259],
        ['name' => 'UNIDAD EDUCATIVA 23 DE MARZO', 'code' => '309010126', 'locality' => 'Quillacollo', 'mesas' => 3, 'last_mesa_voters' => 159, 'oep_inicio' => 303267],
        ['name' => 'ESCUELA FELIZ MARTINEZ', 'code' => '309010130', 'locality' => 'Quillacollo', 'mesas' => 29, 'last_mesa_voters' => 120, 'oep_inicio' => 303387],
        ['name' => 'CENTRO INTEGRAL NIÑO JESUS FE Y ALEGRIA', 'code' => '309010131', 'locality' => 'Quillacollo', 'mesas' => 9, 'last_mesa_voters' => 128, 'oep_inicio' => 303416],
        ['name' => 'UNIDAD EDUCATIVA VILLA URCUPIÑA', 'code' => '309010133', 'locality' => 'Quillacollo', 'mesas' => 18, 'last_mesa_voters' => 71, 'oep_inicio' => 303309, 'suffix' => '-U'],
        ['name' => 'ESCUELA ARTURO QUITON', 'code' => '309010140', 'locality' => 'Quillacollo', 'mesas' => 19, 'last_mesa_voters' => 198, 'oep_inicio' => 303354],
        ['name' => 'UNIDAD EDUCATIVA RENE CRESPO RICO', 'code' => '309010141', 'locality' => 'Quillacollo', 'mesas' => 3, 'last_mesa_voters' => 78, 'oep_inicio' => 303373],
        ['name' => 'UNIDAD EDUCATIVA JOSE MIGUEL LANZA', 'code' => '309010201', 'locality' => 'Illataco', 'mesas' => 9, 'last_mesa_voters' => 58, 'oep_inicio' => 303376],
        ['name' => 'NORMAL SIMON RODRIGUEZ', 'code' => '309010301', 'locality' => 'Piñami', 'mesas' => 14, 'last_mesa_voters' => 32, 'oep_inicio' => 303429],
        ['name' => 'UNIDAD EDUCATIVA 21 DE SEPTIEMBRE', 'code' => '309010302', 'locality' => 'Piñami', 'mesas' => 13, 'last_mesa_voters' => 165, 'oep_inicio' => 303296],
        ['name' => 'UNIDAD EDUCATIVA POCPOCOLLO', 'code' => '309010303', 'locality' => 'Piñami', 'mesas' => 2, 'last_mesa_voters' => 262, 'oep_inicio' => 303385],
        ['name' => 'UNIDAD EDUCATIVA JOSE BALLIVIAN', 'code' => '309010304', 'locality' => 'Piñami', 'mesas' => 14, 'last_mesa_voters' => 117, 'oep_inicio' => 303465],
        ['name' => 'UNIDAD EDUCATIVA OSCAR ALFARO', 'code' => '309010401', 'locality' => 'Paucarpata', 'mesas' => 4, 'last_mesa_voters' => 194, 'oep_inicio' => 303425],
        ['name' => 'UNIDAD EDUCATIVA CERRO COTA', 'code' => '309010501', 'locality' => 'Cotapachi', 'mesas' => 2, 'last_mesa_voters' => 226, 'oep_inicio' => 303327],
        ['name' => 'UNIDAD EDUCATIVA COTAPACHI', 'code' => '309010502', 'locality' => 'Cotapachi', 'mesas' => 2, 'last_mesa_voters' => 331, 'oep_inicio' => 303463],
        ['name' => 'UNIDAD EDUCATIVA CALVARIO', 'code' => '309010503', 'locality' => 'Cotapachi', 'mesas' => 13, 'last_mesa_voters' => 222, 'oep_inicio' => 303309, 'suffix' => '-C'],
        ['name' => 'UNIDAD EDUCATIVA PIÑAMI', 'code' => '309010504', 'locality' => 'Cotapachi', 'mesas' => 19, 'last_mesa_voters' => 266, 'oep_inicio' => 303354, 'suffix' => '-P'],
        ['name' => 'UNIDAD EDUCATIVA EL PASO', 'code' => '309010601', 'locality' => 'El Paso', 'mesas' => 18, 'last_mesa_voters' => 166, 'oep_inicio' => 303486],
        ['name' => 'UNIDAD EDUCATIVA MARIA AUXILIADORA', 'code' => '309010602', 'locality' => 'El Paso', 'mesas' => 7, 'last_mesa_voters' => 139, 'oep_inicio' => 303479],
        ['name' => 'INSTITUTO TECNOLOGICO EL PASO', 'code' => '309010603', 'locality' => 'El Paso', 'mesas' => 9, 'last_mesa_voters' => 213, 'oep_inicio' => 303520],
        ['name' => 'UNIDAD EDUCATIVA EL PASO A', 'code' => '309010604', 'locality' => 'El Paso', 'mesas' => 16, 'last_mesa_voters' => 170, 'oep_inicio' => 303504],
        ['name' => 'UNIDAD EDUCATIVA MOLLE MOLLE', 'code' => '309010605', 'locality' => 'El Paso', 'mesas' => 2, 'last_mesa_voters' => 203, 'oep_inicio' => 303529],
        ['name' => 'UNIDAD EDUCATIVA SANTIAGO APOSTOL', 'code' => '309010606', 'locality' => 'El Paso', 'mesas' => 2, 'last_mesa_voters' => 225, 'oep_inicio' => 303531],
        ['name' => 'UNIDAD EDUCATIVA BELLA VISTA', 'code' => '309010701', 'locality' => 'Bella Vista', 'mesas' => 23, 'last_mesa_voters' => 220, 'oep_inicio' => 303533],
        ['name' => 'UNIDAD EDUCATIVA RENE BARRIENTOS ORTUÑO', 'code' => '309010801', 'locality' => 'Misicuni', 'mesas' => 2, 'last_mesa_voters' => 156, 'oep_inicio' => 303556],
        ['name' => 'CENTRO INTERNADO MISICUNI', 'code' => '309010802', 'locality' => 'Misicuni', 'mesas' => 3, 'last_mesa_voters' => 100, 'oep_inicio' => 303558],
        ['name' => 'UNIDAD EDUCATIVA LIRIUNI', 'code' => '309010901', 'locality' => 'Liriuni', 'mesas' => 1, 'last_mesa_voters' => 246, 'oep_inicio' => 303561],
        ['name' => 'UNIDAD EDUCATIVA POTRERO', 'code' => '309011001', 'locality' => 'Potrero', 'mesas' => 6, 'last_mesa_voters' => 271, 'oep_inicio' => 303443],
    ];

    public function run(): void
    {
        $municipality = Municipality::where('name', 'Quillacollo')->first();
        $election = ElectionType::where('name', 'LIKE', '%Municipal%2026%')->first();
        if (!$municipality || !$election) return;

        DB::beginTransaction();
        try {
            foreach ($this->recintos as $data) {
                // Calcular total de ciudadanos real basado en actas
                $totalCitizens = ($data['mesas'] > 1)
                    ? (240 * ($data['mesas'] - 1)) + $data['last_mesa_voters']
                    : $data['last_mesa_voters'];

                // 1. Crear Localidad
                $locality = Locality::firstOrCreate(['name' => $data['locality'], 'municipality_id' => $municipality->id]);

                // 2. Crear Institución
                $inst = Institution::updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'name' => $data['name'],
                        'municipality_id' => $municipality->id,
                        'locality_id' => $locality->id,
                        'registered_citizens' => $totalCitizens,
                        'total_voting_tables' => $data['mesas'],
                        'status' => 'activo'
                    ]
                );

                // 3. Generar Mesas con lógica OEP real
                for ($i = 1; $i <= $data['mesas']; $i++) {
                    $currentVoters = ($i < $data['mesas']) ? 240 : $data['last_mesa_voters'];

                    // Manejo de recintos de mesa única
                    if ($data['mesas'] === 1) {
                        $currentVoters = $data['last_mesa_voters'];
                    }

                    $suffix = $data['suffix'] ?? '';
                    $oepCode = ($data['oep_inicio'] + ($i - 1)) . "-1" . $suffix;

                    $mesa = VotingTable::updateOrCreate(
                        ['institution_id' => $inst->id, 'number' => $i],
                        [
                            'oep_code' => $oepCode,
                            'internal_code' => "INT-{$inst->id}-" . str_pad($i, 2, '0', STR_PAD_LEFT) . $suffix,
                            'expected_voters' => $currentVoters,
                            'type' => 'mixta'
                        ]
                    );

                    VotingTableElection::updateOrCreate(
                        ['voting_table_id' => $mesa->id, 'election_type_id' => $election->id],
                        ['status' => 'configurada', 'election_date' => $election->election_date]
                    );
                }
            }
            DB::commit();
            $this->command->info("✅ Éxito: 52 recintos cargados con ciudadanos calculados desde actas reales.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error: " . $e->getMessage());
        }
    }
}
