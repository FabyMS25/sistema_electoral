<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Institution, VotingTable, VotingTableElection, ElectionType, Municipality};
use Illuminate\Support\Facades\DB;

class QuillacolloTablesSeeder extends Seeder
{
    public function run(): void
    {
        $municipality = Municipality::where('name', 'Quillacollo')->first();

        // Buscamos la elección activa o la primera que coincida con Municipal 2026
        $election = ElectionType::where('name', 'LIKE', '%Municipal%2026%')
            ->where('active', true)
            ->first() ?? ElectionType::where('name', 'LIKE', '%Municipal%2026%')->first();

        if (!$municipality || !$election) {
            $this->command->error('❌ Error: No se encontró el municipio de Quillacollo o el Tipo de Elección Municipal 2026.');
            return;
        }

        $institutions = Institution::where('municipality_id', $municipality->id)->get();

        if ($institutions->isEmpty()) {
            $this->command->error('❌ Error: No hay instituciones/recintos cargados para Quillacollo.');
            return;
        }

        DB::beginTransaction();
        try {
            $totalCreated = 0;
            foreach ($institutions as $inst) {
                // Estándar oficial: ~235 ciudadanos por mesa
                $numMesas = (int) ceil($inst->registered_citizens / 235);

                // Si el recinto tiene ciudadanos pero el cálculo da 0, forzamos 1 mesa
                if ($inst->registered_citizens > 0 && $numMesas == 0) $numMesas = 1;

                for ($i = 1; $i <= $numMesas; $i++) {
                    // Generamos códigos únicos para cumplir con la migración
                    $oepCode = "{$inst->code}-{$i}";
                    $internalCode = "INT-" . str_replace('REC-', '', $inst->code) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);

                    $mesa = VotingTable::updateOrCreate(
                        [
                            'institution_id' => $inst->id,
                            'number' => $i
                        ],
                        [
                            'oep_code'      => $oepCode,
                            'internal_code' => $internalCode, // CAMBIO: Ahora no es nulo
                            'letter'        => null,
                            'type'          => 'mixta',
                            'expected_voters' => (int) round($inst->registered_citizens / $numMesas),
                        ]
                    );

                    // Registrar la mesa en la elección específica
                    VotingTableElection::updateOrCreate(
                        [
                            'voting_table_id'  => $mesa->id,
                            'election_type_id' => $election->id
                        ],
                        [
                            'status'        => 'configurada',
                            'election_date' => $election->election_date,
                        ]
                    );
                    $totalCreated++;
                }

                // Actualizar el contador en la tabla de instituciones
                $inst->update(['total_voting_tables' => $numMesas]);
            }

            DB::commit();
            $this->command->info("✅ Se generaron correctamente {$totalCreated} mesas para los 52 recintos de Quillacollo.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("❌ Error durante el seeding: " . $e->getMessage());
        }
    }
}
