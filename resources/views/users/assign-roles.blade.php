{{-- resources/views/users/assign-roles.blade.php --}}
{{--
    SCOPE LOGIC (driven by role default_scope):
    ─────────────────────────────────────────────────────────────────────────────
    global only  ──► only Roles & Permissions panel is visible/active
    recinto      ──► Roles panel + Delegación en Recinto (1 recinto picker)
    mesa         ──► Roles panel + Delegación en Mesa   (pick mesa → recinto auto)
                     The Recinto delegation form is HIDDEN when scope=mesa
                     because institution_id comes from the selected mesa.

    There is never more than one recinto picker visible at once.
    ─────────────────────────────────────────────────────────────────────────────
--}}
@extends('layouts.master')

@section('title') Asignaciones – {{ $user->name }} {{ $user->last_name }} @endsection

@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    /* Role + permission cards — same as create */
    .permission-group { border:1px solid #e9e9ef; border-radius:.25rem; margin-bottom:1rem; }
    .permission-group-header { background:#f3f6f9; padding:.75rem 1rem; border-bottom:1px solid #e9e9ef; font-weight:600; }
    .permission-group-body { padding:1rem; display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:.5rem; }
    .role-card { border:1px solid #e9e9ef; border-radius:.25rem; padding:.75rem; margin-bottom:.5rem; cursor:pointer; transition:all .15s; }
    .role-card:hover { background:#f3f6f9; }
    .role-card.selected { background:#e7f1ff; border-color:#0ab39c; }

    /* Delegation panels */
    .delegation-card { transition:opacity .2s; }
    .delegation-card.locked { opacity:.35; pointer-events:none; }

    /* Sidebar pills */
    .a-pill { border-left:3px solid #0ab39c; background:#f0faf8; padding:.55rem .875rem; border-radius:0 .35rem .35rem 0; margin-bottom:.4rem; }
    .a-pill.mesa { border-left-color:#299cdb; background:#f0f7ff; }
</style>
@endsection

@section('content')
@component('components.breadcrumb')
    @slot('li_1') Usuarios @endslot
    @slot('li_2') <a href="{{ route('users.index') }}">Lista</a> @endslot
    @slot('title') Asignaciones: {{ $user->name }} {{ $user->last_name }} @endslot
@endcomponent

{{-- Identity strip --}}
<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ $user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg') }}"
                 alt="" class="rounded-circle" style="width:46px;height:46px;object-fit:cover;">
            <div>
                <h5 class="mb-0">{{ $user->name }} {{ $user->last_name }}</h5>
                <p class="text-muted small mb-0">{{ $user->email }}@if($user->id_card) &bull; CI: {{ $user->id_card }}@endif</p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('users.show', $user) }}" class="btn btn-soft-secondary btn-sm">
                    <i class="ri-arrow-left-line align-middle"></i> Ver Perfil
                </a>
                <a href="{{ route('users.edit', $user) }}" class="btn btn-soft-warning btn-sm">
                    <i class="ri-pencil-line align-middle"></i> Editar
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <i class="ri-checkbox-circle-line align-middle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <i class="ri-error-warning-line align-middle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

{{-- ════ LEFT ════════════════════════════════════════════════════════════════ --}}
<div class="col-lg-8 d-flex flex-column gap-4">

    {{-- ── SECTION 1: Roles + Permissions ──────────────────────────────────── --}}
    @if(auth()->user()->allPermissions->contains('name', 'assign_roles'))
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-1">Roles y Permisos del Sistema</h5>
            <p class="text-muted small mb-0">
                Selecciona los roles. Los permisos se marcan automáticamente y pueden ajustarse.
                La sección de delegación se activa según el ámbito del rol elegido.
            </p>
        </div>
        <div class="card-body">
            <form action="{{ route('users.assign-roles', $user) }}" method="POST" id="rolesForm">
                @csrf

                @php $currentMap = $currentRoles->keyBy('id'); @endphp

                {{-- Hidden role payload — enabled/disabled by JS --}}
                @foreach($roles as $index => $role)
                <input type="hidden" class="h-role-id"    name="roles[{{ $index }}][role_id]"           value="{{ $role->id }}"                                                                        disabled>
                <input type="hidden" class="h-role-scope" name="roles[{{ $index }}][scope]"             value="{{ $currentMap->get($role->id)?->pivot?->scope ?? $role->default_scope ?? 'global' }}" id="hScope_{{ $role->id }}" disabled>
                <input type="hidden" class="h-role-et"    name="roles[{{ $index }}][election_type_id]"  value="{{ $currentMap->get($role->id)?->pivot?->election_type_id ?? '' }}"                    id="hEt_{{ $role->id }}"    disabled>
                @endforeach

                <div class="row">
                    {{-- Roles list --}}
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-soft-primary">
                                <h6 class="card-title mb-0">Roles disponibles</h6>
                            </div>
                            <div class="card-body" style="max-height:420px;overflow-y:auto;">
                                @foreach($roles as $role)
                                @php $isChecked = (bool) $currentMap->get($role->id); @endphp
                                <div class="role-card {{ $isChecked ? 'selected' : '' }}"
                                     data-role-id="{{ $role->id }}"
                                     data-default-scope="{{ $role->default_scope ?? 'global' }}"
                                     data-role-name="{{ strtolower($role->name) }}">
                                    <div class="form-check mb-0">
                                        <input type="checkbox"
                                               class="form-check-input role-cb"
                                               id="role_{{ $role->id }}"
                                               data-role-id="{{ $role->id }}"
                                               {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label d-flex align-items-start gap-1" for="role_{{ $role->id }}">
                                            <div>
                                                <strong>{{ $role->display_name ?? $role->name }}</strong>
                                                <span class="badge ms-1
                                                    @if(($role->default_scope ?? 'global') === 'global')  bg-primary-subtle text-primary  @endif
                                                    @if(($role->default_scope ?? 'global') === 'recinto') bg-success-subtle text-success @endif
                                                    @if(($role->default_scope ?? 'global') === 'mesa')    bg-info-subtle    text-info    @endif
                                                ">{{ ucfirst($role->default_scope ?? 'global') }}</span>
                                                @if($role->description)
                                                <br><small class="text-muted">{{ $role->description }}</small>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                    </div>

                    {{-- Permissions --}}
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-soft-info d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Permisos</h6>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-soft-success" id="selAllPerms">Todos</button>
                                    <button type="button" class="btn btn-sm btn-soft-danger"  id="deselAllPerms">Ninguno</button>
                                </div>
                            </div>
                            <div class="card-body" style="max-height:500px;overflow-y:auto;">
                                @foreach($permissions as $group => $groupPermissions)
                                <div class="permission-group">
                                    <div class="permission-group-header">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input grp-cb"
                                                   id="grp_{{ Str::slug($group) }}" data-group="{{ $group }}">
                                            <label class="form-check-label fw-bold" for="grp_{{ Str::slug($group) }}">{{ $group }}</label>
                                        </div>
                                    </div>
                                    <div class="permission-group-body">
                                        @foreach($groupPermissions as $perm)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input perm-cb"
                                                   id="perm_{{ $perm->id }}"
                                                   name="permissions[]"
                                                   value="{{ $perm->id }}"
                                                   data-group="{{ $group }}"
                                                   {{ in_array($perm->id, $userPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $perm->id }}">
                                                {{ $perm->display_name ?? $perm->name }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Tipo de Elección
                            <small class="text-muted fw-normal">(para los roles — opcional)</small>
                        </label>
                        <select class="form-select" id="sharedEt">
                            <option value="">Todas las elecciones</option>
                            @foreach($electionTypes as $et)
                                <option value="{{ $et->id }}">{{ $et->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Deja en "Todas" si el usuario trabaja ambas elecciones del día.
                        </small>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-middle me-1"></i> Guardar Roles y Permisos
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ── SECTION 2a: Delegación en Recinto ──────────────────────────────── --}}
    {{-- Visible + active only when highest selected scope = 'recinto'         --}}
    {{-- Hidden entirely when scope = 'mesa' (recinto comes from the mesa)     --}}
    @if(auth()->user()->allPermissions->contains('name', 'assign_delegates'))

    <div class="card delegation-card locked" id="cardRecinto" style="display:none">
        <div class="card-header">
            <h5 class="card-title mb-1">
                <i class="ri-building-line align-middle me-2 text-success"></i>
                Delegación en Recinto
            </h5>
            <p class="text-muted small mb-0" id="recintoHint">
                Activa al seleccionar un rol de ámbito <strong>Recinto</strong>.
            </p>
        </div>
        <div class="card-body">
            <form action="{{ route('users.assign-institution', $user) }}" method="POST" id="formRecinto">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Recinto <span class="text-danger">*</span></label>
                        <select class="form-select @error('institution_id') is-invalid @enderror"
                                name="institution_id" id="recintoSelect" required>
                            <option value="">Seleccione recinto…</option>
                            @foreach($institutions->groupBy(fn($i) => $i->municipality?->name ?? 'Sin municipio') as $municipio => $instList)
                            <optgroup label="{{ $municipio }}">
                                @foreach($instList as $inst)
                                <option value="{{ $inst->id }}" {{ old('institution_id') == $inst->id ? 'selected' : '' }}>
                                    {{ $inst->name }} ({{ $inst->code }})
                                </option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        @error('institution_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Función <span class="text-danger">*</span></label>
                        <select class="form-select @error('delegate_type') is-invalid @enderror"
                                name="delegate_type" required>
                            <option value="">Seleccione…</option>
                            <option value="delegado_general" {{ old('delegate_type') == 'delegado_general' ? 'selected' : '' }}>Delegado General</option>
                            <option value="tecnico"          {{ old('delegate_type') == 'tecnico'          ? 'selected' : '' }}>Técnico / Soporte</option>
                            <option value="observador"       {{ old('delegate_type') == 'observador'       ? 'selected' : '' }}>Observador</option>
                        </select>
                        @error('delegate_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Fecha Asignación</label>
                        <input type="date" class="form-control" name="assignment_date"
                               value="{{ old('assignment_date', now()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Expiración <small class="text-muted">(opcional)</small></label>
                        <input type="date" class="form-control" name="expiration_date"
                               id="recintoExpDate" value="{{ old('expiration_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">N° Credencial <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" name="credential_number" value="{{ old('credential_number') }}">
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="ri-building-line align-middle me-1"></i> Agregar Delegación en Recinto
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── SECTION 2b: Delegación en Mesa ─────────────────────────────────── --}}
    {{-- Order: 1) pick recinto → 2) mesa dropdown filters to that recinto    --}}

    <div class="card delegation-card locked" id="cardMesa" style="display:none">
        <div class="card-header">
            <h5 class="card-title mb-1">
                <i class="ri-table-line align-middle me-2 text-info"></i>
                Delegación en Mesa de Votación
            </h5>
            <p class="text-muted small mb-0">
                Activa al seleccionar un rol de ámbito <strong>Mesa</strong>.
                Primero elige el recinto y luego la mesa dentro de ese recinto.
            </p>
        </div>
        <div class="card-body">
            <form action="{{ route('users.assign-table', $user) }}" method="POST" id="formMesa">
                @csrf
                {{-- Filled from recinto picker below --}}
                <input type="hidden" name="institution_id" id="mesaInstId" value="">

                <div class="row g-3">

                    {{-- Step 1: Recinto --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="badge bg-secondary me-1">1</span>
                            Recinto <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="mesaRecintoFilter" required>
                            <option value="">Seleccione recinto…</option>
                            @foreach($institutions->groupBy(fn($i) => $i->municipality?->name ?? 'Sin municipio') as $municipio => $instList)
                            <optgroup label="{{ $municipio }}">
                                @foreach($instList as $inst)
                                <option value="{{ $inst->id }}">
                                    {{ $inst->name }} ({{ $inst->code }})
                                </option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Step 2: Mesa — filtered by chosen recinto --}}
                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="badge bg-secondary me-1">2</span>
                            Mesa <span class="text-danger">*</span>
                        </label>
                        <select class="form-select @error('voting_table_id') is-invalid @enderror"
                                name="voting_table_id" id="mesaSelect" required disabled>
                            <option value="">Primero seleccione recinto…</option>
                            {{-- All options pre-rendered; JS shows/hides by data-inst-id --}}
                            @foreach($votingTables as $table)
                            <option value="{{ $table->id }}"
                                    data-inst-id="{{ $table->institution_id }}"
                                    {{ old('voting_table_id') == $table->id ? 'selected' : '' }}>
                                Mesa {{ $table->number }}{{ $table->letter ? ' ('.$table->letter.')' : '' }}
                                — {{ ucfirst($table->type) }}
                                ({{ $table->oep_code ?? $table->internal_code }})
                            </option>
                            @endforeach
                        </select>
                        @error('voting_table_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Función en la Mesa <span class="text-danger">*</span></label>
                        <select class="form-select @error('delegate_type') is-invalid @enderror"
                                name="delegate_type" required>
                            <option value="">Seleccione…</option>
                            <option value="presidente"    {{ old('delegate_type') == 'presidente'    ? 'selected' : '' }}>Presidente de Mesa</option>
                            <option value="secretario"    {{ old('delegate_type') == 'secretario'    ? 'selected' : '' }}>Secretario</option>
                            <option value="vocal"         {{ old('delegate_type') == 'vocal'         ? 'selected' : '' }}>Vocal</option>
                            <option value="delegado_mesa" {{ old('delegate_type') == 'delegado_mesa' ? 'selected' : '' }}>Delegado de Mesa</option>
                        </select>
                        @error('delegate_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Fecha Asignación</label>
                        <input type="date" class="form-control" name="assignment_date"
                               value="{{ old('assignment_date', now()->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Expiración <small class="text-muted">(opcional)</small></label>
                        <input type="date" class="form-control" name="expiration_date"
                               id="mesaExpDate" value="{{ old('expiration_date') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">N° Credencial <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" name="credential_number" value="{{ old('credential_number') }}">
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-info text-white btn-sm">
                        <i class="ri-table-line align-middle me-1"></i> Agregar Delegación en Mesa
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endif {{-- assign_delegates --}}
</div>{{-- /col-lg-8 --}}

{{-- ════ RIGHT: summary sidebar ════════════════════════════════════════════ --}}
<div class="col-lg-4">

    <div class="card mb-3">
        <div class="card-header py-2">
            <h6 class="mb-0 small text-uppercase fw-semibold text-muted">Roles Actuales</h6>
        </div>
        <div class="card-body p-0">
            @forelse($currentRoles as $role)
            <div class="px-3 py-2 border-bottom">
                <span class="fw-semibold">{{ $role->display_name ?? $role->name }}</span>
                <div class="d-flex gap-1 mt-1 flex-wrap">
                    @switch($role->pivot->scope)
                        @case('global')  <span class="badge bg-primary-subtle text-primary">Global</span>  @break
                        @case('recinto') <span class="badge bg-success-subtle text-success">Recinto</span> @break
                        @case('mesa')    <span class="badge bg-info-subtle text-info">Mesa</span>          @break
                    @endswitch
                </div>
            </div>
            @empty
            <p class="text-muted small text-center py-3 mb-0">Sin roles asignados</p>
            @endforelse
        </div>
    </div>

    @if(auth()->user()->allPermissions->contains('name', 'assign_delegates'))

    <div class="card mb-3">
        <div class="card-header py-2">
            <h6 class="mb-0 small text-uppercase fw-semibold text-muted">Delegaciones en Recinto</h6>
        </div>
        <div class="card-body py-2 px-2">
            @php $ras = $user->assignments->where('status','activo')->whereNull('voting_table_id'); @endphp
            @forelse($ras as $a)
            <div class="a-pill">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold small">{{ $a->institution?->name ?? '—' }}</div>
                        <div class="d-flex gap-1 mt-1">
                            <span class="badge bg-success-subtle text-success">{{ $a->delegate_type_label }}</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem">Desde {{ $a->assignment_date?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <form action="{{ route('users.remove-assignment', [$user, $a]) }}" method="POST"
                          onsubmit="return confirm('¿Remover delegación?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-soft-danger"><i class="ri-close-line"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted small text-center py-2 mb-0">Sin delegaciones de recinto</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-header py-2">
            <h6 class="mb-0 small text-uppercase fw-semibold text-muted">Delegaciones en Mesa</h6>
        </div>
        <div class="card-body py-2 px-2">
            @php $mas = $user->assignments->where('status','activo')->whereNotNull('voting_table_id'); @endphp
            @forelse($mas as $a)
            <div class="a-pill mesa">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold small">
                            Mesa {{ $a->votingTable?->number ?? '—' }}
                            <span class="fw-normal text-muted">· {{ $a->votingTable?->institution?->name ?? '—' }}</span>
                        </div>
                        <div class="d-flex gap-1 mt-1">
                            <span class="badge bg-info-subtle text-info">{{ $a->delegate_type_label }}</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem">Desde {{ $a->assignment_date?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <form action="{{ route('users.remove-assignment', [$user, $a]) }}" method="POST"
                          onsubmit="return confirm('¿Remover delegación?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-soft-danger"><i class="ri-close-line"></i></button>
                    </form>
                </div>
            </div>
            @empty
            <p class="text-muted small text-center py-2 mb-0">Sin delegaciones de mesa</p>
            @endforelse
        </div>
    </div>

    @endif
</div>

</div>{{-- /row --}}
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data from PHP ─────────────────────────────────────────────────────────
    const ROLE_PERMS  = @json($roles->mapWithKeys(fn($r) => [$r->id => $r->permissions->pluck('id')]));
    const ROLE_SCOPE  = @json($roles->mapWithKeys(fn($r) => [$r->id => $r->default_scope ?? 'global']));
    const SCOPE_RANK  = { global: 0, recinto: 1, mesa: 2 };

    // ── Scope state ───────────────────────────────────────────────────────────
    function highestScope() {
        let best = 'global';
        document.querySelectorAll('.role-cb:checked').forEach(cb => {
            const s = ROLE_SCOPE[cb.dataset.roleId] ?? 'global';
            if (SCOPE_RANK[s] > SCOPE_RANK[best]) best = s;
        });
        return best;
    }

    // ── Show/hide delegation cards based on scope ─────────────────────────────
    //
    //   global  → both cards hidden
    //   recinto → cardRecinto shown+unlocked,  cardMesa hidden
    //   mesa    → cardRecinto hidden,           cardMesa shown+unlocked
    //             (recinto is read-only text derived from the selected mesa)
    //
    function applyScope() {
        const scope = highestScope();
        const cardR = document.getElementById('cardRecinto');
        const cardM = document.getElementById('cardMesa');

        if (scope === 'global') {
            cardR.style.display = 'none';
            cardM.style.display = 'none';
        } else if (scope === 'recinto') {
            cardR.style.display = '';
            cardR.classList.remove('locked');
            cardM.style.display = 'none';
        } else { // mesa
            cardR.style.display = 'none';   // ← hides the recinto picker entirely
            cardM.style.display = '';
            cardM.classList.remove('locked');
        }

        // Sync hidden role inputs
        document.querySelectorAll('.role-cb').forEach(cb => {
            const id = cb.dataset.roleId;
            const on = cb.checked;
            document.getElementById('hScope_' + id).disabled = !on;
            document.getElementById('hEt_'    + id).disabled = !on;
            document.querySelector(`.h-role-id[value="${id}"]`).disabled = !on;
            if (on) document.getElementById('hScope_' + id).value = ROLE_SCOPE[id] ?? 'global';
        });

        syncElectionType();
    }

    // ── Roles → permissions ───────────────────────────────────────────────────
    function updatePermsFromRoles() {
        const enabled = new Set();
        document.querySelectorAll('.role-cb:checked').forEach(cb => {
            (ROLE_PERMS[cb.dataset.roleId] ?? []).forEach(id => enabled.add(Number(id)));
        });
        document.querySelectorAll('.perm-cb').forEach(cb => {
            if (enabled.has(Number(cb.value))) cb.checked = true;
        });
        syncGroups();
    }

    function syncGroups() {
        document.querySelectorAll('.grp-cb').forEach(gc => {
            const all = [...document.querySelectorAll(`.perm-cb[data-group="${gc.dataset.group}"]`)];
            const chk = all.filter(c => c.checked);
            gc.checked       = all.length > 0 && chk.length === all.length;
            gc.indeterminate = chk.length > 0 && chk.length < all.length;
        });
    }



    // ── Election type → push to all active role hidden et inputs ─────────────
    function syncElectionType() {
        const val = document.getElementById('sharedEt')?.value ?? '';
        document.querySelectorAll('.h-role-et:not([disabled])').forEach(inp => inp.value = val);
    }
    document.getElementById('sharedEt')?.addEventListener('change', syncElectionType);

    // ── Role checkboxes ───────────────────────────────────────────────────────
    document.querySelectorAll('.role-cb').forEach(cb => {
        cb.addEventListener('change', function () {
            this.closest('.role-card').classList.toggle('selected', this.checked);
            updatePermsFromRoles();
            applyScope();
        });
    });

    // ── Permission group toggles ──────────────────────────────────────────────
    document.querySelectorAll('.grp-cb').forEach(gc => {
        gc.addEventListener('change', function () {
            document.querySelectorAll(`.perm-cb[data-group="${this.dataset.group}"]`)
                    .forEach(cb => cb.checked = this.checked);
        });
    });
    document.querySelectorAll('.perm-cb').forEach(cb => cb.addEventListener('change', syncGroups));
    document.getElementById('selAllPerms')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = true); syncGroups();
    });
    document.getElementById('deselAllPerms')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false); syncGroups();
    });

    // ── Mesa section: recinto picker → filters mesa dropdown ────────────────
    const mesaRecintoFilter = document.getElementById('mesaRecintoFilter');
    const mesaSelect        = document.getElementById('mesaSelect');
    const mesaInstId        = document.getElementById('mesaInstId');

    function filterMesasByRecinto() {
        const instId = mesaRecintoFilter?.value ?? '';

        // Set hidden institution_id from recinto picker
        if (mesaInstId) mesaInstId.value = instId;

        if (!mesaSelect) return;

        // Reset mesa dropdown
        mesaSelect.value    = '';
        mesaSelect.disabled = !instId;
        mesaSelect.options[0].text = instId ? 'Seleccione mesa…' : 'Primero seleccione recinto…';

        // Show only options matching the chosen recinto; hide the rest
        let visibleCount = 0;
        Array.from(mesaSelect.options).forEach(opt => {
            if (!opt.value) return; // skip placeholder
            const match = opt.dataset.instId === instId;
            opt.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        if (visibleCount === 0 && instId) {
            mesaSelect.options[0].text = 'Sin mesas disponibles en este recinto';
        }
    }

    mesaRecintoFilter?.addEventListener('change', filterMesasByRecinto);

    document.getElementById('formMesa')?.addEventListener('submit', function (e) {
        if (!mesaInstId?.value) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un recinto',
                        text: 'Debes elegir un recinto antes de seleccionar la mesa.' });
            return;
        }
        if (!mesaSelect?.value) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona una mesa',
                        text: 'Debes elegir una mesa de votación.' });
        }
    });

    // ── Expiration guards ─────────────────────────────────────────────────────
    ['recintoExpDate', 'mesaExpDate'].forEach(expId => {
        document.getElementById(expId)?.addEventListener('change', function () {
            const assignVal = this.closest('form')?.querySelector('[name="assignment_date"]')?.value;
            if (assignVal && this.value && this.value < assignVal) {
                Swal.fire({ icon: 'error', title: 'Fecha inválida',
                            text: 'La expiración debe ser posterior a la fecha de asignación.' });
                this.value = '';
            }
        });
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    syncGroups();
    applyScope();
    filterMesasByRecinto();
});
</script>
@endsection
