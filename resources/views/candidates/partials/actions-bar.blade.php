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
                    <div class="col-md-5 filter-item">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="ri-search-line text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0"
                                placeholder="Buscar por nombre, partido, lista..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-3 filter-item">
                        <select name="department_id" class="form-select" id="filter-department">
                            <option value="">Todos los departamentos</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Election Type Category Filter -->
                    <div class="col-md-4 filter-item">
                        <select name="election_type_category_id" class="form-select" id="filter-category">
                            <option value="">Todas las categorías</option>
                            @foreach($electionTypeCategories as $etc)
                                <option value="{{ $etc->id }}" {{ request('election_type_category_id') == $etc->id ? 'selected' : '' }}>
                                    {{ $etc->electionType->name }} - {{ $etc->electionCategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-2 mt-2">
                    <!-- Province Filter (hidden by default, shown when department selected) -->
                    <div class="col-md-4 filter-item">
                        <select name="province_id" class="form-select" id="filter-province" {{ request('department_id') ? '' : 'disabled' }}>
                            <option value="">Todas las provincias</option>
                            @if(isset($provinces) && $provinces->isNotEmpty())
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                                        {{ $province->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Municipality Filter (hidden by default, shown when province selected) -->
                    <div class="col-md-4 filter-item">
                        <select name="municipality_id" class="form-select" id="filter-municipality" {{ request('province_id') ? '' : 'disabled' }}>
                            <option value="">Todos los municipios</option>
                            @if(isset($municipalities) && $municipalities->isNotEmpty())
                                @foreach($municipalities as $municipality)
                                    <option value="{{ $municipality->id }}" {{ request('municipality_id') == $municipality->id ? 'selected' : '' }}>
                                        {{ $municipality->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="ri-filter-3-line me-1"></i> Filtrar
                            </button>

                            @if(request()->hasAny(['search', 'election_type_category_id', 'department_id', 'province_id', 'municipality_id']))
                                <a href="{{ route('candidates.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros">
                                    <i class="ri-close-line"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for sorting and pagination -->
                <input type="hidden" name="sort" value="{{ request('sort', 'name') }}">
                <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
            </form>
        </div>

        <!-- Active filters display -->
        @if(request()->hasAny(['search', 'election_type_category_id', 'department_id', 'province_id', 'municipality_id']))
            <div class="mt-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small">Filtros activos:</span>

                    @if(request('search'))
                        <span class="badge bg-primary d-inline-flex align-items-center">
                            <i class="ri-search-line me-1"></i> "{{ request('search') }}"
                            <a href="{{ route('candidates.index', request()->except(['search', 'page'])) }}" class="text-white ms-2">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif

                    @if(request('election_type_category_id') && $electionTypeCategories->find(request('election_type_category_id')))
                        @php
                            $selectedCategory = $electionTypeCategories->find(request('election_type_category_id'));
                        @endphp
                        <span class="badge bg-info d-inline-flex align-items-center">
                            <i class="ri-stack-line me-1"></i>
                            {{ $selectedCategory->electionType->name }} - {{ $selectedCategory->electionCategory->name }}
                            <a href="{{ route('candidates.index', request()->except(['election_type_category_id', 'page'])) }}" class="text-white ms-2">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    @endif

                    @if(request('department_id'))
                        @php
                            $dept = $departments->find(request('department_id'));
                        @endphp
                        @if($dept)
                            <span class="badge bg-success d-inline-flex align-items-center">
                                <i class="ri-map-pin-line me-1"></i> {{ $dept->name }}
                                <a href="{{ route('candidates.index', request()->except(['department_id', 'province_id', 'municipality_id', 'page'])) }}" class="text-white ms-2">
                                    <i class="ri-close-line"></i>
                                </a>
                            </span>
                        @endif
                    @endif

                    @if(request('province_id') && isset($provinces))
                        @php
                            $prov = $provinces->find(request('province_id'));
                        @endphp
                        @if($prov)
                            <span class="badge bg-warning d-inline-flex align-items-center">
                                <i class="ri-map-pin-line me-1"></i> {{ $prov->name }}
                                <a href="{{ route('candidates.index', request()->except(['province_id', 'municipality_id', 'page'])) }}" class="text-white ms-2">
                                    <i class="ri-close-line"></i>
                                </a>
                            </span>
                        @endif
                    @endif

                    @if(request('municipality_id') && isset($municipalities))
                        @php
                            $mun = $municipalities->find(request('municipality_id'));
                        @endphp
                        @if($mun)
                            <span class="badge bg-secondary d-inline-flex align-items-center">
                                <i class="ri-map-pin-line me-1"></i> {{ $mun->name }}
                                <a href="{{ route('candidates.index', request()->except(['municipality_id', 'page'])) }}" class="text-white ms-2">
                                    <i class="ri-close-line"></i>
                                </a>
                            </span>
                        @endif
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
    // Handle department change - load provinces via redirect
    const departmentSelect = document.getElementById('filter-department');
    if (departmentSelect) {
        departmentSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('department_id', this.value);
                url.searchParams.delete('province_id');
                url.searchParams.delete('municipality_id');
            } else {
                url.searchParams.delete('department_id');
                url.searchParams.delete('province_id');
                url.searchParams.delete('municipality_id');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // Handle province change - load municipalities via redirect
    const provinceSelect = document.getElementById('filter-province');
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('province_id', this.value);
                url.searchParams.delete('municipality_id');
            } else {
                url.searchParams.delete('province_id');
                url.searchParams.delete('municipality_id');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // Handle category filter change
    const categorySelect = document.getElementById('filter-category');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('election_type_category_id', this.value);
            } else {
                url.searchParams.delete('election_type_category_id');
            }
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // Auto-submit search on enter (already handled by form submit)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('filter-form').submit();
            }
        });
    }
});
</script>
