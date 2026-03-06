<?php
namespace App\Http\Controllers;

use App\Models\Acta;
use App\Models\ActaCategoryResult;
use App\Models\VotingTable;
use App\Models\VotingTableElection;
use App\Models\ElectionTypeCategory;
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
        $this->middleware('permission:view_actas')->only(['index', 'show', 'getByTable', 'getTableActas']);
        $this->middleware('permission:upload_actas')->only(['upload', 'store']);
        $this->middleware('permission:verify_actas')->only(['verify', 'approve', 'observe']);
    }

    public function index(Request $request)
    {
        $query = Acta::with(['votingTable.institution', 'user', 'signedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('voting_table_id')) {
            $query->where('voting_table_id', $request->voting_table_id);
        }

        $actas = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('actas.index', compact('actas'));
    }

    public function show(Acta $acta)
    {
        $acta->load(['votingTable.institution', 'user', 'signedBy', 'categoryResults.electionTypeCategory.electionCategory']);
        return view('actas.show', compact('acta'));
    }

    public function upload(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_table_id'  => 'required|integer|exists:voting_tables,id',
                'election_type_id' => 'required|integer|exists:election_types,id',
                'acta_number'      => 'required|string|max:50',
                'photo'            => 'required|file|image|mimes:jpeg,png,jpg|max:5120',
                'pdf'              => 'nullable|file|mimes:pdf|max:10240',
                'has_physical'     => 'nullable',
            ]);

            DB::beginTransaction();
            $votingTable = VotingTable::findOrFail($validated['voting_table_id']);
            $existing = Acta::where('voting_table_id', $validated['voting_table_id'])
                ->where('election_type_id', $validated['election_type_id'])
                ->whereNotIn('status', [Acta::STATUS_REJECTED])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un acta activa para esta mesa y tipo de elección (estado: ' . $existing->status . ')',
                ], 422);
            }
            $photoPath = $request->file('photo')->store('actas/photos/' . date('Y/m'), 'public');
            $pdfPath = null;
            if ($request->hasFile('pdf')) {
                $pdfPath = $request->file('pdf')->store('actas/pdfs/' . date('Y/m'), 'public');
            }
            $fileHash = hash_file('sha256', $request->file('photo')->getRealPath());
            $hasPhysical = in_array($request->input('has_physical'), ['on', '1', 1, 'true', true], true);
            $acta = Acta::create([
                'code'              => Acta::generateCode(),
                'acta_number'       => $validated['acta_number'],
                'voting_table_id'   => $validated['voting_table_id'],
                'election_type_id'  => (int) $validated['election_type_id'],
                'user_id'           => Auth::id(),
                'photo_path'        => $photoPath,
                'pdf_path'          => $pdfPath,
                'original_filename' => $request->file('photo')->getClientOriginalName(),
                'file_size'         => $request->file('photo')->getSize(),
                'hash'              => $fileHash,
                'status'            => Acta::STATUS_UPLOADED,
                'is_consistent'     => false,
                'metadata'          => [
                    'mime_type'    => $request->file('photo')->getMimeType(),
                    'upload_ip'    => $request->ip(),
                    'user_agent'   => $request->userAgent(),
                    'has_physical' => $hasPhysical,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acta subida correctamente',
                'acta'    => [
                    'id'          => $acta->id,
                    'code'        => $acta->code,
                    'acta_number' => $acta->acta_number,
                    'photo_url'   => Storage::url($acta->photo_path),
                    'pdf_url'     => $acta->pdf_path ? Storage::url($acta->pdf_path) : null,
                    'status'      => $acta->status,
                ],
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ActaController@upload: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el acta: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        return $this->upload($request);
    }

    public function verify(int $id)
    {
        try {
            DB::beginTransaction();

            $acta = Acta::findOrFail($id);

            if ($acta->status !== Acta::STATUS_UPLOADED) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta acta ya fue procesada (estado: ' . $acta->status . ')',
                ], 422);
            }

            $acta->markAsVerified(Auth::id());
            DB::commit();

            return response()->json([
                'success'         => true,
                'message'         => 'Acta verificada exitosamente',
                'is_consistent'   => $acta->is_consistent,
                'inconsistencies' => $acta->inconsistencies,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ActaController@verify: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function observe(Request $request, int $id)
    {
        try {
            $validated = $request->validate(['notes' => 'required|string|max:500']);

            DB::beginTransaction();
            $acta = Acta::findOrFail($id);
            $acta->markAsObserved(Auth::id(), $validated['notes']);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Acta observada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ActaController@observe: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function approve(int $id)
    {
        try {
            DB::beginTransaction();
            $acta = Acta::findOrFail($id);

            if ($acta->status !== Acta::STATUS_VERIFIED) {
                return response()->json([
                    'success' => false,
                    'message' => 'El acta debe estar verificada antes de aprobarse',
                ], 422);
            }

            $acta->markAsApproved(Auth::id());
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Acta aprobada exitosamente']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ActaController@approve: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getByTable(int $tableId)
    {
        return $this->getTableActas($tableId);
    }

    public function getTableActas(int $tableId)
    {
        try {
            $actas = Acta::where('voting_table_id', $tableId)
                ->with(['user', 'signedBy'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($acta) => [
                    'id'              => $acta->id,
                    'code'            => $acta->code,
                    'acta_number'     => $acta->acta_number,
                    'status'          => $acta->status,
                    'status_badge'    => $acta->status_badge,
                    'is_consistent'   => $acta->is_consistent,
                    'inconsistencies' => $acta->inconsistencies,
                    'photo_url'       => Storage::url($acta->photo_path),
                    'pdf_url'         => $acta->pdf_path ? Storage::url($acta->pdf_path) : null,
                    'uploaded_by'     => $acta->user?->name ?? 'N/A',
                    'created_at'      => $acta->created_at->format('d/m/Y H:i'),
                ]);

            return response()->json($actas);

        } catch (\Exception $e) {
            Log::error('ActaController@getTableActas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar actas'], 500);
        }
    }

    public function destroy(Acta $acta)
    {
        try {
            if ($acta->photo_path) Storage::disk('public')->delete($acta->photo_path);
            if ($acta->pdf_path)   Storage::disk('public')->delete($acta->pdf_path);
            $acta->delete();

            return response()->json(['success' => true, 'message' => 'Acta eliminada correctamente']);

        } catch (\Exception $e) {
            Log::error('ActaController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el acta'], 500);
        }
    }
}
