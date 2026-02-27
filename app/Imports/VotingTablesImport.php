<?php
// app/Imports/VotingTablesImport.php

namespace App\Imports;

use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VotingTablesImport
{
    private $errors = [];
    private $successCount = 0;
    private $warnings = [];

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
        // Column indices (0-based from Excel)
        $colCode = 0;           // A - Código Mesa
        $colCodeIne = 1;        // B - Código INE
        $colNumber = 2;         // C - N° Mesa
        $colLetter = 3;         // D - Letra
        $colType = 4;           // E - Tipo
        $colInstitutionName = 5; // F - Recinto
        $colInstitutionCode = 6; // G - Código Recinto
        $colElectionType = 11;  // L - Tipo Elección
        $colFromName = 12;      // M - Desde Apellido
        $colToName = 13;        // N - Hasta Apellido
        $colFromNumber = 14;    // O - Desde C.I.
        $colToNumber = 15;      // P - Hasta C.I.
        $colRegistered = 16;    // Q - Ciudadanos Habilitados
        $colVoted = 17;         // R - Votaron
        $colBlankVotes = 19;    // T - Votos Blanco
        $colNullVotes = 20;     // U - Votos Nulos
        $colComputed = 21;      // V - Papeletas Computadas
        $colAnnulled = 22;      // W - Papeletas Anuladas
        $colEnabled = 23;       // X - Papeletas Habilitadas
        $colPresident = 24;     // Y - Presidente
        $colSecretary = 25;     // Z - Secretario
        $colVocal1 = 26;        // AA - Vocal 1
        $colVocal2 = 27;        // AB - Vocal 2
        $colVocal3 = 28;        // AC - Vocal 3
        $colVocal4 = 29;        // AD - Vocal 4
        $colOpenTime = 30;      // AE - Hora Apertura
        $colCloseTime = 31;     // AF - Hora Cierre
        $colElectionDate = 32;  // AG - Fecha Elección
        $colActaNumber = 33;    // AH - N° Acta
        $colStatus = 34;        // AI - Estado

        // Validar campos requeridos
        if (empty($row[$colNumber])) {
            throw new \Exception("El número de mesa es requerido");
        }

        // Buscar institución
        $institution = null;
        if (!empty($row[$colInstitutionCode])) {
            $institution = Institution::where('code', trim($row[$colInstitutionCode]))->first();
        }
        if (!$institution && !empty($row[$colInstitutionName])) {
            $institution = Institution::where('name', 'ilike', '%' . trim($row[$colInstitutionName]) . '%')->first();
        }
        if (!$institution) {
            throw new \Exception("Recinto no encontrado: " . ($row[$colInstitutionName] ?? 'vacío'));
        }

        // Buscar tipo de elección
        $electionType = null;
        if (!empty($row[$colElectionType])) {
            $electionType = ElectionType::where('name', 'ilike', '%' . trim($row[$colElectionType]) . '%')
                ->where('active', true)
                ->first();
        }
        if (!$electionType) {
            $electionType = ElectionType::where('active', true)->first();
            if (!$electionType) {
                throw new \Exception("No hay un tipo de elección activo en el sistema");
            }
            $this->warnings[] = "Fila {$rowNumber}: No se encontró el tipo de elección, se usará el activo por defecto.";
        }

        // Buscar delegados
        $president = $this->findUser($row[$colPresident] ?? null);
        $secretary = $this->findUser($row[$colSecretary] ?? null);
        $vocal1 = $this->findUser($row[$colVocal1] ?? null);
        $vocal2 = $this->findUser($row[$colVocal2] ?? null);
        $vocal3 = $this->findUser($row[$colVocal3] ?? null);
        $vocal4 = $this->findUser($row[$colVocal4] ?? null);

        // Determinar tipo
        $type = 'mixta';
        if (!empty($row[$colType])) {
            $type = match(strtolower(trim($row[$colType]))) {
                'masculina', 'masculino' => 'masculina',
                'femenina', 'femenino' => 'femenina',
                default => 'mixta',
            };
        }

        // Determinar estado
        $status = 'pendiente';
        if (!empty($row[$colStatus])) {
            $status = match(strtolower(trim($row[$colStatus]))) {
                'en proceso', 'en_proceso' => 'en_proceso',
                'cerrado', 'cerrada' => 'cerrado',
                'en cómputo', 'en_computo' => 'en_computo',
                'computado', 'computada' => 'computado',
                'observado', 'observada' => 'observado',
                'anulado', 'anulada' => 'anulado',
                default => 'pendiente',
            };
        }

        // Generar o usar código
        $code = !empty($row[$colCode]) ? trim($row[$colCode]) : $this->generateTableCode($institution, $row[$colNumber]);

        // Verificar si la mesa ya existe
        $existingTable = VotingTable::where('institution_id', $institution->id)
            ->where('number', intval($row[$colNumber]))
            ->first();

        // Parsear fechas y horas
        $electionDate = !empty($row[$colElectionDate]) ? $this->parseDate($row[$colElectionDate]) : null;
        $openTime = !empty($row[$colOpenTime]) ? $this->parseTime($row[$colOpenTime]) : null;
        $closeTime = !empty($row[$colCloseTime]) ? $this->parseTime($row[$colCloseTime]) : null;

        $tableData = [
            'code' => $code,
            'code_ine' => !empty($row[$colCodeIne]) ? trim($row[$colCodeIne]) : null,
            'number' => intval($row[$colNumber]),
            'letter' => !empty($row[$colLetter]) ? trim($row[$colLetter]) : null,
            'type' => $type,
            'institution_id' => $institution->id,
            'election_type_id' => $electionType->id,
            'from_name' => !empty($row[$colFromName]) ? trim($row[$colFromName]) : null,
            'to_name' => !empty($row[$colToName]) ? trim($row[$colToName]) : null,
            'from_number' => !empty($row[$colFromNumber]) ? intval($row[$colFromNumber]) : null,
            'to_number' => !empty($row[$colToNumber]) ? intval($row[$colToNumber]) : null,
            'registered_citizens' => !empty($row[$colRegistered]) ? intval($row[$colRegistered]) : 0,
            'voted_citizens' => !empty($row[$colVoted]) ? intval($row[$colVoted]) : 0,
            'blank_votes' => !empty($row[$colBlankVotes]) ? intval($row[$colBlankVotes]) : 0,
            'null_votes' => !empty($row[$colNullVotes]) ? intval($row[$colNullVotes]) : 0,
            'computed_records' => !empty($row[$colComputed]) ? intval($row[$colComputed]) : 0,
            'annulled_records' => !empty($row[$colAnnulled]) ? intval($row[$colAnnulled]) : 0,
            'enabled_records' => !empty($row[$colEnabled]) ? intval($row[$colEnabled]) : 0,
            'president_id' => $president?->id,
            'secretary_id' => $secretary?->id,
            'vocal1_id' => $vocal1?->id,
            'vocal2_id' => $vocal2?->id,
            'vocal3_id' => $vocal3?->id,
            'vocal4_id' => $vocal4?->id,
            'opening_time' => $openTime,
            'closing_time' => $closeTime,
            'election_date' => $electionDate,
            'acta_number' => !empty($row[$colActaNumber]) ? trim($row[$colActaNumber]) : null,
            'status' => $status,
        ];

        if ($existingTable) {
            $existingTable->update($tableData);
        } else {
            // Verificar que el código no exista
            $existingCode = VotingTable::where('code', $code)->first();
            if ($existingCode) {
                throw new \Exception("El código {$code} ya existe en otra mesa");
            }
            VotingTable::create($tableData);
        }

        $this->successCount++;
    }

    private function findUser($value)
    {
        if (empty($value)) return null;

        // Buscar por email
        $user = User::where('email', 'ilike', trim($value))->first();
        if ($user) return $user;

        // Buscar por CI
        $user = User::where('id_card', trim($value))->first();
        if ($user) return $user;

        // Buscar por nombre completo
        $nameParts = explode(' ', trim($value));
        if (count($nameParts) >= 2) {
            $firstName = $nameParts[0];
            $lastName = implode(' ', array_slice($nameParts, 1));

            $user = User::where('name', 'ilike', '%' . $firstName . '%')
                ->where('last_name', 'ilike', '%' . $lastName . '%')
                ->first();
            if ($user) return $user;
        }

        return null;
    }

    private function parseTime($value)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            // Excel serial time
            $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $timestamp->format('H:i:s');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        if (is_numeric($value)) {
            $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            return $timestamp->format('Y-m-d');
        }

        try {
            // Intentar varios formatos
            if (strpos($value, '/') !== false) {
                $parts = explode('/', $value);
                if (count($parts) === 3) {
                    // Asumir formato d/m/Y
                    return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                }
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function generateTableCode($institution, $number)
    {
        $prefix = $institution->code . '-M';
        $code = $prefix . str_pad($number, 2, '0', STR_PAD_LEFT);

        $counter = 1;
        while (VotingTable::where('code', $code)->exists()) {
            $code = $prefix . str_pad($number, 2, '0', STR_PAD_LEFT) . '-' . $counter;
            $counter++;
        }

        return $code;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getWarnings()
    {
        return $this->warnings;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }
}