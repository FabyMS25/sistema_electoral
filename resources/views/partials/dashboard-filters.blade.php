{{-- resources/views/partials/dashboard-filters.blade.php --}}
{{--
    Variables expected (all passed by HomeController via buildDashboardData):
      $dashboard            — Dashboard model (show_election_switcher, show_category_filter)
      $electionTypes        — all active ElectionType records
      $selectedElectionType — current ElectionType|null
      $departments, $provinces, $municipalities
      $selectedDepartment, $selectedProvince, $selectedMunicipality
      $typeCategories       — ElectionTypeCategory collection for the selected election type
      $activeCategoryCode   — currently selected category CODE string
--}}
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2 px-3">
        <form method="GET" action="{{ url()->current() }}" id="dashboardFilterForm">
            <div class="row g-2 align-items-end">

                {{-- ── Election type switcher (controlled by show_election_switcher) ── --}}
                @if($dashboard?->show_election_switcher !== false)
                <div class="col-lg-3 col-md-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-vote-line me-1"></i>Tipo de Elección
                    </label>
                    <select name="election_type" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                        @foreach($electionTypes as $et)
                        <option value="{{ $et->id }}"
                            {{ $selectedElectionType?->id == $et->id ? 'selected' : '' }}>
                            {{ $et->short_name ?? $et->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @else
                {{-- Hidden — keeps value in form even when select is hidden --}}
                <input type="hidden" name="election_type" value="{{ $selectedElectionType?->id ?? '' }}">
                @endif

                {{-- ── Category filter (controlled by show_category_filter) ── --}}
                @if($dashboard?->show_category_filter !== false && $typeCategories->count() > 1)
                <div class="col-lg-3 col-md-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-bar-chart-line me-1"></i>Categoría
                    </label>
                    <div class="d-flex gap-1 flex-wrap">
                        @foreach($typeCategories as $tc)
                        @php $code = $tc->electionCategory?->code ?? 'UNK'; @endphp
                        <button type="button"
                                class="btn btn-sm {{ $code === $activeCategoryCode ? 'btn-primary' : 'btn-outline-secondary' }} category-pill-btn"
                                data-category="{{ $code }}"
                                onclick="setCategoryAndSync('{{ $code }}')">
                            @switch($code)
                                @case('ALC') <i class="ri-user-star-line me-1"></i> @break
                                @case('CON') <i class="ri-group-line me-1"></i>      @break
                                @case('GOB') <i class="ri-government-line me-1"></i> @break
                                @default     <i class="ri-bar-chart-line me-1"></i>
                            @endswitch
                            {{ $tc->electionCategory?->name ?? $code }}
                        </button>
                        @endforeach
                    </div>
                    {{-- Hidden input carries the active category on form submit --}}
                    <input type="hidden" name="category" id="filter-category-input"
                           value="{{ $activeCategoryCode ?? '' }}">
                </div>
                @else
                <input type="hidden" name="category" value="{{ $activeCategoryCode ?? '' }}">
                @endif

                {{-- ── Geography ── --}}
                <div class="col-lg-2 col-md-4 col-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-map-2-line me-1"></i>Departamento
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

                <div class="col-lg-1 col-md-4 col-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-map-pin-2-line me-1"></i>Provincia
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

                <div class="col-lg-1 col-md-4">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-community-line me-1"></i>Municipio
                    </label>
                    <select name="municipality" id="muni-select" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                        @foreach($municipalities as $muni)
                        <option value="{{ $muni->id }}"
                            {{ $selectedMunicipality == $muni->id ? 'selected' : '' }}>
                            {{ $muni->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- ── Actions + timestamp ── --}}
                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2 justify-content-end">
                    <span class="text-muted small me-1 d-none d-lg-block text-end lh-1">
                        @if($selectedElectionType?->election_date)
                        <span class="d-block fw-semibold text-dark" style="font-size:.75rem;">
                            {{ \Carbon\Carbon::parse($selectedElectionType->election_date)->format('d/m/Y') }}
                        </span>
                        @endif
                        <span id="ds-filter-time">{{ now()->format('H:i') }}</span>
                    </span>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ri-filter-line me-1"></i>Aplicar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="ElectionDashboard?.refresh()" title="Actualizar datos">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // ── Category pill: update hidden input + sync content tabs ────────────────
    window.setCategoryAndSync = function (code) {
        // Update hidden input so form submission carries the right category
        const inp = document.getElementById('filter-category-input');
        if (inp) inp.value = code;

        // Highlight the clicked pill
        document.querySelectorAll('.category-pill-btn').forEach(btn => {
            const active = btn.dataset.category === code;
            btn.classList.toggle('btn-primary',          active);
            btn.classList.toggle('btn-outline-secondary', !active);
        });

        // Switch the Bootstrap tab in dashboard-content (no page reload needed)
        const tabLink = document.querySelector(`#categoryTabs [data-category="${code}"]`);
        if (tabLink) {
            const bsTab = bootstrap.Tab.getOrCreateInstance(tabLink);
            bsTab.show();
        }

        // Sync chart pickers (locality + donut) if they exist
        ['locality-category-picker', 'donut-category-picker'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = code.toLowerCase();
                el.dispatchEvent(new Event('change'));
            }
        });
    };

    // ── Tab click → sync filter pills back ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#categoryTabs [data-category]').forEach(link => {
            link.addEventListener('shown.bs.tab', e => {
                const code = e.target.dataset.category;
                if (!code) return;
                // Sync pill buttons
                document.querySelectorAll('.category-pill-btn').forEach(btn => {
                    const active = btn.dataset.category === code;
                    btn.classList.toggle('btn-primary',           active);
                    btn.classList.toggle('btn-outline-secondary', !active);
                });
                // Sync hidden input
                const inp = document.getElementById('filter-category-input');
                if (inp) inp.value = code;
            });
        });
    });

    // ── Geography cascade ─────────────────────────────────────────────────────
    document.getElementById('dept-select')?.addEventListener('change', function () {
        fetch(`/api/provinces/${this.value}`)
            .then(r => r.json())
            .then(data => {
                const ps = document.getElementById('prov-select');
                ps.innerHTML = data.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
                return fetch(`/api/municipalities/${data[0]?.id}`);
            })
            .then(r => r?.json())
            .then(data => {
                if (!data) return;
                const ms = document.getElementById('muni-select');
                ms.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
            })
            .catch(console.warn);
    });

    document.getElementById('prov-select')?.addEventListener('change', function () {
        fetch(`/api/municipalities/${this.value}`)
            .then(r => r.json())
            .then(data => {
                const ms = document.getElementById('muni-select');
                ms.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
            })
            .catch(console.warn);
    });
})();
</script>
