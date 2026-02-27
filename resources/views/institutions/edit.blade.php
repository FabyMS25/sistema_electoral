{{-- resources/views/institutions/edit.blade.php --}}
@extends('layouts.master')

@section('title')
    Editar Recinto
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" />
    <style>
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
            <a href="{{ route('institutions.index') }}">Recintos</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('institutions.show', $institution->id) }}">{{ $institution->name }}</a>
        @endslot
        @slot('title')
            Editar Recinto
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-edit-line me-1"></i>
                        Editando: {{ $institution->name }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('institutions.update', $institution->id) }}" method="POST" id="institutionForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        @include('institutions.partials.form-fields', [
                            'institution' => $institution,
                            'departments' => $departments
                        ])

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('institutions.show', $institution->id) }}" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Actualizar Recinto
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
    @include('institutions.scripts.institution-js')
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Precargar valores existentes para edición
            const institution = @json($institution);
            
            setTimeout(() => {
                if (institution.locality?.municipality?.province?.department?.id) {
                    document.getElementById('department-field').value = institution.locality.municipality.province.department.id;
                    document.getElementById('department-field').dispatchEvent(new Event('change'));
                    
                    setTimeout(() => {
                        document.getElementById('province-field').value = institution.locality.municipality.province.id;
                        document.getElementById('province-field').dispatchEvent(new Event('change'));
                        
                        setTimeout(() => {
                            document.getElementById('municipality-field').value = institution.locality.municipality.id;
                            document.getElementById('municipality-field').dispatchEvent(new Event('change'));
                            
                            setTimeout(() => {
                                document.getElementById('locality-field').value = institution.locality_id;
                                if (institution.district_id) {
                                    setTimeout(() => {
                                        document.getElementById('district-field').value = institution.district_id;
                                        if (institution.zone_id) {
                                            setTimeout(() => {
                                                document.getElementById('zone-field').value = institution.zone_id;
                                            }, 300);
                                        }
                                    }, 300);
                                }
                            }, 300);
                        }, 300);
                    }, 300);
                }
            }, 500);
        });
    </script>
@endsection