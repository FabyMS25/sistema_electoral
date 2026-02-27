{{-- resources/views/voting-tables/edit.blade.php --}}
@extends('layouts.master')

@section('title')
    Editar Mesa
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" />
    <style>
        .required-field label:after {
            content: " *";
            color: red;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0ab39c;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('voting-tables.index') }}">Mesas</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('voting-tables.show', $votingTable->id) }}">Mesa {{ $votingTable->code }}</a>
        @endslot
        @slot('title')
            Editar Mesa
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-edit-line me-1"></i>
                        Editando Mesa: {{ $votingTable->code }} - N° {{ $votingTable->number }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('voting-tables.update', $votingTable->id) }}" method="POST" id="votingTableForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        @include('voting-tables.partials.form-fields', [
                            'votingTable' => $votingTable,
                            'institutions' => $institutions,
                            'electionTypes' => $electionTypes
                        ])

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('voting-tables.show', $votingTable->id) }}" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Actualizar Mesa
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    @include('voting-tables.scripts.voting-table-js')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Precargar valores existentes para edición
            const votingTable = @json($votingTable);
            
            // Si hay valores específicos que necesitan carga adicional
            if (votingTable.institution_id) {
                setTimeout(() => {
                    if (typeof Choices !== 'undefined') {
                        const institutionSelect = document.getElementById('institution_id-field');
                        if (institutionSelect && institutionSelect._choices) {
                            institutionSelect._choices.setChoiceByValue(votingTable.institution_id.toString());
                        }
                        
                        const electionTypeSelect = document.getElementById('election_type_id-field');
                        if (electionTypeSelect && electionTypeSelect._choices) {
                            electionTypeSelect._choices.setChoiceByValue(votingTable.election_type_id?.toString() || '');
                        }
                    }
                }, 300);
            }
        });
    </script>
@endsection