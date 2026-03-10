<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\VotingTableElection;
use App\Models\ElectionType;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;

class QuillacolloTablesSeeder extends Seeder
{
    private $distribucionMesas = [
        'UNIDAD EDUCATIVA NESTOR ADRIAZOLA'               => 70, // ~6976/250 ≈ 28, but adjusted for actual distribution
        'ESCUELA SIMON BOLIVAR'                            => 44, // 3501/250 ≈ 14, adjusted
        'INSTITUTO PARTICULAR QUILLACOLLO'                 => 41, // 3249/250 ≈ 13, adjusted
        'UNIDAD EDUCATIVA VILLA MODERNA'                    => 53, // 4247/250 ≈ 17, adjusted
        'CARCEL PENAL DE SAN PABLO'                         => 8,  // 400/50 ≈ 8 (special case for prison)
        'UNIDAD EDUCATIVA 1RO DE MAYO'                      => 17, // 840/50 ≈ 17 (smaller tables)
        'UNIDAD EDUCATIVA SAN MARTIN DE PORRES (TARDE)'     => 10, // 250/25 ≈ 10 (very small)
        'COLEGIO CRISTINA PRADO'                            => 38, // 3072/80 ≈ 38
        'COLEGIO FRANZ TAMAYO'                              => 64, // 5109/80 ≈ 64
        'LICEO AMERICA'                                      => 65, // 5175/80 ≈ 65
        'ESCUELA FIDELIA C. DE SANCHEZ'                     => 33, // 2613/80 ≈ 33
        'UNIDAD EDUCATIVA HEROINAS'                          => 36, // 2910/80 ≈ 36
        'TEOFILO VARGAS CANDIA B'                            => 62, // 4985/80 ≈ 62
        'UNIDAD EDUCATIVA FLORA SALINAS HINOJOSA - AMALIA ECHALAR' => 8, // 166/20 ≈ 8
        'UNIDAD EDUCATIVA NUESTRA SEÑORA DE URCUPIÑA'       => 19, // 474/25 ≈ 19
        'UNIDAD EDUCATIVA MILIVOY ETEROVIC MATENDA'         => 38, // 3048/80 ≈ 38
        'ESCUELA 12 DE SEPTIEMBRE'                           => 44, // 3486/80 ≈ 44
        'ESCUELA TOMAS BATA'                                 => 36, // 2894/80 ≈ 36
        'UNIDAD EDUCATIVA 12 DE ENERO B'                     => 24, // 1214/50 ≈ 24
        'UNIDAD EDUCATIVA VILLA ASUNCION'                    => 19, // 944/50 ≈ 19
        'UNIDAD EDUCATIVA SAN MARTIN DE PORRES'              => 13, // 529/40 ≈ 13
        'COLEGIO NACIONAL CALAMA'                             => 28, // 1438/50 ≈ 28
        'UNIDAD EDUCATIVA IRONCOLLO'                          => 39, // 3147/80 ≈ 39
        'UNIDAD EDUCATIVA MARTIN CARDENAS'                    => 32, // 2592/80 ≈ 32
        'UNIDAD EDUCATIVA TUNARI'                              => 22, // 1787/80 ≈ 22
        'UNIDAD EDUCATIVA 23 DE MARZO'                         => 13, // 639/50 ≈ 13
        'UNIDAD EDUCATIVA JOSE MIGUEL LANZA'                   => 25, // 1978/80 ≈ 25
        'NORMAL SIMON RODRIGUEZ'                               => 39, // 3152/80 ≈ 39
        'UNIDAD EDUCATIVA 21 DE SEPTIEMBRE'                    => 38, // 3045/80 ≈ 38
        'ESCUELA FELIZ MARTINEZ'                               => 80, // 6360/80 ≈ 80
        'CENTRO INTEGRAL NIÑO JESUS FE Y ALEGRIA'              => 26, // 2048/80 ≈ 26
        'UNIDAD EDUCATIVA POCPOCOLLO'                           => 10, // 502/50 ≈ 10
        'UNIDAD EDUCATIVA VILLA URCUPIÑA'                       => 52, // 4151/80 ≈ 52
        'UNIDAD EDUCATIVA CERRO COTA'                           => 9,  // 466/50 ≈ 9
        'UNIDAD EDUCATIVA COTAPACHI'                            => 11, // 571/50 ≈ 11
        'UNIDAD EDUCATIVA MARQUINA'                             => 59, // 4695/80 ≈ 59
        'UNIDAD EDUCATIVA MARQUINA SECUNDARIA'                  => 20, // 1006/50 ≈ 20
        'UNIDAD EDUCATIVA BELLA VISTA'                          => 67, // 5338/80 ≈ 67
        'UNIDAD EDUCATIVA POTRERO'                              => 18, // 1471/80 ≈ 18
        'ESCUELA ARTURO QUITON'                                 => 54, // 4278/80 ≈ 54
        'UNIDAD EDUCATIVA RENE CRESPO RICO'                     => 11, // 558/50 ≈ 11
        'UNIDAD EDUCATIVA OSCAR ALFARO'                          => 18, // 914/50 ≈ 18
        'UNIDAD EDUCATIVA EL PASO'                               => 50, // 4006/80 ≈ 50
        'UNIDAD EDUCATIVA MARIA AUXILIADORA'                     => 20, // 1579/80 ≈ 20
        'INSTITUTO TECNOLOGICO EL PASO'                          => 27, // 2133/80 ≈ 27
        'UNIDAD EDUCATIVA EL PASO A'                             => 47, // 3770/80 ≈ 47
        'UNIDAD EDUCATIVA MOLLE MOLLE'                           => 9,  // 443/50 ≈ 9
        'UNIDAD EDUCATIVA SANTIAGO APOSTOL'                      => 9,  // 465/50 ≈ 9
        'UNIDAD EDUCATIVA RENE BARRIENTOS ORTUÑO'                => 8,  // 396/50 ≈ 8
        'CENTRO INTERNADO MISICUNI'                              => 12, // 580/50 ≈ 12
        'UNIDAD EDUCATIVA LIRIUNI'                               => 5,  // 246/50 ≈ 5
        'UNIDAD EDUCATIVA JOSE BALLIVIAN'                        => 40, // 3237/80 ≈ 40
    ];

    public function run(): void
    {
        $municipalElection = ElectionType::where('name', 'LIKE', '%Municipal%2026%')
            ->where('active', true)
            ->first()
            ?? ElectionType::where('name', 'LIKE', '%Municipal%2026%')->first();

        $departamentalElection = ElectionType::where('name', 'LIKE', '%Departamental%2026%')
            ->where('active', true)
            ->first()
            ?? ElectionType::where('name', 'LIKE', '%Departamental%2026%')->first();

        if (!$municipalElection) {
            $this->command->error('❌ No se encontró el tipo de elección Municipal 2026');
            return;
        }

        if (!$departamentalElection) {
            $this->command->warn('⚠️  No se encontró elección Departamental 2026 — solo se crearán entradas municipales.');
        }

        $this->command->info("📅 Municipal:      {$municipalElection->name}");
        if ($departamentalElection) {
            $this->command->info("📅 Departamental:  {$departamentalElection->name}");
        }

        $municipality = Municipality::where('name', 'Quillacollo')->first();
        if (!$municipality) {
            $this->command->error('❌ No se encontró el municipio de Quillacollo');
            return;
        }

        $institutions = Institution::where('municipality_id', $municipality->id)->get();
        if ($institutions->isEmpty()) {
            $this->command->error('❌ No se encontraron recintos en Quillacollo');
            return;
        }

        DB::beginTransaction();
        try {
            $totalMesas     = 0;
            $totalPivots    = 0;
            $procesados     = 0;
            $errores        = [];

            foreach ($institutions as $institution) {
                $this->command->info("📊 {$institution->name}");
                $numMesas = $this->getNumeroMesas($institution);

                if (!$numMesas || $numMesas <= 0) {
                    $numMesas = $this->calcularMesasPorVotantes($institution->registered_citizens);
                    $this->command->warn("   ⚠️ Usando cálculo basado en votantes: {$numMesas}");
                }

                $votantesPorMesa = $this->calcularVotantesPorMesa($institution, $numMesas);
                $mesasCreadas = 0;

                for ($i = 1; $i <= $numMesas; $i++) {
                    try {
                        $mesa = VotingTable::updateOrCreate(
                            [
                                'institution_id' => $institution->id,
                                'number'         => $i,
                            ],
                            [
                                'oep_code'               => $institution->code . '-' . $i,
                                'internal_code'          => $institution->code . '-M' . str_pad($i, 2, '0', STR_PAD_LEFT),
                                'letter'                 => null,
                                'type'                   => 'mixta',
                                'expected_voters'        => $votantesPorMesa,
                                'voter_range_start_name' => null,
                                'voter_range_end_name'   => null,
                                'president_id'           => null,
                                'secretary_id'           => null,
                                'vocal1_id'              => null,
                                'vocal2_id'              => null,
                                'vocal3_id'              => null,
                                'vocal4_id'              => null,
                                'observations'           => null,
                            ]
                        );

                        $this->createTableElection($mesa, $municipalElection);
                        if ($departamentalElection) {
                            $this->createTableElection($mesa, $departamentalElection);
                            $totalPivots += 2;
                        } else {
                            $totalPivots++;
                        }
                        $mesasCreadas++;
                    } catch (\Exception $e) {
                        $errores[] = "{$institution->code} Mesa {$i}: " . $e->getMessage();
                    }
                }

                if ($mesasCreadas > 0) {
                    $institution->update(['total_voting_tables' => $mesasCreadas]);
                    $totalMesas += $mesasCreadas;
                    $procesados++;
                    $this->command->info("   ✅ {$mesasCreadas} mesas creadas");
                }
            }

            DB::commit();

            $this->command->info("\n📊 RESUMEN FINAL:");
            $this->command->info("   • Instituciones procesadas: {$procesados}");
            $this->command->info("   • Total mesas creadas: {$totalMesas}");
            $this->command->info("   • Total registros pivote: {$totalPivots}");

            if (!empty($errores)) {
                $this->command->warn("\n⚠️  Errores:");
                foreach ($errores as $e) {
                    $this->command->warn("   • {$e}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createTableElection(VotingTable $mesa, ElectionType $election): void
    {
        VotingTableElection::updateOrCreate(
            [
                'voting_table_id'  => $mesa->id,
                'election_type_id' => $election->id,
            ],
            [
                'status' => 'configurada',
                'election_date'    => $election->election_date,
                'ballots_received' => 0,
                'ballots_used'     => 0,
                'ballots_leftover' => 0,
                'ballots_spoiled'  => 0,
                'total_voters'     => 0,
                'opening_time'     => null,
                'closing_time'     => null,
                'observations'     => null,
            ]
        );
    }

    private function getNumeroMesas(Institution $institution): int
    {
        $nombre = strtoupper(trim($institution->name));

        // Exact match
        if (isset($this->distribucionMesas[$nombre])) {
            return $this->distribucionMesas[$nombre];
        }

        // Partial match
        foreach ($this->distribucionMesas as $key => $value) {
            if (str_contains($nombre, $key) || str_contains($key, $nombre)) {
                return $value;
            }
        }

        // Calculate based on registered citizens
        return $this->calcularMesasPorVotantes($institution->registered_citizens);
    }

    private function calcularMesasPorVotantes(int $votantes): int
    {
        if ($votantes <= 0) {
            return 30;
        }

        // Special case for prison (CARCEL PENAL DE SAN PABLO)
        if ($votantes <= 500) {
            return max(5, (int) ceil($votantes / 50));
        }

        // Normal calculation: approximately 250-300 voters per table
        return max(10, (int) ceil($votantes / 250));
    }

    private function calcularVotantesPorMesa(Institution $institution, int $numMesas): int
    {
        if ($institution->registered_citizens <= 0 || $numMesas <= 0) {
            return 250;
        }

        // Special case for prison
        if (str_contains($institution->name, 'CARCEL PENAL')) {
            return (int) ceil($institution->registered_citizens / $numMesas);
        }

        // Distribute voters evenly, but ensure reasonable numbers
        $baseVoters = (int) ceil($institution->registered_citizens / $numMesas);

        // Cap at 350 voters per table maximum
        return min(350, max(200, $baseVoters));
    }
}
