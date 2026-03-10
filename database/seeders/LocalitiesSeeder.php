<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\District;
use App\Models\Zone;

class LocalitiesSeeder extends Seeder
{
    public function run()
    {
        $municipalities = Municipality::all();

        foreach ($municipalities as $municipality) {
            $localities = $this->getLocalitiesForMunicipality($municipality->name);

            foreach ($localities as $localityData) {
                Locality::updateOrCreate(
                    [
                        'name' => $localityData['name'],
                        'municipality_id' => $municipality->id,
                    ],
                    [
                        'latitude' => $localityData['latitude'],
                        'longitude' => $localityData['longitude'],
                    ]
                );
            }
        }

        $quillacolloMunicipality = Municipality::where('name', 'Quillacollo')->first();
        if (!$quillacolloMunicipality) {
            $this->command->warn("⚠️ Municipality 'Quillacollo' not found, skipping district creation.");
            return;
        }
        $districts = [
            ['name' => 'Distrito 1 - Centro', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 2 - Norte', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 3 - Sur', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 4 - Este', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 5 - Oeste', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 6 - Periurbano Norte', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 7 - Periurbano Sur', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 8 - Periurbano Este', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 9 - Periurbano Oeste', 'municipality_id' => $quillacolloMunicipality->id],
            ['name' => 'Distrito 10 - Rural', 'municipality_id' => $quillacolloMunicipality->id],
        ];
        foreach ($districts as $districtData) {
            $district = District::updateOrCreate(
                ['name' => $districtData['name'], 'municipality_id' => $districtData['municipality_id']],
                $districtData
            );
            $zones = $this->getZonesForDistrict($district->name);
            foreach ($zones as $zone) {
                Zone::updateOrCreate(
                    ['name' => $zone, 'district_id' => $district->id],
                    ['name' => $zone, 'district_id' => $district->id]
                );
            }
        }
    }

    private function getLocalitiesForMunicipality($municipalityName)
    {
        $localitiesData = [
            'Cochabamba' => [
                ['name' => 'Cochabamba', 'latitude' => -17.3895, 'longitude' => -66.1568],
                ['name' => 'Tirani', 'latitude' => -17.3722, 'longitude' => -66.1745],
            ],
            'Colcapirhua' => [
                ['name' => 'Colcapirhua', 'latitude' => -17.3857, 'longitude' => -66.2381],
                ['name' => 'Esquilan Grande', 'latitude' => -17.42, 'longitude' => -66.24],
                ['name' => 'La Florida', 'latitude' => -17.4, 'longitude' => -66.24],
                ['name' => 'Santa Rosa', 'latitude' => -17.40, 'longitude' => -66.23],
                ['name' => 'Rumi Mayu', 'latitude' => null, 'longitude' => null],
                ['name' => 'San Jose (Cuatro Esquinas)', 'latitude' => -17.395, 'longitude' => -66.239],
                ['name' => 'Sumumpaya', 'latitude' => -17.41, 'longitude' => -66.24],
            ],
            'Vinto' => [
                ['name' => 'Anocaraire', 'latitude' => null, 'longitude' => null],
                ['name' => 'Keraya', 'latitude' => null, 'longitude' => null],
                ['name' => 'La Chulla', 'latitude' => null, 'longitude' => null],
                ['name' => 'Llave Grande', 'latitude' => null, 'longitude' => null],
                ['name' => 'Machac Marca', 'latitude' => null, 'longitude' => null],
                ['name' => 'Thiomoko', 'latitude' => null, 'longitude' => null],
                ['name' => 'Vilomilla', 'latitude' => null, 'longitude' => null],
                ['name' => 'Vinto', 'latitude' => null, 'longitude' => null],
            ],
            'Sipe Sipe' => [
                ['name' => 'Caramarca', 'latitude' => -17.4667, 'longitude' => -66.3833],
                ['name' => 'Caviloma', 'latitude' => -17.4583, 'longitude' => -66.3750],
                ['name' => 'Itapaya', 'latitude' => -17.4500, 'longitude' => -66.3667],
                ['name' => 'Mallco Chapi', 'latitude' => -17.4417, 'longitude' => -66.3583],
                ['name' => 'Mallco Rancho', 'latitude' => -17.4333, 'longitude' => -66.3500],
                ['name' => 'Parotani', 'latitude' => -17.4250, 'longitude' => -66.3417],
                ['name' => 'Sauce Rancho', 'latitude' => -17.4167, 'longitude' => -66.3333],
                ['name' => 'Sipe Sipe', 'latitude' => -17.4475, 'longitude' => -66.3438],
                ['name' => 'Siqui Siquia', 'latitude' => -17.4083, 'longitude' => -66.3250],
                ['name' => 'Suticollo', 'latitude' => -17.4000, 'longitude' => -66.3167],
                ['name' => 'Uchu Uchu', 'latitude' => -17.3917, 'longitude' => -66.3083],
                ['name' => 'Viloma', 'latitude' => -17.3833, 'longitude' => -66.3000],
                ['name' => 'Viloma Cala Cala', 'latitude' => -17.3750, 'longitude' => -66.2917],
            ],
            'Tiquipaya' => [
                ['name' => '4 Esquinas', 'latitude' => -17.3500, 'longitude' => -66.2333],
                ['name' => 'Callaj Chulpa (Tiquipaya)', 'latitude' => -17.3417, 'longitude' => -66.2250],
                ['name' => 'Chapisirca', 'latitude' => -17.3333, 'longitude' => -66.2167],
                ['name' => 'Ciudad del Niño', 'latitude' => -17.3250, 'longitude' => -66.2083],
                ['name' => 'Cuatro Esquinas (Tiquipaya)', 'latitude' => -17.3167, 'longitude' => -66.2000],
                ['name' => 'Linde Chiquicollo', 'latitude' => -17.3083, 'longitude' => -66.1917],
                ['name' => 'Rumi Mayu (Tiquipaya)', 'latitude' => -17.3000, 'longitude' => -66.1833],
                ['name' => 'Rumy Corral', 'latitude' => -17.2917, 'longitude' => -66.1750],
                ['name' => 'Tiquipaya', 'latitude' => -17.3380, 'longitude' => -66.2158],
                ['name' => 'Trojes', 'latitude' => -17.2833, 'longitude' => -66.1667],
                ['name' => 'Waripucara', 'latitude' => -17.2750, 'longitude' => -66.1583],
            ],
            'Quillacollo' => [
                ['name' => 'Bella Vista', 'latitude' => -17.32144, 'longitude' => -66.28196],
                ['name' => 'Cotapachi', 'latitude' => -17.43139, 'longitude' => -66.27175],
                ['name' => 'El Paso', 'latitude' => -17.4083, 'longitude' => -66.2917],
                ['name' => 'Illataco', 'latitude' => -17.36861, 'longitude' => -66.30000],
                ['name' => 'Liriumi', 'latitude' => -17.31667, 'longitude' => -66.33333],
                ['name' => 'Misicuni', 'latitude' => -17.16667, 'longitude' => -66.36667],
                ['name' => 'Paucarpata', 'latitude' => -17.4000, 'longitude' => -66.3000],
                ['name' => 'Piñami', 'latitude' => -17.3833, 'longitude' => -66.2667],
                ['name' => 'Quillacollo', 'latitude' => -17.39228, 'longitude' => -66.27838],
            ],
        ];

        return $localitiesData[$municipalityName] ?? [];
    }

    private function getZonesForDistrict($districtName)
    {
        $zones = [
            'Distrito 1 - Centro' => [
                'Zona Central',
                'Zona Plaza Principal',
                'Zona Mercado Campesino',
                'Zona Estación de Ferrocarril',
                'Zona San Miguel',
                'Zona Catedral'
            ],
            'Distrito 2 - Norte' => [
                'Zona Villa América',
                'Zona Villa Bolívar',
                'Zona Villa Primero de Mayo',
                'Zona Villa Juan XXIII',
                'Zona Villa 14 de Septiembre',
                'Zona Villa Nuevo Amanecer'
            ],
            'Distrito 3 - Sur' => [
                'Zona Villa España',
                'Zona Villa Obrera',
                'Zona Villa Ecología',
                'Zona Villa San Cristóbal',
                'Zona Villa Los Ángeles',
                'Zona Villa San Antonio'
            ],
            'Distrito 4 - Este' => [
                'Zona Villa Fatima',
                'Zona Villa Los Olivos',
                'Zona Villa Universitaria',
                'Zona Villa Teresa',
                'Zona Villa Esperanza',
                'Zona Villa San Pedro'
            ],
            'Distrito 5 - Oeste' => [
                'Zona Villa Copacabana',
                'Zona Villa Pagador',
                'Zona Villa Armonía',
                'Zona Villa Sebastián Pagador',
                'Zona Villa Bella Vista',
                'Zona Villa San José'
            ],
            'Distrito 6 - Periurbano Norte' => [
                'Zona Lomas de Aranjuez',
                'Zona Valle Hermoso',
                'Zona Los Pinos',
                'Zona Santa Rosa',
                'Zona El Carmen'
            ],
            'Distrito 7 - Periurbano Sur' => [
                'Zona San Isidro',
                'Zona La Tamborada',
                'Zona Los Laureles',
                'Zona Villa Tunari',
                'Zona El Rosal'
            ],
            'Distrito 8 - Periurbano Este' => [
                'Zona El Pedregal',
                'Zona Los Tusequis',
                'Zona La Florida',
                'Zona Villa Israel',
                'Zona Los Molinos'
            ],
            'Distrito 9 - Periurbano Oeste' => [
                'Zona El Mirador',
                'Zona Villa Victoria',
                'Zona Los Cactus',
                'Zona Villa Liberación',
                'Zona Las Palmas'
            ],
            'Distrito 10 - Rural' => [
                'Zona Rural Norte - Comunidades',
                'Zona Rural Sur - Comunidades',
                'Zona Rural Este - Comunidades',
                'Zona Rural Oeste - Comunidades',
                'Zona Suburbana - Asentamientos',
                'Zona Agrícola - Campos de Cultivo'
            ]
        ];

        return $zones[$districtName] ?? [];
    }
}
