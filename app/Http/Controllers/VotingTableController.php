<?php

namespace App\Http\Controllers;

use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use App\Models\VotingTableElection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\VotingTablesExport;
use App\Imports\VotingTablesImport;
use App\Models\User;

/**
 * VotingTable CRUD controller.
 *
 * Schema facts (from migration):
 *   voting_tables        → physical table identity, institution, padron, delegates
 *   voting_table_elections → one row per (table × election_type): ballots, status, times
 *
 * A voting table is NOT bound to a single election type.
 * When a table is created, a VotingTableElection row is auto-created for
 * every currently active ElectionType.
 */
class VotingTableController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    // ─── Status catalogue ─────────────────────────────────────────────────────

    public static function statusOptions(): array
    {
        return [
            'configurada'   => 'Configurada',
            'en_espera'     => 'En Espera',
            'votacion'      => 'En Votación',
            'cerrada'       => 'Cerrada',
            'en_escrutinio' => 'En Escrutinio',
            'escrutada'     => 'Escrutada',
            'observada'     => 'Observada',
            'transmitida'   => 'Transmitida',
            'anulada'       => 'Anulada',
        ];
    }

    // ─── Constructor ──────────────────────────────────────────────────────────

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_mesas')->only(['index', 'show', 'exportAll', 'exportSelected', 'downloadTemplate']);
        $this->middleware('permission:create_mesas')->only(['create', 'store', 'import']);
        $this->middleware('permission:edit_mesas')->only(['edit', 'update', 'electionConfig', 'updateElectionConfig', 'assignDelegatesForm', 'assignDelegates']);
        $this->middleware('permission:delete_mesas')->only(['destroy', 'deleteMultiple']);
    }

    // ─── Validation rules (only voting_tables columns) ────────────────────────

    private function validationRules(?int $id = null): array
    {
        $uniqueOep      = 'nullable|string|max:20|unique:voting_tables,oep_code'      . ($id ? ',' . $id : '');
        $uniqueInternal = 'nullable|string|max:20|unique:voting_tables,internal_code' . ($id ? ',' . $id : '');

        return [
            'oep_code'               => $uniqueOep,
            'internal_code'          => $uniqueInternal,
            'number'                 => 'required|integer|min:1',
            'letter'                 => 'nullable|string|max:1',
            'type'                   => 'nullable|in:mixta,masculina,femenina',
            'institution_id'         => 'required|exists:institutions,id',
            'expected_voters'        => 'nullable|integer|min:0',
            'voter_range_start_name' => 'nullable|string|max:255',
            'voter_range_end_name'   => 'nullable|string|max:255',
            'president_id'           => 'nullable|exists:users,id',
            'secretary_id'           => 'nullable|exists:users,id',
            'vocal1_id'              => 'nullable|exists:users,id',
            'vocal2_id'              => 'nullable|exists:users,id',
            'vocal3_id'              => 'nullable|exists:users,id',
            'vocal4_id'              => 'nullable|exists:users,id',
            'observations'           => 'nullable|string',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'number.required'            => 'El número de mesa es obligatorio.',
            'number.integer'             => 'El número de mesa debe ser un valor numérico.',
            'number.min'                 => 'El número de mesa debe ser mayor o igual a 1.',
            'oep_code.unique'            => 'El código OEP ya está siendo utilizado por otra mesa.',
            'oep_code.max'               => 'El código OEP no puede exceder los 20 caracteres.',
            'internal_code.unique'       => 'El código interno ya está siendo utilizado por otra mesa.',
            'internal_code.max'          => 'El código interno no puede exceder los 20 caracteres.',
            'institution_id.required'    => 'Debe seleccionar una institución/recinto.',
            'institution_id.exists'      => 'La institución seleccionada no existe en el sistema.',
            'letter.max'                 => 'La letra debe tener máximo 1 carácter.',
            'type.in'                    => 'El tipo de mesa debe ser: mixta, masculina o femenina.',
            'expected_voters.integer'    => 'Los votantes esperados deben ser un valor numérico.',
            'expected_voters.min'        => 'Los votantes esperados deben ser mayor o igual a 0.',
        ];
    }

    // ─── Shared data helpers ──────────────────────────────────────────────────

    private function institutions()
    {
        return Institution::where('status', 'activo')->orderBy('name')->get();
    }

    private function users()
    {
        return User::where('is_active', true)->orderBy('name')->get();
    }

    // ─── CRUD ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            $query = VotingTable::withCount('elections')->with([
                'institution.locality.municipality.province.department',
                'elections.electionType',
                'president', 'secretary',
                'vocal1', 'vocal2', 'vocal3', 'vocal4',
            ]);

            if ($request->filled('institution_id')) {
                $query->where('institution_id', $request->institution_id);
            }

            if ($request->filled('status')) {
                $query->whereHas('elections', fn($q) => $q->where('status', $request->status));
            }

            if ($request->filled('election_type_id')) {
                $query->whereHas('elections', fn($q) => $q->where('election_type_id', $request->election_type_id));
            }

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function ($q) use ($s) {
                    $q->where('oep_code', 'ilike', "%{$s}%")
                      ->orWhere('internal_code', 'ilike', "%{$s}%")
                      ->orWhere('number', 'ilike', "%{$s}%")
                      ->orWhereHas('institution', fn($sub) =>
                          $sub->where('name', 'ilike', "%{$s}%")
                              ->orWhere('code', 'ilike', "%{$s}%"));
                });
            }

            $sortField     = $request->get('sort', 'institution_id');
            $sortDirection = $request->get('direction', 'asc');
            $allowed       = ['institution_id', 'number', 'oep_code', 'internal_code', 'expected_voters', 'institution_name'];

            if (in_array($sortField, $allowed)) {
                if ($sortField === 'institution_name') {
                    $query->leftJoin('institutions', 'voting_tables.institution_id', '=', 'institutions.id')
                          ->orderBy('institutions.name', $sortDirection)
                          ->select('voting_tables.*');
                } else {
                    $query->orderBy($sortField, $sortDirection);
                }
            } else {
                $query->orderBy('institution_id')->orderBy('number');
            }

            $perPage      = $request->get('per_page', self::ITEMS_PER_PAGE);
            $votingTables = $query->paginate($perPage)->withQueryString();

            // Enrich each row with aggregated election data for display
            $votingTables->getCollection()->transform(function ($table) {
                $latest = $table->elections->sortByDesc('updated_at')->first();
                // Attach transient display attributes (not DB columns on voting_tables)
                $table->display_status      = $latest?->status          ?? 'sin_configurar';
                $table->display_total_voters = $latest?->total_voters   ?? 0;
                return $table;
            });

            $electionTypes = ElectionType::where('active', true)->orderBy('name')->get();

            return view('voting-tables.index', [
                'votingTables'  => $votingTables,
                'institutions'  => $this->institutions(),
                'electionTypes' => $electionTypes,
                'statusOptions' => self::statusOptions(),
                'sortField'     => $sortField,
                'sortDirection' => $sortDirection,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading voting tables: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Error al cargar las mesas de votación.');
        }
    }

    public function create()
    {
        try {
            return view('voting-tables.create', [
                'votingTable' => null,
                'institutions' => $this->institutions(),
                'users'        => $this->users(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->validationRules(null), $this->validationMessages());

            // Uniqueness within institution (the migration enforces this via partial index too)
            if (VotingTable::where('institution_id', $validated['institution_id'])
                           ->where('number', $validated['number'])
                           ->when(!empty($validated['letter']), fn($q) => $q->where('letter', $validated['letter']))
                           ->exists()) {
                return redirect()->back()->withInput()
                    ->withErrors(['number' => 'Ya existe una mesa con este número' . (!empty($validated['letter']) ? ' y letra' : '') . ' en la institución seleccionada.']);
            }

            // Auto-generate codes when left blank
            $institution = Institution::findOrFail($validated['institution_id']);
            if (empty($validated['oep_code'])) {
                $validated['oep_code'] = $institution->code . '-' . $validated['number'] . ($validated['letter'] ?? '');
            }
            if (empty($validated['internal_code'])) {
                $validated['internal_code'] = $institution->code . '-M' . str_pad($validated['number'], 2, '0', STR_PAD_LEFT) . ($validated['letter'] ?? '');
            }

            DB::beginTransaction();

            $votingTable = VotingTable::create($validated);

            // Auto-create a VotingTableElection for every currently active ElectionType
            foreach (ElectionType::where('active', true)->get() as $electionType) {
                VotingTableElection::create([
                    'voting_table_id'  => $votingTable->id,
                    'election_type_id' => $electionType->id,
                    'ballots_received' => 0,
                    'ballots_used'     => 0,
                    'ballots_leftover' => 0,
                    'ballots_spoiled'  => 0,
                    'total_voters'     => 0,
                    'status'           => 'configurada',
                    'election_date'    => $electionType->election_date,
                ]);
            }

            DB::commit();

            return redirect()->route('voting-tables.show', $votingTable->id)
                ->with('success', '✅ La mesa de votación fue creada con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating voting table: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', '❌ Error al crear la mesa: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $votingTable = VotingTable::with([
                'institution.locality.municipality.province.department',
                'elections.electionType',
                'president', 'secretary',
                'vocal1', 'vocal2', 'vocal3', 'vocal4',
                'createdBy', 'updatedBy',
                'votes.candidate',
            ])->findOrFail($id);

            return view('voting-tables.show', compact('votingTable'));
        } catch (\Exception $e) {
            Log::error('Error showing voting table: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('voting-tables.index')
                ->with('error', 'Error al cargar los detalles de la mesa de votación.');
        }
    }
    public function edit($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            return view('voting-tables.edit', [
                'votingTable'  => $votingTable,
                'institutions' => $this->institutions(),
                'users'        => $this->users(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('voting-tables.index')
                ->with('error', 'Error al cargar el formulario de edición.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $validated   = $request->validate($this->validationRules((int) $id), $this->validationMessages());

            if (VotingTable::where('institution_id', $validated['institution_id'])
                           ->where('number', $validated['number'])
                           ->when(!empty($validated['letter']), fn($q) => $q->where('letter', $validated['letter']))
                           ->where('id', '!=', $id)->exists()) {
                return redirect()->back()->withInput()
                    ->withErrors(['number' => 'Ya existe una mesa con este número en la institución seleccionada.']);
            }

            DB::beginTransaction();
            $votingTable->update($validated);
            DB::commit();

            return redirect()->route('voting-tables.show', $id)
                ->with('success', '✅ La mesa de votación fue actualizada con éxito.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating voting table: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->withInput()
                ->with('error', '❌ Error al actualizar la mesa de votación.');
        }
    }

    public function destroy($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);

            if ($votingTable->votes()->count() > 0) {
                return redirect()->back()
                    ->with('error', '❌ No se puede eliminar la mesa porque tiene votos registrados.');
            }

            DB::transaction(function () use ($votingTable) {
                $votingTable->elections()->delete();
                $votingTable->delete();
            });

            return redirect()->route('voting-tables.index')
                ->with('success', '✅ La mesa de votación fue eliminada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error deleting voting table: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->back()->with('error', '❌ Error al eliminar la mesa de votación.');
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:voting_tables,id']);
            $ids = $request->input('ids');

            if (VotingTable::whereIn('id', $ids)->whereHas('votes')->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar mesas que tienen votos registrados.',
                ], 422);
            }

            DB::transaction(function () use ($ids) {
                VotingTableElection::whereIn('voting_table_id', $ids)->delete();
                VotingTable::whereIn('id', $ids)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => '✅ Se eliminaron ' . count($ids) . ' mesas de votación correctamente.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting multiple voting tables: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => '❌ Error al eliminar las mesas.'], 500);
        }
    }

    // ─── Election configuration (edits voting_table_elections rows) ───────────

    public function electionConfig($id)
    {
        try {
            $votingTable   = VotingTable::with(['elections.electionType'])->findOrFail($id);
            $electionTypes = ElectionType::where('active', true)->orderBy('name')->get();
            return view('voting-tables.election-config', compact('votingTable', 'electionTypes'));
        } catch (\Exception $e) {
            Log::error('Error loading election config: ' . $e->getMessage());
            return redirect()->route('voting-tables.show', $id)
                ->with('error', 'Error al cargar el formulario de configuración.');
        }
    }

    public function updateElectionConfig(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $validated   = $request->validate([
                'election_type_id' => 'required|exists:election_types,id',
                'ballots_received' => 'required|integer|min:0',
                'ballots_used'     => 'required|integer|min:0',
                'ballots_leftover' => 'required|integer|min:0',
                'ballots_spoiled'  => 'required|integer|min:0',
                'total_voters'     => 'required|integer|min:0',
                'status'           => 'required|in:' . implode(',', array_keys(self::statusOptions())),
                'opening_time'     => 'nullable',
                'closing_time'     => 'nullable',
                'election_date'    => 'nullable|date',
                'observations'     => 'nullable|string',
            ]);

            if ($validated['ballots_received'] < $validated['total_voters']) {
                return redirect()->back()->withInput()
                    ->withErrors(['ballots_received' => 'Las papeletas recibidas no pueden ser menores al total de votantes.']);
            }

            $total = $validated['ballots_used'] + $validated['ballots_leftover'] + $validated['ballots_spoiled'];
            if ($total !== (int) $validated['ballots_received']) {
                return redirect()->back()->withInput()
                    ->withErrors(['ballots_used' => 'La suma de usadas + sobrantes + deterioradas debe igualar las recibidas.']);
            }

            DB::transaction(function () use ($votingTable, $validated) {
                VotingTableElection::updateOrCreate(
                    ['voting_table_id' => $votingTable->id, 'election_type_id' => $validated['election_type_id']],
                    $validated
                );
            });

            return redirect()->route('voting-tables.show', $id)
                ->with('success', '✅ Configuración de elección actualizada correctamente.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating election config: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', '❌ Error al actualizar la configuración: ' . $e->getMessage());
        }
    }

    // ─── Delegates ────────────────────────────────────────────────────────────

    public function assignDelegatesForm($id)
    {
        try {
            $votingTable = VotingTable::with(['president', 'secretary', 'vocal1', 'vocal2', 'vocal3', 'vocal4'])
                ->findOrFail($id);
            return view('voting-tables.assign-delegates', [
                'votingTable' => $votingTable,
                'users'       => $this->users(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading delegates form: ' . $e->getMessage());
            return redirect()->route('voting-tables.show', $id)
                ->with('error', 'Error al cargar el formulario de asignación.');
        }
    }

    public function assignDelegates(Request $request, $id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $validated   = $request->validate([
                'president_id' => 'nullable|exists:users,id',
                'secretary_id' => 'nullable|exists:users,id',
                'vocal1_id'    => 'nullable|exists:users,id',
                'vocal2_id'    => 'nullable|exists:users,id',
                'vocal3_id'    => 'nullable|exists:users,id',
                'vocal4_id'    => 'nullable|exists:users,id',
            ]);
            $votingTable->update($validated);
            return redirect()->route('voting-tables.show', $id)
                ->with('success', '✅ Delegados asignados correctamente.');
        } catch (\Exception $e) {
            Log::error('Error assigning delegates: ' . $e->getMessage());
            return redirect()->back()->withInput()
                ->with('error', 'Error al asignar delegados: ' . $e->getMessage());
        }
    }

    // ─── Export / Import / Template ───────────────────────────────────────────

    public function exportAll(Request $request)
    {
        try {
            $export   = new VotingTablesExport();
            $filePath = $export->export($request->only(['institution_id', 'status', 'election_type_id', 'search']));
            return response()->download(storage_path("app/{$filePath}"))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting voting tables: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar: ' . $e->getMessage());
        }
    }

    public function exportSelected(Request $request)
    {
        try {
            $request->validate(['selected_ids' => 'required|json']);
            $ids = json_decode($request->selected_ids, true);
            if (empty($ids)) {
                return redirect()->back()->with('error', '❌ No se seleccionaron mesas para exportar.');
            }
            $export   = new VotingTablesExport();
            $filePath = $export->export(['selected_ids' => $ids]);
            return response()->download(storage_path("app/{$filePath}"))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error exporting selected voting tables: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al exportar las mesas seleccionadas.');
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);
            $import = new VotingTablesImport();
            $result = $import->import($request->file('file'));

            if (!$result['success'] || ($result['success_count'] === 0 && !empty($result['errors']))) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('error', '❌ No se pudo importar ninguna mesa.');
            }

            if (!empty($result['errors'])) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('success_count', $result['success_count'])
                    ->with('warning', "⚠️ Se importaron {$result['success_count']} mesas con algunos errores.");
            }

            return redirect()->route('voting-tables.index')
                ->with('success', "✅ Se importaron {$result['success_count']} mesas correctamente.");
        } catch (\Exception $e) {
            Log::error('Error importing voting tables: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                ->with('error', '❌ Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export   = new VotingTablesExport();
            $filePath = $export->downloadTemplate();
            return response()->download(storage_path("app/{$filePath}"))->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Error al generar la plantilla.');
        }
    }

    // ─── AJAX helpers ─────────────────────────────────────────────────────────

    public function getByInstitution($institutionId)
    {
        try {
            $tables = VotingTable::where('institution_id', $institutionId)
                ->select('id', 'number', 'letter', 'oep_code', 'internal_code')
                ->orderBy('number')
                ->get();
            return response()->json($tables);
        } catch (\Exception $e) {
            Log::error('Error getting voting tables by institution: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading voting tables'], 500);
        }
    }
}
