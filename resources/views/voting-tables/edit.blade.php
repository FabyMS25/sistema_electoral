{{-- resources/views/voting-tables/edit.blade.php --}}
@extends('layouts.master')

@section('title', 'Editar Mesa de Votación')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('voting-tables.index') }}">Mesas</a> @endslot
        @slot('li_2')
            <a href="{{ route('voting-tables.show', $votingTable->id) }}">
                Mesa {{ $votingTable->oep_code ?? $votingTable->internal_code }}
            </a>
        @endslot
        @slot('title') Editar Mesa @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="card-title mb-0">
                        <i class="ri-edit-line me-1"></i>
                        Editando Mesa: {{ $votingTable->oep_code ?? $votingTable->internal_code }}
                        <small class="text-dark-50 ms-2">(N° {{ $votingTable->number }}{{ $votingTable->letter }})</small>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('voting-tables.update', $votingTable->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @include('voting-tables.partials.form-fields', [
                            'votingTable' => $votingTable,
                            'institutions' => $institutions,
                            'users' => $users,
                        ])

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('voting-tables.show', $votingTable->id) }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="ri-save-line me-1"></i>Actualizar Mesa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('voting-tables.scripts.voting-table-js')
    @stack('scripts')
@endsection
