{{-- resources/views/voting-table-votes/index.blade.php --}}
@extends('layouts.master')
@section('title')
    Registro de Votos - {{ $electionType->name ?? 'Elecciones' }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <style>
        .vote-input:focus {
            border-color: #0ab39c;
            box-shadow: 0 0 0 0.2rem rgba(10, 179, 156, 0.25);
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .quick-actions .btn-group-vertical {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .pagination-info {
            padding: 8px 0;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Votos
        @endslot
        @slot('title')
            Registro de Votos - {{ $electionType->name ?? 'Elecciones' }}
        @endslot
    @endcomponent

    <div class="filter-section">
        <form method="GET" action="{{ route('voting-table-votes.index') }}" class="row g-3">
            <input type="hidden" name="election_type_id" value="{{ $electionTypeId }}">
            <div class="col-md-3">
                <label class="form-label">Institución/Recinto</label>
                <select name="institution_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($institutions as $institution)
                        <option value="{{ $institution->id }}" {{ request('institution_id') == $institution->id ? 'selected' : '' }}>
                            {{ $institution->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado Mesa</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Validación</label>
                <select name="validation_status" class="form-select">
                    <option value="">Todos</option>
                    @foreach($validationLabels as $value => $label)
                        <option value="{{ $value }}" {{ request('validation_status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">N° Mesa</label>
                <input type="number" name="table_number" class="form-control" value="{{ request('table_number') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-filter-3-line me-1"></i>Filtrar
                </button>
                <a href="{{ route('voting-table-votes.index', ['election_type_id' => $electionTypeId]) }}" class="btn btn-secondary">
                    <i class="ri-refresh-line me-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
    <div class="row mb-2">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Mesas</h6>
                            <h3 class="mb-0">{{ $votingTables->total() }}</h3>
                        </div>
                        <i class="ri-table-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Votos</h6>
                            <h3 class="mb-0">{{ number_format($totals['total'] ?? 0) }}</h3>
                        </div>
                        <i class="ri-bar-chart-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Habilitados</h6>
                            <h3 class="mb-0">{{ number_format($totals['expected'] ?? 0) }}</h3>
                        </div>
                        <i class="ri-group-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Participación</h6>
                            <h3 class="mb-0">{{ $totals['participation'] ?? 0 }}%</h3>
                        </div>
                        <i class="ri-percent-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @forelse($votingTables->items() as $table)
                @include('voting-table-votes.partials.table', [
                    'table' => $table,
                    'candidatesByCategory' => $candidatesByCategory,
                    'statusLabels' => $statusLabels,
                    'validationLabels' => $validationLabels,
                    'userCan' => [
                        'register' => auth()->user()->can('register_votes'),
                        'review' => auth()->user()->can('review_votes'),
                        'correct' => auth()->user()->can('correct_votes'),
                        'validate' => auth()->user()->can('validate_votes'),
                        'observe' => auth()->user()->can('create_observations'),
                        'upload_acta' => auth()->user()->can('upload_actas'),
                        'close' => auth()->user()->can('close_tables')
                    ]
                ])
            @empty
                <div class="text-center py-5">
                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                        colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                    </lord-icon>
                    <h5 class="mt-3">No hay mesas disponibles</h5>
                    <p class="text-muted">
                        No se encontraron mesas para los filtros seleccionados.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
    @if($votingTables->hasPages())
    <div class="pagination-wrapper">
        <div class="d-flex justify-content-between">
            <div class="pagination-info">
                Mostrando {{ $votingTables->firstItem() ?? 0 }} - {{ $votingTables->lastItem() ?? 0 }} de {{ $votingTables->total() }} mesas
            </div>
            <div class="pagination-info">
                Página {{ $votingTables->currentPage() }} de {{ $votingTables->lastPage() }}
            </div>
            <div>
                {{ $votingTables->links() }}
            </div>
        </div>
    </div>
    @endif

    @if($votingTables->total() > 0)
        <div class="quick-actions">
            <div class="btn-group-vertical">
                <button class="btn btn-success" id="saveAllBtn" title="Guardar todas (Ctrl+S)">
                    <i class="ri-save-line"></i> Guardar Todo
                </button>
                <button class="btn btn-warning" id="closeAllBtn" title="Cerrar todas (Ctrl+C)">
                    <i class="ri-lock-line"></i> Cerrar Todo
                </button>
            </div>
        </div>
    @endif
    {{-- Modales --}}
    @include('voting-table-votes.partials.modals.observation-modal')
    @include('voting-table-votes.partials.modals.upload-acta-modal')
    @include('voting-table-votes.partials.modals.validation-modal')
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        var currentObservationTable = null;
        var currentActaTable = null;
        var currentValidationTable = null;
        var pendingTables = new Set();
        var saveTimeouts = {};

        window.updateTableTotals = function(tableId) {
            console.log('🔄 Actualizando totales para mesa:', tableId);

            const inputs = document.querySelectorAll(`#table-${tableId} .vote-input`);
            let totalAlcalde = 0;
            let totalConcejal = 0;

            inputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                if (input.dataset.category === 'alcalde') {
                    totalAlcalde += value;
                } else if (input.dataset.category === 'concejal') {
                    totalConcejal += value;
                }
            });

            const totalAlcaldeEl = document.getElementById(`total-alcalde-${tableId}`);
            const totalConcejalEl = document.getElementById(`total-concejal-${tableId}`);
            const totalEl = document.getElementById(`total-${tableId}`);

            if (totalAlcaldeEl) totalAlcaldeEl.textContent = totalAlcalde;
            if (totalConcejalEl) totalConcejalEl.textContent = totalConcejal;
            if (totalEl) totalEl.textContent = totalAlcalde + totalConcejal;

            console.log(`📊 Mesa ${tableId} - Alcaldes: ${totalAlcalde}, Concejales: ${totalConcejal}, Total: ${totalAlcalde + totalConcejal}`);

            return { totalAlcalde, totalConcejal };
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando variables...');

            // Verificar que las variables existen
            window.electionTypeId = {{ $electionTypeId ?? 'null' }};
            window.userPermissions = {
                register: {{ auth()->user()->can('register_votes') ? 'true' : 'false' }},
                review: {{ auth()->user()->can('review_votes') ? 'true' : 'false' }},
                correct: {{ auth()->user()->can('correct_votes') ? 'true' : 'false' }},
                validate: {{ auth()->user()->can('validate_votes') ? 'true' : 'false' }},
                observe: {{ auth()->user()->can('create_observations') ? 'true' : 'false' }},
                uploadActa: {{ auth()->user()->can('upload_actas') ? 'true' : 'false' }}
            };
            console.log('✅ Variables inicializadas:', {
                electionTypeId: window.electionTypeId,
                userPermissions: window.userPermissions
            });

            // 🔴 IMPORTANTE: Inicializar los listeners después de que el DOM esté listo
            if (typeof window.initVoteListeners === 'function') {
                window.initVoteListeners();
                console.log('✅ Listeners de votos inicializados');
            } else {
                console.error('❌ Función initVoteListeners no encontrada');
            }

            if (typeof window.initObservationListeners === 'function') {
                window.initObservationListeners();
            }

            if (typeof window.initViewToggle === 'function') {
                window.initViewToggle();
            }

            // Inicializar atajos de teclado
            function initKeyboardShortcuts() {
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        if (typeof window.saveAllTables === 'function') {
                            window.saveAllTables();
                        }
                    }
                    if (e.ctrlKey && e.key === 'c') {
                        e.preventDefault();
                        if (typeof window.closeAllTables === 'function') {
                            window.closeAllTables();
                        }
                    }
                });
            }
            initKeyboardShortcuts();
        });
    </script>
    @include('voting-table-votes.scripts.votes-table-js')
    @include('voting-table-votes.scripts.observations-js')
    @include('voting-table-votes.scripts.observations-by-vote-js')
    @include('voting-table-votes.scripts.view-toggle-js')

@endsection
