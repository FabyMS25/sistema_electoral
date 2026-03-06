@extends('layouts.master')

@section('title')
    @lang('translation.list-candidates')
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .image-preview-container {
            margin-top: 10px;
            text-align: center;
        }
        .image-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
            margin: 5px;
        }
        .stats-toggle {
            cursor: pointer;
            user-select: none;
        }
        .stats-toggle i {
            transition: transform 0.3s ease;
        }
        .stats-toggle.collapsed i {
            transform: rotate(-90deg);
        }
        #statsContainer {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        #statsContainer.collapsed {
            display: none;
        }
        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .color-preview {
            width: 30px;
            height: 30px;
            border-radius: 4px;
            border: 1px solid #e9e9ef;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Tables
        @endslot
        @slot('title')
            Candidatos
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-user-star-line me-1"></i>
                        Administración de Candidatos
                    </h4>
                    <div class="stats-toggle" onclick="toggleStats()" id="statsToggle">
                        <span class="badge bg-light text-dark p-1">
                            <i class="ri-arrow-down-s-line me-1"></i>
                            Estadísticas
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    @include('components.alerts')

                    <div id="statsContainer" class="mb-2">
                        @include('candidates.partials.stats-cards', ['candidates' => $candidates])
                    </div>

                    @include('candidates.partials.actions-bar')

                    <div id="candidateList">
                        @include('candidates.partials.table')

                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                                <option value="{{ route('candidates.index', ['per_page' => 20] + request()->except('per_page', 'page')) }}" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="{{ route('candidates.index', ['per_page' => 50] + request()->except('per_page', 'page')) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="{{ route('candidates.index', ['per_page' => 100] + request()->except('per_page', 'page')) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="{{ route('candidates.index', ['per_page' => 200] + request()->except('per_page', 'page')) }}" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                            </select>
                            <div class="pagination-info">
                                Mostrando {{ $candidates->firstItem() }} a {{ $candidates->lastItem() }} de {{ $candidates->total() }} resultados
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

    <!-- Modals -->
    @include('candidates.partials.modal-view')
    @include('candidates.partials.modal-form')
    @include('candidates.partials.modal-delete')
    @include('candidates.partials.modal-import')
    @if(session('import_errors'))
        @include('candidates.partials.modal-import-errors')
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    @include('candidates.scripts.candidates-js')
    <script>
        function toggleStats() {
            const statsContainer = document.getElementById('statsContainer');
            const toggleBtn = document.getElementById('statsToggle');
            const icon = toggleBtn.querySelector('i');
            statsContainer.classList.toggle('collapsed');
            toggleBtn.classList.toggle('collapsed');
            if (statsContainer.classList.contains('collapsed')) {
                icon.classList.remove('ri-arrow-down-s-line');
                icon.classList.add('ri-arrow-right-s-line');
            } else {
                icon.classList.remove('ri-arrow-right-s-line');
                icon.classList.add('ri-arrow-down-s-line');
            }
            localStorage.setItem('showStats', !statsContainer.classList.contains('collapsed'));
        }
        document.addEventListener('DOMContentLoaded', function() {
            const showStats = localStorage.getItem('showStats');
            const statsContainer = document.getElementById('statsContainer');
            const toggleBtn = document.getElementById('statsToggle');
            if (statsContainer && toggleBtn && showStats === 'false') {
                const icon = toggleBtn.querySelector('i');
                statsContainer.classList.add('collapsed');
                toggleBtn.classList.add('collapsed');
                icon.classList.remove('ri-arrow-down-s-line');
                icon.classList.add('ri-arrow-right-s-line');
            }
        });
    </script>
@endsection
