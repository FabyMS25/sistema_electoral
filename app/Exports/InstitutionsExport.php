<?php
namespace App\Exports;

use App\Models\Institution;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InstitutionsExport
{
    public function export($filters = [])
    {
        try {
            $query = Institution::with([
                'locality.municipality.province.department',
                'district',
                'zone'
            ]);
            if (!empty($filters['selected_ids'])) {
                $query->whereIn('id', $filters['selected_ids']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('code', 'ilike', "%{$search}%")
                      ->orWhere('short_name', 'ilike', "%{$search}%");
                });
            }
            if (!empty($filters['department_id'])) {
                $query->whereHas('locality.municipality.province', function($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            $institutions = $query->orderBy('name')->get();
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'A1' => 'Código',
                'B1' => 'Nombre',
                'C1' => 'Nombre Corto',
                'D1' => 'Departamento',
                'E1' => 'Provincia',
                'F1' => 'Municipio',
                'G1' => 'Localidad',
                'H1' => 'Distrito',
                'I1' => 'Zona',
                'J1' => 'Dirección',
                'K1' => 'Referencia',
                'L1' => 'Teléfono',
                'M1' => 'Email',
                'N1' => 'Responsable',
                'O1' => 'Ciudadanos Habilitados',
                'P1' => 'Total Mesas',
                'Q1' => 'Actas Computadas',
                'R1' => 'Actas Anuladas',
                'S1' => 'Actas Habilitadas',
                'T1' => 'Latitud',
                'U1' => 'Longitud',
                'V1' => 'Estado',
                'W1' => 'Operativo',
                'X1' => 'Observaciones'
            ];
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $headerRange = 'A1:X1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($headerRange)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 2;
            foreach ($institutions as $institution) {
                $sheet->setCellValue('A' . $row, $institution->code ?? '');
                $sheet->setCellValue('B' . $row, $institution->name ?? '');
                $sheet->setCellValue('C' . $row, $institution->short_name ?? '');
                $sheet->setCellValue('D' . $row, $institution->locality->municipality->province->department->name ?? '');
                $sheet->setCellValue('E' . $row, $institution->locality->municipality->province->name ?? '');
                $sheet->setCellValue('F' . $row, $institution->locality->municipality->name ?? '');
                $sheet->setCellValue('G' . $row, $institution->locality->name ?? '');
                $sheet->setCellValue('H' . $row, $institution->district->name ?? '');
                $sheet->setCellValue('I' . $row, $institution->zone->name ?? '');
                $sheet->setCellValue('J' . $row, $institution->address ?? '');
                $sheet->setCellValue('K' . $row, $institution->reference ?? '');
                $sheet->setCellValue('L' . $row, $institution->phone ?? '');
                $sheet->setCellValue('M' . $row, $institution->email ?? '');
                $sheet->setCellValue('N' . $row, $institution->responsible_name ?? '');
                $sheet->setCellValue('O' . $row, $institution->registered_citizens ?? 0);
                $sheet->setCellValue('P' . $row, $institution->voting_tables_count ?? 0);
                $sheet->setCellValue('Q' . $row, $institution->total_computed_records ?? 0);
                $sheet->setCellValue('R' . $row, $institution->total_annulled_records ?? 0);
                $sheet->setCellValue('S' . $row, $institution->total_enabled_records ?? 0);
                $sheet->setCellValue('T' . $row, $institution->latitude ?? '');
                $sheet->setCellValue('U' . $row, $institution->longitude ?? '');
                $sheet->setCellValue('V' . $row, $this->getStatusLabel($institution->status));
                $sheet->setCellValue('W' . $row, $institution->is_operative ? 'Sí' : 'No');
                $sheet->setCellValue('X' . $row, $institution->observations ?? '');

                $row++;
            }
            $columns = range('A', 'X');
            foreach ($columns as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $fileName = 'recintos_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = "exports/{$fileName}";
            Storage::makeDirectory('exports');
            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path("app/{$filePath}"));
            return $filePath;
        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            throw new \Exception('Error al generar el archivo de exportación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = [
                'A1' => 'Código',
                'B1' => 'Nombre',
                'C1' => 'Nombre Corto',
                'D1' => 'Departamento',
                'E1' => 'Provincia',
                'F1' => 'Municipio',
                'G1' => 'Localidad',
                'H1' => 'Distrito',
                'I1' => 'Zona',
                'J1' => 'Dirección',
                'K1' => 'Referencia',
                'L1' => 'Teléfono',
                'M1' => 'Email',
                'N1' => 'Responsable',
                'O1' => 'Ciudadanos Habilitados',
                'P1' => 'Estado',
                'Q1' => 'Operativo',
                'R1' => 'Observaciones'
            ];
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $headerRange = 'A1:R1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sampleData = [
                ['INST001', 'UNIDAD EDUCATIVA SIMÓN BOLÍVAR', 'UE SIMÓN BOLÍVAR', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Quillacollo (Urbano)', '', '', 'Av. Blanco Galindo Km 12', 'Frente a la plaza', '4-1234567', 'ue.simon@ejemplo.com', 'Juan Pérez', '350', 'activo', 'Sí', 'Recinto principal'],
                ['INST002', 'COLEGIO NACIONAL QUILLACOLLO', 'CNQ', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Centro', '', '', 'Calle Sucre', 'Al lado de la iglesia', '4-7654321', 'cnq@ejemplo.com', 'María Gómez', '280', 'activo', 'Sí', ''],
            ];

            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            $columns = range('A', 'R');
            foreach ($columns as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $instructionRow = $row + 2;
            $sheet->setCellValue('A' . $instructionRow, 'INSTRUCCIONES:');
            $sheet->getStyle('A' . $instructionRow)->getFont()->setBold(true);

            $instructions = [
                'El código se genera automáticamente si se deja vacío',
                'Los campos Nombre, Departamento, Provincia, Municipio y Localidad son obligatorios',
                'Para el campo Estado use: activo, inactivo o en_mantenimiento',
                'Para el campo Operativo use: Sí o No',
                'Los departamentos, provincias, municipios y localidades deben existir en el sistema',
                'Elimine estas filas de ejemplo antes de importar sus datos'
            ];

            foreach ($instructions as $i => $instruction) {
                $sheet->setCellValue('A' . ($instructionRow + $i + 1), '• ' . $instruction);
            }

            $fileName = 'plantilla_recintos.xlsx';
            $filePath = "templates/{$fileName}";

            Storage::makeDirectory('templates');

            $writer = new Xlsx($spreadsheet);
            $writer->save(storage_path("app/{$filePath}"));

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Template generation error: ' . $e->getMessage());
            throw new \Exception('Error al generar la plantilla: ' . $e->getMessage());
        }
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'activo' => 'Activo',
            'inactivo' => 'Inactivo',
            'en_mantenimiento' => 'En Mantenimiento',
            default => $status,
        };
    }
}
