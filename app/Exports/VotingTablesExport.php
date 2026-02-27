<?php
namespace App\Exports;

use App\Models\VotingTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VotingTablesExport
{
    public function export($filters = [])
    {
        try {
            $query = VotingTable::with([
                'institution.locality.municipality.province.department',
                'electionType',
                'president',
                'secretary',
                'vocal1',
                'vocal2',
                'vocal3',
                'vocal4'
            ]);

            if (!empty($filters['institution_id'])) {
                $query->where('institution_id', $filters['institution_id']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['election_type_id'])) {
                $query->where('election_type_id', $filters['election_type_id']);
            }

            $votingTables = $query->orderBy('institution_id')->orderBy('number')->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Headers
            $headers = [
                'A1' => 'Código Mesa',
                'B1' => 'Código INE',
                'C1' => 'N° Mesa',
                'D1' => 'Letra',
                'E1' => 'Tipo',
                'F1' => 'Recinto',
                'G1' => 'Código Recinto',
                'H1' => 'Departamento',
                'I1' => 'Provincia',
                'J1' => 'Municipio',
                'K1' => 'Localidad',
                'L1' => 'Tipo Elección',
                'M1' => 'Desde Apellido',
                'N1' => 'Hasta Apellido',
                'O1' => 'Desde C.I.',
                'P1' => 'Hasta C.I.',
                'Q1' => 'Ciudadanos Habilitados',
                'R1' => 'Votaron',
                'S1' => 'Ausentes',
                'T1' => 'Votos Válidos',
                'U1' => 'Votos Blanco',
                'V1' => 'Votos Nulos',
                'W1' => 'Papeletas Computadas',
                'X1' => 'Papeletas Anuladas',
                'Y1' => 'Papeletas Habilitadas',
                'Z1' => 'Presidente',
                'AA1' => 'Secretario',
                'AB1' => 'Vocal 1',
                'AC1' => 'Vocal 2',
                'AD1' => 'Vocal 3',
                'AE1' => 'Vocal 4',
                'AF1' => 'Hora Apertura',
                'AG1' => 'Hora Cierre',
                'AH1' => 'Fecha Elección',
                'AI1' => 'N° Acta',
                'AJ1' => 'Fecha Subida Acta',
                'AK1' => 'Estado',
                'AL1' => 'Observaciones'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Style headers
            $headerRange = 'A1:AL1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($headerRange)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 2;
            foreach ($votingTables as $table) {
                // Calculate valid votes
                $validVotes = $table->computed_records - $table->blank_votes - $table->null_votes;

                $sheet->setCellValue('A' . $row, $table->code ?? '');
                $sheet->setCellValue('B' . $row, $table->code_ine ?? '');
                $sheet->setCellValue('C' . $row, $table->number ?? '');
                $sheet->setCellValue('D' . $row, $table->letter ?? '');
                $sheet->setCellValue('E' . $row, $this->getTypeLabel($table->type));
                $sheet->setCellValue('F' . $row, $table->institution->name ?? '');
                $sheet->setCellValue('G' . $row, $table->institution->code ?? '');
                $sheet->setCellValue('H' . $row, $table->institution->department->name ?? '');
                $sheet->setCellValue('I' . $row, $table->institution->province->name ?? '');
                $sheet->setCellValue('J' . $row, $table->institution->municipality->name ?? '');
                $sheet->setCellValue('K' . $row, $table->institution->locality->name ?? '');
                $sheet->setCellValue('L' . $row, $table->electionType->name ?? '');
                $sheet->setCellValue('M' . $row, $table->from_name ?? '');
                $sheet->setCellValue('N' . $row, $table->to_name ?? '');
                $sheet->setCellValue('O' . $row, $table->from_number ?? '');
                $sheet->setCellValue('P' . $row, $table->to_number ?? '');
                $sheet->setCellValue('Q' . $row, $table->registered_citizens ?? 0);
                $sheet->setCellValue('R' . $row, $table->voted_citizens ?? 0);
                $sheet->setCellValue('S' . $row, ($table->registered_citizens - $table->voted_citizens) ?? 0);
                $sheet->setCellValue('T' . $row, $validVotes);
                $sheet->setCellValue('U' . $row, $table->blank_votes ?? 0);
                $sheet->setCellValue('V' . $row, $table->null_votes ?? 0);
                $sheet->setCellValue('W' . $row, $table->computed_records ?? 0);
                $sheet->setCellValue('X' . $row, $table->annulled_records ?? 0);
                $sheet->setCellValue('Y' . $row, $table->enabled_records ?? 0);
                $sheet->setCellValue('Z' . $row, $table->president ? $table->president->name . ' ' . ($table->president->last_name ?? '') : '');
                $sheet->setCellValue('AA' . $row, $table->secretary ? $table->secretary->name . ' ' . ($table->secretary->last_name ?? '') : '');
                $sheet->setCellValue('AB' . $row, $table->vocal1 ? $table->vocal1->name . ' ' . ($table->vocal1->last_name ?? '') : '');
                $sheet->setCellValue('AC' . $row, $table->vocal2 ? $table->vocal2->name . ' ' . ($table->vocal2->last_name ?? '') : '');
                $sheet->setCellValue('AD' . $row, $table->vocal3 ? $table->vocal3->name . ' ' . ($table->vocal3->last_name ?? '') : '');
                $sheet->setCellValue('AE' . $row, $table->vocal4 ? $table->vocal4->name . ' ' . ($table->vocal4->last_name ?? '') : '');
                $sheet->setCellValue('AF' . $row, $table->opening_time ? \Carbon\Carbon::parse($table->opening_time)->format('H:i') : '');
                $sheet->setCellValue('AG' . $row, $table->closing_time ? \Carbon\Carbon::parse($table->closing_time)->format('H:i') : '');
                $sheet->setCellValue('AH' . $row, $table->election_date ? \Carbon\Carbon::parse($table->election_date)->format('d/m/Y') : '');
                $sheet->setCellValue('AI' . $row, $table->acta_number ?? '');
                $sheet->setCellValue('AJ' . $row, $table->acta_uploaded_at ? \Carbon\Carbon::parse($table->acta_uploaded_at)->format('d/m/Y H:i') : '');
                $sheet->setCellValue('AK' . $row, $this->getStatusLabel($table->status));
                $sheet->setCellValue('AL' . $row, $table->observations ?? '');

                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'AL') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $fileName = 'mesas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
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
                'A1' => 'Código Mesa',
                'B1' => 'Código INE',
                'C1' => 'N° Mesa',
                'D1' => 'Letra',
                'E1' => 'Tipo',
                'F1' => 'Recinto',
                'G1' => 'Código Recinto',
                'H1' => 'Departamento',
                'I1' => 'Provincia',
                'J1' => 'Municipio',
                'K1' => 'Localidad',
                'L1' => 'Tipo Elección',
                'M1' => 'Desde Apellido',
                'N1' => 'Hasta Apellido',
                'O1' => 'Desde C.I.',
                'P1' => 'Hasta C.I.',
                'Q1' => 'Ciudadanos Habilitados',
                'R1' => 'Votaron',
                'S1' => 'Votos Válidos',
                'T1' => 'Votos Blanco',
                'U1' => 'Votos Nulos',
                'V1' => 'Papeletas Computadas',
                'W1' => 'Papeletas Anuladas',
                'X1' => 'Papeletas Habilitadas',
                'Y1' => 'Presidente',
                'Z1' => 'Secretario',
                'AA1' => 'Vocal 1',
                'AB1' => 'Vocal 2',
                'AC1' => 'Vocal 3',
                'AD1' => 'Vocal 4',
                'AE1' => 'Hora Apertura',
                'AF1' => 'Hora Cierre',
                'AG1' => 'Fecha Elección',
                'AH1' => 'N° Acta',
                'AI1' => 'Estado'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            $headerRange = 'A1:AI1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');

            // Sample data
            $sampleData = [
                ['MESA001', 'INE001', '1', 'A', 'mixta', 'UNIDAD EDUCATIVA SIMÓN BOLÍVAR', 'INST0001', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Quillacollo (Urbano)', 'Elecciones Generales 2025', 'ACOSTA', 'ZEBALLOS', '1000000', '1999999', '350', '320', '305', '5', '10', '320', '5', '310', '', '', '', '', '', '', '08:00', '17:00', '19/10/2025', 'ACTA-001', 'pendiente'],
                ['MESA002', 'INE002', '2', 'B', 'mixta', 'COLEGIO NACIONAL QUILLACOLLO', 'INST0002', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Centro', 'Elecciones Generales 2025', 'FLORES', 'PEREZ', '2000000', '2999999', '280', '250', '240', '4', '6', '250', '4', '240', '', '', '', '', '', '', '08:15', '17:00', '19/10/2025', 'ACTA-002', 'pendiente'],
                ['', '', '3', '', 'mixta', 'UNIDAD EDUCATIVA ADELA ZAMUDIO', '', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'El Paso', 'Elecciones Generales 2025', '', '', '', '', '320', '300', '290', '5', '5', '300', '5', '290', '', '', '', '', '', '', '', '', '', '', 'pendiente']
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

            // Auto-size columns
            foreach (range('A', 'AI') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Add instructions
            $instructionRow = $row + 2;
            $sheet->setCellValue('A' . $instructionRow, 'INSTRUCCIONES:');
            $sheet->getStyle('A' . $instructionRow)->getFont()->setBold(true);

            $instructions = [
                'El código de mesa se genera automáticamente si se deja vacío',
                'Los campos N° Mesa, Recinto y Estado son obligatorios',
                'Para el campo Tipo use: mixta, masculina o femenina',
                'Para el campo Estado use: pendiente, en_proceso, cerrado, en_computo, computado, observado, anulado',
                'Los presidentes, secretarios y vocales deben ser emails o nombres completos válidos',
                'Las fechas deben tener formato dd/mm/aaaa',
                'Las horas deben tener formato HH:MM (24h)',
                'Elimine estas filas de ejemplo antes de importar sus datos'
            ];

            foreach ($instructions as $i => $instruction) {
                $sheet->setCellValue('A' . ($instructionRow + $i + 1), '• ' . $instruction);
            }

            $fileName = 'plantilla_mesas.xlsx';
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

    private function getTypeLabel($type)
    {
        return match($type) {
            'mixta' => 'Mixta',
            'masculina' => 'Masculina',
            'femenina' => 'Femenina',
            default => $type,
        };
    }

    private function getStatusLabel($status)
    {
        return match($status) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'cerrado' => 'Cerrado',
            'en_computo' => 'En Cómputo',
            'computado' => 'Computado',
            'observado' => 'Observado',
            'anulado' => 'Anulado',
            default => $status,
        };
    }
}