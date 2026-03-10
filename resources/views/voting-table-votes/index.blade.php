{{-- resources/views/voting-table-votes/index.blade.php --}}
@extends('layouts.master')
@section('title')
    Registro de Votos
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <style>
        .table-card.status-observada  { border-left: 4px solid #f06548; }
        .table-card.status-cerrada    { border-left: 4px solid #8590a5; opacity: .92; }
        .table-card.status-escrutada  { border-left: 4px solid #0ab39c; }
        .table-card.status-transmitida{ border-left: 4px solid #405189; }
        .table-card.status-anulada    { border-left: 4px solid #212529; opacity: .8; }
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .inconsistency-warning {
            background-color: #fff3cd;
            border: 1px solid #ffe69c;
            color: #664d03;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Registro de Votos @endslot
        @slot('title') {{ $electionType->name ?? 'Elecciones' }} @endslot
    @endcomponent
    @include('voting-table-votes.partials.filters')
    @include('voting-table-votes.partials.quick-stats')
    <div class="row mb-3 g-3">
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card bg-primary text-white h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Mesas</div>
                            <h3 class="mb-0 fw-bold">{{ $votingTables->total() }}</h3>
                        </div>
                        <i class="ri-table-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card bg-info text-white h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Habilitados</div>
                            <h3 class="mb-0 fw-bold">{{ number_format($totals['expected'] ?? 0) }}</h3>
                        </div>
                        <i class="ri-group-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl-2">
            <div class="card bg-warning text-white h-100">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Participación</div>
                            <h3 class="mb-0 fw-bold">{{ $totals['participation'] ?? 0 }}%</h3>
                        </div>
                        <i class="ri-percent-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        @php
            $catCardColors = ['success', 'secondary', 'danger', 'info', 'warning', 'primary', 'dark'];
            $colorIdx = 0;
        @endphp
        @foreach($typeCategories as $tc)
            @php
                $code  = $tc->electionCategory->code;
                $name  = $tc->electionCategory->name;
                $total = $totals['by_category'][$code] ?? 0;
                $color = $catCardColors[$colorIdx % count($catCardColors)];
                $colorIdx++;
            @endphp
            <div class="col-6 col-md-3 col-xl-2">
                <div class="card bg-{{ $color }} text-white h-100">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">{{ $name }}</div>
                                <h3 class="mb-0 fw-bold">{{ number_format($total) }}</h3>
                                <small class="text-white-50 d-block">votos válidos</small>
                            </div>
                            <i class="ri-bar-chart-line fs-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            @forelse($votingTables->items() as $table)
                @include('voting-table-votes.partials.table', [
                    'table'                => $table,
                    'candidatesByCategory' => $candidatesByCategory,
                    'statusLabels'         => $statusLabels,
                    'validationLabels'     => $validationLabels,
                    'permissions'          => $permissions,
                    'categoryColorMap'     => $categoryColorMap ?? [],
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
    <div class="mt-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="pagination-info">
                Mostrando {{ $votingTables->firstItem() ?? 0 }}–{{ $votingTables->lastItem() ?? 0 }}
                de {{ $votingTables->total() }} mesas
            </div>
            <div>{{ $votingTables->links() }}</div>
        </div>
    </div>
    @endif

    @include('voting-table-votes.partials.quick-actions')
    {{-- ── Modals ── --}}
    @include('voting-table-votes.partials.modals.observation-modal')
    @include('voting-table-votes.partials.modals.upload-acta-modal')
    @include('voting-table-votes.partials.modals.validation-modal')
    @include('voting-table-votes.partials.modals.view-actas-modal')
    @include('voting-table-votes.partials.modals.confirm-close')
    @include('voting-table-votes.partials.modals.bulk-update')

@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        window.electionTypeId = {{ $electionTypeId ?? 'null' }};

        window.userPermissions = {
            register:   {{ ($permissions['can_register']    ?? false) ? 'true' : 'false' }},
            review:     {{ ($permissions['can_review']      ?? false) ? 'true' : 'false' }},
            correct:    {{ ($permissions['can_correct']     ?? false) ? 'true' : 'false' }},
            validate:   {{ ($permissions['can_validate']    ?? false) ? 'true' : 'false' }},
            observe:    {{ ($permissions['can_observe']     ?? false) ? 'true' : 'false' }},
            uploadActa: {{ ($permissions['can_upload_acta'] ?? false) ? 'true' : 'false' }},
            close:      {{ ($permissions['can_close']       ?? false) ? 'true' : 'false' }},
            reopen:     {{ ($permissions['can_reopen']      ?? false) ? 'true' : 'false' }},
        };
    </script>
    @include('voting-table-votes.scripts.votes-table-js')
    @include('voting-table-votes.scripts.observations-js')
    @include('voting-table-votes.scripts.observations-by-vote-js')
    @include('voting-table-votes.scripts.view-toggle-js')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initVoteListeners        === 'function') window.initVoteListeners();
        if (typeof window.initObservationListeners === 'function') window.initObservationListeners();
        if (typeof window.initViewToggle           === 'function') window.initViewToggle();
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                new bootstrap.Popover(el, { sanitize: false });
            });
        }
    });
    </script>
@endsection
