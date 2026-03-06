<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\District;
use App\Models\Zone;

class ProvincesMunicipalitiesSeeder extends Seeder
{
    public function run()
    {
        $cochabamba = Department::where('name', 'Cochabamba')->first();

        if (!$cochabamba) {
            $cochabamba = Department::create(['name' => 'Cochabamba', 'capital' => 'Cochabamba']);
        }
        $provinces = [
            ['name' => 'Arani', 'department_id' => $cochabamba->id],
            ['name' => 'Arque', 'department_id' => $cochabamba->id],
            ['name' => 'Ayopaya', 'department_id' => $cochabamba->id],
            ['name' => 'Bolívar', 'department_id' => $cochabamba->id],
            ['name' => 'Campero', 'department_id' => $cochabamba->id],
            ['name' => 'Capinota', 'department_id' => $cochabamba->id],
            ['name' => 'Carrasco', 'department_id' => $cochabamba->id],
            ['name' => 'Cercado', 'department_id' => $cochabamba->id],
            ['name' => 'Chapare', 'department_id' => $cochabamba->id],
            ['name' => 'Esteban Arce', 'department_id' => $cochabamba->id],
            ['name' => 'Germán Jordán', 'department_id' => $cochabamba->id],
            ['name' => 'Mizque', 'department_id' => $cochabamba->id],
            ['name' => 'Punata', 'department_id' => $cochabamba->id],
            ['name' => 'Quillacollo', 'department_id' => $cochabamba->id,
                'latitude' => -17.3333, 'longitude' => -66.2500,],
            ['name' => 'Tapacarí', 'department_id' => $cochabamba->id],
            ['name' => 'Tiraque', 'department_id' => $cochabamba->id],
        ];
        foreach ($provinces as $province) {
            Province::firstOrCreate(
                ['name' => $province['name'], 'department_id' => $province['department_id']],
                $province
            );
        }
        $this->addAraniMunicipalities();
        $this->addArqueMunicipalities();
        $this->addAyopayaMunicipalities();
        $this->addBolivarMunicipalities();
        $this->addCamperoMunicipalities();
        $this->addCapinotaMunicipalities();
        $this->addCarrascoMunicipalities();
        $this->addCercadoMunicipalities();
        $this->addChapareMunicipalities();
        $this->addEstebanArceMunicipalities();
        $this->addGermanJordanMunicipalities();
        $this->addMizqueMunicipalities();
        $this->addPunataMunicipalities();
        $this->addQuillacolloMunicipalities();
        $this->addTapacariMunicipalities();
        $this->addTiraqueMunicipalities();
    }

    private function addAraniMunicipalities()
    {
        $province = Province::where('name', 'Arani')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Arani', 'province_id' => $province->id],
            ['name' => 'Vacas', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addArqueMunicipalities()
    {
        $province = Province::where('name', 'Arque')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Arque', 'province_id' => $province->id],
            ['name' => 'Tacopaya', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addAyopayaMunicipalities()
    {
        $province = Province::where('name', 'Ayopaya')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Ayopaya', 'province_id' => $province->id],
            ['name' => 'Morochata', 'province_id' => $province->id],
            ['name' => 'Cocapata', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addBolivarMunicipalities()
    {
        $province = Province::where('name', 'Bolívar')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Bolívar', 'province_id' => $province->id],
            ['name' => 'Villa Tunari', 'province_id' => $province->id],
            ['name' => 'Sacaca', 'province_id' => $province->id],
            ['name' => 'Carasi', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addCamperoMunicipalities()
    {
        $province = Province::where('name', 'Campero')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Aiquile', 'province_id' => $province->id],
            ['name' => 'Pasorapa', 'province_id' => $province->id],
            ['name' => 'Omereque', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addCapinotaMunicipalities()
    {
        $province = Province::where('name', 'Capinota')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Capinota', 'province_id' => $province->id],
            ['name' => 'Santiváñez', 'province_id' => $province->id],
            ['name' => 'Sicaya', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addCarrascoMunicipalities()
    {
        $province = Province::where('name', 'Carrasco')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Totora', 'province_id' => $province->id],
            ['name' => 'Pojo', 'province_id' => $province->id],
            ['name' => 'Pocona', 'province_id' => $province->id],
            ['name' => 'Chimoré', 'province_id' => $province->id],
            ['name' => 'Puerto Villarroel', 'province_id' => $province->id],
            ['name' => 'Entre Ríos', 'province_id' => $province->id],
            ['name' => 'Tiraque', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addCercadoMunicipalities()
    {
        $province = Province::where('name', 'Cercado')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Cochabamba', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addChapareMunicipalities()
    {
        $province = Province::where('name', 'Chapare')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Sacaba', 'province_id' => $province->id],
            ['name' => 'Colomi', 'province_id' => $province->id],
            ['name' => 'Villa Tunari', 'province_id' => $province->id],
            ['name' => 'Shinahota', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addEstebanArceMunicipalities()
    {
        $province = Province::where('name', 'Esteban Arce')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Tarata', 'province_id' => $province->id],
            ['name' => 'Anzaldo', 'province_id' => $province->id],
            ['name' => 'Arbieto', 'province_id' => $province->id],
            ['name' => 'Sacabamba', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addGermanJordanMunicipalities()
    {
        $province = Province::where('name', 'Germán Jordán')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Cliza', 'province_id' => $province->id],
            ['name' => 'Toco', 'province_id' => $province->id],
            ['name' => 'Tolata', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addMizqueMunicipalities()
    {
        $province = Province::where('name', 'Mizque')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Mizque', 'province_id' => $province->id],
            ['name' => 'Vila Vila', 'province_id' => $province->id],
            ['name' => 'Alalay', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addPunataMunicipalities()
    {
        $province = Province::where('name', 'Punata')->first();
        if (!$province) return;
        $municipalities = [
            ['name' => 'Punata', 'province_id' => $province->id],
            ['name' => 'Villa Rivero', 'province_id' => $province->id],
            ['name' => 'San Benito', 'province_id' => $province->id],
            ['name' => 'Tacachi', 'province_id' => $province->id],
            ['name' => 'Cuchumuela', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addQuillacolloMunicipalities()
    {
        $province = Province::where('name', 'Quillacollo')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Quillacollo','province_id' => $province->id,
                'latitude' => -17.3983,'longitude' => -66.2771,],
            ['name' => 'Sipe Sipe','province_id' => $province->id,
                'latitude' => -17.447,'longitude' => -66.3438],
            ['name' => 'Tiquipaya','province_id' => $province->id,
                'latitude' => -17.338,  'longitude' => -66.2158],
            ['name' => 'Vinto','province_id' => $province->id,
                'latitude' => -17.0000,'longitude' => -66.3000],
            ['name' => 'Colcapirhua','province_id' => $province->id,
                'latitude' => -17.4000,'longitude' => -66.2333],
        ];
        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addTapacariMunicipalities()
    {
        $province = Province::where('name', 'Tapacarí')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Tapacarí', 'province_id' => $province->id],
            ['name' => 'Leque', 'province_id' => $province->id],
            ['name' => 'Ramadas', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }

    private function addTiraqueMunicipalities()
    {
        $province = Province::where('name', 'Tiraque')->first();
        if (!$province) return;

        $municipalities = [
            ['name' => 'Tiraque', 'province_id' => $province->id],
            ['name' => 'Shinahota', 'province_id' => $province->id],
            ['name' => 'Puerto Villarroel', 'province_id' => $province->id],
        ];

        foreach ($municipalities as $municipality) {
            Municipality::firstOrCreate(
                ['name' => $municipality['name'], 'province_id' => $municipality['province_id']],
                $municipality
            );
        }
    }
}
