<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuillacolloInstitutionsSeeder extends Seeder
{
    protected $recintos = [
        ['name' => 'UNIDAD EDUCATIVA NESTOR ADRIAZOLA', 'code' => 'REC-QUI-001', 'address' => 'Av. Constantino Morales y Blanco Galindo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 6976],
        ['name' => 'ESCUELA SIMON BOLIVAR', 'code' => 'REC-QUI-002', 'address' => 'Cleomedes Blanco y Atacama', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3501],
        ['name' => 'INSTITUTO PARTICULAR QUILLACOLLO', 'code' => 'REC-QUI-003', 'address' => 'Av. General Pando', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3249],
        ['name' => 'UNIDAD EDUCATIVA VILLA MODERNA', 'code' => 'REC-QUI-004', 'address' => 'Waldo Ballivian esq. Rafael Pabon', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 4247],
        ['name' => 'CARCEL PENAL DE SAN PABLO', 'code' => 'REC-QUI-005', 'address' => 'Av. Abaroa', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 400],
        ['name' => 'UNIDAD EDUCATIVA 1RO DE MAYO', 'code' => 'REC-QUI-006', 'address' => 'Calle 1ro de Mayo entre 21 de Septiembre y Tomas Bata', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 840],
        ['name' => 'UNIDAD EDUCATIVA SAN MARTIN DE PORRES (TARDE)', 'code' => 'REC-QUI-007', 'address' => 'Av. Albina Patiño casi frente Fab. Manaco', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 250],
        ['name' => 'COLEGIO CRISTINA PRADO', 'code' => 'REC-QUI-008', 'address' => '23 de Marzo y Carmela Serruto', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3072],
        ['name' => 'COLEGIO FRANZ TAMAYO', 'code' => 'REC-QUI-009', 'address' => 'Av. Suarez Miranda Nro. 515', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 5109],
        ['name' => 'LICEO AMERICA', 'code' => 'REC-QUI-010', 'address' => 'Gral. Pando y Pacheco', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 5175],
        ['name' => 'ESCUELA FIDELIA C. DE SANCHEZ', 'code' => 'REC-QUI-011', 'address' => 'Calle 6 de Agosto y Cleomedes Blanco', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 2613],
        ['name' => 'UNIDAD EDUCATIVA HEROINAS', 'code' => 'REC-QUI-012', 'address' => 'Calle Ayacucho entre Gral. Camacho y Pacheco', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 2910],
        ['name' => 'TEOFILO VARGAS CANDIA B', 'code' => 'REC-QUI-013', 'address' => '23 de Marzo y Carmela Serruto', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 4985],
        ['name' => 'UNIDAD EDUCATIVA FLORA SALINAS HINOJOSA - AMALIA ECHALAR', 'code' => 'REC-QUI-014', 'address' => 'Luis Uria y 23 de Marzo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 166],
        ['name' => 'UNIDAD EDUCATIVA NUESTRA SEÑORA DE URCUPIÑA', 'code' => 'REC-QUI-015', 'address' => 'Calle Ricardo Soruco entre Walker Mareño y Nataniel Aguirre', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 474],
        ['name' => 'UNIDAD EDUCATIVA MILIVOY ETEROVIC MATENDA', 'code' => 'REC-QUI-016', 'address' => 'Km 12 1/2 Av. Blanco Galindo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3048],
        ['name' => 'ESCUELA 12 DE SEPTIEMBRE', 'code' => 'REC-QUI-017', 'address' => 'Calle Gral Camacho final Sud', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3486],
        ['name' => 'ESCUELA TOMAS BATA', 'code' => 'REC-QUI-018', 'address' => 'Calle 12 de Enero', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 2894],
        ['name' => 'UNIDAD EDUCATIVA 12 DE ENERO B', 'code' => 'REC-QUI-019', 'address' => 'Av. Gral. Camacho entre calle 10 y Fructuoso Mercado', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1214],
        ['name' => 'UNIDAD EDUCATIVA VILLA ASUNCION', 'code' => 'REC-QUI-020', 'address' => 'Villa Asuncion lado Centro de salud', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 944],
        ['name' => 'UNIDAD EDUCATIVA SAN MARTIN DE PORRES', 'code' => 'REC-QUI-021', 'address' => 'Barrio Manaco, calle 12 de Enero casi Martin Cardenas', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 529],
        ['name' => 'COLEGIO NACIONAL CALAMA', 'code' => 'REC-QUI-022', 'address' => 'Av. Ferroviaria Sud', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1438],
        ['name' => 'UNIDAD EDUCATIVA IRONCOLLO', 'code' => 'REC-QUI-023', 'address' => 'Ironcollo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3147],
        ['name' => 'UNIDAD EDUCATIVA MARTIN CARDENAS', 'code' => 'REC-QUI-024', 'address' => 'Barrio Fabril Esmeralda', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 2592],
        ['name' => 'UNIDAD EDUCATIVA TUNARI', 'code' => 'REC-QUI-025', 'address' => 'Calle 23 de Marzo entre Nueva Luz y OTB Tunari', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1787],
        ['name' => 'UNIDAD EDUCATIVA 23 DE MARZO', 'code' => 'REC-QUI-026', 'address' => 'Calle Huachirancho', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 639],
        ['name' => 'UNIDAD EDUCATIVA JOSE MIGUEL LANZA', 'code' => 'REC-QUI-027', 'address' => 'Illataco a 5 Km. al norte de Quillacollo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1978],
        ['name' => 'NORMAL SIMON RODRIGUEZ', 'code' => 'REC-QUI-028', 'address' => 'A 4 Km al norte de Quillacollo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3152],
        ['name' => 'UNIDAD EDUCATIVA 21 DE SEPTIEMBRE', 'code' => 'REC-QUI-029', 'address' => 'OTB AASANA Villa Maria - calle 13 al frente de la plaza', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3045],
        ['name' => 'ESCUELA FELIZ MARTINEZ', 'code' => 'REC-QUI-030', 'address' => 'Av. Blanco Galindo Km. 10 1/2 a 3 cuadras lado norte', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 6360],
        ['name' => 'CENTRO INTEGRAL NIÑO JESUS FE Y ALEGRIA', 'code' => 'REC-QUI-031', 'address' => 'Calle 4 Miraflores', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 2048],
        ['name' => 'UNIDAD EDUCATIVA POCPOCOLLO', 'code' => 'REC-QUI-032', 'address' => 'Comunidad Pocpocollo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 502],
        ['name' => 'UNIDAD EDUCATIVA VILLA URCUPIÑA', 'code' => 'REC-QUI-033', 'address' => 'Final Sud av. Martin Cardenas Calvario', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 4151],
        ['name' => 'UNIDAD EDUCATIVA CERRO COTA', 'code' => 'REC-QUI-034', 'address' => 'Cerro Cota, OTB Cota, zona Calvario', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 466],
        ['name' => 'UNIDAD EDUCATIVA COTAPACHI', 'code' => 'REC-QUI-035', 'address' => 'Carretera Quillacollo a Cochabamba, sindicato agrario Cotapachi', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 571],
        ['name' => 'UNIDAD EDUCATIVA MARQUINA', 'code' => 'REC-QUI-036', 'address' => 'Quillacollo Marquina', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 4695],
        ['name' => 'UNIDAD EDUCATIVA MARQUINA SECUNDARIA', 'code' => 'REC-QUI-037', 'address' => 'Zona Marquina a 6 Km de Quillacollo camino a Morochata', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1006],
        ['name' => 'UNIDAD EDUCATIVA BELLA VISTA', 'code' => 'REC-QUI-038', 'address' => 'Bella Vista a 7 Km de Quillacollo camino a Morochata', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 5338],
        ['name' => 'UNIDAD EDUCATIVA POTRERO', 'code' => 'REC-QUI-039', 'address' => 'Comunidad Potrero al norte de Quillacollo', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1471],
        ['name' => 'ESCUELA ARTURO QUITON', 'code' => 'REC-QUI-040', 'address' => 'Calle Final Antofagasta', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 4278],
        ['name' => 'UNIDAD EDUCATIVA RENE CRESPO RICO', 'code' => 'REC-QUI-041', 'address' => 'Quillacollo calle final Antofagasta', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 558],
        ['name' => 'UNIDAD EDUCATIVA OSCAR ALFARO', 'code' => 'REC-QUI-042', 'address' => 'Pandoja Baja parada micro P', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 914],
        ['name' => 'UNIDAD EDUCATIVA EL PASO', 'code' => 'REC-QUI-043', 'address' => 'El Paso', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 4006],
        ['name' => 'UNIDAD EDUCATIVA MARIA AUXILIADORA', 'code' => 'REC-QUI-044', 'address' => 'A 17 Km de Quillacollo entre el Paso y Tiquipaya', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 1579],
        ['name' => 'INSTITUTO TECNOLOGICO EL PASO', 'code' => 'REC-QUI-045', 'address' => 'Av. Elias Meneses', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 2133],
        ['name' => 'UNIDAD EDUCATIVA EL PASO A', 'code' => 'REC-QUI-046', 'address' => 'Zona Central el Paso', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3770],
        ['name' => 'UNIDAD EDUCATIVA MOLLE MOLLE', 'code' => 'REC-QUI-047', 'address' => 'Comunidad Molle Molle', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 443],
        ['name' => 'UNIDAD EDUCATIVA SANTIAGO APOSTOL', 'code' => 'REC-QUI-048', 'address' => 'Zona Candelaria Urinsaya - El Paso', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 465],
        ['name' => 'UNIDAD EDUCATIVA RENE BARRIENTOS ORTUÑO', 'code' => 'REC-QUI-049', 'address' => 'Misicuni', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 396],
        ['name' => 'CENTRO INTERNADO MISICUNI', 'code' => 'REC-QUI-050', 'address' => 'Misicuni', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 580],
        ['name' => 'UNIDAD EDUCATIVA LIRIUNI', 'code' => 'REC-QUI-051', 'address' => 'Liruini', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 246],
        ['name' => 'UNIDAD EDUCATIVA JOSE BALLIVIAN', 'code' => 'REC-QUI-052', 'address' => 'Barrio Saavedra, av. Cap. Victor Ustariz entre av. Circunvalacion - Cotapachi', 'locality' => 'Quillacollo (Urbano)', 'registered_citizens' => 3237],
    ];

    /**
     * Generate a short name from the full institution name
     */
    private function generateShortName(string $fullName): ?string
    {
        // Remove common prefixes
        $name = preg_replace('/^(UNIDAD EDUCATIVA|COLEGIO|ESCUELA|LICEO|INSTITUTO|CENTRO)\s+/i', '', $fullName);

        // Take first 3-4 words maximum
        $words = explode(' ', $name);
        $shortName = implode(' ', array_slice($words, 0, 3));

        // Limit length
        if (strlen($shortName) > 50) {
            $shortName = substr($shortName, 0, 47) . '...';
        }

        return $shortName ?: null;
    }

    public function run(): void
    {
        $department = Department::where('name', 'Cochabamba')->first();
        if (!$department) {
            $this->command->error('❌ No se encontró el departamento de Cochabamba');
            return;
        }
        $province = Province::where('name', 'Quillacollo')
            ->where('department_id', $department->id)
            ->first();
        if (!$province) {
            $this->command->error('❌ No se encontró la provincia de Quillacollo');
            return;
        }
        $municipality = Municipality::where('name', 'Quillacollo')
            ->where('province_id', $province->id)
            ->first();
        if (!$municipality) {
            $this->command->error('❌ No se encontró el municipio de Quillacollo');
            return;
        }
        DB::beginTransaction();
        try {
            $created = 0;
            $skipped = 0;
            $localitiesCreated = 0;
            $totalVoters = 0;
            foreach ($this->recintos as $index => $recinto) {
                if (($index + 1) % 10 === 0) {
                    $this->command->line("   Procesados " . ($index + 1) . "/" . count($this->recintos) . " recintos...");
                }
                $locality = Locality::firstOrCreate(
                    [
                        'name' => $recinto['locality'],
                        'municipality_id' => $municipality->id
                    ],
                    [
                        'latitude' => null,
                        'longitude' => null
                    ]
                );
                if ($locality->wasRecentlyCreated) {
                    $localitiesCreated++;
                    $this->command->line("   📍 Localidad creada: {$recinto['locality']}");
                }
                $existingInstitution = Institution::where('name', $recinto['name'])
                    ->where('locality_id', $locality->id)
                    ->first();
                if ($existingInstitution) {
                    $skipped++;
                    if ($existingInstitution->registered_citizens != $recinto['registered_citizens']) {
                        $existingInstitution->update([
                            'registered_citizens' => $recinto['registered_citizens']
                        ]);
                    }

                    continue;
                }
                Institution::create([
                    'code' => $recinto['code'],
                    'name' => $recinto['name'],
                    'short_name' => $this->generateShortName($recinto['name']),
                    'municipality_id' => $municipality->id,
                    'locality_id' => $locality->id,
                    'district_id' => null,
                    'zone_id' => null,
                    'address' => $recinto['address'],
                    'reference' => null,
                    'latitude' => null,
                    'longitude' => null,
                    'registered_citizens' => $recinto['registered_citizens'],
                    'total_voting_tables' => 0,
                    'total_computed_records' => 0,
                    'total_annulled_records' => 0,
                    'total_enabled_records' => 0,
                    'total_pending_records' => 0,
                    'phone' => null,
                    'email' => null,
                    'responsible_name' => null,
                    'status' => 'activo',
                    'is_operative' => true,
                    'observations' => null,
                    'created_by' => null,
                    'updated_by' => null,
                ]);
                $created++;
            }
            DB::commit();
            $this->command->info("   • Total recintos: " . count($this->recintos));
            $this->command->info("   • Creados: {$created}");
            $this->command->info("   • Ya existían: {$skipped}");
            $this->command->info("   • Localidades creadas: {$localitiesCreated}");
            $this->command->info("   • Total votantes registrados: " . number_format($totalVoters, 0, ',', '.'));
            if ($created > 0) {
                $this->command->info("\n✅ Seeder completado exitosamente!");
            } else {
                $this->command->warn("\n⚠️ No se crearon nuevas instituciones.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('   Archivo: ' . $e->getFile());
            $this->command->error('   Línea: ' . $e->getLine());
            throw $e;
        }
    }
}
