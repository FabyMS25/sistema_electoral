<?php

namespace App\Exports;

use App\Models\VotingTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VotingTablesExport
{
    // ─── Full export ──────────────────────────────────────────────────────────

    public function export(array $filters = []): string
    {
        try {
            $query = VotingTable::with([
                'institution.locality.municipality.province.department',
                'elections.electionType',   // voting_table_elections → election_types
                'president', 'secretary',
                'vocal1', 'vocal2', 'vocal3', 'vocal4',
            ]);

            if (!empty($filters['selected_ids'])) {
                $query->whereIn('id', $filters['selected_ids']);
            }
            if (!empty($filters['institution_id'])) {
                $query->where('institution_id', $filters['institution_id']);
            }
            if (!empty($filters['status'])) {
                $query->whereHas('elections', fn($q) => $q->where('status', $filters['status']));
            }
            if (!empty($filters['election_type_id'])) {
                $query->whereHas('elections', fn($q) => $q->where('election_type_id', $filters['election_type_id']));
            }

            $tables = $query->orderBy('institution_id')->orderBy('number')->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Mesas de Votación');

            // Headers — matches the import template column names
            $headers = [
                'A'  => 'Código OEP',
                'B'  => 'Código Interno',
                'C'  => 'N° Mesa',
                'D'  => 'Letra',
                'E'  => 'Tipo',
                'F'  => 'Recinto',
                'G'  => 'Código Recinto',
                'H'  => 'Departamento',
                'I'  => 'Provincia',
                'J'  => 'Municipio',
                'K'  => 'Localidad',
                'L'  => 'Rango Desde (Apellido)',
                'M'  => 'Rango Hasta (Apellido)',
                'N'  => 'Votantes Esperados',
                'O'  => 'Presidente',
                'P'  => 'Secretario',
                'Q'  => 'Vocal 1',
                'R'  => 'Vocal 2',
                'S'  => 'Vocal 3',
                'T'  => 'Vocal 4',
                'U'  => 'Observaciones',
                // Election-level (from voting_table_elections — latest row)
                'V'  => 'Tipo Elección (último)',
                'W'  => 'Estado (último)',
                'X'  => 'Total Votantes (último)',
                'Y'  => 'Papeletas Recibidas (último)',
                'Z'  => 'Papeletas Usadas (último)',
                'AA' => 'Papeletas Sobrantes (último)',
                'AB' => 'Papeletas Deterioradas (último)',
                'AC' => 'Hora Apertura (último)',
                'AD' => 'Hora Cierre (último)',
                'AE' => 'Fecha Elección (último)',
            ];

            foreach ($headers as $col => $label) {
                $sheet->setCellValue("{$col}1", $label);
            }

            $lastCol = array_key_last($headers);
            $hRange  = "A1:{$lastCol}1";
            $sheet->getStyle($hRange)->getFont()->setBold(true);
            $sheet->getStyle($hRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
            $sheet->getStyle($hRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row = 2;
            foreach ($tables as $table) {
                $latest = $table->elections->sortByDesc('updated_at')->first();

                $sheet->setCellValue('A'  . $row, $table->oep_code      ?? '');
                $sheet->setCellValue('B'  . $row, $table->internal_code ?? '');
                $sheet->setCellValue('C'  . $row, $table->number);
                $sheet->setCellValue('D'  . $row, $table->letter ?? '');
                $sheet->setCellValue('E'  . $row, $table->getTypeLabel());
                $sheet->setCellValue('F'  . $row, $table->institution->name ?? '');
                $sheet->setCellValue('G'  . $row, $table->institution->code ?? '');
                $sheet->setCellValue('H'  . $row, $table->institution->department->name ?? '');
                $sheet->setCellValue('I'  . $row, $table->institution->province->name   ?? '');
                $sheet->setCellValue('J'  . $row, $table->institution->municipality->name ?? '');
                $sheet->setCellValue('K'  . $row, $table->institution->locality->name   ?? '');
                $sheet->setCellValue('L'  . $row, $table->voter_range_start_name ?? '');
                $sheet->setCellValue('M'  . $row, $table->voter_range_end_name   ?? '');
                $sheet->setCellValue('N'  . $row, $table->expected_voters ?? 0);
                $sheet->setCellValue('O'  . $row, $this->delegateName($table->president));
                $sheet->setCellValue('P'  . $row, $this->delegateName($table->secretary));
                $sheet->setCellValue('Q'  . $row, $this->delegateName($table->vocal1));
                $sheet->setCellValue('R'  . $row, $this->delegateName($table->vocal2));
                $sheet->setCellValue('S'  . $row, $this->delegateName($table->vocal3));
                $sheet->setCellValue('T'  . $row, $this->delegateName($table->vocal4));
                $sheet->setCellValue('U'  . $row, $table->observations ?? '');
                // Election-level (from latest VotingTableElection)
                $sheet->setCellValue('V'  . $row, $latest?->electionType?->name ?? '');
                $sheet->setCellValue('W'  . $row, $this->statusLabel($latest?->status));
                $sheet->setCellValue('X'  . $row, $latest?->total_voters      ?? 0);
                $sheet->setCellValue('Y'  . $row, $latest?->ballots_received  ?? 0);
                $sheet->setCellValue('Z'  . $row, $latest?->ballots_used      ?? 0);
                $sheet->setCellValue('AA' . $row, $latest?->ballots_leftover  ?? 0);
                $sheet->setCellValue('AB' . $row, $latest?->ballots_spoiled   ?? 0);
                $sheet->setCellValue('AC' . $row, $latest?->opening_time ? \Carbon\Carbon::parse($latest->opening_time)->format('H:i') : '');
                $sheet->setCellValue('AD' . $row, $latest?->closing_time ? \Carbon\Carbon::parse($latest->closing_time)->format('H:i') : '');
                $sheet->setCellValue('AE' . $row, $latest?->election_date ? \Carbon\Carbon::parse($latest->election_date)->format('d/m/Y') : '');

                $row++;
            }

            foreach (array_keys($headers) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $fileName = 'mesas_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
            $filePath = "exports/{$fileName}";
            Storage::makeDirectory('exports');
            (new Xlsx($spreadsheet))->save(storage_path("app/{$filePath}"));
            return $filePath;
        } catch (\Exception $e) {
            Log::error('VotingTablesExport::export error: ' . $e->getMessage());
            throw new \Exception('Error al generar el archivo de exportación: ' . $e->getMessage());
        }
    }

    // ─── Import template ──────────────────────────────────────────────────────

    public function downloadTemplate(): string
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Plantilla Mesas');

            $headers = [
                'A'  => 'Código OEP',
                'B'  => 'Código Interno',
                'C'  => 'N° Mesa',
                'D'  => 'Letra',
                'E'  => 'Tipo',
                'F'  => 'Recinto',
                'G'  => 'Código Recinto',
                'H'  => 'Departamento',
                'I'  => 'Provincia',
                'J'  => 'Municipio',
                'K'  => 'Localidad',
                'L'  => 'Rango Desde (Apellido)',
                'M'  => 'Rango Hasta (Apellido)',
                'N'  => 'Votantes Esperados',
                'O'  => 'Presidente',
                'P'  => 'Secretario',
                'Q'  => 'Vocal 1',
                'R'  => 'Vocal 2',
                'S'  => 'Vocal 3',
                'T'  => 'Vocal 4',
                'U'  => 'Papeletas Recibidas',
                'V'  => 'Papeletas Deterioradas',
                'W'  => 'Total Votantes',
                'X'  => 'Estado',
                'Y'  => 'Hora Apertura',
                'Z'  => 'Hora Cierre',
                'AA' => 'Fecha Elección',
                'AB' => 'Observaciones',
            ];

            foreach ($headers as $col => $label) {
                $sheet->setCellValue("{$col}1", $label);
            }

            $lastCol = array_key_last($headers);
            $hRange  = "A1:{$lastCol}1";
            $sheet->getStyle($hRange)->getFont()->setBold(true);
            $sheet->getStyle($hRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');

            // Sample rows
            $samples = [
                ['', '', '1', 'A', 'mixta', 'UNIDAD EDUCATIVA ADELA ZAMUDIO', 'REC-QUI-001',
                 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Quillacollo Urbano',
                 'ACOSTA', 'ZEBALLOS', '350',
                 'juan.perez@oep.bo', 'maria.gomez@oep.bo', '', '', '', '',
                 '350', '0', '0', 'configurada', '08:00', '17:00', '22/03/2026', ''],
                ['', '', '2', '', 'mixta', 'COLEGIO NACIONAL QUILLACOLLO', 'REC-QUI-002',
                 'Cochabamba', 'Quillacollo', 'Quillacollo', 'Centro',
                 'FLORES', 'PEREZ', '280',
                 '', '', '', '', '', '',
                 '280', '0', '0', 'configurada', '08:00', '17:00', '22/03/2026', ''],
            ];

            $r = 2;
            foreach ($samples as $sample) {
                $colKeys = array_keys($headers);
                foreach ($sample as $i => $val) {
                    if (isset($colKeys[$i])) {
                        $sheet->setCellValue($colKeys[$i] . $r, $val);
                    }
                }
                $r++;
            }

            // Instructions
            $instrRow = $r + 2;
            $sheet->setCellValue('A' . $instrRow, 'INSTRUCCIONES:');
            $sheet->getStyle('A' . $instrRow)->getFont()->setBold(true);

            $instructions = [
                'N° Mesa y Recinto (nombre o código) son obligatorios.',
                'Código OEP y Código Interno se generan automáticamente si se dejan vacíos.',
                'Tipo: mixta | masculina | femenina.',
                'Estado: configurada | en espera | votacion | cerrada | en escrutinio | escrutada | observada | transmitida | anulada.',
                'La mesa quedará habilitada para TODOS los tipos de elección activos en el sistema.',
                'Papeletas/estado/hora se aplican a todos los tipos de elección activos.',
                'Delegados: ingrese el correo electrónico del usuario registrado.',
                'Departamento, Provincia, Municipio, Localidad son solo informativos (no se importan).',
                'Elimine estas filas de ejemplo antes de importar sus datos reales.',
            ];

            foreach ($instructions as $i => $instr) {
                $sheet->setCellValue('A' . ($instrRow + $i + 1), '• ' . $instr);
            }

            foreach (array_keys($headers) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            Storage::makeDirectory('templates');
            $filePath = 'templates/plantilla_mesas.xlsx';
            (new Xlsx($spreadsheet))->save(storage_path("app/{$filePath}"));
            return $filePath;
        } catch (\Exception $e) {
            Log::error('VotingTablesExport::downloadTemplate error: ' . $e->getMessage());
            throw new \Exception('Error al generar la plantilla: ' . $e->getMessage());
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function statusLabel(?string $status): string
    {
        return \App\Http\Controllers\VotingTableController::statusOptions()[$status] ?? (string) $status;
    }

    private function delegateName(?object $user): string
    {
        if (!$user) return '';
        return trim($user->name . ' ' . ($user->last_name ?? ''));
    }
}
