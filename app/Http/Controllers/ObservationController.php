<?php
// app/Http/Controllers/ObservationController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Institution;
use App\Models\Observation;
use App\Models\RecintoDelegate;
use App\Models\Reviewer;
use App\Models\TableDelegate;
use App\Models\User;
use App\Models\VotingTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ObservationController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_observations')->only(['index', 'show', 'getByTable']);
        $this->middleware('permission:create_observations')->only(['store']);
        $this->middleware('permission:resolve_observations')->only(['resolve']);
    }
    
    public function index(Request $request)
    {
        $query = Observation::with(['votingTable.institution', 'reviewer', 'resolver']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('voting_table_id')) {
            $query->where('voting_table_id', $request->voting_table_id);
        }

        $observations = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('observations.index', compact('observations'));
    }

    public function store(Request $request)
    {
        try {
            // Log completo de la solicitud para depuración
            Log::info('=== INICIO CREACIÓN OBSERVACIÓN ===');
            Log::info('Datos completos recibidos:', $request->all());
            Log::info('Headers:', $request->headers->all());

            // Validar los datos
            $validated = $request->validate([
                'voting_table_id' => 'required|exists:voting_tables,id',
                'type' => 'required|in:inconsistencia_acta,error_datos,falta_firma,acta_ilegible,votos_inconsistentes,mesa_anulada,reclamo_partido,otro',
                'description' => 'required|string|max:1000',
                'severity' => 'required|in:info,warning,error,critical',
            ]);

            Log::info('Datos validados:', $validated);

            DB::beginTransaction();

            // ===== GENERAR CÓDIGO ÚNICO =====
            $date = now()->format('Ymd');
            $prefix = "OBS-{$date}-";
            
            // Buscar el último código con este prefijo
            $lastObservation = Observation::where('code', 'like', "{$prefix}%")
                ->orderBy('code', 'desc')
                ->first();
            
            if ($lastObservation) {
                // Extraer el número del último código
                $lastCode = $lastObservation->code;
                $lastNumber = intval(substr($lastCode, -4));
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
            
            // Formatear el número con 4 dígitos
            $code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            
            // Verificar que el código sea único (por si acaso)
            $attempts = 0;
            while (Observation::where('code', $code)->exists() && $attempts < 10) {
                $nextNumber++;
                $code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
                $attempts++;
            }
            
            Log::info('Código generado:', ['code' => $code]);

            // Obtener el election_type_id de la mesa
            $votingTable = VotingTable::find($validated['voting_table_id']);
            if (!$votingTable) {
                throw new \Exception('Mesa de votación no encontrada');
            }

            // Preparar los datos para la inserción
            $observationData = [
                'code' => $code,
                'voting_table_id' => $validated['voting_table_id'],
                'type' => $validated['type'],
                'description' => $validated['description'],
                'severity' => $validated['severity'],
                'reviewed_by' => Auth::id(),
                'reviewer_role' => $this->getReviewerRole(),
                'status' => 'pending',
                'election_type_id' => $votingTable->election_type_id,
            ];

            Log::info('Datos a insertar:', $observationData);

            // Crear la observación
            $observation = Observation::create($observationData);

            Log::info('Observación creada:', ['id' => $observation->id, 'code' => $observation->code]);

            // Actualizar estado de la mesa
            if ($votingTable) {
                $votingTable->status = 'observado';
                $votingTable->validation_status = 'observed';
                $votingTable->save();
                Log::info('Estado de mesa actualizado', ['table_id' => $votingTable->id, 'status' => 'observado']);
            }

            // Registrar en activity log si existe la clase
            if (class_exists('App\Models\ActivityLog')) {
                try {
                    ActivityLog::log('observed', $votingTable, null, [
                        'observation_id' => $observation->id,
                        'type' => $validated['type'],
                        'severity' => $validated['severity']
                    ], $validated['description']);
                } catch (\Exception $e) {
                    Log::warning('Error al registrar en ActivityLog: ' . $e->getMessage());
                }
            }

            DB::commit();
            Log::info('=== OBSERVACIÓN CREADA EXITOSAMENTE ===');

            return response()->json([
                'success' => true,
                'message' => 'Observación creada correctamente',
                'observation' => [
                    'id' => $observation->id,
                    'code' => $observation->code,
                    'type' => $observation->type,
                    'description' => $observation->description,
                    'severity' => $observation->severity
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Error de validación:', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating observation: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la observación: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getReviewerRole()
    {
        $user = Auth::user();
        
        // Verificar que el método hasRole existe
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('revisor')) return 'revisor';
            if ($user->hasRole('fiscal')) return 'fiscal';
            if ($user->hasRole('coordinador_recinto')) return 'coordinador';
            if ($user->hasRole('validador')) return 'notario';
        }
        
        return 'revisor';
    }

    private function getElectionTypeId($votingTableId)
    {
        $votingTable = VotingTable::find($votingTableId);
        return $votingTable ? $votingTable->election_type_id : null;
    }

    public function show(Observation $observation)
    {
        $observation->load(['votingTable.institution', 'reviewer', 'resolver']);
        return view('observations.show', compact('observation'));
    }

    public function resolve(Request $request, Observation $observation)
    {
        try {
            $validated = $request->validate([
                'resolution_notes' => 'required|string|max:1000',
                'resolution_type' => 'required|in:correccion,anulacion,rechazo,escalamiento',
            ]);

            DB::beginTransaction();

            $observation->update([
                'status' => 'resolved',
                'resolved_by' => Auth::id(),
                'resolved_at' => now(),
                'resolution_notes' => $validated['resolution_notes'],
                'resolution_type' => $validated['resolution_type'],
            ]);

            // Si es escalamiento, notificar a superiores
            if ($validated['resolution_type'] === 'escalamiento') {
                $observation->update([
                    'is_escalated' => true,
                    'escalated_at' => now(),
                    'escalated_to' => $this->getSupervisorId(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Observación resuelta correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resolving observation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al resolver la observación'
            ], 500);
        }
    }

    public function getByTable($tableId)
    {
        try {
            $observations = Observation::where('voting_table_id', $tableId)
                ->with(['reviewer', 'resolver'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($obs) {
                    return [
                        'id' => $obs->id,
                        'code' => $obs->code,
                        'type' => $obs->type,
                        'description' => $obs->description,
                        'severity' => $obs->severity,
                        'status' => $obs->status,
                        'created_at' => $obs->created_at->format('d/m/Y H:i'),
                        'reviewer_name' => $obs->reviewer->name ?? 'N/A',
                    ];
                });

            return response()->json($observations);

        } catch (\Exception $e) {
            Log::error('Error getting observations: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar observaciones'], 500);
        }
    }

    private function getSupervisorId($votingTableId = null)
    {
        $user = Auth::user();
        
        // Si el usuario tiene asignado un recinto, su supervisor es el coordinador municipal
        $recintoDelegate = RecintoDelegate::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($recintoDelegate) {
            // Buscar coordinador municipal del mismo municipio
            $municipalityId = $recintoDelegate->institution->municipality_id;
            
            $coordinator = User::whereHas('roles', function($q) {
                    $q->where('name', 'coordinador_municipal');
                })
                ->whereHas('reviewerAssignments', function($q) use ($municipalityId) {
                    $q->where('assignable_type', Institution::class)
                      ->whereHasMorph('assignable', [Institution::class], function($subq) use ($municipalityId) {
                          $subq->where('municipality_id', $municipalityId);
                      });
                })->first();
                
            if ($coordinator) return $coordinator->id;
        }

        // Si el usuario tiene asignada una mesa, su supervisor es el delegado del recinto
        $tableDelegate = TableDelegate::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($tableDelegate && $votingTableId) {
            $votingTable = VotingTable::find($votingTableId);
            if ($votingTable) {
                $recintoDelegate = RecintoDelegate::where('institution_id', $votingTable->institution_id)
                    ->where('is_active', true)
                    ->first();
                    
                if ($recintoDelegate) return $recintoDelegate->user_id;
            }
        }

        // Si el usuario es revisor/modificador de un ámbito, escalar al coordinador correspondiente
        $reviewer = Reviewer::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($reviewer) {
            if ($reviewer->assignable_type === Institution::class) {
                // Es revisor de recinto, escalar a coordinador municipal
                $municipalityId = $reviewer->assignable->municipality_id;
                $coordinator = User::whereHas('roles', function($q) {
                        $q->where('name', 'coordinador_municipal');
                    })
                    ->whereHas('reviewerAssignments', function($q) use ($municipalityId) {
                        $q->where('assignable_type', Institution::class)
                          ->whereHasMorph('assignable', [Institution::class], function($subq) use ($municipalityId) {
                              $subq->where('municipality_id', $municipalityId);
                          });
                    })->first();
                    
                if ($coordinator) return $coordinator->id;
            } else {
                // Es revisor de mesa, escalar a delegado de recinto
                $institutionId = $reviewer->assignable->institution_id;
                $recintoDelegate = RecintoDelegate::where('institution_id', $institutionId)
                    ->where('is_active', true)
                    ->first();
                    
                if ($recintoDelegate) return $recintoDelegate->user_id;
            }
        }

        // Último recurso: buscar administrador
        $admin = User::whereHas('roles', function($q) {
            $q->where('name', 'administrador');
        })->first();

        return $admin?->id;
    }
}