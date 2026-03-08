<div class="row g-4 mb-2">
    {{-- ── Left: Action buttons ── --}}
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2 align-items-center">

            @can('create_candidatos')
            <button type="button" class="btn btn-success"
                    data-bs-toggle="modal" data-bs-target="#candidateModal" id="create-btn">
                <i class="ri-add-line me-1"></i> Agregar Candidato
            </button>
            @endcan

            {{-- Export dropdown --}}
            <div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="ri-download-line me-1"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item"
                           href="{{ route('candidates.export-all') . '?' . http_build_query(request()->except('selected_ids','page')) }}">
                            <i class="ri-file-excel-line me-2 text-success"></i>
                            Exportar Todo
                            <small class="text-muted d-block">
                                ({{ $candidates->total() }} registros con filtros actuales)
                            </small>
                        </a>
                    </li>
                    <li>
                        <button class="dropdown-item" id="export-selected-btn"
                                onclick="exportSelected()" disabled>
                            <i class="ri-file-excel-line me-2 text-success"></i>
                            Exportar Seleccionados
                            <span id="selected-count-badge" class="badge bg-primary ms-1" style="display:none;">0</span>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('candidates.template') }}">
                            <i class="ri-file-download-line me-2 text-secondary"></i> Descargar Plantilla CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#"
                           data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-file-upload-line me-2 text-secondary"></i> Importar Datos
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Delete multiple (visible only when items are checked) --}}
            <button class="btn btn-soft-danger d-none" id="delete-multiple-btn"
                    onclick="deleteMultiple()">
                <i class="ri-delete-bin-2-line me-1"></i> Eliminar Seleccionados
            </button>
        </div>
    </div>

    {{-- ── Right: Filters ── --}}
    <div class="col-sm">
        <form method="GET" action="{{ route('candidates.index') }}" id="filter-form">

            <div class="row g-2 align-items-center">
                {{-- Search --}}
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="ri-search-line text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Nombre, partido, lista…"
                               value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Department --}}
                <div class="col-md-3">
                    <select name="department_id" class="form-select" id="filter-department">
                        <option value="">Todos los departamentos</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Election type / category --}}
                <div class="col-md-4">
                    <select name="election_type_category_id" class="form-select" id="filter-category">
                        <option value="">Todas las categorías</option>
                        @foreach($electionTypeCategories as $etc)
                            <option value="{{ $etc->id }}"
                                {{ request('election_type_category_id') == $etc->id ? 'selected' : '' }}>
                                {{ $etc->electionType->name }} – {{ $etc->electionCategory->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-2 mt-1">
                {{-- Province --}}
                <div class="col-md-4">
                    <select name="province_id" class="form-select" id="filter-province"
                            {{ request('department_id') ? '' : 'disabled' }}>
                        <option value="">Todas las provincias</option>
                        @foreach($provinces as $prov)
                            <option value="{{ $prov->id }}"
                                {{ request('province_id') == $prov->id ? 'selected' : '' }}>
                                {{ $prov->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Municipality --}}
                <div class="col-md-4">
                    <select name="municipality_id" class="form-select" id="filter-municipality"
                            {{ request('province_id') ? '' : 'disabled' }}>
                        <option value="">Todos los municipios</option>
                        @foreach($municipalities as $mun)
                            <option value="{{ $mun->id }}"
                                {{ request('municipality_id') == $mun->id ? 'selected' : '' }}>
                                {{ $mun->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit / clear --}}
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="ri-filter-3-line me-1"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['search','election_type_category_id','department_id','province_id','municipality_id']))
                            <a href="{{ route('candidates.index') }}"
                               class="btn btn-outline-secondary" title="Limpiar filtros">
                                <i class="ri-close-line"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Preserve sort / pagination --}}
            <input type="hidden" name="sort"      value="{{ request('sort', 'name') }}">
            <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
            <input type="hidden" name="per_page"  value="{{ request('per_page', 20) }}">
        </form>

        {{-- Active filter badges --}}
        @if(request()->hasAny(['search','election_type_category_id','department_id','province_id','municipality_id']))
        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small">Filtros:</span>

            @if(request('search'))
                <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                    <i class="ri-search-line"></i> "{{ request('search') }}"
                    <a href="{{ route('candidates.index', request()->except(['search','page'])) }}" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            @endif

            @if(request('election_type_category_id') && ($selCat = $electionTypeCategories->find(request('election_type_category_id'))))
                <span class="badge bg-info d-inline-flex align-items-center gap-1">
                    <i class="ri-stack-line"></i> {{ $selCat->electionType->name }} – {{ $selCat->electionCategory->name }}
                    <a href="{{ route('candidates.index', request()->except(['election_type_category_id','page'])) }}" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            @endif

            @if(request('department_id') && ($selDept = $departments->find(request('department_id'))))
                <span class="badge bg-success d-inline-flex align-items-center gap-1">
                    <i class="ri-map-pin-line"></i> {{ $selDept->name }}
                    <a href="{{ route('candidates.index', request()->except(['department_id','province_id','municipality_id','page'])) }}" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            @endif

            @if(request('province_id') && ($selProv = $provinces->find(request('province_id'))))
                <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                    <i class="ri-map-pin-line"></i> {{ $selProv->name }}
                    <a href="{{ route('candidates.index', request()->except(['province_id','municipality_id','page'])) }}" class="text-dark"><i class="ri-close-line"></i></a>
                </span>
            @endif

            @if(request('municipality_id') && ($selMun = $municipalities->find(request('municipality_id'))))
                <span class="badge bg-secondary d-inline-flex align-items-center gap-1">
                    <i class="ri-map-pin-line"></i> {{ $selMun->name }}
                    <a href="{{ route('candidates.index', request()->except(['municipality_id','page'])) }}" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            @endif
        </div>
        @endif
    </div>
</div>

{{--
    Export-selected form
    JS populates this with multiple hidden inputs (selected_ids[]) before submit
--}}
<form id="export-selected-form"
      action="{{ route('candidates.export-selected') }}"
      method="POST" style="display:none;">
    @csrf
    {{-- JS appends <input name="selected_ids[]" value="…"> elements here --}}
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Department → Province cascade (filter bar) ───────────────
    const filterDept = document.getElementById('filter-department');
    if (filterDept) {
        filterDept.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value
                ? url.searchParams.set('department_id', this.value)
                : url.searchParams.delete('department_id');
            url.searchParams.delete('province_id');
            url.searchParams.delete('municipality_id');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // ── Province → Municipality cascade (filter bar) ──────────────
    const filterProv = document.getElementById('filter-province');
    if (filterProv) {
        filterProv.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value
                ? url.searchParams.set('province_id', this.value)
                : url.searchParams.delete('province_id');
            url.searchParams.delete('municipality_id');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // ── Category filter (auto-submit) ────────────────────────────
    const filterCat = document.getElementById('filter-category');
    if (filterCat) {
        filterCat.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value
                ? url.searchParams.set('election_type_category_id', this.value)
                : url.searchParams.delete('election_type_category_id');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }
});
</script>
