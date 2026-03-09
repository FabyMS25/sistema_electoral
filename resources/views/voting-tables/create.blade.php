{{-- resources/views/voting-tables/create.blade.php --}}
@extends('layouts.master')

@section('title', 'Crear Nueva Mesa de Votación')

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') <a href="{{ route('voting-tables.index') }}">Mesas</a> @endslot
        @slot('title') Crear Nueva Mesa @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="ri-add-line me-1"></i>
                        Nueva Mesa de Votación
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('voting-tables.store') }}" method="POST" id="votingTableForm">
                        @csrf

                        @include('voting-tables.partials.form-fields', [
                            'votingTable' => null,
                            'institutions' => $institutions,
                            'users' => $users,
                        ])

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('voting-tables.index') }}" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>Crear Mesa
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
