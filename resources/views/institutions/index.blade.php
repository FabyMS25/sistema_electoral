{{-- resources/views/institutions/index.blade.php --}}
@extends('layouts.master')

@section('title')
    @lang('translation.list-institutions')
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" />
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
        .required-field label:after {
            content: " *";
            color: red;
        }
        .info-tooltip {
            cursor: help;
            border-bottom: 1px dotted #ccc;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Recintos
        @endslot
        @slot('title')
            Gestión de Recintos Electorales
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-building-line me-1"></i>
                        Administración de Recintos Electorales
                    </h4>
                </div>
                
                <div class="card-body">
                    @include('components.alerts')
                    
                    <!-- Stats Cards -->
                    @include('institutions.partials.stats-cards', ['institutions' => $institutions])

                    <div class="listjs-table" id="institutionList">
                        <!-- Barra de acciones -->
                        @include('institutions.partials.actions-bar')

                        <!-- Tabla de recintos -->
                        @include('institutions.partials.table', ['institutions' => $institutions])
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-end mt-3">
                            {{ $institutions->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    @include('institutions.partials.modal-delete')
    @include('institutions.partials.modal-import')

    @if(session('import_errors'))
        @include('institutions.partials.modal-import-errors')
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    
    @include('institutions.scripts.institution-js')
@endsection