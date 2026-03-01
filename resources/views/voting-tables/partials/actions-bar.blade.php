{{-- resources/views/voting-tables/partials/actions-bar.blade.php --}}
<div class="row g-4 mb-2">
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2">
            @can('create_mesas')
            <a href="{{ route('voting-tables.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Agregar Mesa
            </a>
            @endcan
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-download-line align-bottom me-1"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('voting-tables.export-all') . '?' . http_build_query(request()->except('selected_ids')) }}">
                            <i class="ri-file-excel-line me-2"></i> Exportar Todo
                            <small class="text-muted d-block">({{ $votingTables->total() }} registros encontrados)</small>
                        </a>
                    </li>
                    <li>
                        <button class="dropdown-item" id="export-selected-btn" onclick="exportSelected()" disabled>
                            <i class="ri-file-excel-line me-2"></i> Exportar Seleccionados
                            <span id="selected-count-badge" class="badge bg-primary ms-2" style="display: none;">0</span>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('voting-tables.template') }}">
                            <i class="ri-file-download-line me-2"></i> Descargar Plantilla
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-file-upload-line me-2"></i> Importar Datos
                        </a>
                    </li>
                </ul>
            </div>
            <button class="btn btn-soft-danger" id="delete-multiple-btn" onclick="deleteMultiple()" style="display:none;">
                <i class="ri-delete-bin-2-line me-1"></i>
                Eliminar
            </button>
        </div>
    </div>
    
    <div class="col-sm">
        <div class="filters-row">
            <form method="GET" action="{{ route('voting-tables.index') }}" id="filter-form">
                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-md-3 filter-item">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text" name="search" class="form-control" 
                                placeholder="Buscar código OEP, interno, N° mesa, institución..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <!-- Institution Filter -->
                    <div class="col-md-3 filter-item">
                        <select name="institution_id" class="form-select">
                            <option value="">Institución</option>
                            @foreach($institutions as $institution)
                                <option value="{{ $institution->id }}" {{ request('institution_id') == $institution->id ? 'selected' : '' }}>
                                    {{ $institution->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-2 filter-item">
                        <select name="status" class="form-select">
                            <option value="">Estado</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Election Type Filter -->
                    <div class="col-md-2 filter-item">
                        <select name="election_type_id" class="form-select">
                            <option value="">Tipo Elección</option>
                            @foreach($electionTypes as $type)
                                <option value="{{ $type->id }}" {{ request('election_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="col-md-2">
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-filter-3-line"></i> Filtrar
                            </button>
                            
                            @if(request()->hasAny(['search', 'institution_id', 'status', 'election_type_id']))
                                <a href="{{ route('voting-tables.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-close-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Hidden inputs for sorting -->
                <input type="hidden" name="sort" value="{{ request('sort', 'institution_id') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
            </form>
        </div>
        @if(request()->hasAny(['search', 'institution_id', 'status', 'election_type_id']))
            <div class="m-1">
                <div class="d-flex gap-2 flex-wrap">
                    @if(request('search'))
                        <span class="badge bg-primary">
                            <i class="ri-search-line"></i> "{{ request('search') }}"
                            <a href="{{ route('voting-tables.index', request()->except(['search', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('institution_id') && $institutions->find(request('institution_id')))
                        <span class="badge bg-info">
                            {{ $institutions->find(request('institution_id'))->name }}
                            <a href="{{ route('voting-tables.index', request()->except(['institution_id', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('status') && isset($statusOptions[request('status')]))
                        <span class="badge bg-success">
                            {{ $statusOptions[request('status')] }}
                            <a href="{{ route('voting-tables.index', request()->except(['status', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('election_type_id') && $electionTypes->find(request('election_type_id')))
                        <span class="badge bg-warning text-dark">
                            {{ $electionTypes->find(request('election_type_id'))->name }}
                            <a href="{{ route('voting-tables.index', request()->except(['election_type_id', 'page'])) }}" class="text-dark ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Hidden form for exporting selected items -->
<form id="export-selected-form" action="{{ route('voting-tables.export-selected') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="selected_ids" id="selected-ids-input" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#filter-form select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
});
</script>