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
    private array $errors       = [];
    private array $warnings     = [];
    private int   $successCount = 0;

    // ── Pre-loaded lookup maps (populated once before the row loop) ──────────
    private array $deptLookup  = [];   // name_lower → Department
    private array $provLookup  = [];   // "dept_id|name_lower" → Province
    private array $munLookup   = [];   // "prov_id|name_lower" → Municipality
    private array $locLookup   = [];   // "mun_id|name_lower"  → Locality
    private array $distLookup  = [];   // "mun_id|name_lower"  → District
    private array $zoneLookup  = [];   // "dist_id|name_lower" → Zone

    // ── Column indices ────────────────────────────────────────────────────────
    private const COL_CODE        = 0;
    private const COL_NAME        = 1;
    private const COL_SHORT_NAME  = 2;
    private const COL_DEPARTMENT  = 3;
    private const COL_PROVINCE    = 4;
    private const COL_MUNICIPALITY= 5;
    private const COL_LOCALITY    = 6;
    private const COL_DISTRICT    = 7;
    private const COL_ZONE        = 8;
    private const COL_ADDRESS     = 9;
    private const COL_REFERENCE   = 10;
    private const COL_PHONE       = 11;
    private const COL_EMAIL       = 12;
    private const COL_RESPONSIBLE = 13;
    private const COL_CITIZENS    = 14;
    private const COL_STATUS      = 15;
    private const COL_OPERATIVE   = 16;
    private const COL_OBS         = 17;

    // ─────────────────────────────────────────────────────────────────────────
    public function import($uploadedFile): array
    {
        try {
            Log::info('Starting institutions import', [
                'file' => $uploadedFile->getClientOriginalName(),
            ]);

            $filePath = $uploadedFile->store('imports');
            $fullPath = storage_path("app/{$filePath}");

            $spreadsheet = IOFactory::load($fullPath);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray();

            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('El archivo está vacío o no tiene datos válidos.');
            }

            // Pre-load all geographic data into memory maps
            $this->buildLookupMaps();

            // ── Two-pass: validate first, then bulk insert ──
            $toImport = [];

            foreach (array_slice($rows, 1) as $index => $row) {
                $rowNum = $index + 2;

                if (empty(array_filter(array_map('strval', $row)))) {
                    continue; // blank row
                }

                try {
                    $data = $this->validateRow($row, $rowNum);
                    if ($data !== null) {
                        $toImport[] = $data;
                    }
                } catch (\Exception $e) {
                    $this->errors[] = "Fila {$rowNum}: " . $e->getMessage();
                }
            }

            // Only commit if there are no hard errors
            if (empty($this->errors) && !empty($toImport)) {
                DB::transaction(function () use ($toImport) {
                    foreach ($toImport as $data) {
                        $existing = Institution::where('name', $data['name'])->first();
                        if ($existing) {
                            $existing->update($data);
                        } else {
                            Institution::create($data);
                        }
                        $this->successCount++;
                    }
                });
            }

            Storage::delete($filePath);

            Log::info("Import finished. Success: {$this->successCount}, Errors: " . count($this->errors));

            return [
                'success'       => true,
                'errors'        => $this->errors,
                'warnings'      => $this->warnings,
                'success_count' => $this->successCount,
            ];

        } catch (\Exception $e) {
            if (isset($filePath)) {
                Storage::delete($filePath);
            }
            Log::error('Import fatal error: ' . $e->getMessage());

            return [
                'success'       => false,
                'errors'        => [$e->getMessage()],
                'warnings'      => [],
                'success_count' => 0,
            ];
        }
    }

    // ── Build in-memory lookup maps (no N+1 per row) ─────────────────────────
    private function buildLookupMaps(): void
    {
        Department::all()->each(function ($d) {
            $this->deptLookup[mb_strtolower($d->name)] = $d;
        });

        Province::all()->each(function ($p) {
            $key = $p->department_id . '|' . mb_strtolower($p->name);
            $this->provLookup[$key] = $p;
        });

        Municipality::all()->each(function ($m) {
            $key = $m->province_id . '|' . mb_strtolower($m->name);
            $this->munLookup[$key] = $m;
        });

        Locality::all()->each(function ($l) {
            $key = $l->municipality_id . '|' . mb_strtolower($l->name);
            $this->locLookup[$key] = $l;
        });

        District::all()->each(function ($d) {
            $key = $d->municipality_id . '|' . mb_strtolower($d->name);
            $this->distLookup[$key] = $d;
        });

        Zone::all()->each(function ($z) {
            $key = $z->district_id . '|' . mb_strtolower($z->name);
            $this->zoneLookup[$key] = $z;
        });
    }

    // ── Validate one row and return the data array (or null to skip) ──────────
    private function validateRow(array $row, int $rowNum): ?array
    {
        $name = trim($row[self::COL_NAME] ?? '');
        if ($name === '') {
            throw new \Exception('El nombre del recinto es requerido.');
        }

        // ── Geographic resolution ─────────────────────────────────────────────
        $deptVal = trim($row[self::COL_DEPARTMENT] ?? '');
        $provVal = trim($row[self::COL_PROVINCE]   ?? '');
        $munVal  = trim($row[self::COL_MUNICIPALITY] ?? '');
        $locVal  = trim($row[self::COL_LOCALITY]   ?? '');

        if ($deptVal === '') {
            throw new \Exception('El Departamento es requerido.');
        }

        $department = $this->deptLookup[mb_strtolower($deptVal)] ?? null;
        if (!$department) {
            throw new \Exception("Departamento no encontrado: \"{$deptVal}\"");
        }

        if ($provVal === '') {
            throw new \Exception('La Provincia es requerida.');
        }
        $province = $this->provLookup[$department->id . '|' . mb_strtolower($provVal)] ?? null;
        if (!$province) {
            throw new \Exception("Provincia no encontrada: \"{$provVal}\" en {$department->name}");
        }

        if ($munVal === '') {
            throw new \Exception('El Municipio es requerido.');
        }
        $municipality = $this->munLookup[$province->id . '|' . mb_strtolower($munVal)] ?? null;
        if (!$municipality) {
            throw new \Exception("Municipio no encontrado: \"{$munVal}\" en {$province->name}");
        }

        if ($locVal === '') {
            throw new \Exception('La Localidad es requerida.');
        }
        $locality = $this->locLookup[$municipality->id . '|' . mb_strtolower($locVal)] ?? null;
        if (!$locality) {
            throw new \Exception("Localidad no encontrada: \"{$locVal}\" en {$municipality->name}");
        }

        // ── Optional: district & zone ─────────────────────────────────────────
        $district = null;
        $distVal  = trim($row[self::COL_DISTRICT] ?? '');
        if ($distVal !== '') {
            $district = $this->distLookup[$municipality->id . '|' . mb_strtolower($distVal)] ?? null;
            if (!$district) {
                $this->warnings[] = "Fila {$rowNum}: Distrito \"{$distVal}\" no encontrado — se omite.";
            }
        }

        $zone    = null;
        $zoneVal = trim($row[self::COL_ZONE] ?? '');
        if ($zoneVal !== '' && $district) {
            $zone = $this->zoneLookup[$district->id . '|' . mb_strtolower($zoneVal)] ?? null;
            if (!$zone) {
                $this->warnings[] = "Fila {$rowNum}: Zona \"{$zoneVal}\" no encontrada — se omite.";
            }
        }

        // ── Code uniqueness (soft check – will also be enforced by DB unique) ─
        $code = !empty($row[self::COL_CODE]) ? trim($row[self::COL_CODE]) : null;
        if ($code !== null) {
            $duplicate = Institution::where('code', $code)
                ->where('name', '!=', $name)   // allow update of same record
                ->exists();
            if ($duplicate) {
                throw new \Exception("El código \"{$code}\" ya está en uso por otro recinto.");
            }
        }

        // ── Status & operative ────────────────────────────────────────────────
        $rawStatus = strtolower(trim($row[self::COL_STATUS] ?? ''));
        $validStatuses = ['activo', 'inactivo', 'en_mantenimiento'];
        $status = in_array($rawStatus, $validStatuses) ? $rawStatus : 'activo';

        $rawOp      = strtolower(trim($row[self::COL_OPERATIVE] ?? ''));
        $isOperative = in_array($rawOp, ['sí', 'si', 'yes', '1', 'true']);

        // ── Build the data array (only columns that exist in the migration) ───
        return [
            'code'               => $code,
            'name'               => $name,
            'short_name'         => trim($row[self::COL_SHORT_NAME] ?? '') ?: null,
            'municipality_id'    => $municipality->id,   // ← from locality chain
            'locality_id'        => $locality->id,
            'district_id'        => $district?->id,
            'zone_id'            => $zone?->id,
            'address'            => trim($row[self::COL_ADDRESS]     ?? '') ?: null,
            'reference'          => trim($row[self::COL_REFERENCE]   ?? '') ?: null,
            'phone'              => trim($row[self::COL_PHONE]       ?? '') ?: null,
            'email'              => trim($row[self::COL_EMAIL]       ?? '') ?: null,
            'responsible_name'   => trim($row[self::COL_RESPONSIBLE] ?? '') ?: null,
            'registered_citizens'=> (int) ($row[self::COL_CITIZENS] ?? 0),
            'status'             => $status,
            'is_operative'       => $isOperative,
            'observations'       => trim($row[self::COL_OBS] ?? '') ?: null,
        ];
    }
}
