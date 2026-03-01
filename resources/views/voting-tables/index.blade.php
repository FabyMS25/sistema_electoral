@extends('layouts.master')
@section('title')
    @lang('translation.list-voting-tables')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <style>
        .stats-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            gap: 5px;
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
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Mesas
        @endslot
        @slot('title')
            Gestión de Mesas Electorales
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Listas de Mesas de Votación
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
                        @include('voting-tables.partials.stats-cards', ['votingTables' => $votingTables])
                    </div>
                    @include('voting-tables.partials.actions-bar')
                    <div id="votingTableList">
                        @include('voting-tables.partials.table', ['votingTables' => $votingTables])
                        <div class="d-flex justify-content-between align-items-center">
                            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                                <option value="{{ route('voting-tables.index', ['per_page' => 20] + request()->except('per_page', 'page')) }}" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                                <option value="{{ route('voting-tables.index', ['per_page' => 50] + request()->except('per_page', 'page')) }}" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="{{ route('voting-tables.index', ['per_page' => 100] + request()->except('per_page', 'page')) }}" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                <option value="{{ route('voting-tables.index', ['per_page' => 200] + request()->except('per_page', 'page')) }}" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                            </select>
                            <div class="pagination-info">
                                Mostrando {{ $votingTables->firstItem() }} a {{ $votingTables->lastItem() }} de {{ $votingTables->total() }} resultados
                            </div>                      
                            <div class="pagination-wrap">
                                    {{ $votingTables->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    @include('voting-tables.partials.modal-delete')
    @include('voting-tables.partials.modal-import')
    @if(session('import_errors'))
        @include('voting-tables.partials.modal-import-errors')
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    @include('voting-tables.scripts.voting-table-js')    
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