<?php
// app/Http/Controllers/VotingTableController.php

namespace App\Http\Controllers;

use App\Models\VotingTable;
use App\Models\Institution;
use App\Models\ElectionType;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Exports\VotingTablesExport;
use App\Imports\VotingTablesImport;

class VotingTableController extends Controller
{
    const ITEMS_PER_PAGE = 20;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_mesas')->only(['index', 'show', 'export']);
        $this->middleware('permission:create_mesas')->only(['create', 'store']);
        $this->middleware('permission:edit_mesas')->only(['edit', 'update']);
        $this->middleware('permission:delete_mesas')->only(['destroy', 'deleteMultiple']);
    }

public function index(Request $request)
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

        // Filtros
        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('election_type_id')) {
            $query->where('election_type_id', $request->election_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('code_ine', 'ilike', "%{$search}%")
                  ->orWhere('number', 'ilike', "%{$search}%")
                  ->orWhereHas('institution', function($subq) use ($search) {
                      $subq->where('name', 'ilike', "%{$search}%")
                           ->orWhere('code', 'ilike', "%{$search}%");
                  });
            });
        }

        $votingTables = $query->orderBy('institution_id')
                              ->orderBy('number')
                              ->paginate(self::ITEMS_PER_PAGE)
                              ->withQueryString();

        $institutions = Institution::where('status', 'activo')
                                   ->orderBy('name')
                                   ->get();
        $electionTypes = ElectionType::where('active', true)
                                     ->orderBy('name')
                                     ->get();

        return view('voting-tables.index', compact(
            'votingTables', 
            'institutions', 
            'electionTypes'
        ));

    } catch (\Exception $e) {
        // Mostrar el error real para depuración
        dd($e->getMessage(), $e->getFile(), $e->getLine());
        
        Log::error('Error loading voting tables: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->back()->with('error', 'Error al cargar las mesas de votación.');
    }
}

    public function create()
    {
        try {
            $institutions = Institution::where('status', 'activo')
                                       ->orderBy('name')
                                       ->get();
            $electionTypes = ElectionType::where('active', true)
                                         ->orderBy('name')
                                         ->get();

            return view('voting-tables.create', compact('institutions', 'electionTypes'));

        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                            ->with('error', 'Error al cargar el formulario de creación.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'nullable|string|max:50',
                'code_ine' => 'nullable|string|max:50',
                'number' => 'required|integer|min:1',
                'letter' => 'nullable|string|max:1',
                'type' => 'nullable|in:mixta,masculina,femenina',
                'from_name' => 'nullable|string|max:255',
                'to_name' => 'nullable|string|max:255',
                'from_number' => 'nullable|integer|min:0',
                'to_number' => 'nullable|integer|min:0',
                'registered_citizens' => 'nullable|integer|min:0',
                'voted_citizens' => 'nullable|integer|min:0',
                'blank_votes' => 'nullable|integer|min:0',
                'null_votes' => 'nullable|integer|min:0',
                'computed_records' => 'nullable|integer|min:0',
                'annulled_records' => 'nullable|integer|min:0',
                'enabled_records' => 'nullable|integer|min:0',
                'status' => 'required|in:pendiente,en_proceso,cerrado,en_computo,computado,observado,anulado',
                'institution_id' => 'required|exists:institutions,id',
                'election_type_id' => 'required|exists:election_types,id',
                'opening_time' => 'nullable',
                'closing_time' => 'nullable',
                'election_date' => 'nullable|date',
                'acta_number' => 'nullable|string|max:50',
                'observations' => 'nullable|string',
            ]);

            // Verificar si ya existe una mesa con el mismo número en la misma institución
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->first();

            if ($existingTable) {
                throw ValidationException::withMessages([
                    'number' => 'Ya existe una mesa con este número en la institución seleccionada.',
                ]);
            }

            // Generar código si está vacío
            if (empty($validated['code'])) {
                $institution = Institution::find($validated['institution_id']);
                $validated['code'] = $this->generateTableCode($institution, $validated['number']);
            }

            DB::transaction(function () use ($validated) {
                VotingTable::create($validated);
            });

            return redirect()->route('voting-tables.index')
                ->with('success', 'La mesa de votación fue creada con éxito.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating voting table: ' . $e->getMessage(), [
                'data' => $request->except('_token'),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la mesa de votación. Por favor intente nuevamente.');
        }
    }

    public function show($id)
    {
        try {
            $votingTable = VotingTable::with([
                'institution.locality.municipality.province.department',
                'electionType',
                'president',
                'secretary',
                'vocal1',
                'vocal2',
                'vocal3',
                'vocal4',
                'votes.candidate'
            ])->findOrFail($id);

            return view('voting-tables.show', compact('votingTable'));

        } catch (\Exception $e) {
            Log::error('Error showing voting table: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('voting-tables.index')
                ->with('error', 'Error al cargar los detalles de la mesa de votación.');
        }
    }

    public function edit($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);
            $institutions = Institution::where('status', 'activo')
                                       ->orderBy('name')
                                       ->get();
            $electionTypes = ElectionType::where('active', true)
                                         ->orderBy('name')
                                         ->get();

            return view('voting-tables.edit', compact('votingTable', 'institutions', 'electionTypes'));

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

            $validated = $request->validate([
                'code' => 'nullable|string|max:50',
                'code_ine' => 'nullable|string|max:50',
                'number' => 'required|integer|min:1',
                'letter' => 'nullable|string|max:1',
                'type' => 'nullable|in:mixta,masculina,femenina',
                'from_name' => 'nullable|string|max:255',
                'to_name' => 'nullable|string|max:255',
                'from_number' => 'nullable|integer|min:0',
                'to_number' => 'nullable|integer|min:0',
                'registered_citizens' => 'nullable|integer|min:0',
                'voted_citizens' => 'nullable|integer|min:0',
                'blank_votes' => 'nullable|integer|min:0',
                'null_votes' => 'nullable|integer|min:0',
                'computed_records' => 'nullable|integer|min:0',
                'annulled_records' => 'nullable|integer|min:0',
                'enabled_records' => 'nullable|integer|min:0',
                'status' => 'required|in:pendiente,en_proceso,cerrado,en_computo,computado,observado,anulado',
                'institution_id' => 'required|exists:institutions,id',
                'election_type_id' => 'required|exists:election_types,id',
                'opening_time' => 'nullable',
                'closing_time' => 'nullable',
                'election_date' => 'nullable|date',
                'acta_number' => 'nullable|string|max:50',
                'observations' => 'nullable|string',
            ]);

            // Verificar si ya existe otra mesa con el mismo número en la misma institución
            $existingTable = VotingTable::where('institution_id', $validated['institution_id'])
                ->where('number', $validated['number'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingTable) {
                throw ValidationException::withMessages([
                    'number' => 'Ya existe una mesa con este número en la institución seleccionada.',
                ]);
            }

            DB::transaction(function () use ($votingTable, $validated) {
                $votingTable->update($validated);
            });

            return redirect()->route('voting-tables.show', $id)
                ->with('success', 'La mesa de votación fue actualizada con éxito.');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating voting table: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->except('_token'),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la mesa de votación. Por favor intente nuevamente.');
        }
    }

    public function destroy($id)
    {
        try {
            $votingTable = VotingTable::findOrFail($id);

            // Verificar si tiene votos registrados
            if ($votingTable->votes()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la mesa porque tiene votos registrados.');
            }

            DB::transaction(function () use ($votingTable) {
                $votingTable->delete();
            });

            return redirect()->route('voting-tables.index')
                ->with('success', 'La mesa de votación fue eliminada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error deleting voting table: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Error al eliminar la mesa de votación. Por favor intente nuevamente.');
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:voting_tables,id'
            ]);

            $ids = $request->input('ids');
            $count = count($ids);

            // Verificar si alguna mesa tiene votos
            $tablesWithVotes = VotingTable::whereIn('id', $ids)
                ->whereHas('votes')
                ->count();

            if ($tablesWithVotes > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar mesas que tienen votos registrados.'
                ], 422);
            }

            $deleted = VotingTable::whereIn('id', $ids)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => "Se eliminaron {$count} mesas de votación correctamente.",
                    'deleted_count' => $deleted
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudieron eliminar las mesas de votación.'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error deleting multiple voting tables: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error inesperado al eliminar las mesas de votación.'
            ], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $filters = $request->only(['institution_id', 'status', 'election_type_id']);
            $export = new VotingTablesExport();
            $filePath = $export->export($filters);

            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error exporting voting tables: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al exportar las mesas de votación: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ]);

            $import = new VotingTablesImport();
            $result = $import->import($request->file('file'));

            if (!$result['success']) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('error', 'Error durante la importación.');
            }

            if (!empty($result['errors']) && $result['success_count'] === 0) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('error', 'No se pudo importar ninguna mesa de votación.');
            } elseif (!empty($result['errors'])) {
                return redirect()->route('voting-tables.index')
                    ->with('import_errors', $result['errors'])
                    ->with('import_warnings', $result['warnings'] ?? [])
                    ->with('success_count', $result['success_count'])
                    ->with('warning', "Se importaron {$result['success_count']} mesas de votación. Algunas filas tuvieron errores.");
            }

            return redirect()->route('voting-tables.index')
                ->with('success', "Se importaron {$result['success_count']} mesas de votación correctamente.");

        } catch (\Exception $e) {
            Log::error('Error importing voting tables: ' . $e->getMessage());
            return redirect()->route('voting-tables.index')
                ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $export = new VotingTablesExport();
            $filePath = $export->downloadTemplate();

            return response()->download(storage_path("app/{$filePath}"))
                ->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al generar la plantilla: ' . $e->getMessage());
        }
    }

    public function getByInstitution($institutionId)
    {
        try {
            $votingTables = VotingTable::where('institution_id', $institutionId)
                ->select('id', 'number', 'code')
                ->orderBy('number')
                ->get();

            return response()->json($votingTables);

        } catch (\Exception $e) {
            Log::error('Error getting voting tables by institution: ' . $e->getMessage(), [
                'institution_id' => $institutionId
            ]);

            return response()->json(['error' => 'Error loading voting tables'], 500);
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
}