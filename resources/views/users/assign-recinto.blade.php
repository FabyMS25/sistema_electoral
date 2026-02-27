{{-- resources/views/users/assign-recinto.blade.php --}}
@extends('layouts.master')

@section('title')
    Asignar Recinto
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Usuarios
        @endslot
        @slot('li_2')
            <a href="{{ route('users.show', $user) }}">{{ $user->name }} {{ $user->last_name }}</a>
        @endslot
        @slot('title')
            Asignar Recinto
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Asignar Recinto a {{ $user->name }} {{ $user->last_name }}</h4>
                </div>
                <div class="card-body">
                    @if($currentAssignment)
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="ri-information-line fs-16"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <strong>Asignación actual:</strong>
                                <p class="mb-0">{{ $currentAssignment->institution->name }}</p>
                                <small>Desde: {{ $currentAssignment->assigned_at->format('d/m/Y') }}</small>
                                @if($currentAssignment->assigned_until)
                                    <br><small>Hasta: {{ $currentAssignment->assigned_until->format('d/m/Y') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('users.assign-recinto', $user) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="institution_id" class="form-label">Seleccionar Recinto <span class="text-danger">*</span></label>
                                    <select class="form-select @error('institution_id') is-invalid @enderror" 
                                            id="institution_id" name="institution_id" 
                                            data-choices data-choices-search-false required>
                                        <option value="">Seleccione un recinto...</option>
                                        @foreach($recintos as $recinto)
                                        <option value="{{ $recinto->id }}" 
                                            {{ old('institution_id', $currentAssignment->institution_id ?? '') == $recinto->id ? 'selected' : '' }}>
                                            {{ $recinto->name }} - {{ $recinto->locality->municipality->name ?? '' }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('institution_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="assigned_until" class="form-label">Fecha de Fin (opcional)</label>
                                    <input type="date" class="form-control @error('assigned_until') is-invalid @enderror" 
                                           id="assigned_until" name="assigned_until" 
                                           value="{{ old('assigned_until', $currentAssignment->assigned_until ?? '') }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('assigned_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="ri-information-line align-middle me-1"></i>
                            Al asignar un nuevo recinto, la asignación anterior se desactivará automáticamente.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-soft-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="ri-save-line align-middle me-1"></i> Guardar Asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection