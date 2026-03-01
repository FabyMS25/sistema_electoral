{{-- resources/views/voting-tables/create.blade.php --}}
@extends('layouts.master')

@section('title', 'Crear Mesa')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Mesas @endslot
        @slot('li_2') <a href="{{ route('voting-tables.index') }}">Lista de Mesas</a> @endslot
        @slot('title') Crear Nueva Mesa @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-add-line me-1"></i>
                        Nueva Mesa de Votación
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('voting-tables.store') }}" method="POST" id="votingTableForm">
                        @csrf
                        
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        @include('voting-tables.partials.form-fields', [
                            'votingTable' => null,
                            'institutions' => $institutions,
                            'electionTypes' => $electionTypes,
                            'statusOptions' => $statusOptions 
                        ])
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('voting-tables.index') }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>
                                Crear Mesa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
    @include('voting-tables.scripts.voting-table-js')
@endsection