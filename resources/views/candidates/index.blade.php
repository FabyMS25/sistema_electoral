@extends('layouts.master')

@section('title')
    @lang('translation.list-candidates')
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" />
    <style>
        .color-preview  { width:30px; height:30px; border-radius:4px; border:1px solid #e9e9ef; display:inline-block; }
        .stats-toggle   { cursor:pointer; user-select:none; }
        .stats-toggle i { transition:transform .3s; }
        .stats-toggle.collapsed i { transform:rotate(-90deg); }
        #statsContainer { transition:all .3s; overflow:hidden; }
        .pagination-info { font-size:.875rem; color:#6c757d; }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Tables @endslot
        @slot('title') Candidatos @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                {{-- ── Card header ── --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-user-star-line me-1"></i> Administración de Candidatos
                    </h4>
                    <div class="stats-toggle" onclick="toggleStats()" id="statsToggle">
                        <span class="badge bg-light text-dark p-1">
                            <i class="ri-arrow-down-s-line me-1"></i> Estadísticas
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @include('components.alerts')

                    {{-- ── Stats ── --}}
                    <div id="statsContainer" class="mb-2">
                        @include('candidates.partials.stats-cards')
                    </div>

                    {{-- ── Actions & Filters ── --}}
                    @include('candidates.partials.actions-bar')

                    {{-- ── Table ── --}}
                    <div id="candidateList">
                        @include('candidates.partials.table')

                        {{-- ── Pagination row ── --}}
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">

                            {{-- per-page selector --}}
                            <select class="form-select form-select-sm" style="width:auto;"
                                    onchange="window.location.href=this.value">
                                @foreach([20, 50, 100, 200] as $pp)
                                    <option value="{{ route('candidates.index', ['per_page' => $pp] + request()->except('per_page','page')) }}"
                                        {{ request('per_page', 20) == $pp ? 'selected' : '' }}>
                                        {{ $pp }} por página
                                    </option>
                                @endforeach
                            </select>

                            <div class="pagination-info">
                                @if($candidates instanceof \Illuminate\Pagination\LengthAwarePaginator && $candidates->total() > 0)
                                    Mostrando {{ $candidates->firstItem() }}–{{ $candidates->lastItem() }}
                                    de {{ $candidates->total() }} resultados
                                @else
                                    Sin resultados
                                @endif
                            </div>

                            <div class="pagination-wrap">
                                {{ $candidates->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Modals ── --}}
    @include('candidates.partials.modal-view')
    @include('candidates.partials.modal-form')
    @include('candidates.partials.modal-delete')
    @include('candidates.partials.modal-import')
    @if(session('import_errors'))
        @include('candidates.partials.modal-import-errors')
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    @include('candidates.scripts.candidates-js')

    <script>
    // ── Stats toggle (persisted in localStorage) ──────────────────
    function toggleStats() {
        const container = document.getElementById('statsContainer');
        const btn       = document.getElementById('statsToggle');
        const icon      = btn.querySelector('i');
        const hide      = !container.classList.contains('d-none');

        container.classList.toggle('d-none', hide);
        btn.classList.toggle('collapsed', hide);
        icon.className = hide ? 'ri-arrow-right-s-line me-1' : 'ri-arrow-down-s-line me-1';
        localStorage.setItem('candidateStatsVisible', String(!hide));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const visible = localStorage.getItem('candidateStatsVisible');
        if (visible === 'false') {
            const container = document.getElementById('statsContainer');
            const btn       = document.getElementById('statsToggle');
            if (container && btn) {
                container.classList.add('d-none');
                btn.classList.add('collapsed');
                btn.querySelector('i').className = 'ri-arrow-right-s-line me-1';
            }
        }
    });
    </script>
@endsection
