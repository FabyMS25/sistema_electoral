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
    // ===== CHANGES FROM ORIGINAL =====
    // ❌ REMOVED from VotingTable::updateOrCreate:
    //    election_type_id, status, ballots_received/used/leftover/spoiled,
    //    valid_votes*, blank_votes*, null_votes*, total_voters*, election_date,
    //    opening_time, closing_time, acta_number, acta_photo, acta_uploaded_at
    //    — ALL of these moved to VotingTableElection (one row per mesa × election)
    //
    // ✅ ADDED: After creating each mesa, create 2 VotingTableElection rows:
    //    one for Departamental, one for Municipal.
    //    This is the correct model for simultaneous elections on the same physical mesa.

    private $distribucionMesas = [
        'UNIDAD EDUCATIVA ADELA ZAMUDIO'               => 47,
        'UNIDAD EDUCATIVA ALFONSO VILLANUEVA PINTO'    => 36,
        'UNIDAD EDUCATIVA ANDRES BELLO'                => 42,
        'UNIDAD EDUCATIVA ANTONIO JOSE DE SUCRE'       => 39,
        'UNIDAD EDUCATIVA BOLIVIA'                     => 45,
        'UNIDAD EDUCATIVA CAPITAN VICTOR USTARIZ'      => 34,
        'UNIDAD EDUCATIVA CARLOS BELTRAN MORALES'      => 49,
        'UNIDAD EDUCATIVA CESAREO ORDOÑEZ'             => 36,
        'UNIDAD EDUCATIVA DEMETRIO CANELAS'            => 38,
        'UNIDAD EDUCATIVA EDELMIRA SILES DE LEMBERT'   => 41,
        'UNIDAD EDUCATIVA ELIODORO VILLAZON'           => 38,
        'UNIDAD EDUCATIVA FELIPE PAZ QUIROZ'           => 43,
        'UNIDAD EDUCATIVA FELIX BARRADAS'              => 46,
        'UNIDAD EDUCATIVA FERNANDO VELARDE GUZMAN'     => 35,
        'UNIDAD EDUCATIVA FRANCISCO DE VITORIA'        => 40,
        'UNIDAD EDUCATIVA FRANZ TAMAYO'                => 37,
        'UNIDAD EDUCATIVA GENERAL JOSE DE SAN MARTIN'  => 45,
        'UNIDAD EDUCATIVA GERMAN BUSCH'                => 33,
        'UNIDAD EDUCATIVA GUALBERTO VILLARROEL'        => 48,
        'UNIDAD EDUCATIVA HEROINAS DE LA CORONILLA'    => 36,
        'UNIDAD EDUCATIVA HUASCAR'                     => 39,
        'UNIDAD EDUCATIVA JAPON'                       => 41,
        'UNIDAD EDUCATIVA JESUS DE NAZARETH'           => 37,
        'UNIDAD EDUCATIVA JOSE ANTONIO CAMACHO'        => 42,
        'UNIDAD EDUCATIVA JOSE IGNACIO SANJINES'       => 46,
        'UNIDAD EDUCATIVA JUAN JOSE TORREZ'            => 35,
        'UNIDAD EDUCATIVA JUAN PABLO II'               => 39,
        'UNIDAD EDUCATIVA JUANA AZURDUY DE PADILLA'    => 40,
        'UNIDAD EDUCATIVA JULIO CUEVAS'                => 38,
        'UNIDAD EDUCATIVA LINO ESPINOZA'               => 43,
        'UNIDAD EDUCATIVA MANUEL ASCENCIO VILLARROEL'  => 47,
        'UNIDAD EDUCATIVA MARCELO QUIROGA SANTA CRUZ'  => 36,
        'UNIDAD EDUCATIVA MARIA AUXILIADORA'           => 41,
        'UNIDAD EDUCATIVA MARIANO BAPTISTA'            => 35,
        'UNIDAD EDUCATIVA MARTIN CARDENAS'             => 44,
        'UNIDAD EDUCATIVA NACIONAL QUILLACOLLO'        => 58,
        'UNIDAD EDUCATIVA NARCISO CAMPERO'             => 40,
        'UNIDAD EDUCATIVA NUEVA ESPERANZA'             => 33,
        'UNIDAD EDUCATIVA PEDRO DOMINGO MURILLO'       => 38,
        'UNIDAD EDUCATIVA PRIMERO DE MAYO'             => 42,
        'UNIDAD EDUCATIVA QUILLACOLLO'                 => 50,
        'UNIDAD EDUCATIVA REPUBLICA DE ARGENTINA'      => 43,
        'UNIDAD EDUCATIVA REPUBLICA DE BOLIVIA'        => 47,
        'UNIDAD EDUCATIVA REPUBLICA DE CHILE'          => 38,
        'UNIDAD EDUCATIVA REPUBLICA DE FRANCIA'        => 35,
        'UNIDAD EDUCATIVA REPUBLICA DE ITALIA'         => 40,
        'UNIDAD EDUCATIVA RICARDO JAIMES FREYRE'       => 37,
        'UNIDAD EDUCATIVA SAN AGUSTIN'                 => 43,
        'UNIDAD EDUCATIVA SAN JORGE'                   => 34,
        'UNIDAD EDUCATIVA SAN JOSE'                    => 39,
    ];

    public function run(): void
    {
        // ✅ Load BOTH election types — mesas participate in both on the same day
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
        $this->command->info("🏫 Recintos encontrados: {$institutions->count()}\n");

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
                    $numMesas = 30;
                    $this->command->warn("   ⚠️ Usando valor por defecto: {$numMesas}");
                }

                $votantesPorMesa = $institution->registered_citizens > 0
                    ? (int) ceil($institution->registered_citizens / $numMesas)
                    : 250;

                $mesasCreadas = 0;

                for ($i = 1; $i <= $numMesas; $i++) {
                    try {
                        // ✅ VotingTable: physical mesa only — NO election_type_id, NO status, NO vote totals
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

                        // ✅ VotingTableElection: one row per (mesa × election) — status + ballots per election
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

            $this->command->info("\n========================================");
            $this->command->info('✅ PROCESO COMPLETADO');
            $this->command->info("========================================");
            $this->command->info("📊 Mesas físicas:         {$totalMesas}");
            $this->command->info("🗳️  Entradas por elección: {$totalPivots}");
            $this->command->info("🏫 Recintos procesados:   {$procesados}");

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

    /**
     * Create or update the VotingTableElection pivot row for a given mesa + election.
     * This is where per-election status, ballot counts, and dates live.
     */
    private function createTableElection(VotingTable $mesa, ElectionType $election): void
    {
        VotingTableElection::updateOrCreate(
            [
                'voting_table_id'  => $mesa->id,
                'election_type_id' => $election->id,
            ],
            [
                'status'           => VotingTableElection::STATUS_CONFIGURADA,
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
        if (isset($this->distribucionMesas[$nombre])) {
            return $this->distribucionMesas[$nombre];
        }
        foreach ($this->distribucionMesas as $key => $value) {
            if (str_contains($nombre, $key)) {
                return $value;
            }
        }
        if ($institution->registered_citizens > 0) {
            return (int) ceil($institution->registered_citizens / 250);
        }
        return 30;
    }
}
