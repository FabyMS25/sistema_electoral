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
        @slot('li_1') Votos @endslot
        @slot('title') Registro de Votos - {{ $electionType->name ?? 'Elecciones' }} @endslot
    @endcomponent

    @include('voting-table-votes.partials.filters')

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
                    <p class="text-muted">No se encontraron mesas para los filtros seleccionados.</p>
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
            <div>{{ $votingTables->links() }}</div>
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
    const categoryTotals = {};

    inputs.forEach(input => {
        const value = parseInt(input.value) || 0;
        const category = input.dataset.category;

        if (!categoryTotals[category]) {
            categoryTotals[category] = 0;
        }
        categoryTotals[category] += value;
    });

    console.log(`📊 Mesa ${tableId} - Totales por categoría:`, categoryTotals);

    // Actualizar totales por categoría
    Object.entries(categoryTotals).forEach(([category, total]) => {
        const el = document.getElementById(`total-${category}-${tableId}`);
        if (el) {
            el.textContent = total;
        }
    });

    // Calcular total general (todas las categorías deberían tener el mismo total)
    const totals = Object.values(categoryTotals);
    const totalVotes = totals.length > 0 ? totals[0] : 0;

    const totalEl = document.getElementById(`total-${tableId}`);
    if (totalEl) {
        totalEl.textContent = totalVotes;
    }

    // Actualizar contadores de selección
    updateSelectedCounts(tableId);

    return categoryTotals;
};

function updateSelectedCounts(tableId) {
    const checkboxes = document.querySelectorAll(`#table-${tableId} .observe-checkbox:checked`);
    const categoryCounts = {};

    checkboxes.forEach(cb => {
        const category = cb.dataset.category;
        if (!categoryCounts[category]) categoryCounts[category] = 0;
        categoryCounts[category]++;
    });

    const totalSelected = checkboxes.length;
    const selectedCountEl = document.getElementById(`selected-count-${tableId}`);
    if (selectedCountEl) selectedCountEl.textContent = totalSelected;

    Object.entries(categoryCounts).forEach(([category, count]) => {
        const el = document.getElementById(`selected-${category}-${tableId}`);
        if (el) el.textContent = count;
    });
}
    </script>

    @include('voting-table-votes.scripts.votes-table-js')
    @include('voting-table-votes.scripts.observations-js')
    @include('voting-table-votes.scripts.observations-by-vote-js')
    @include('voting-table-votes.scripts.view-toggle-js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando variables...');

            window.electionTypeId = {{ $electionTypeId ?? 'null' }};
            window.userPermissions = {
                register: {{ auth()->user()->can('register_votes') ? 'true' : 'false' }},
                review: {{ auth()->user()->can('review_votes') ? 'true' : 'false' }},
                correct: {{ auth()->user()->can('correct_votes') ? 'true' : 'false' }},
                validate: {{ auth()->user()->can('validate_votes') ? 'true' : 'false' }},
                observe: {{ auth()->user()->can('create_observations') ? 'true' : 'false' }},
                uploadActa: {{ auth()->user()->can('upload_actas') ? 'true' : 'false' }}
            };

            console.log('✅ Variables inicializadas:', window.userPermissions);

            if (typeof window.initVoteListeners === 'function') {
                window.initVoteListeners();
                console.log('✅ Listeners de votos inicializados');
            }

            if (typeof window.initObservationListeners === 'function') {
                window.initObservationListeners();
            }

            if (typeof window.initViewToggle === 'function') {
                window.initViewToggle();
            }

            function initKeyboardShortcuts() {
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        if (typeof window.saveAllTables === 'function') window.saveAllTables();
                    }
                    if (e.ctrlKey && e.key === 'c') {
                        e.preventDefault();
                        if (typeof window.closeAllTables === 'function') window.closeAllTables();
                    }
                });
            }
            initKeyboardShortcuts();
        });
    </script>
@endsection
