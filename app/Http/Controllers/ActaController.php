<?php
// app/Http/Controllers/ActaController.php

namespace App\Http\Controllers;

use App\Models\Acta;
use App\Models\ActivityLog;
use App\Models\VotingTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ActaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_actas')->only(['index', 'show', 'getByTable']);
        $this->middleware('permission:upload_actas')->only(['upload']);
        $this->middleware('permission:verify_actas')->only(['verify', 'approve']);
    }

    public function index(Request $request)
    {
        $query = Acta::with(['votingTable.institution', 'user', 'signedBy']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('voting_table_id')) {
            $query->where('voting_table_id', $request->voting_table_id);
        }

        $actas = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('actas.index', compact('actas'));
    }

    /**
     * Subir un acta
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_table_id' => 'required|exists:voting_tables,id',
                'acta_number' => 'required|string|max:50',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
                'pdf' => 'nullable|mimes:pdf|max:20480',
                'total_votes' => 'required|integer|min:0',
                'blank_votes' => 'required|integer|min:0',
                'null_votes' => 'required|integer|min:0',
                'valid_votes' => 'required|integer|min:0',
            ]);

            DB::beginTransaction();

            $votingTable = VotingTable::findOrFail($validated['voting_table_id']);

            // Verificar que no haya un acta previa no resuelta
            if ($votingTable->actas()->whereNotIn('status', ['rejected', 'approved'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un acta pendiente para esta mesa'
                ], 422);
            }

            // Subir foto
            $photoPath = $request->file('photo')->store('actas/photos', 'public');

            // Subir PDF si existe
            $pdfPath = null;
            if ($request->hasFile('pdf')) {
                $pdfPath = $request->file('pdf')->store('actas/pdfs', 'public');
            }

            // Calcular hash del archivo
            $hash = hash_file('sha256', $request->file('photo')->getRealPath());

            // Crear acta
            $acta = Acta::create([
                'code' => Acta::generateCode(),
                'acta_number' => $validated['acta_number'],
                'voting_table_id' => $validated['voting_table_id'],
                'election_type_id' => $votingTable->election_type_id,
                'user_id' => Auth::id(),
                'photo_path' => $photoPath,
                'pdf_path' => $pdfPath,
                'original_filename' => $request->file('photo')->getClientOriginalName(),
                'total_votes' => $validated['total_votes'],
                'blank_votes' => $validated['blank_votes'],
                'null_votes' => $validated['null_votes'],
                'valid_votes' => $validated['valid_votes'],
                'status' => Acta::STATUS_UPLOADED,
                'hash' => $hash,
                'file_size' => $request->file('photo')->getSize(),
            ]);

            // Verificar consistencia con la mesa
            $acta->verifyConsistency($votingTable);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta subida exitosamente',
                'acta' => $acta,
                'is_consistent' => $acta->is_consistent,
                'inconsistencies' => $acta->inconsistencies
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading acta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar un acta
     */
    public function verify($id)
    {
        try {
            DB::beginTransaction();

            $acta = Acta::with('votingTable')->findOrFail($id);

            if ($acta->status !== Acta::STATUS_UPLOADED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta acta ya ha sido procesada'
                ], 422);
            }

            $acta->markAsVerified(Auth::id());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta verificada exitosamente',
                'is_consistent' => $acta->is_consistent,
                'inconsistencies' => $acta->inconsistencies
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verifying acta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Observar un acta
     */
    public function observe(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'notes' => 'required|string|max:500',
            ]);

            DB::beginTransaction();

            $acta = Acta::findOrFail($id);
            $acta->markAsObserved(Auth::id(), $validated['notes']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta observada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error observing acta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al observar el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar un acta
     */
    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $acta = Acta::findOrFail($id);

            if ($acta->status !== Acta::STATUS_VERIFIED) {
                return response()->json([
                    'success' => false,
                    'message' => 'El acta debe ser verificada primero'
                ], 422);
            }

            if (!$acta->is_consistent) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede aprobar un acta con inconsistencias'
                ], 422);
            }

            $acta->markAsApproved(Auth::id());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta aprobada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving acta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener actas de una mesa
     */
    public function getTableActas($tableId)
    {
        try {
            $actas = Acta::where('voting_table_id', $tableId)
                ->with(['user', 'signedBy'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($acta) {
                    return [
                        'id' => $acta->id,
                        'code' => $acta->code,
                        'acta_number' => $acta->acta_number,
                        'status' => $acta->status,
                        'status_badge' => $acta->status_badge,
                        'total_votes' => $acta->total_votes,
                        'blank_votes' => $acta->blank_votes,
                        'null_votes' => $acta->null_votes,
                        'valid_votes' => $acta->valid_votes,
                        'is_consistent' => $acta->is_consistent,
                        'inconsistencies' => $acta->inconsistencies,
                        'photo_url' => Storage::url($acta->photo_path),
                        'pdf_url' => $acta->pdf_path ? Storage::url($acta->pdf_path) : null,
                        'uploaded_by' => $acta->user->name,
                        'created_at' => $acta->created_at->format('d/m/Y H:i'),
                    ];
                });

            return response()->json($actas);

        } catch (\Exception $e) {
            Log::error('Error getting table actas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar actas'], 500);
        }
    }



    public function upload(Request $request)
    {
        try {
            // Validar con reglas específicas
            $validated = $request->validate([
                'voting_table_id' => 'required|exists:voting_tables,id',
                'acta_number' => 'required|string|max:50',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB
                'pdf' => 'nullable|mimes:pdf|max:10240', // 10MB
                'has_physical' => 'nullable|in:on,off,true,false,1,0', // Aceptar múltiples formatos
            ]);

            DB::beginTransaction();

            // Obtener la mesa de votación
            $votingTable = VotingTable::findOrFail($validated['voting_table_id']);

            // Generar código único
            $lastActa = Acta::orderBy('id', 'desc')->first();
            $nextId = $lastActa ? $lastActa->id + 1 : 1;
            $code = 'ACTA-' . date('Ymd') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Verificar que no exista ya un acta con el mismo número para esta mesa
            $existingActa = Acta::where('voting_table_id', $validated['voting_table_id'])
                ->where('acta_number', $validated['acta_number'])
                ->first();

            if ($existingActa) {
                throw new \Exception('Ya existe un acta con este número para esta mesa');
            }

            // Guardar foto
            $photoPath = $request->file('photo')->store('actas/photos', 'public');

            // Guardar PDF si existe
            $pdfPath = null;
            if ($request->hasFile('pdf')) {
                $pdfPath = $request->file('pdf')->store('actas/pdfs', 'public');
            }

            // Calcular hash del archivo para verificación
            $fileHash = hash_file('sha256', $request->file('photo')->path());

            // Determinar valor de has_physical - CORREGIDO
            $hasPhysical = false;
            if ($request->has('has_physical')) {
                $value = $request->input('has_physical');
                // Aceptar diferentes formatos
                $hasPhysical = in_array($value, ['on', '1', 1, 'true', true], true);
            }

            $acta = Acta::create([
                'code' => $code,
                'acta_number' => $validated['acta_number'],
                'voting_table_id' => $validated['voting_table_id'],
                'election_type_id' => $votingTable->election_type_id,
                'user_id' => Auth::id(),
                'photo_path' => $photoPath,
                'pdf_path' => $pdfPath,
                'original_filename' => $request->file('photo')->getClientOriginalName(),
                'file_size' => $request->file('photo')->getSize(),
                'hash' => $fileHash,
                'has_physical_acta' => $hasPhysical,
                'status' => 'uploaded',
                'metadata' => json_encode([
                    'mime_type' => $request->file('photo')->getMimeType(),
                    'dimensions' => getimagesize($request->file('photo')->path()),
                    'upload_ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ])
            ]);

            // Actualizar la mesa con la referencia al acta
            $votingTable->acta_number = $validated['acta_number'];
            $votingTable->acta_uploaded_at = now();
            $votingTable->save();
            ActivityLog::log('uploaded', $acta, null, [
                'acta_number' => $validated['acta_number'],
                'file_size' => $request->file('photo')->getSize()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta subida correctamente',
                'acta' => [
                    'id' => $acta->id,
                    'code' => $acta->code,
                    'acta_number' => $acta->acta_number,
                    'photo_url' => Storage::url($acta->photo_path),
                    'pdf_url' => $acta->pdf_path ? Storage::url($acta->pdf_path) : null
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading acta: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al subir el acta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Acta $acta)
    {
        $acta->load(['votingTable.institution', 'user', 'signedBy']);
        return view('actas.show', compact('acta'));
    }

    public function destroy(Acta $acta)
    {
        try {
            // Eliminar archivos físicos
            if ($acta->photo_path) {
                Storage::disk('public')->delete($acta->photo_path);
            }
            if ($acta->pdf_path) {
                Storage::disk('public')->delete($acta->pdf_path);
            }

            $acta->delete();

            return response()->json([
                'success' => true,
                'message' => 'Acta eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting acta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el acta'
            ], 500);
        }
    }

    public function getByTable($tableId)
    {
        try {
            $actas = Acta::where('voting_table_id', $tableId)
                ->with(['user', 'signedBy'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($acta) {
                    return [
                        'id' => $acta->id,
                        'code' => $acta->code,
                        'acta_number' => $acta->acta_number,
                        'status' => $acta->status,
                        'created_at' => $acta->created_at->format('d/m/Y H:i'),
                        'photo_url' => Storage::url($acta->photo_path),
                        'pdf_url' => $acta->pdf_path ? Storage::url($acta->pdf_path) : null,
                    ];
                });

            return response()->json($actas);

        } catch (\Exception $e) {
            Log::error('Error getting actas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar actas'], 500);
        }
    }

    private function getElectionTypeId($votingTableId)
    {
        $votingTable = VotingTable::find($votingTableId);
        return $votingTable ? $votingTable->election_type_id : null;
    }
}
