<div class="row g-4 mb-2">
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2">
            @can('create_candidatos')
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#candidateModal" id="create-btn">
                <i class="ri-add-line align-bottom me-1"></i> Agregar Candidato
            </button>
            @endcan
            
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-download-line align-bottom me-1"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('candidates.export-all') . '?' . http_build_query(request()->except('selected_ids')) }}">
                            <i class="ri-file-excel-line me-2"></i> Exportar Todo
                            <small class="text-muted d-block">({{ $candidates->total() }} registros encontrados)</small>
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
                        <a class="dropdown-item" href="{{ route('candidates.template') }}">
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
                Eliminar Seleccionados
            </button>
        </div>
    </div>
    
    <div class="col-sm">
        <div class="filters-row">
            <form method="GET" action="{{ route('candidates.index') }}" id="filter-form">
                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-md-4 filter-item">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text" name="search" class="form-control" 
                                placeholder="Buscar nombre, partido..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    
                    <!-- Election Type Category Filter (combina tipo y categoría) -->
                    <div class="col-md-4 filter-item">
                        <select name="election_type_category_id" class="form-select">
                            <option value="">Todas las categorías</option>
                            @foreach($electionTypeCategories as $etc)
                                <option value="{{ $etc->id }}" {{ request('election_type_category_id') == $etc->id ? 'selected' : '' }}>
                                    {{ $etc->electionType->name }} - {{ $etc->electionCategory->name }} ({{ $etc->electionCategory->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Type Filter -->
                    <div class="col-md-2 filter-item">
                        <select name="type" class="form-select">
                            <option value="">Todos los tipos</option>
                            @foreach($typeOptions as $value => $label)
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                    {{ $label }}
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
                            
                            @if(request()->hasAny(['search', 'election_type_category_id', 'type']))
                                <a href="{{ route('candidates.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-close-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Hidden inputs for sorting -->
                <input type="hidden" name="sort" value="{{ request('sort', 'name') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
            </form>
        </div>
        
        @if(request()->hasAny(['search', 'election_type_category_id', 'type']))
            <div class="mt-2">
                <div class="d-flex gap-2 flex-wrap">
                    @if(request('search'))
                        <span class="badge bg-primary">
                            <i class="ri-search-line"></i> "{{ request('search') }}"
                            <a href="{{ route('candidates.index', request()->except(['search', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('election_type_category_id') && $electionTypeCategories->find(request('election_type_category_id')))
                        @php
                            $selectedE = $electionTypeCategories->find(request('election_type_category_id'));
                        @endphp
                        <span class="badge bg-info">
                            {{ $selectedE->electionType->name }} - {{ $selectedE->electionCategory->name }}
                            <a href="{{ route('candidates.index', request()->except(['election_type_category_id', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    
                    @if(request('type') && isset($typeOptions[request('type')]))
                        <span class="badge bg-success">
                            {{ $typeOptions[request('type')] }}
                            <a href="{{ route('candidates.index', request()->except(['type', 'page'])) }}" class="text-white ms-1">
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
<form id="export-selected-form" action="{{ route('candidates.export-selected') }}" method="POST" style="display: none;">
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