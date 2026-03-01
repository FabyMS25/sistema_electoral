<?php
namespace App\Imports;

use App\Models\Institution;
use App\Models\Department;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Locality;
use App\Models\District;
use App\Models\Zone;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InstitutionsImport
{
    private $errors = [];
    private $successCount = 0;
    private $warnings = [];

    public function import($uploadedFile)
    {
        try {
            Log::info('Starting institutions import process', ['file' => $uploadedFile->getClientOriginalName()]);
            $filePath = $uploadedFile->store('imports');
            $fullPath = storage_path("app/{$filePath}");
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('El archivo está vacío o no tiene datos válidos.');
            }
            DB::beginTransaction();
            foreach (array_slice($rows, 1) as $index => $row) {
                try {
                    $this->processRow($row, $index + 2);
                } catch (\Exception $e) {
                    $this->errors[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
                }
            }
            if (empty($this->errors)) {
                DB::commit();
                Log::info('Import completed successfully. Records: ' . $this->successCount);
            } else {
                DB::rollBack();
                Log::warning('Import completed with errors. Success: ' . $this->successCount . ', Errors: ' . count($this->errors));
            }
            Storage::delete($filePath);
            return [
                'success' => true,
                'errors' => $this->errors,
                'warnings' => $this->warnings,
                'success_count' => $this->successCount
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            Log::error('Import error: ' . $e->getMessage());
            return [
                'success' => false,
                'errors' => [$e->getMessage()],
                'success_count' => 0
            ];
        }
    }

    private function processRow($row, $rowNumber)
    {
        if (empty(array_filter($row))) {
            return;
        }
        $colCode = 0;               // A - Código
        $colName = 1;               // B - Nombre
        $colShortName = 2;          // C - Nombre Corto
        $colDepartment = 3;         // D - Departamento
        $colProvince = 4;           // E - Provincia
        $colMunicipality = 5;       // F - Municipio
        $colLocality = 6;           // G - Localidad
        $colDistrict = 7;           // H - Distrito
        $colZone = 8;               // I - Zona
        $colAddress = 9;            // J - Dirección
        $colReference = 10;         // K - Referencia
        $colPhone = 11;             // L - Teléfono
        $colEmail = 12;             // M - Email
        $colResponsible = 13;       // N - Responsable
        $colCitizens = 14;          // O - Ciudadanos Habilitados
        $colStatus = 15;            // P - Estado
        $colOperative = 16;         // Q - Operativo
        $colObservations = 17;      // R - Observaciones

        if (empty(trim($row[$colName] ?? ''))) {
            throw new \Exception("El nombre del recinto es requerido");
        }
        $department = $this->findDepartment($row[$colDepartment] ?? null, $rowNumber);
        if (!$department) {
            throw new \Exception("Departamento no encontrado: " . ($row[$colDepartment] ?? 'vacío'));
        }
        $province = $this->findProvince($row[$colProvince] ?? null, $department->id, $rowNumber);
        if (!$province) {
            throw new \Exception("Provincia no encontrada: " . ($row[$colProvince] ?? 'vacío'));
        }
        $municipality = $this->findMunicipality($row[$colMunicipality] ?? null, $province->id, $rowNumber);
        if (!$municipality) {
            throw new \Exception("Municipio no encontrado: " . ($row[$colMunicipality] ?? 'vacío'));
        }
        $locality = $this->findLocality($row[$colLocality] ?? null, $municipality->id, $rowNumber);
        if (!$locality) {
            throw new \Exception("Localidad no encontrada: " . ($row[$colLocality] ?? 'vacío'));
        }
        $district = $this->findDistrict($row[$colDistrict] ?? null, $municipality->id, $rowNumber);
        $zone = $this->findZone($row[$colZone] ?? null, $district->id ?? null, $rowNumber);
        $this->processInstitutionData($row, $rowNumber, $locality, $district, $zone);
    }

    private function findDepartment($value, $rowNumber)
    {
        if (empty($value)) return null;
        $department = Department::where('name', $value)->first();
        if (!$department) {
            $department = Department::where('name', 'ilike', '%' . $value . '%')->first();
        }
        return $department;
    }

    private function findProvince($value, $departmentId, $rowNumber)
    {
        if (empty($value)) return null;
        $province = Province::where('name', $value)
            ->where('department_id', $departmentId)
            ->first();
        if (!$province) {
            $province = Province::where('name', 'ilike', '%' . $value . '%')
                ->where('department_id', $departmentId)
                ->first();
        }
        return $province;
    }

    private function findMunicipality($value, $provinceId, $rowNumber)
    {
        if (empty($value)) return null;
        $municipality = Municipality::where('name', $value)
            ->where('province_id', $provinceId)
            ->first();
        if (!$municipality) {
            $municipality = Municipality::where('name', 'ilike', '%' . $value . '%')
                ->where('province_id', $provinceId)
                ->first();
        }
        return $municipality;
    }

    private function findLocality($value, $municipalityId, $rowNumber)
    {
        if (empty($value)) return null;
        $locality = Locality::where('name', $value)
            ->where('municipality_id', $municipalityId)
            ->first();
        if (!$locality) {
            $locality = Locality::where('name', 'ilike', '%' . $value . '%')
                ->where('municipality_id', $municipalityId)
                ->first();
        }
        return $locality;
    }

    private function findDistrict($value, $municipalityId, $rowNumber)
    {
        if (empty($value)) return null;
        $district = District::where('name', $value)
            ->where('municipality_id', $municipalityId)
            ->first();
        if (!$district) {
            $district = District::where('name', 'ilike', '%' . $value . '%')
                ->where('municipality_id', $municipalityId)
                ->first();
            if ($district) {
                $this->warnings[] = "Fila {$rowNumber}: Se usó coincidencia parcial para el distrito: {$district->name}";
            }
        }
        return $district;
    }

    private function findZone($value, $districtId, $rowNumber)
    {
        if (empty($value) || !$districtId) return null;
        $zone = Zone::where('name', $value)
            ->where('district_id', $districtId)
            ->first();
        if (!$zone) {
            $zone = Zone::where('name', 'ilike', '%' . $value . '%')
                ->where('district_id', $districtId)
                ->first();
            if ($zone) {
                $this->warnings[] = "Fila {$rowNumber}: Se usó coincidencia parcial para la zona: {$zone->name}";
            }
        }
        return $zone;
    }

    private function processInstitutionData($row, $rowNumber, $locality, $district, $zone)
    {
        $colCode = 0;
        $colName = 1;
        $colShortName = 2;
        $colAddress = 9;
        $colReference = 10;
        $colPhone = 11;
        $colEmail = 12;
        $colResponsible = 13;
        $colCitizens = 14;
        $colStatus = 15;
        $colOperative = 16;
        $colObservations = 17;
        $name = trim($row[$colName]);
        $code = !empty($row[$colCode]) ? trim($row[$colCode]) : null;
        $status = !empty($row[$colStatus]) ? strtolower(trim($row[$colStatus])) : 'activo';
        $isOperative = !empty($row[$colOperative]) && strtolower(trim($row[$colOperative])) === 'sí';
        $existingInstitution = Institution::where('name', $name)->first();
        $data = [
            'code' => $code,
            'name' => $name,
            'short_name' => !empty($row[$colShortName]) ? trim($row[$colShortName]) : null,
            'locality_id' => $locality->id,
            'district_id' => $district?->id,
            'zone_id' => $zone?->id,
            'address' => !empty($row[$colAddress]) ? trim($row[$colAddress]) : null,
            'reference' => !empty($row[$colReference]) ? trim($row[$colReference]) : null,
            'phone' => !empty($row[$colPhone]) ? trim($row[$colPhone]) : null,
            'email' => !empty($row[$colEmail]) ? trim($row[$colEmail]) : null,
            'responsible_name' => !empty($row[$colResponsible]) ? trim($row[$colResponsible]) : null,
            'registered_citizens' => !empty($row[$colCitizens]) ? intval($row[$colCitizens]) : 0,
            'status' => $status,
            'is_operative' => $isOperative,
            'observations' => !empty($row[$colObservations]) ? trim($row[$colObservations]) : null,
        ];
        $data['department_id'] = $locality->municipality->province->department->id;
        $data['province_id'] = $locality->municipality->province->id;
        $data['municipality_id'] = $locality->municipality->id;
        if ($existingInstitution) {
            $existingInstitution->update($data);
            Log::info("Updated institution ID: {$existingInstitution->id}");
        } else {
            if ($code) {
                $existingCode = Institution::where('code', $code)->first();
                if ($existingCode) {
                    throw new \Exception("El código {$code} ya existe en otro recinto");
                }
            }
            Institution::create($data);
        }
        $this->successCount++;
    }
}
