{{-- resources/views/partials/dashboard-filters.blade.php --}}
<div class="row mb-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row g-3">
                    <div class="col-md-8 d-flex gap-3">
                        <h5 class="card-title mb-0 mt-2">Tipo de Elección: </h5>
                        <form method="GET" action="{{ url()->current() }}" class="flex-grow-1">
                            <div class="row">
                                <div class="col-md-8">
                                    <select name="election_type" class="form-select" onchange="this.form.submit()">
                                        @foreach($electionTypes as $electionType)
                                            <option value="{{ $electionType->id }}" 
                                                {{ $selectedElectionType && $selectedElectionType->id == $electionType->id ? 'selected' : '' }}>
                                                {{ $electionType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>             
                    <div class="col-md-4">
                        <div class="d-flex justify-content-end align-items-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                                <i class="ri-refresh-line"></i> Actualizar
                            </button>
                            <div class="ms-2">
                                <small class="text-muted" id="last-update-time">
                                    Última actualización: {{ now()->format('H:i:s') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}" class="row g-3 p-1" id="locationFilterForm">
                    <input type="hidden" name="election_type" value="{{ $selectedElectionType ? $selectedElectionType->id : '' }}">
                    <div class="col-md-3">
                        <label for="department" class="form-label mb-0">Departamento</label>
                        <select name="department" id="department" class="form-select">
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                    {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="col-md-3">
                        <label for="province" class="form-label mb-0">Provincia</label>
                        <select name="province" id="province" class="form-select">
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" 
                                    {{ $selectedProvince == $province->id ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="col-md-3">
                        <label for="municipality" class="form-label mb-0">Municipio</label>
                        <select name="municipality" id="municipality" class="form-select">
                            @foreach($municipalities as $municipality)
                                <option value="{{ $municipality->id }}" 
                                    {{ $selectedMunicipality == $municipality->id ? 'selected' : '' }}>
                                    {{ $municipality->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="col-md-3 mt-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filtrar por ubicación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deptSelect = document.getElementById('department');
    const provSelect = document.getElementById('province');
    const munSelect = document.getElementById('municipality');
    const filterForm = document.getElementById('locationFilterForm');

    function loadProvinces() {
        fetch(`/api/provinces/${deptSelect.value}`)
            .then(response => response.json())
            .then(data => {
                provSelect.innerHTML = data.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
                loadMunicipalities();
            });
    }

    function loadMunicipalities() {
        fetch(`/api/municipalities/${provSelect.value}`)
            .then(response => response.json())
            .then(data => {
                munSelect.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
            });
    }

    deptSelect.addEventListener('change', loadProvinces);
    provSelect.addEventListener('change', loadMunicipalities);
});
</script>