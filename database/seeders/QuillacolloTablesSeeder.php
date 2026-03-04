<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\ElectionType;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;

class QuillacolloTablesSeeder extends Seeder
{
    // Distribución de mesas por recinto (basado en datos reales)
    private $distribucionMesas = [
        'UNIDAD EDUCATIVA ADELA ZAMUDIO' => 47,
        'UNIDAD EDUCATIVA ALFONSO VILLANUEVA PINTO' => 36,
        'UNIDAD EDUCATIVA ANDRES BELLO' => 42,
        'UNIDAD EDUCATIVA ANTONIO JOSE DE SUCRE' => 39,
        'UNIDAD EDUCATIVA BOLIVIA' => 45,
        'UNIDAD EDUCATIVA CAPITAN VICTOR USTARIZ' => 34,
        'UNIDAD EDUCATIVA CARLOS BELTRAN MORALES' => 49,
        'UNIDAD EDUCATIVA CESAREO ORDOÑEZ' => 36,
        'UNIDAD EDUCATIVA DEMETRIO CANELAS' => 38,
        'UNIDAD EDUCATIVA EDELMIRA SILES DE LEMBERT' => 41,
        'UNIDAD EDUCATIVA ELIODORO VILLAZON' => 38,
        'UNIDAD EDUCATIVA FELIPE PAZ QUIROZ' => 43,
        'UNIDAD EDUCATIVA FELIX BARRADAS' => 46,
        'UNIDAD EDUCATIVA FERNANDO VELARDE GUZMAN' => 35,
        'UNIDAD EDUCATIVA FRANCISCO DE VITORIA' => 40,
        'UNIDAD EDUCATIVA FRANZ TAMAYO' => 37,
        'UNIDAD EDUCATIVA GENERAL JOSE DE SAN MARTIN' => 45,
        'UNIDAD EDUCATIVA GERMAN BUSCH' => 33,
        'UNIDAD EDUCATIVA GUALBERTO VILLARROEL' => 48,
        'UNIDAD EDUCATIVA HEROINAS DE LA CORONILLA' => 36,
        'UNIDAD EDUCATIVA HUASCAR' => 39,
        'UNIDAD EDUCATIVA JAPON' => 41,
        'UNIDAD EDUCATIVA JESUS DE NAZARETH' => 37,
        'UNIDAD EDUCATIVA JOSE ANTONIO CAMACHO' => 42,
        'UNIDAD EDUCATIVA JOSE IGNACIO SANJINES' => 46,
        'UNIDAD EDUCATIVA JUAN JOSE TORREZ' => 35,
        'UNIDAD EDUCATIVA JUAN PABLO II' => 39,
        'UNIDAD EDUCATIVA JUANA AZURDUY DE PADILLA' => 40,
        'UNIDAD EDUCATIVA JULIO CUEVAS' => 38,
        'UNIDAD EDUCATIVA LINO ESPINOZA' => 43,
        'UNIDAD EDUCATIVA MANUEL ASCENCIO VILLARROEL' => 47,
        'UNIDAD EDUCATIVA MARCELO QUIROGA SANTA CRUZ' => 36,
        'UNIDAD EDUCATIVA MARIA AUXILIADORA' => 41,
        'UNIDAD EDUCATIVA MARIANO BAPTISTA' => 35,
        'UNIDAD EDUCATIVA MARTIN CARDENAS' => 44,
        'UNIDAD EDUCATIVA NACIONAL QUILLACOLLO' => 58,
        'UNIDAD EDUCATIVA NARCISO CAMPERO' => 40,
        'UNIDAD EDUCATIVA NUEVA ESPERANZA' => 33,
        'UNIDAD EDUCATIVA PEDRO DOMINGO MURILLO' => 38,
        'UNIDAD EDUCATIVA PRIMERO DE MAYO' => 42,
        'UNIDAD EDUCATIVA QUILLACOLLO' => 50,
        'UNIDAD EDUCATIVA REPUBLICA DE ARGENTINA' => 43,
        'UNIDAD EDUCATIVA REPUBLICA DE BOLIVIA' => 47,
        'UNIDAD EDUCATIVA REPUBLICA DE CHILE' => 38,
        'UNIDAD EDUCATIVA REPUBLICA DE FRANCIA' => 35,
        'UNIDAD EDUCATIVA REPUBLICA DE ITALIA' => 40,
        'UNIDAD EDUCATIVA RICARDO JAIMES FREYRE' => 37,
        'UNIDAD EDUCATIVA SAN AGUSTIN' => 43,
        'UNIDAD EDUCATIVA SAN JORGE' => 34,
        'UNIDAD EDUCATIVA SAN JOSE' => 39,
    ];

    public function run()
    {
        $electionType = ElectionType::where('active', true)->first();
        if (!$electionType) {
            $electionType = ElectionType::where('name', 'Elecciones Municipales 2026')->first();
            if (!$electionType) {
                $this->command->error('❌ No se encontró el tipo de elección');
                return;
            }
            $this->command->warn("⚠️  Usando tipo: {$electionType->name}");
        }
        $this->command->info("📅 Tipo de elección: {$electionType->name} (ID: {$electionType->id})");

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
        $this->command->info("🏫 Recintos encontrados: {$institutions->count()}");

        DB::beginTransaction();

        try {
            $totalMesas = 0;
            $procesados = 0;
            $errores = [];

            foreach ($institutions as $institution) {
                $this->command->info("📊 Procesando: {$institution->name}");
                $this->command->info("   ├─ Código: {$institution->code}");
                $this->command->info("   ├─ Votantes: " . number_format($institution->registered_citizens ?? 0));

                $numMesas = $this->getNumeroMesas($institution);

                if (!$numMesas || $numMesas <= 0) {
                    $numMesas = 30;
                    $this->command->warn("   ⚠️ Usando valor por defecto: {$numMesas}");
                }

                $this->command->info("   └─ Mesas a crear: {$numMesas}");

                $mesasCreadas = 0;
                $votantesPorMesa = $institution->registered_citizens > 0
                    ? ceil($institution->registered_citizens / $numMesas)
                    : 250;

                for ($i = 1; $i <= $numMesas; $i++) {
                    $internalCode = $institution->code . '-M' . str_pad($i, 2, '0', STR_PAD_LEFT);
                    $oepCode = $institution->code . '-' . $i;
                    try {
                        VotingTable::updateOrCreate(
                            [
                                'institution_id' => $institution->id,
                                'number' => $i
                            ],
                            [
                                // CÓDIGOS - usando los nuevos campos de la migración
                                'oep_code' => $oepCode,
                                'internal_code' => $internalCode,

                                // Datos básicos
                                'letter' => null,
                                'type' => 'mixta',

                                // Relaciones
                                'election_type_id' => $electionType->id,

                                // Datos pre-electorales
                                'expected_voters' => $votantesPorMesa,
                                'ballots_received' => 0,
                                'ballots_spoiled' => 0,

                                // Rango de votantes (opcional)
                                'voter_range_start_name' => null,
                                'voter_range_end_name' => null,

                                // Personal de mesa (opcional)
                                'president_id' => null,
                                'secretary_id' => null,
                                'vocal1_id' => null,
                                'vocal2_id' => null,
                                'vocal3_id' => null,
                                'vocal4_id' => null,

                                // Fechas
                                'election_date' => $electionType->election_date,
                                'opening_time' => null,
                                'closing_time' => null,

                                // Estado
                                'status' => 'configurada',

                                // Control de papeletas
                                'ballots_used' => 0,
                                'ballots_leftover' => 0,

                                // Resultados (inicialmente cero)
                                'valid_votes' => 0,
                                'blank_votes' => 0,
                                'null_votes' => 0,
                                'valid_votes_second' => 0,
                                'blank_votes_second' => 0,
                                'null_votes_second' => 0,
                                'total_voters' => 0,
                                'total_voters_second' => 0,

                                // Acta
                                'acta_number' => null,
                                'acta_photo' => null,
                                'acta_uploaded_at' => null,
                                'observations' => null,
                            ]
                        );
                        $mesasCreadas++;
                    } catch (\Exception $e) {
                        $errores[] = "Mesa {$i}: " . $e->getMessage();
                    }
                }

                if ($mesasCreadas > 0) {
                    $institution->update(['total_voting_tables' => $mesasCreadas]);
                    $totalMesas += $mesasCreadas;
                    $procesados++;
                    $this->command->info("   ✅ Creadas {$mesasCreadas} mesas");
                }

                $this->command->info('');
            }

            DB::commit();

            $this->command->info('========================================');
            $this->command->info('✅ PROCESO COMPLETADO');
            $this->command->info('========================================');
            $this->command->info("📊 Total de mesas creadas: {$totalMesas}");
            $this->command->info("🏫 Recintos procesados: {$procesados}");

            if (!empty($errores)) {
                $this->command->warn("\n⚠️  Errores encontrados:");
                foreach ($errores as $error) {
                    $this->command->warn("   • {$error}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getNumeroMesas($institution)
    {
        $nombre = strtoupper(trim($institution->name));

        if (isset($this->distribucionMesas[$nombre])) {
            return $this->distribucionMesas[$nombre];
        }

        foreach ($this->distribucionMesas as $key => $value) {
            if (strpos($nombre, $key) !== false) {
                return $value;
            }
        }

        if ($institution->registered_citizens > 0) {
            return ceil($institution->registered_citizens / 250);
        }

        return 30;
    }
}
