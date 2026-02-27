{{-- resources/views/voting-table-votes/partials/filters.blade.php --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('voting-table-votes.index') }}" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Recinto</label>
                        <select name="institution_id" class="form-select" id="institutionFilter">
                            <option value="">Todos los recintos</option>
                            @foreach($institutions as $institution)
                                <option value="{{ $institution->id }}" 
                                    {{ ($institutionId ?? '') == $institution->id ? 'selected' : '' }}>
                                    {{ $institution->name }} ({{ $institution->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tipo de Elección</label>
                        <select name="election_type_id" class="form-select" id="electionTypeFilter">
                            @foreach($electionTypes as $type)
                                <option value="{{ $type->id }}" 
                                    {{ ($electionTypeId ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} - {{ \Carbon\Carbon::parse($type->election_date)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-filter-line me-1"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>