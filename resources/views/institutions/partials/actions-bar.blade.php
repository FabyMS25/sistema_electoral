<div class="row g-4 mb-1">
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2">
            @can('create_recintos')
            <a href="{{ route('institutions.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Agregar Recinto
            </a>
            @endcan

            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-download-line align-bottom me-1"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('institutions.export-all') . '?' . http_build_query(request()->except('selected_ids')) }}">
                            <i class="ri-file-excel-line me-1"></i> Exportar Todo
                            <small class="text-muted d-block">({{ $institutions->total() }} registros encontrados)</small>
                        </a>
                    </li>
                    <li>
                        <button class="dropdown-item" id="export-selected-btn" onclick="exportSelected()" disabled>
                            <i class="ri-file-excel-line me-1"></i> Exportar Seleccionados
                            <span id="selected-count-badge" class="badge bg-primary ms-2" style="display: none;">0</span>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('institutions.template') }}">
                            <i class="ri-file-download-line me-1"></i> Descargar Plantilla
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-file-upload-line me-1"></i> Importar Datos
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
            <form method="GET" action="{{ route('institutions.index') }}" id="filter-form">
                <div class="row g-2 align-items-center">
                    <div class="col-md-3 filter-item">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text" name="search" class="form-control"
                                placeholder="Buscar recinto por nombre, código..."
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3 filter-item">
                        <select name="department_id" class="form-select">
                            <option value="">Departamento</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                    <div class="col-md-2 filter-item">
                        <select name="operative" class="form-select">
                            <option value="">Operativo</option>
                            <option value="true" {{ request('operative') == 'true' ? 'selected' : '' }}>Sí</option>
                            <option value="false" {{ request('operative') == 'false' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary btn">
                                <i class="ri-filter-3-line"></i> Filtrar
                            </button>
                            @if(request()->hasAny(['search', 'department_id', 'status', 'operative']))
                                <a href="{{ route('institutions.index') }}" class="btn btn-outline-secondary btn">
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
        @if(request()->hasAny(['search', 'department_id', 'status', 'operative']))
            <div class="m-1">
                <div class="d-flex gap-2 flex-wrap">
                    @if(request('search'))
                        <span class="badge bg-primary">
                            <i class="ri-search-line"></i> "{{ request('search') }}"
                            <a href="{{ route('institutions.index', request()->except(['search', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    @if(request('department_id') && $departments->find(request('department_id')))
                        <span class="badge bg-info">
                            {{ $departments->find(request('department_id'))->name }}
                            <a href="{{ route('institutions.index', request()->except(['department_id', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    @if(request('status') && isset($statusOptions[request('status')]))
                        <span class="badge bg-success">
                            {{ $statusOptions[request('status')] }}
                            <a href="{{ route('institutions.index', request()->except(['status', 'page'])) }}" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif
                    @if(request('operative') !== null)
                        <span class="badge bg-warning text-dark">
                            Operativo: {{ request('operative') == 'true' ? 'Sí' : 'No' }}
                            <a href="{{ route('institutions.index', request()->except(['operative', 'page'])) }}" class="text-dark ms-1">
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
<form id="export-selected-form" action="{{ route('institutions.export-selected') }}" method="POST" style="display: none;">
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
