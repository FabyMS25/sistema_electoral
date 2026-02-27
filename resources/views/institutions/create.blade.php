{{-- resources/views/institutions/create.blade.php --}}
@extends('layouts.master')

@section('title')
    Crear Recinto
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
        @slot('title')
            Crear Nuevo Recinto
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-add-line me-1"></i>
                        Nuevo Recinto Electoral
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('institutions.store') }}" method="POST" id="institutionForm">
                        @csrf
                        
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        @include('institutions.partials.form-fields', [
                            'institution' => null,
                            'departments' => $departments
                        ])

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('institutions.index') }}" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Crear Recinto
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
@endsection