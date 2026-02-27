{{-- resources/views/voting-tables/index.blade.php --}}
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
        .page-link {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #0ab39c;
            background-color: #fff;
            border: 1px solid #e9e9ef;
            border-radius: 0.25rem;
            text-decoration: none;
        }
        .page-item.active .page-link {
            background-color: #0ab39c;
            border-color: #0ab39c;
            color: #fff;
        }
        .badge-count {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
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
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Administración de Mesas de Votación
                    </h4>
                </div>
                
                <div class="card-body">
                    @include('components.alerts')
                    
                    <!-- Stats Cards -->
                    @include('voting-tables.partials.stats-cards', ['votingTables' => $votingTables])

                    <div id="votingTableList">
                        <!-- Barra de acciones -->
                        @include('voting-tables.partials.actions-bar')

                        <!-- Tabla de mesas -->
                        @include('voting-tables.partials.table', ['votingTables' => $votingTables])
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Mostrando {{ $votingTables->firstItem() }} a {{ $votingTables->lastItem() }} de {{ $votingTables->total() }} resultados
                            </div>
                            <div class="pagination-wrap">
                                {{ $votingTables->onEachSide(1)->links('pagination::bootstrap-5') }}
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
@endsection