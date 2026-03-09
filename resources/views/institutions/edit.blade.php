{{-- resources/views/institutions/edit.blade.php --}}
@extends('layouts.master')

@section('title', 'Editar Recinto')

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" />
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
                            'institution'   => $institution,
                            'departments'   => $departments,
                            'statusOptions' => $statusOptions,
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
    @include('institutions.scripts.institution-js')
@endsection
