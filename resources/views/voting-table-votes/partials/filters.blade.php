{{-- resources/views/voting-table-votes/partials/filters.blade.php --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="ri-filter-3-line me-1"></i>
                    Filtros de Búsqueda
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('voting-table-votes.index') }}" id="filterForm" class="row g-3">
                    <!-- Fila 1: Filtros principales -->
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Recinto/Institución</label>
                            <select name="institution_id" class="form-select select2" id="institutionFilter">
                                <option value="">Todos los recintos</option>
                                @foreach($institutions as $institution)
                                    <option value="{{ $institution->id }}" 
                                        data-code="{{ $institution->code }}"
                                        {{ ($institutionId ?? '') == $institution->id ? 'selected' : '' }}>
                                        {{ $institution->name }} ({{ $institution->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tipo de Elección</label>
                            <select name="election_type_id" class="form-select select2" id="electionTypeFilter">
                                @foreach($electionTypes as $type)
                                    <option value="{{ $type->id }}" 
                                        {{ ($electionTypeId ?? '') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} - {{ \Carbon\Carbon::parse($type->election_date)->format('d/m/Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Estado de Mesa</label>
                            <select name="status" class="form-select" id="statusFilter">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="en_proceso" {{ request('status') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                <option value="activo" {{ request('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="cerrado" {{ request('status') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                                <option value="observado" {{ request('status') == 'observado' ? 'selected' : '' }}>Observado</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label fw-bold">N° Mesa</label>
                            <input type="number" name="table_number" class="form-control" 
                                   placeholder="Ej: 1, 2, 3..." value="{{ request('table_number') }}">
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-flex gap-2 w-100">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="ri-search-line me-1"></i>
                                    Buscar
                                </button>
                                <a href="{{ route('voting-table-votes.index', ['election_type_id' => $electionTypeId ?? '']) }}" 
                                   class="btn btn-secondary" title="Limpiar filtros">
                                    <i class="ri-refresh-line"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Filtros avanzados (colapsables) -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <a class="text-muted" data-bs-toggle="collapse" href="#advancedFilters" role="button">
                                <i class="ri-arrow-down-s-line me-1"></i>
                                Filtros avanzados
                            </a>
                        </div>
                    </div>

                    <div class="collapse" id="advancedFilters">
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Código de Mesa</label>
                                <input type="text" name="table_code" class="form-control" 
                                       placeholder="Ej: MESA-001" value="{{ request('table_code') }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Desde (Apellido)</label>
                                <input type="text" name="from_name" class="form-control" 
                                       placeholder="Apellido inicial" value="{{ request('from_name') }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Hasta (Apellido)</label>
                                <input type="text" name="to_name" class="form-control" 
                                       placeholder="Apellido final" value="{{ request('to_name') }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tipo de Mesa</label>
                                <select name="table_type" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="mixta" {{ request('table_type') == 'mixta' ? 'selected' : '' }}>Mixta</option>
                                    <option value="masculina" {{ request('table_type') == 'masculina' ? 'selected' : '' }}>Masculina</option>
                                    <option value="femenina" {{ request('table_type') == 'femenina' ? 'selected' : '' }}>Femenina</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Votos Mínimos</label>
                                <input type="number" name="min_votes" class="form-control" 
                                       placeholder="Mínimo" value="{{ request('min_votes') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Votos Máximos</label>
                                <input type="number" name="max_votes" class="form-control" 
                                       placeholder="Máximo" value="{{ request('max_votes') }}">
                            </div>
                            
                            <div class="col-md-2">
                                <label class="form-label fw-bold">Participación %</label>
                                <select name="participation" class="form-select">
                                    <option value="">Cualquier</option>
                                    <option value="alta" {{ request('participation') == 'alta' ? 'selected' : '' }}>Alta (>75%)</option>
                                    <option value="media" {{ request('participation') == 'media' ? 'selected' : '' }}>Media (50-75%)</option>
                                    <option value="baja" {{ request('participation') == 'baja' ? 'selected' : '' }}>Baja (<50%)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Con Observaciones</label>
                                <select name="has_observations" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="1" {{ request('has_observations') == '1' ? 'selected' : '' }}>Con observaciones</option>
                                    <option value="0" {{ request('has_observations') == '0' ? 'selected' : '' }}>Sin observaciones</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Ordenar por</label>
                                <select name="sort_by" class="form-select">
                                    <option value="number" {{ request('sort_by') == 'number' ? 'selected' : '' }}>Número de Mesa</option>
                                    <option value="registered_citizens" {{ request('sort_by') == 'registered_citizens' ? 'selected' : '' }}>Ciudadanos</option>
                                    <option value="computed_records" {{ request('sort_by') == 'computed_records' ? 'selected' : '' }}>Votos Computados</option>
                                    <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Estado</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-search-line me-1"></i>
                                    Aplicar filtros avanzados
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    .filter-badge {
        background: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .filter-badge .remove-filter {
        margin-left: 0.5rem;
        cursor: pointer;
        color: #dc3545;
    }
    .filter-badge .remove-filter:hover {
        color: #bb2d3b;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Select2 para mejor experiencia
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccionar...',
            allowClear: true
        });

        // Auto-submit al cambiar filtros principales (opcional)
        $('#institutionFilter, #electionTypeFilter, #statusFilter').on('change', function() {
            $('#filterForm').submit();
        });

        // Mostrar filtros activos
        const activeFilters = [];
        
        @if(request('institution_id'))
            activeFilters.push({
                name: 'Recinto',
                value: $('#institutionFilter option:selected').text(),
                param: 'institution_id'
            });
        @endif
        
        @if(request('status'))
            activeFilters.push({
                name: 'Estado',
                value: $('#statusFilter option:selected').text(),
                param: 'status'
            });
        @endif
        
        @if(request('table_number'))
            activeFilters.push({
                name: 'Mesa N°',
                value: '{{ request('table_number') }}',
                param: 'table_number'
            });
        @endif

        if (activeFilters.length > 0) {
            let filterHtml = '<div class="mt-2"><strong>Filtros activos:</strong> ';
            activeFilters.forEach(filter => {
                filterHtml += `<span class="filter-badge">
                    ${filter.name}: ${filter.value}
                    <span class="remove-filter" data-param="${filter.param}">
                        <i class="ri-close-line"></i>
                    </span>
                </span>`;
            });
            filterHtml += '</div>';
            $('#filterForm .card-body').append(filterHtml);

            $('.remove-filter').on('click', function() {
                const param = $(this).data('param');
                const url = new URL(window.location.href);
                url.searchParams.delete(param);
                window.location.href = url.toString();
            });
        }
    });
</script>
@endpush