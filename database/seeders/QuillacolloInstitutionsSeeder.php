<?php
// database/seeders/QuillacolloInstitutionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;

class QuillacolloInstitutionsSeeder extends Seeder
{
    protected $recintos = [
        // Recintos de Quillacollo (según PDF del OEP)
        [
            'name' => 'UNIDAD EDUCATIVA ADELA ZAMUDIO',
            'code' => 'REC-QUI-001',
            'address' => 'Av. Blanco Galindo Km 12',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2350,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA ALFONSO VILLANUEVA PINTO',
            'code' => 'REC-QUI-002',
            'address' => 'Calle Junín esq. Sucre',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1820,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA ANDRES BELLO',
            'code' => 'REC-QUI-003',
            'address' => 'Av. Blanco Galindo Km 13',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2100,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA ANTONIO JOSE DE SUCRE',
            'code' => 'REC-QUI-004',
            'address' => 'Calle Bolívar s/n',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1950,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA BOLIVIA',
            'code' => 'REC-QUI-005',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2240,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA CAPITAN VICTOR USTARIZ',
            'code' => 'REC-QUI-006',
            'address' => 'Calle Cochabamba',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1680,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA CARLOS BELTRAN MORALES',
            'code' => 'REC-QUI-007',
            'address' => 'Av. Blanco Galindo Km 14',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2450,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA CESAREO ORDOÑEZ',
            'code' => 'REC-QUI-008',
            'address' => 'Calle Santa Cruz',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1780,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA DEMETRIO CANELAS',
            'code' => 'REC-QUI-009',
            'address' => 'Av. Blanco Galindo Km 11',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1920,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA EDELMIRA SILES DE LEMBERT',
            'code' => 'REC-QUI-010',
            'address' => 'Calle Baptista',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2050,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA ELIODORO VILLAZON',
            'code' => 'REC-QUI-011',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1880,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA FELIPE PAZ QUIROZ',
            'code' => 'REC-QUI-012',
            'address' => 'Calle Litoral',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2150,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA FELIX BARRADAS',
            'code' => 'REC-QUI-013',
            'address' => 'Av. Blanco Galindo Km 12',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2320,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA FERNANDO VELARDE GUZMAN',
            'code' => 'REC-QUI-014',
            'address' => 'Calle Lanza',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1740,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA FRANCISCO DE VITORIA',
            'code' => 'REC-QUI-015',
            'address' => 'Av. Blanco Galindo Km 13',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1980,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA FRANZ TAMAYO',
            'code' => 'REC-QUI-016',
            'address' => 'Calle Murillo',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1860,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA GENERAL JOSE DE SAN MARTIN',
            'code' => 'REC-QUI-017',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2230,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA GERMAN BUSCH',
            'code' => 'REC-QUI-018',
            'address' => 'Calle Potosí',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1670,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA GUALBERTO VILLARROEL',
            'code' => 'REC-QUI-019',
            'address' => 'Av. Blanco Galindo Km 14',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2410,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA HEROINAS DE LA CORONILLA',
            'code' => 'REC-QUI-020',
            'address' => 'Calle Oruro',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1790,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA HUASCAR',
            'code' => 'REC-QUI-021',
            'address' => 'Av. Blanco Galindo Km 11',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1930,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JAPON',
            'code' => 'REC-QUI-022',
            'address' => 'Calle Tarata',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2070,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JESUS DE NAZARETH',
            'code' => 'REC-QUI-023',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1850,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JOSE ANTONIO CAMACHO',
            'code' => 'REC-QUI-024',
            'address' => 'Calle Linares',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2120,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JOSE IGNACIO SANJINES',
            'code' => 'REC-QUI-025',
            'address' => 'Av. Blanco Galindo Km 12',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2290,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JUAN JOSE TORREZ',
            'code' => 'REC-QUI-026',
            'address' => 'Calle España',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1760,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JUAN PABLO II',
            'code' => 'REC-QUI-027',
            'address' => 'Av. Blanco Galindo Km 13',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1950,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JUANA AZURDUY DE PADILLA',
            'code' => 'REC-QUI-028',
            'address' => 'Calle Campero',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2010,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA JULIO CUEVAS',
            'code' => 'REC-QUI-029',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1880,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA LINO ESPINOZA',
            'code' => 'REC-QUI-030',
            'address' => 'Calle Jordán',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2140,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA MANUEL ASCENCIO VILLARROEL',
            'code' => 'REC-QUI-031',
            'address' => 'Av. Blanco Galindo Km 14',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2370,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA MARCELO QUIROGA SANTA CRUZ',
            'code' => 'REC-QUI-032',
            'address' => 'Calle La Paz',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1820,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA MARIA AUXILIADORA',
            'code' => 'REC-QUI-033',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2040,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA MARIANO BAPTISTA',
            'code' => 'REC-QUI-034',
            'address' => 'Calle Esteban Arze',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1770,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA MARTIN CARDENAS',
            'code' => 'REC-QUI-035',
            'address' => 'Av. Blanco Galindo Km 12',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2210,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA NACIONAL QUILLACOLLO',
            'code' => 'REC-QUI-036',
            'address' => 'Planta Principal',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2890,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA NARCISO CAMPERO',
            'code' => 'REC-QUI-037',
            'address' => 'Calle Sucre',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1980,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA NUEVA ESPERANZA',
            'code' => 'REC-QUI-038',
            'address' => 'Zona Sud',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1650,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA PEDRO DOMINGO MURILLO',
            'code' => 'REC-QUI-039',
            'address' => 'Calle Bolívar',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1890,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA PRIMERO DE MAYO',
            'code' => 'REC-QUI-040',
            'address' => 'Av. Blanco Galindo',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2080,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA QUILLACOLLO',
            'code' => 'REC-QUI-041',
            'address' => 'Centro',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2520,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA REPUBLICA DE ARGENTINA',
            'code' => 'REC-QUI-042',
            'address' => 'Av. 6 de Agosto',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2130,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA REPUBLICA DE BOLIVIA',
            'code' => 'REC-QUI-043',
            'address' => 'Plaza Principal',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2360,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA REPUBLICA DE CHILE',
            'code' => 'REC-QUI-044',
            'address' => 'Calle Baptista',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1910,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA REPUBLICA DE FRANCIA',
            'code' => 'REC-QUI-045',
            'address' => 'Calle La Paz',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1750,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA REPUBLICA DE ITALIA',
            'code' => 'REC-QUI-046',
            'address' => 'Av. Blanco Galindo',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2020,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA RICARDO JAIMES FREYRE',
            'code' => 'REC-QUI-047',
            'address' => 'Calle España',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1850,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA SAN AGUSTIN',
            'code' => 'REC-QUI-048',
            'address' => 'Calle Santa Cruz',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 2140,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA SAN JORGE',
            'code' => 'REC-QUI-049',
            'address' => 'Zona Norte',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1690,
        ],
        [
            'name' => 'UNIDAD EDUCATIVA SAN JOSE',
            'code' => 'REC-QUI-050',
            'address' => 'Calle Cochabamba',
            'locality' => 'Quillacollo (Urbano)',
            'registered_citizens' => 1960,
        ],
    ];

    public function run()
    {
        $this->command->info('Cargando recintos de Quillacollo...');

        // Obtener departamento de Cochabamba
        $department = Department::where('name', 'Cochabamba')->first();
        if (!$department) {
            $this->command->error('No se encontró el departamento de Cochabamba');
            return;
        }

        // Obtener provincia de Quillacollo
        $province = Province::where('name', 'Quillacollo')
            ->where('department_id', $department->id)
            ->first();
        if (!$province) {
            $this->command->error('No se encontró la provincia de Quillacollo');
            return;
        }

        // Obtener municipio de Quillacollo
        $municipality = Municipality::where('name', 'Quillacollo')
            ->where('province_id', $province->id)
            ->first();
        if (!$municipality) {
            $this->command->error('No se encontró el municipio de Quillacollo');
            return;
        }

        DB::beginTransaction();

        try {
            $count = 0;
            foreach ($this->recintos as $recinto) {
                // Buscar o crear localidad
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
                    $this->command->info("Localidad creada: {$recinto['locality']}");
                }

                // Verificar si ya existe la institución
                $existingInstitution = Institution::where('name', $recinto['name'])
                    ->where('locality_id', $locality->id)
                    ->first();

                if ($existingInstitution) {
                    $this->command->warn("Recinto ya existe: {$recinto['name']}");
                    continue;
                }

                // Crear institución - usando SOLO los campos que existen en tu migración
                Institution::create([
                    'code' => $recinto['code'],
                    'name' => $recinto['name'],
                    'short_name' => null,
                    'department_id' => $department->id,
                    'province_id' => $province->id,
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

                $count++;
                
                if ($count % 10 == 0) {
                    $this->command->info("Procesados {$count} recintos...");
                }
            }

            DB::commit();
            $this->command->info("¡Éxito! Se cargaron {$count} recintos de Quillacollo.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }
}