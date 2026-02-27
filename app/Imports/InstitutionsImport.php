<?php
// app/Imports/InstitutionsImport.php

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

    public function import($uploadedFile)
    {
        try {
            $filePath = $uploadedFile->store('imports');
            $spreadsheet = IOFactory::load(storage_path("app/{$filePath}"));
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
            
            DB::commit();
            Storage::delete($filePath);
            
            return [
                'success' => true,
                'errors' => $this->errors,
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
        // Validar campos requeridos
        if (empty($row[1])) {
            throw new \Exception("El nombre es requerido");
        }
        
        if (empty($row[6])) {
            throw new \Exception("La localidad es requerida");
        }

        // Buscar departamento
        $department = !empty($row[3]) ? Department::where('name', 'ilike', '%' . trim($row[3]) . '%')->first() : null;
        if (!empty($row[3]) && !$department) {
            throw new \Exception("Departamento no encontrado: " . trim($row[3]));
        }

        // Buscar provincia
        $province = null;
        if (!empty($row[4]) && $department) {
            $province = Province::where('name', 'ilike', '%' . trim($row[4]) . '%')
                              ->where('department_id', $department->id)
                              ->first();
            if (!$province) {
                throw new \Exception("Provincia no encontrada: " . trim($row[4]));
            }
        }

        // Buscar municipio
        $municipality = null;
        if (!empty($row[5]) && $province) {
            $municipality = Municipality::where('name', 'ilike', '%' . trim($row[5]) . '%')
                                       ->where('province_id', $province->id)
                                       ->first();
            if (!$municipality) {
                throw new \Exception("Municipalidad no encontrada: " . trim($row[5]));
            }
        }

        // Buscar localidad
        $locality = null;
        if (!empty($row[6]) && $municipality) {
            $locality = Locality::where('name', 'ilike', '%' . trim($row[6]) . '%')
                               ->where('municipality_id', $municipality->id)
                               ->first();
        }
        
        if (!$locality) {
            throw new \Exception("Localidad no encontrada: " . (trim($row[6]) ?? 'vacía'));
        }

        // Buscar distrito (opcional)
        $district = null;
        if (!empty($row[7]) && $municipality) {
            $district = District::where('name', 'ilike', '%' . trim($row[7]) . '%')
                               ->where('municipality_id', $municipality->id)
                               ->first();
        }

        // Buscar zona (opcional)
        $zone = null;
        if (!empty($row[8])) {
            $zone = Zone::where('name', 'ilike', '%' . trim($row[8]) . '%')->first();
        }

        // Determinar estado activo
        $active = true;
        if (isset($row[13])) {
            $activeValue = strtolower(trim($row[13]));
            $active = in_array($activeValue, ['sí', 'si', 'yes', 'true', '1', 'activo', 'activado']);
        }

        // Generar o usar código
        $code = !empty($row[0]) ? trim($row[0]) : $this->generateInstitutionCode(trim($row[1]));

        // Crear o actualizar institución
        Institution::updateOrCreate(
            [
                'name' => trim($row[1]),
                'locality_id' => $locality->id
            ],
            [
                'code' => $code,
                'address' => !empty($row[2]) ? trim($row[2]) : null,
                'locality_id' => $locality->id,
                'district_id' => $district->id ?? null,
                'zone_id' => $zone->id ?? null,
                'registered_citizens' => is_numeric($row[9] ?? null) ? intval($row[9]) : 0,
                'total_computed_records' => is_numeric($row[10] ?? null) ? intval($row[10]) : 0,
                'total_annulled_records' => is_numeric($row[11] ?? null) ? intval($row[11]) : 0,
                'total_enabled_records' => is_numeric($row[12] ?? null) ? intval($row[12]) : 0,
                'active' => $active,
            ]
        );
        
        $this->successCount++;
    }

    private function generateInstitutionCode($name, $excludeId = null)
    {
        $baseCode = 'INST' . strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        $counter = 1;
        $code = $baseCode . sprintf('%03d', $counter);
        
        $query = Institution::where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        while ($query->exists()) {
            $counter++;
            $code = $baseCode . sprintf('%03d', $counter);
            $query = Institution::where('code', $code);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }
        
        return $code;
    }

    // ========== NUEVOS MÉTODOS PÚBLICOS ==========
    
    /**
     * Obtiene el número de registros importados exitosamente
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * Obtiene la lista de errores
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Verifica si hubo errores
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Obtiene el primer error (útil para mensajes rápidos)
     */
    public function getFirstError()
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Limpia los contadores (útil para reutilizar la instancia)
     */
    public function reset()
    {
        $this->successCount = 0;
        $this->errors = [];
    }
}