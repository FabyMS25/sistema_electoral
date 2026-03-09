<?php

namespace App\Imports;

use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use App\Models\VotingTableElection;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Two-pass import for VotingTable.
 *
 * Schema facts (from migration):
 *   voting_tables        → identity, institution, padron, delegates
 *   voting_table_elections → one row per (table × election_type): ballots, status, times
 *
 * A table is NOT tied to a single election type in voting_tables.
 * On import we auto-create a VotingTableElection for every active ElectionType,
 * using any ballots/status data from the spreadsheet columns if present.
 *
 * Columns resolved by header name (accent/case insensitive) so column order never matters.
 */
class VotingTablesImport
{
    private array $errors       = [];
    private array $warnings     = [];
    private int   $successCount = 0;

    // Pre-loaded lookup maps
    private array $institutionsByCode  = [];
    private array $institutionsByName  = [];
    private array $activeElectionTypes = []; // all active election types
    private array $usersByEmail        = [];
    private array $usersByIdCard       = [];

    // ─── Entry point ─────────────────────────────────────────────────────────

    public function import($uploadedFile): array
    {
        try {
            $filePath = $uploadedFile->store('imports');
            $fullPath = storage_path("app/{$filePath}");

            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows  = $sheet->toArray(null, true, true, false);

            Storage::delete($filePath);

            if (count($rows) < 2) {
                return $this->fail('El archivo está vacío o no tiene datos válidos.');
            }

            $colMap = $this->buildColumnMap($rows[0]);
            if (empty($colMap)) {
                return $this->fail('No se reconocieron los encabezados. Descargue la plantilla oficial.');
            }

            $this->preloadLookups();

            if (empty($this->activeElectionTypes)) {
                return $this->fail('No hay tipos de elección activos en el sistema. Actívelos antes de importar.');
            }

            // ── Pass 1: validate ──────────────────────────────────────────────
            $payloads = [];
            foreach (array_slice($rows, 1) as $i => $row) {
                $rowNum = $i + 2;
                if (empty(array_filter($row))) continue;
                try {
                    $payload = $this->validateRow($row, $rowNum, $colMap);
                    if ($payload !== null) {
                        $payloads[$rowNum] = $payload;
                    }
                } catch (\RuntimeException $e) {
                    $this->errors[] = "❌ Fila {$rowNum}: " . $e->getMessage();
                }
            }

            if (empty($payloads)) {
                return ['success' => false, 'errors' => $this->errors, 'warnings' => $this->warnings, 'success_count' => 0];
            }

            // ── Pass 2: insert ────────────────────────────────────────────────
            DB::beginTransaction();
            try {
                foreach ($payloads as $rowNum => $payload) {
                    $this->insertRow($payload, $rowNum);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('VotingTablesImport insert error: ' . $e->getMessage());
                return $this->fail('Error al guardar los datos: ' . $e->getMessage());
            }

            Log::info("VotingTablesImport done. Inserted: {$this->successCount}, Errors: " . count($this->errors));

            return [
                'success'       => true,
                'errors'        => $this->errors,
                'warnings'      => $this->warnings,
                'success_count' => $this->successCount,
            ];
        } catch (\Exception $e) {
            Log::error('VotingTablesImport fatal: ' . $e->getMessage());
            return $this->fail($e->getMessage());
        }
    }

    // ─── Column map ──────────────────────────────────────────────────────────

    private function buildColumnMap(array $headerRow): array
    {
        $normalize = fn($s) => strtolower(trim(
            preg_replace('/\s+/', ' ',
                iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $s))
        ));

        $expected = [
            'codigo oep'               => 'oep_code',
            'codigo interno'           => 'internal_code',
            'n mesa'                   => 'number',
            'n° mesa'                  => 'number',
            'numero mesa'              => 'number',
            'numero de mesa'           => 'number',
            'letra'                    => 'letter',
            'tipo'                     => 'type',
            'recinto'                  => 'institution_name',
            'nombre recinto'           => 'institution_name',
            'codigo recinto'           => 'institution_code',
            'rango desde (apellido)'   => 'voter_range_start_name',
            'rango desde apellido'     => 'voter_range_start_name',
            'rango hasta (apellido)'   => 'voter_range_end_name',
            'rango hasta apellido'     => 'voter_range_end_name',
            'votantes esperados'       => 'expected_voters',
            'votantes esperados (padron)' => 'expected_voters',
            // election-level (optional — written to voting_table_elections)
            'papeletas recibidas'      => 'ballots_received',
            'papeletas deterioradas'   => 'ballots_spoiled',
            'total votantes'           => 'total_voters',
            'estado'                   => 'status',
            'hora apertura'            => 'opening_time',
            'hora cierre'              => 'closing_time',
            'fecha eleccion'           => 'election_date',
            'fecha de eleccion'        => 'election_date',
            // delegates
            'presidente'               => 'president',
            'secretario'               => 'secretary',
            'vocal 1'                  => 'vocal1',
            'vocal 2'                  => 'vocal2',
            'vocal 3'                  => 'vocal3',
            'vocal 4'                  => 'vocal4',
            'observaciones'            => 'observations',
        ];

        $map = [];
        foreach ($headerRow as $idx => $header) {
            $key = $normalize((string) $header);
            if (isset($expected[$key])) {
                $map[$expected[$key]] = $idx;
            }
        }
        return $map;
    }

    // ─── Pre-load lookups ─────────────────────────────────────────────────────

    private function preloadLookups(): void
    {
        foreach (Institution::select('id', 'code', 'name')->get() as $i) {
            $this->institutionsByCode[strtolower($i->code)] = $i;
            $this->institutionsByName[strtolower($i->name)] = $i;
        }

        foreach (ElectionType::where('active', true)->get() as $et) {
            $this->activeElectionTypes[$et->id] = $et;
        }

        foreach (User::where('is_active', true)->select('id', 'email', 'id_card', 'name', 'last_name')->get() as $u) {
            if ($u->email)   $this->usersByEmail[strtolower($u->email)]    = $u;
            if ($u->id_card) $this->usersByIdCard[strtolower($u->id_card)] = $u;
        }
    }

    // ─── Row helpers ──────────────────────────────────────────────────────────

    private function str(array $row, array $colMap, string $field): ?string
    {
        if (!isset($colMap[$field])) return null;
        $v = $row[$colMap[$field]] ?? null;
        return ($v !== null && trim((string) $v) !== '') ? trim((string) $v) : null;
    }

    private function int_(array $row, array $colMap, string $field): ?int
    {
        $v = $this->str($row, $colMap, $field);
        return ($v !== null && is_numeric($v)) ? (int) $v : null;
    }

    // ─── Pass 1 ───────────────────────────────────────────────────────────────

    private function validateRow(array $row, int $rowNum, array $colMap): ?array
    {
        // Number
        $numberRaw = $this->str($row, $colMap, 'number');
        if ($numberRaw === null || !is_numeric($numberRaw) || (int) $numberRaw < 1) {
            throw new \RuntimeException('El número de mesa es obligatorio y debe ser ≥ 1.');
        }
        $number = (int) $numberRaw;
        $letter = $this->str($row, $colMap, 'letter');

        // Institution
        $institution = null;
        $code = $this->str($row, $colMap, 'institution_code');
        $name = $this->str($row, $colMap, 'institution_name');

        if ($code) $institution = $this->institutionsByCode[strtolower($code)] ?? null;
        if (!$institution && $name) {
            $institution = $this->institutionsByName[strtolower($name)] ?? null;
            if (!$institution) {
                foreach ($this->institutionsByName as $n => $inst) {
                    if (str_contains($n, strtolower($name)) || str_contains(strtolower($name), $n)) {
                        $institution = $inst;
                        $this->warnings[] = "⏭️ Fila {$rowNum}: coincidencia parcial de recinto → {$inst->name}";
                        break;
                    }
                }
            }
        }
        if (!$institution) {
            throw new \RuntimeException('Recinto no encontrado. Use nombre o código exacto del sistema.');
        }

        // Uniqueness
        if (VotingTable::where('institution_id', $institution->id)
                       ->where('number', $number)
                       ->when($letter, fn($q) => $q->where('letter', $letter))
                       ->exists()) {
            $label = $number . ($letter ? $letter : '');
            throw new \RuntimeException("Ya existe la Mesa {$label} en '{$institution->name}'. Se omite.");
        }

        // Type
        $typeRaw = strtolower($this->str($row, $colMap, 'type') ?? 'mixta');
        $type = match(true) {
            in_array($typeRaw, ['masculina', 'masculino']) => 'masculina',
            in_array($typeRaw, ['femenina',  'femenino'])  => 'femenina',
            default                                        => 'mixta',
        };

        // Codes
        $oepCode      = $this->str($row, $colMap, 'oep_code')      ?? ($institution->code . '-' . $number . ($letter ?? ''));
        $internalCode = $this->str($row, $colMap, 'internal_code') ?? ($institution->code . '-M' . str_pad($number, 2, '0', STR_PAD_LEFT) . ($letter ?? ''));

        // Delegates
        $presidentId = $this->findUser($this->str($row, $colMap, 'president'))?->id;
        $secretaryId = $this->findUser($this->str($row, $colMap, 'secretary'))?->id;
        $vocal1Id    = $this->findUser($this->str($row, $colMap, 'vocal1'))?->id;
        $vocal2Id    = $this->findUser($this->str($row, $colMap, 'vocal2'))?->id;
        $vocal3Id    = $this->findUser($this->str($row, $colMap, 'vocal3'))?->id;
        $vocal4Id    = $this->findUser($this->str($row, $colMap, 'vocal4'))?->id;

        // Election-level optional fields (will be written to voting_table_elections)
        $statusMap = [
            'configurada'   => 'configurada',  'en espera'     => 'en_espera',
            'en_espera'     => 'en_espera',    'votacion'      => 'votacion',
            'en votacion'   => 'votacion',     'cerrada'       => 'cerrada',
            'en escrutinio' => 'en_escrutinio','en_escrutinio' => 'en_escrutinio',
            'escrutada'     => 'escrutada',    'observada'     => 'observada',
            'transmitida'   => 'transmitida',  'anulada'       => 'anulada',
        ];
        $statusRaw = strtolower($this->str($row, $colMap, 'status') ?? '');
        $status    = $statusMap[$statusRaw] ?? 'configurada';

        return [
            'institution'  => $institution,
            'tableData'    => [
                'oep_code'               => $oepCode,
                'internal_code'          => $internalCode,
                'number'                 => $number,
                'letter'                 => $letter,
                'type'                   => $type,
                'institution_id'         => $institution->id,
                'expected_voters'        => $this->int_($row, $colMap, 'expected_voters') ?? 0,
                'voter_range_start_name' => $this->str($row, $colMap, 'voter_range_start_name'),
                'voter_range_end_name'   => $this->str($row, $colMap, 'voter_range_end_name'),
                'president_id'           => $presidentId,
                'secretary_id'           => $secretaryId,
                'vocal1_id'              => $vocal1Id,
                'vocal2_id'              => $vocal2Id,
                'vocal3_id'              => $vocal3Id,
                'vocal4_id'              => $vocal4Id,
                'observations'           => $this->str($row, $colMap, 'observations'),
            ],
            'electionData' => [
                // Written to voting_table_elections — one row per active election type
                'ballots_received' => $this->int_($row, $colMap, 'ballots_received') ?? 0,
                'ballots_spoiled'  => $this->int_($row, $colMap, 'ballots_spoiled')  ?? 0,
                'total_voters'     => $this->int_($row, $colMap, 'total_voters')     ?? 0,
                'status'           => $status,
                'opening_time'     => $this->parseTime($this->str($row, $colMap, 'opening_time')),
                'closing_time'     => $this->parseTime($this->str($row, $colMap, 'closing_time')),
                'election_date'    => $this->parseDate($this->str($row, $colMap, 'election_date')),
            ],
        ];
    }

    // ─── Pass 2 ───────────────────────────────────────────────────────────────

    private function insertRow(array $payload, int $rowNum): void
    {
        $institution  = $payload['institution'];
        $tableData    = $payload['tableData'];
        $electionData = $payload['electionData'];

        $existing = VotingTable::where('institution_id', $institution->id)
            ->where('number', $tableData['number'])
            ->when($tableData['letter'], fn($q) => $q->where('letter', $tableData['letter']))
            ->first();

        if ($existing) {
            $existing->update($tableData);
            $table = $existing;
            $this->warnings[] = "⏭️ Fila {$rowNum}: Mesa ya existía → actualizada.";
        } else {
            $table = VotingTable::create($tableData);
        }

        // Create/update a VotingTableElection for EVERY active election type
        foreach ($this->activeElectionTypes as $electionType) {
            VotingTableElection::updateOrCreate(
                ['voting_table_id' => $table->id, 'election_type_id' => $electionType->id],
                array_merge([
                    'ballots_used'     => 0,
                    'ballots_leftover' => 0,
                    'election_date'    => $electionType->election_date,
                ], $electionData)
            );
        }

        $this->successCount++;
    }

    // ─── Lookup helpers ───────────────────────────────────────────────────────

    private function findUser(?string $value): ?User
    {
        if (!$value) return null;
        $lower = strtolower(trim($value));
        if (isset($this->usersByEmail[$lower]))  return $this->usersByEmail[$lower];
        if (isset($this->usersByIdCard[$lower])) return $this->usersByIdCard[$lower];
        $parts = explode(' ', $lower, 2);
        if (count($parts) === 2) {
            foreach ($this->usersByEmail as $user) {
                if (strtolower($user->name) === $parts[0] && strtolower($user->last_name ?? '') === $parts[1]) {
                    return $user;
                }
            }
        }
        return null;
    }

    private function parseTime(?string $value): ?string
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('H:i:s');
            }
            return \Carbon\Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable) { return null; }
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) return null;
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }
            foreach (['d/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y'] as $fmt) {
                try { return \Carbon\Carbon::createFromFormat($fmt, $value)->format('Y-m-d'); } catch (\Throwable) {}
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) { return null; }
    }

    private function fail(string $message): array
    {
        return ['success' => false, 'errors' => [$message], 'warnings' => [], 'success_count' => 0];
    }

    public function getSuccessCount(): int { return $this->successCount; }
    public function getErrors(): array     { return $this->errors; }
    public function getWarnings(): array   { return $this->warnings; }
    public function hasErrors(): bool      { return !empty($this->errors); }
}
