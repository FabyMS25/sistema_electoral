<?php
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
            Log::info('Starting import process', ['file' => $uploadedFile->getClientOriginalName()]);

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

        // Column indices (0-based from Excel)
        $colOepCode = 0;           // A - Código OEP
        $colInternalCode = 1;       // B - Código Interno
        $colNumber = 2;             // C - N° Mesa
        $colLetter = 3;             // D - Letra
        $colType = 4;               // E - Tipo
        $colInstitutionName = 5;    // F - Recinto
        $colInstitutionCode = 6;    // G - Código Recinto
        $colElectionType = 11;      // L - Tipo Elección
        $colFromName = 12;          // M - Rango Desde Apellido
        $colToName = 13;            // N - Rango Hasta Apellido
        $colExpectedVoters = 16;     // Q - Votantes Esperados
        $colBallotsReceived = 17;    // R - Papeletas Recibidas
        $colBallotsSpoiled = 18;     // S - Papeletas Deterioradas
        $colValidVotes = 19;         // T - Votos Válidos Alcalde
        $colBlankVotes = 20;         // U - Votos Blancos Alcalde
        $colNullVotes = 21;          // V - Votos Nulos Alcalde
        $colValidVotesSecond = 22;   // W - Votos Válidos Concejal
        $colBlankVotesSecond = 23;   // X - Votos Blancos Concejal
        $colNullVotesSecond = 24;    // Y - Votos Nulos Concejal
        $colPresident = 25;          // Z - Presidente
        $colSecretary = 26;          // AA - Secretario
        $colVocal1 = 27;             // AB - Vocal 1
        $colVocal2 = 28;             // AC - Vocal 2
        $colVocal3 = 29;             // AD - Vocal 3
        $colOpenTime = 30;           // AE - Hora Apertura
        $colCloseTime = 31;          // AF - Hora Cierre
        $colElectionDate = 32;       // AG - Fecha Elección
        $colActaNumber = 33;         // AH - N° Acta
        $colStatus = 34;             // AI - Estado

        // Validar campos requeridos
        if (empty(trim($row[$colNumber] ?? ''))) {
            throw new \Exception("El número de mesa es requerido");
        }

        // Buscar institución
        $institution = $this->findInstitution($row, $rowNumber);
        if (!$institution) {
            throw new \Exception("Recinto no encontrado");
        }

        // Buscar tipo de elección
        $electionType = $this->findElectionType($row, $rowNumber);
        if (!$electionType) {
            throw new \Exception("No se pudo determinar el tipo de elección");
        }

        $this->processTableData($row, $rowNumber, $institution, $electionType);
    }

    private function findInstitution($row, $rowNumber)
    {
        $colInstitutionCode = 6; // G - Código Recinto
        $colInstitutionName = 5; // F - Recinto

        if (!empty($row[$colInstitutionCode])) {
            $institution = Institution::where('code', trim($row[$colInstitutionCode]))->first();
            if ($institution) return $institution;
        }

        if (!empty($row[$colInstitutionName])) {
            $name = trim($row[$colInstitutionName]);
            $institution = Institution::where('name', $name)->first();
            if ($institution) return $institution;

            $institution = Institution::where('name', 'LIKE', '%' . $name . '%')->first();
            if ($institution) {
                $this->warnings[] = "Fila {$rowNumber}: Se usó coincidencia parcial para el recinto: {$institution->name}";
                return $institution;
            }
        }

        return null;
    }

    private function findElectionType($row, $rowNumber)
    {
        $colElectionType = 11; // L - Tipo Elección

        if (!empty($row[$colElectionType])) {
            $typeName = trim($row[$colElectionType]);
            $electionType = ElectionType::where('name', 'LIKE', '%' . $typeName . '%')
                ->where('active', true)
                ->first();

            if ($electionType) return $electionType;
        }

        $defaultType = ElectionType::where('active', true)->first();
        if ($defaultType) {
            $this->warnings[] = "Fila {$rowNumber}: No se encontró el tipo de elección, se usará: {$defaultType->name}";
            return $defaultType;
        }

        return null;
    }

    private function processTableData($row, $rowNumber, $institution, $electionType)
    {
        // Column indices
        $colOepCode = 0;
        $colInternalCode = 1;
        $colNumber = 2;
        $colLetter = 3;
        $colType = 4;
        $colFromName = 12;
        $colToName = 13;
        $colExpectedVoters = 16;
        $colBallotsReceived = 17;
        $colBallotsSpoiled = 18;
        $colValidVotes = 19;
        $colBlankVotes = 20;
        $colNullVotes = 21;
        $colValidVotesSecond = 22;
        $colBlankVotesSecond = 23;
        $colNullVotesSecond = 24;
        $colPresident = 25;
        $colSecretary = 26;
        $colVocal1 = 27;
        $colVocal2 = 28;
        $colVocal3 = 29;
        $colOpenTime = 30;
        $colCloseTime = 31;
        $colElectionDate = 32;
        $colActaNumber = 33;
        $colStatus = 34;

        // Determinar tipo
        $type = 'mixta';
        if (!empty($row[$colType])) {
            $typeValue = strtolower(trim($row[$colType]));
            if (in_array($typeValue, ['masculina', 'masculino'])) $type = 'masculina';
            elseif (in_array($typeValue, ['femenina', 'femenino'])) $type = 'femenina';
        }

        // Determinar estado
        $status = 'configurada';
        if (!empty($row[$colStatus])) {
            $statusValue = strtolower(trim($row[$colStatus]));
            $statusMap = [
                'configurada' => 'configurada',
                'en espera' => 'en_espera',
                'votacion' => 'votacion',
                'cerrada' => 'cerrada',
                'en escrutinio' => 'en_escrutinio',
                'escrutada' => 'escrutada',
                'observada' => 'observada',
                'transmitida' => 'transmitida',
                'anulada' => 'anulada',
            ];
            $status = $statusMap[$statusValue] ?? 'configurada';
        }

        $number = intval($row[$colNumber]);

        // Generar códigos si están vacíos
        $oepCode = !empty($row[$colOepCode]) ? trim($row[$colOepCode]) : $institution->code . '-' . $number;
        $internalCode = !empty($row[$colInternalCode]) ? trim($row[$colInternalCode]) : $institution->code . '-M' . str_pad($number, 2, '0', STR_PAD_LEFT);

        $existingTable = VotingTable::where('institution_id', $institution->id)
            ->where('number', $number)
            ->first();

        // Parsear fechas
        $electionDate = !empty($row[$colElectionDate]) ? $this->parseDate($row[$colElectionDate]) : null;
        $openTime = !empty($row[$colOpenTime]) ? $this->parseTime($row[$colOpenTime]) : null;
        $closeTime = !empty($row[$colCloseTime]) ? $this->parseTime($row[$colCloseTime]) : null;

        // Buscar delegados
        $president = $this->findUser($row[$colPresident] ?? null);
        $secretary = $this->findUser($row[$colSecretary] ?? null);
        $vocal1 = $this->findUser($row[$colVocal1] ?? null);
        $vocal2 = $this->findUser($row[$colVocal2] ?? null);
        $vocal3 = $this->findUser($row[$colVocal3] ?? null);
        $vocal4 = $this->findUser($row[$colVocal4] ?? null);

        // Calcular totales
        $totalVoters = ($row[$colValidVotes] ?? 0) + ($row[$colBlankVotes] ?? 0) + ($row[$colNullVotes] ?? 0);
        $totalVotersSecond = ($row[$colValidVotesSecond] ?? 0) + ($row[$colBlankVotesSecond] ?? 0) + ($row[$colNullVotesSecond] ?? 0);

        // Validar consistencia
        if ($totalVoters != $totalVotersSecond && ($totalVoters > 0 || $totalVotersSecond > 0)) {
            $this->warnings[] = "Fila {$rowNumber}: El total de votantes de Alcalde ($totalVoters) no coincide con Concejal ($totalVotersSecond)";
        }

        $tableData = [
            // CÓDIGOS - Nuevos campos
            'oep_code' => $oepCode,
            'internal_code' => $internalCode,

            // Datos básicos
            'number' => $number,
            'letter' => !empty($row[$colLetter]) ? trim($row[$colLetter]) : null,
            'type' => $type,

            // Relaciones
            'institution_id' => $institution->id,
            'election_type_id' => $electionType->id,

            // Datos pre-electorales
            'expected_voters' => !empty($row[$colExpectedVoters]) ? intval($row[$colExpectedVoters]) : 0,
            'ballots_received' => !empty($row[$colBallotsReceived]) ? intval($row[$colBallotsReceived]) : 0,
            'ballots_spoiled' => !empty($row[$colBallotsSpoiled]) ? intval($row[$colBallotsSpoiled]) : 0,

            // Rango de votantes
            'voter_range_start_name' => !empty($row[$colFromName]) ? trim($row[$colFromName]) : null,
            'voter_range_end_name' => !empty($row[$colToName]) ? trim($row[$colToName]) : null,

            // Personal de mesa
            'president_id' => $president?->id,
            'secretary_id' => $secretary?->id,
            'vocal1_id' => $vocal1?->id,
            'vocal2_id' => $vocal2?->id,
            'vocal3_id' => $vocal3?->id,
            'vocal4_id' => $vocal4?->id,

            // Fechas y horas
            'election_date' => $electionDate,
            'opening_time' => $openTime,
            'closing_time' => $closeTime,

            // Estado
            'status' => $status,

            // Control de papeletas (se calculan automáticamente)
            'ballots_used' => 0,
            'ballots_leftover' => 0,

            // Resultados Alcalde
            'valid_votes' => !empty($row[$colValidVotes]) ? intval($row[$colValidVotes]) : 0,
            'blank_votes' => !empty($row[$colBlankVotes]) ? intval($row[$colBlankVotes]) : 0,
            'null_votes' => !empty($row[$colNullVotes]) ? intval($row[$colNullVotes]) : 0,

            // Resultados Concejal
            'valid_votes_second' => !empty($row[$colValidVotesSecond]) ? intval($row[$colValidVotesSecond]) : 0,
            'blank_votes_second' => !empty($row[$colBlankVotesSecond]) ? intval($row[$colBlankVotesSecond]) : 0,
            'null_votes_second' => !empty($row[$colNullVotesSecond]) ? intval($row[$colNullVotesSecond]) : 0,

            // Totales
            'total_voters' => $totalVoters,
            'total_voters_second' => $totalVotersSecond,

            // Acta
            'acta_number' => !empty($row[$colActaNumber]) ? trim($row[$colActaNumber]) : null,
            'acta_photo' => null,
            'acta_uploaded_at' => null,
            'observations' => null,
        ];

        if ($existingTable) {
            $existingTable->update($tableData);
            Log::info("Updated table ID: {$existingTable->id}");
        } else {
            // Verificar que los códigos no existan
            $existingOepCode = VotingTable::where('oep_code', $oepCode)->first();
            if ($existingOepCode) {
                throw new \Exception("El código OEP {$oepCode} ya existe en otra mesa");
            }

            $existingInternalCode = VotingTable::where('internal_code', $internalCode)->first();
            if ($existingInternalCode) {
                throw new \Exception("El código interno {$internalCode} ya existe en otra mesa");
            }

            VotingTable::create($tableData);
        }

        $this->successCount++;
    }

    private function findUser($value)
    {
        if (empty($value)) return null;

        $value = trim($value);

        $user = User::where('email', $value)->first();
        if ($user) return $user;

        $user = User::where('id_card', $value)->first();
        if ($user) return $user;

        $nameParts = explode(' ', $value);
        if (count($nameParts) >= 2) {
            $user = User::where('name', 'LIKE', '%' . $nameParts[0] . '%')
                ->where('last_name', 'LIKE', '%' . implode(' ', array_slice($nameParts, 1)) . '%')
                ->first();
            if ($user) return $user;
        }

        return null;
    }

    private function parseTime($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $timestamp->format('H:i:s');
            }
            return \Carbon\Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $timestamp->format('Y-m-d');
            }

            $formats = ['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y'];
            foreach ($formats as $format) {
                try {
                    $date = \Carbon\Carbon::createFromFormat($format, $value);
                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getSuccessCount() { return $this->successCount; }
    public function getErrors() { return $this->errors; }
    public function getWarnings() { return $this->warnings; }
    public function hasErrors() { return !empty($this->errors); }
}
