{{-- resources/views/voting-table-votes/index.blade.php --}}
@extends('layouts.master')

@section('title')
    Registro de Votos
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
    <style>
        
        .role-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .observation-badge {
            cursor: pointer;
        }
        .validation-status {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Votos
        @endslot
        @slot('title')
            Registro de Votos por Mesa
        @endslot
    @endcomponent

    @include('voting-table-votes.partials.filters')
    @include('voting-table-votes.partials.quick-stats')
    @include('voting-table-votes.partials.summary-cards')

    @if(request()->has('institution_id') || request()->has('status') || request()->has('table_number'))
        <div class="alert alert-info">
            <i class="ri-information-line me-1"></i>
            Mostrando resultados para los filtros aplicados. 
            <a href="{{ route('voting-table-votes.index', ['election_type_id' => $electionTypeId]) }}" class="alert-link">
                Limpiar filtros
            </a>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Mesas de Votación
                        <span class="badge bg-primary ms-2">{{ $votingTables->count() }} encontradas</span>
                    </h5>
                    <div class="action-buttons">
                        @can('review_votes')
                            <button class="btn btn-sm btn-info" id="reviewAllBtn" title="Revisar todas">
                                <i class="ri-eye-line me-1"></i>Revisar
                            </button>
                        @endcan
                        @can('validate_votes')
                            <button class="btn btn-sm btn-success" id="validateAllBtn" title="Validar todas">
                                <i class="ri-check-line me-1"></i>Validar
                            </button>
                        @endcan
                        @can('correct_votes')
                            <button class="btn btn-sm btn-warning" id="correctAllBtn" title="Corregir todas">
                                <i class="ri-refund-line me-1"></i>Corregir
                            </button>
                        @endcan
                        @can('register_votes')
                            <button class="btn btn-sm btn-success" id="saveAllBtn">
                                <i class="ri-save-line me-1"></i>Guardar
                            </button>
                            <button class="btn btn-sm btn-warning" id="closeAllBtn">
                                <i class="ri-lock-line me-1"></i>Cerrar
                            </button>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @forelse($votingTables as $table)
                        @include('voting-table-votes.partials.table-row', [
                            'table' => $table,
                            'userCan' => [
                                'register' => auth()->user()->hasPermission('register_votes'),
                                'review' => auth()->user()->hasPermission('review_votes'),
                                'correct' => auth()->user()->hasPermission('correct_votes'),
                                'validate' => auth()->user()->hasPermission('validate_votes'),
                                'observe' => auth()->user()->hasPermission('create_observations'),
                                'upload_acta' => auth()->user()->hasPermission('upload_actas'),
                                'close' => auth()->user()->hasPermission('close_tables')
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
                                @if(request()->has('institution_id') || request()->has('status') || request()->has('table_number'))
                                    <br>
                                    <a href="{{ route('voting-table-votes.index', ['election_type_id' => $electionTypeId]) }}" class="btn btn-link">
                                        Limpiar filtros
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    @if($votingTables->isNotEmpty())
        @include('voting-table-votes.partials.quick-actions')
    @endif

    <!-- Modales -->
    @include('voting-table-votes.partials.modals.observation-modal')
    @include('voting-table-votes.partials.modals.upload-acta-modal')
    @include('voting-table-votes.partials.modals.validation-modal')
    @include('voting-table-votes.partials.modals.confirm-close')
    @include('voting-table-votes.partials.modals.bulk-update')
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    @include('voting-table-votes.scripts.votes-js')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.candidates = @json($candidates);
            window.electionTypeId = {{ $electionTypeId ?? 'null' }};
            window.institutionId = {{ $institutionId ?? 'null' }};
            window.userPermissions = {
                register: {{ auth()->user()->hasPermission('register_votes') ? 'true' : 'false' }},
                review: {{ auth()->user()->hasPermission('review_votes') ? 'true' : 'false' }},
                correct: {{ auth()->user()->hasPermission('correct_votes') ? 'true' : 'false' }},
                validate: {{ auth()->user()->hasPermission('validate_votes') ? 'true' : 'false' }},
                observe: {{ auth()->user()->hasPermission('create_observations') ? 'true' : 'false' }},
                uploadActa: {{ auth()->user()->hasPermission('upload_actas') ? 'true' : 'false' }}
            };
        });
    </script>
@endsection