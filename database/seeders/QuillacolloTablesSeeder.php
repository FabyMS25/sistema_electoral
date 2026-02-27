<?php
// database/seeders/QuillacolloTablesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\VotingTable;
use App\Models\ElectionType;
use Illuminate\Support\Facades\DB;

class QuillacolloTablesSeeder extends Seeder
{
    private function getNumMesasFromName($name)
    {
        // Lógica para determinar número de mesas según el nombre del recinto
        if (strpos($name, 'NACIONAL QUILLACOLLO') !== false) {
            return 58;
        }
        if (strpos($name, 'QUILLACOLLO') !== false && strlen($name) < 30) {
            return 50;
        }
        if (strpos($name, 'CARLOS BELTRAN') !== false) {
            return 49;
        }
        if (strpos($name, 'MANUEL ASCENCIO') !== false) {
            return 47;
        }
        if (strpos($name, 'GUALBERTO VILLARROEL') !== false) {
            return 48;
        }
        if (strpos($name, 'ADELA ZAMUDIO') !== false) {
            return 47;
        }
        
        // Valor por defecto basado en ciudadanos registrados
        return rand(30, 45);
    }

    public function run()
    {
        $this->command->info('Generando mesas para recintos de Quillacollo...');

        // Obtener el tipo de elección activo
        $electionType = ElectionType::where('active', true)->first();
        if (!$electionType) {
            $this->command->error('No hay un tipo de elección activo');
            return;
        }

        // CORREGIDO: Usar municipality_id directamente en lugar de whereHas
        $institutions = Institution::where('municipality_id', function($query) {
            $query->select('id')
                  ->from('municipalities')
                  ->where('name', 'Quillacollo');
        })->get();

        if ($institutions->isEmpty()) {
            $this->command->error('No se encontraron recintos de Quillacollo');
            return;
        }

        $this->command->info("Se encontraron {$institutions->count()} recintos");

        DB::beginTransaction();

        try {
            $totalTables = 0;
            $processedInstitutions = 0;
            
            foreach ($institutions as $institution) {
                $numMesas = $this->getNumMesasFromName($institution->name);
                
                $this->command->info("Procesando: {$institution->name} - {$numMesas} mesas");

                for ($i = 1; $i <= $numMesas; $i++) {
                    VotingTable::firstOrCreate(
                        [
                            'institution_id' => $institution->id,
                            'number' => $i
                        ],
                        [
                            'code' => $institution->code . '-M' . str_pad($i, 2, '0', STR_PAD_LEFT),
                            'code_ine' => null,
                            'letter' => null,
                            'type' => 'mixta',
                            'from_name' => null,
                            'to_name' => null,
                            'from_number' => null,
                            'to_number' => null,
                            'registered_citizens' => ceil($institution->registered_citizens / $numMesas),
                            'voted_citizens' => 0,
                            'absent_citizens' => 0,
                            'computed_records' => 0,
                            'annulled_records' => 0,
                            'enabled_records' => 0,
                            'blank_votes' => 0,
                            'null_votes' => 0,
                            'status' => 'pendiente',
                            'opening_time' => null,
                            'closing_time' => null,
                            'election_date' => null,
                            'election_type_id' => $electionType->id,
                            'president_id' => null,
                            'secretary_id' => null,
                            'vocal1_id' => null,
                            'vocal2_id' => null,
                            'vocal3_id' => null,
                            'vocal4_id' => null,
                            'acta_number' => null,
                            'acta_photo' => null,
                            'acta_pdf' => null,
                            'acta_uploaded_at' => null,
                            'observations' => null,
                        ]
                    );
                }
                
                $totalTables += $numMesas;
                $processedInstitutions++;
                
                // Actualizar el total de mesas en la institución
                $institution->update([
                    'total_voting_tables' => $numMesas
                ]);
            }

            DB::commit();
            $this->command->info("¡Éxito! Se generaron {$totalTables} mesas para {$processedInstitutions} recintos.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}