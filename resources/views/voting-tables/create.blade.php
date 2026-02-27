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
                    <h4 class="card-title mb-0">Nueva Mesa de Votación</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('voting-tables.store') }}" method="POST" id="votingTableForm">
                        @csrf
                        @include('voting-tables.partials.form-fields', [
                            'votingTable' => null,
                            'institutions' => $institutions,
                            'electionTypes' => $electionTypes
                        ])
                        <div class="text-end mt-3">
                            <a href="{{ route('voting-tables.index') }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear Mesa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('voting-tables.scripts.voting-table-js')