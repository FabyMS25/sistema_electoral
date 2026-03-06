{{-- resources/views/partials/dashboard-filters.blade.php --}}
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">

            {{-- Election type --}}
            <div class="col-md-3">
                <label class="form-label form-label-sm fw-semibold mb-1">
                    <i class="ri-vote-line me-1 text-muted"></i>Tipo de Elección
                </label>
                <form method="GET" action="{{ url()->current() }}" id="electionTypeForm">
                    {{-- Keep other params --}}
                    <input type="hidden" name="department"   value="{{ $selectedDepartment }}">
                    <input type="hidden" name="province"     value="{{ $selectedProvince }}">
                    <input type="hidden" name="municipality" value="{{ $selectedMunicipality }}">
                    <select name="election_type" class="form-select form-select-sm"
                            onchange="document.getElementById('electionTypeForm').submit()">
                        @foreach($electionTypes as $et)
                            <option value="{{ $et->id }}"
                                {{ $selectedElectionType?->id == $et->id ? 'selected' : '' }}>
                                {{ $et->name }}
                                ({{ \Carbon\Carbon::parse($et->election_date)->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Geography --}}
            <div class="col-md-7">
                <form method="GET" action="{{ url()->current() }}" id="locationFilterForm"
                      class="row g-2 align-items-end">
                    <input type="hidden" name="election_type" value="{{ $selectedElectionType?->id ?? '' }}">

                    <div class="col-4">
                        <label class="form-label form-label-sm fw-semibold mb-1">
                            <i class="ri-map-2-line me-1 text-muted"></i>Departamento
                        </label>
                        <select name="department" id="dept-select" class="form-select form-select-sm">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ $selectedDepartment == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label form-label-sm fw-semibold mb-1">
                            <i class="ri-map-pin-2-line me-1 text-muted"></i>Provincia
                        </label>
                        <select name="province" id="prov-select" class="form-select form-select-sm">
                            @foreach($provinces as $prov)
                                <option value="{{ $prov->id }}"
                                    {{ $selectedProvince == $prov->id ? 'selected' : '' }}>
                                    {{ $prov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label form-label-sm fw-semibold mb-1">
                            <i class="ri-community-line me-1 text-muted"></i>Municipio
                        </label>
                        <select name="municipality" id="muni-select" class="form-select form-select-sm"
                                onchange="document.getElementById('locationFilterForm').submit()">
                            @foreach($municipalities as $muni)
                                <option value="{{ $muni->id }}"
                                    {{ $selectedMunicipality == $muni->id ? 'selected' : '' }}>
                                    {{ $muni->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Refresh info --}}
            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="ElectionDashboard?.refresh()">
                    <i class="ri-refresh-line me-1"></i>Actualizar
                </button>
                <div class="small text-muted mt-1" id="ds-filter-time">{{ now()->format('H:i') }}</div>
            </div>

        </div>
    </div>
</div>

<script>
// Cascade: dept → province → municipality
document.getElementById('dept-select')?.addEventListener('change', function () {
    fetch(`/api/provinces/${this.value}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('prov-select');
            sel.innerHTML = data.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
            sel.dispatchEvent(new Event('change'));
        });
});

document.getElementById('prov-select')?.addEventListener('change', function () {
    fetch(`/api/municipalities/${this.value}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('muni-select');
            sel.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
        });
});
</script>
