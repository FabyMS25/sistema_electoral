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
            ]);

            if (!empty($filters['selected_ids'])) {
                $query->whereIn('id', $filters['selected_ids']);
            }

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

            // Headers actualizados con los nuevos campos
            $headers = [
                'A1' => 'Código OEP',
                'B1' => 'Código Interno',
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
                'M1' => 'Rango Desde (Apellido)',
                'N1' => 'Rango Hasta (Apellido)',
                'O1' => 'Votantes Esperados',
                'P1' => 'Papeletas Recibidas',
                'Q1' => 'Papeletas Deterioradas',
                'R1' => 'Votos Válidos Alcalde',
                'S1' => 'Votos Blancos Alcalde',
                'T1' => 'Votos Nulos Alcalde',
                'U1' => 'Votos Válidos Concejal',
                'V1' => 'Votos Blancos Concejal',
                'W1' => 'Votos Nulos Concejal',
                'X1' => 'Total Votantes',
                'Y1' => 'Papeletas Usadas',
                'Z1' => 'Papeletas Sobrantes',
                'AA1' => 'Presidente',
                'AB1' => 'Secretario',
                'AC1' => 'Vocal 1',
                'AD1' => 'Vocal 2',
                'AE1' => 'Vocal 3',
                'AF1' => 'Vocal 4',
                'AG1' => 'Hora Apertura',
                'AH1' => 'Hora Cierre',
                'AI1' => 'Fecha Elección',
                'AJ1' => 'N° Acta',
                'AK1' => 'Fecha Subida Acta',
                'AL1' => 'Estado',
                'AM1' => 'Observaciones'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Style headers
            $headerRange = 'A1:AM1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($headerRange)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 2;
            foreach ($votingTables as $table) {
                $ballotsUsed = $table->total_voters;
                $ballotsLeftover = $table->ballots_received - $ballotsUsed - $table->ballots_spoiled;

                $sheet->setCellValue('A' . $row, $table->oep_code ?? '');
                $sheet->setCellValue('B' . $row, $table->internal_code ?? '');
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
                $sheet->setCellValue('M' . $row, $table->voter_range_start_name ?? '');
                $sheet->setCellValue('N' . $row, $table->voter_range_end_name ?? '');
                $sheet->setCellValue('O' . $row, $table->expected_voters ?? 0);
                $sheet->setCellValue('P' . $row, $table->ballots_received ?? 0);
                $sheet->setCellValue('Q' . $row, $table->ballots_spoiled ?? 0);
                $sheet->setCellValue('R' . $row, $table->valid_votes ?? 0);
                $sheet->setCellValue('S' . $row, $table->blank_votes ?? 0);
                $sheet->setCellValue('T' . $row, $table->null_votes ?? 0);
                $sheet->setCellValue('U' . $row, $table->valid_votes_second ?? 0);
                $sheet->setCellValue('V' . $row, $table->blank_votes_second ?? 0);
                $sheet->setCellValue('W' . $row, $table->null_votes_second ?? 0);
                $sheet->setCellValue('X' . $row, $table->total_voters ?? 0);
                $sheet->setCellValue('Y' . $row, $ballotsUsed);
                $sheet->setCellValue('Z' . $row, max(0, $ballotsLeftover));
                $sheet->setCellValue('AA' . $row, $table->president ? $table->president->name . ' ' . ($table->president->last_name ?? '') : '');
                $sheet->setCellValue('AB' . $row, $table->secretary ? $table->secretary->name . ' ' . ($table->secretary->last_name ?? '') : '');
                $sheet->setCellValue('AC' . $row, $table->vocal1 ? $table->vocal1->name . ' ' . ($table->vocal1->last_name ?? '') : '');
                $sheet->setCellValue('AD' . $row, $table->vocal2 ? $table->vocal2->name . ' ' . ($table->vocal2->last_name ?? '') : '');
                $sheet->setCellValue('AE' . $row, $table->vocal3 ? $table->vocal3->name . ' ' . ($table->vocal3->last_name ?? '') : '');
                $sheet->setCellValue('AF' . $row, $table->vocal4 ? $table->vocal4->name . ' ' . ($table->vocal4->last_name ?? '') : '');
                $sheet->setCellValue('AG' . $row, $table->opening_time ? \Carbon\Carbon::parse($table->opening_time)->format('H:i') : '');
                $sheet->setCellValue('AH' . $row, $table->closing_time ? \Carbon\Carbon::parse($table->closing_time)->format('H:i') : '');
                $sheet->setCellValue('AI' . $row, $table->election_date ? \Carbon\Carbon::parse($table->election_date)->format('d/m/Y') : '');
                $sheet->setCellValue('AJ' . $row, $table->acta_number ?? '');
                $sheet->setCellValue('AK' . $row, $table->acta_uploaded_at ? \Carbon\Carbon::parse($table->acta_uploaded_at)->format('d/m/Y H:i') : '');
                $sheet->setCellValue('AL' . $row, $this->getStatusLabel($table->status));
                $sheet->setCellValue('AM' . $row, $table->observations ?? '');

                $row++;
            }

            // Auto-size columns
            $columns = range('A', 'M'); // A-M son 13 columnas
            $columns = array_merge($columns, ['N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM']);

            foreach ($columns as $column) {
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
                'A1' => 'Código OEP',
                'B1' => 'Código Interno',
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
                'M1' => 'Rango Desde (Apellido)',
                'N1' => 'Rango Hasta (Apellido)',
                'O1' => 'Votantes Esperados',
                'P1' => 'Papeletas Recibidas',
                'Q1' => 'Papeletas Deterioradas',
                'R1' => 'Votos Válidos Alcalde',
                'S1' => 'Votos Blancos Alcalde',
                'T1' => 'Votos Nulos Alcalde',
                'U1' => 'Votos Válidos Concejal',
                'V1' => 'Votos Blancos Concejal',
                'W1' => 'Votos Nulos Concejal',
                'X1' => 'Presidente',
                'Y1' => 'Secretario',
                'Z1' => 'Vocal 1',
                'AA1' => 'Vocal 2',
                'AB1' => 'Vocal 3',
                'AC1' => 'Vocal 4',
                'AD1' => 'Hora Apertura',
                'AE1' => 'Hora Cierre',
                'AF1' => 'Fecha Elección',
                'AG1' => 'N° Acta',
                'AH1' => 'Estado'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            $headerRange = 'A1:AH1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E3F2FD');

            // Sample data actualizado
            $sampleData = [
                ['303182-1', 'REC-QUI-001-M01', '1', 'A', 'mixta', 'UNIDAD EDUCATIVA ADELA ZAMUDIO', 'REC-QUI-001', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Quillacollo (Urbano)', 'Elecciones Municipales 2026', 'ACOSTA', 'ZEBALLOS', '350', '350', '0', '305', '5', '10', '300', '4', '6', 'Juan Pérez', 'María Gómez', 'Carlos López', 'Ana Silva', 'Luis Torres', '08:00', '17:00', '22/03/2026', 'ACTA-001', 'configurada'],
                ['303182-2', 'REC-QUI-002-M02', '2', 'B', 'mixta', 'COLEGIO NACIONAL QUILLACOLLO', 'REC-QUI-002', 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Centro', 'Elecciones Municipales 2026', 'FLORES', 'PEREZ', '280', '280', '0', '240', '4', '6', '235', '5', '5', 'Pedro Rodríguez', 'Laura Fernández', 'Diego Castro', 'Sofía Méndez', 'Javier Ruiz', '08:15', '17:00', '22/03/2026', 'ACTA-002', 'configurada'],
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

            $columns = range('A', 'H'); // A-H
            $columns = array_merge($columns, ['I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH']);

            foreach ($columns as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $instructionRow = $row + 2;
            $sheet->setCellValue('A' . $instructionRow, 'INSTRUCCIONES:');
            $sheet->getStyle('A' . $instructionRow)->getFont()->setBold(true);

            $instructions = [
                'El código OEP y código Interno se generan automáticamente si se dejan vacíos',
                'Los campos N° Mesa, Recinto, Tipo Elección y Estado son obligatorios',
                'Para el campo Tipo use: mixta, masculina o femenina',
                'Para el campo Estado use: ' . implode(', ', array_keys(VotingTable::getStatuses())),
                'Los presidentes, secretarios y vocales deben ser emails o nombres de usuarios existentes',
                'Las fechas deben tener formato dd/mm/aaaa',
                'Las horas deben tener formato HH:MM (24h)',
                'Los votos de Alcalde y Concejal se registran por separado',
                'El total de votantes debe ser igual en ambas categorías',
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
        $statuses = VotingTable::getStatuses();
        return $statuses[$status] ?? $status;
    }
}
