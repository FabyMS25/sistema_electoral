<div class="table-responsive table-card mt-3 mb-1">
    <table class="table align-middle table-nowrap" id="customerTable">
        <thead class="table-light">
            <tr>
                {{-- Select all --}}
                <th style="width:50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll">
                    </div>
                </th>

                {{-- Foto (non-sortable) --}}
                <th style="width:60px;">Foto</th>

                {{-- Sortable columns --}}
                @php
                    $cols = [
                        'name'              => 'Nombre',
                        'party'             => 'Partido',
                        'election_type'     => 'Tipo Elección',
                        'election_category' => 'Categoría',
                    ];
                @endphp

                @foreach($cols as $col => $label)
                <th>
                    <a href="{{ route('candidates.index', array_merge(request()->query(), [
                            'sort'      => $col,
                            'direction' => (request('sort') === $col && request('direction') === 'asc') ? 'desc' : 'asc',
                        ])) }}"
                       class="text-dark text-decoration-none d-inline-flex align-items-center gap-1">
                        {{ $label }}
                        @if(request('sort') === $col)
                            <i class="ri-arrow-{{ request('direction') === 'asc' ? 'up' : 'down' }}-line"></i>
                        @else
                            <i class="ri-arrow-up-down-line text-muted opacity-50"></i>
                        @endif
                    </a>
                </th>
                @endforeach

                <th>Color</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody class="list form-check-all">
        @forelse($candidates as $candidate)
            <tr>
                {{-- Checkbox --}}
                <th scope="row">
                    <div class="form-check">
                        <input class="form-check-input child-checkbox" type="checkbox"
                               name="selected_ids[]" value="{{ $candidate->id }}">
                    </div>
                </th>

                {{-- Photo --}}
                <td>
                    @if($candidate->photo)
                        <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}"
                             class="avatar-xs rounded-circle object-fit-cover">
                    @else
                        <div class="avatar-xs bg-light rounded-circle d-flex align-items-center justify-content-center">
                            <i class="ri-user-line text-muted"></i>
                        </div>
                    @endif
                </td>

                {{-- Name --}}
                <td>
                    <div class="fw-semibold">{{ $candidate->name }}</div>
                    @if($candidate->list_name)
                        <small class="text-muted">
                            {{ $candidate->list_name }}
                            @if($candidate->list_order) (Orden: {{ $candidate->list_order }}) @endif
                        </small>
                    @endif
                </td>

                {{-- Party --}}
                <td>
                    <div class="d-flex align-items-center gap-2">
                        @if($candidate->party_logo)
                            <img src="{{ $candidate->party_logo_url }}" alt="{{ $candidate->party }}"
                                 class="avatar-xs rounded-circle flex-shrink-0">
                        @endif
                        <div>
                            <span class="fw-semibold">{{ $candidate->party }}</span>
                            @if($candidate->party_full_name)
                                <br><small class="text-muted">{{ $candidate->party_full_name }}</small>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Election type --}}
                <td>
                    @if($candidate->electionTypeCategory?->electionType)
                        <span class="fw-semibold">
                            {{ $candidate->electionTypeCategory->electionType->name }}
                        </span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>

                {{-- Election category --}}
                <td>
                    @if($candidate->electionTypeCategory?->electionCategory)
                        @php $cat = $candidate->electionTypeCategory->electionCategory; @endphp
                        <span class="badge bg-primary-subtle text-primary">
                            {{ $cat->name }} ({{ $cat->code }})
                        </span>
                        <br>
                        <small class="text-muted">
                            Franja {{ $candidate->electionTypeCategory->ballot_order }}
                        </small>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>

                {{-- Color --}}
                <td>
                    @if($candidate->color)
                        <div class="color-preview" style="background-color:{{ $candidate->color }};"
                             title="{{ $candidate->color }}"></div>
                    @else
                        <span class="text-muted">–</span>
                    @endif
                </td>

                {{-- Actions --}}
                <td>
                    <div class="d-flex gap-1">
                        @can('view_candidatos')
                        <button type="button" class="btn btn-sm btn-info view-item-btn"
                            data-bs-toggle="modal" data-bs-target="#viewCandidateModal"
                            data-id="{{ $candidate->id }}"
                            data-name="{{ $candidate->name }}"
                            data-party="{{ $candidate->party }}"
                            data-party_full_name="{{ $candidate->party_full_name }}"
                            data-color="{{ $candidate->color }}"
                            data-election_type="{{ $candidate->electionTypeCategory?->electionType?->name ?? 'N/A' }}"
                            data-election_category="{{ $candidate->electionTypeCategory?->electionCategory?->name ?? 'N/A' }}"
                            data-election_category_code="{{ $candidate->electionTypeCategory?->electionCategory?->code ?? '' }}"
                            data-ballot_order="{{ $candidate->electionTypeCategory?->ballot_order ?? '' }}"
                            data-votes_per_person="{{ $candidate->electionTypeCategory?->votes_per_person ?? 1 }}"
                            data-list_order="{{ $candidate->list_order }}"
                            data-list_name="{{ $candidate->list_name }}"
                            data-department_name="{{ $candidate->department?->name ?? '' }}"
                            data-province_name="{{ $candidate->province?->name ?? '' }}"
                            data-municipality_name="{{ $candidate->municipality?->name ?? '' }}"
                            data-photo-url="{{ $candidate->photo_url }}"
                            data-party-logo-url="{{ $candidate->party_logo_url }}"
                            data-active="{{ $candidate->active ? '1' : '0' }}"
                            title="Ver detalles">
                            <i class="ri-eye-line"></i>
                        </button>
                        @endcan

                        @can('edit_candidatos')
                        <button type="button" class="btn btn-sm btn-warning edit-item-btn"
                            data-bs-toggle="modal" data-bs-target="#candidateModal"
                            data-id="{{ $candidate->id }}"
                            data-update-url="{{ route('candidates.update', $candidate->id) }}"
                            data-name="{{ $candidate->name }}"
                            data-party="{{ $candidate->party }}"
                            data-party_full_name="{{ $candidate->party_full_name }}"
                            data-color="{{ $candidate->color }}"
                            data-election_type_category_id="{{ $candidate->election_type_category_id }}"
                            data-list_order="{{ $candidate->list_order }}"
                            data-list_name="{{ $candidate->list_name }}"
                            data-department_id="{{ $candidate->department_id }}"
                            data-province_id="{{ $candidate->province_id }}"
                            data-municipality_id="{{ $candidate->municipality_id }}"
                            data-photo-url="{{ $candidate->photo_url }}"
                            data-party-logo-url="{{ $candidate->party_logo_url }}"
                            data-active="{{ $candidate->active ? '1' : '0' }}"
                            title="Editar">
                            <i class="ri-pencil-line"></i>
                        </button>
                        @endcan

                        @can('delete_candidatos')
                        <button class="btn btn-sm btn-danger remove-item-btn"
                            data-bs-toggle="modal" data-bs-target="#deleteRecordModal"
                            data-id="{{ $candidate->id }}"
                            data-name="{{ $candidate->name }}"
                            data-delete-url="{{ route('candidates.destroy', $candidate->id) }}"
                            title="Eliminar">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-center">
                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                            colors="primary:#121331,secondary:#08a88a"
                            style="width:75px;height:75px">
                        </lord-icon>
                        <h5 class="mt-2">Sin resultados</h5>
                        <p class="text-muted mb-2">No hay candidatos que coincidan con los filtros aplicados.</p>
                        <a href="{{ route('candidates.index') }}" class="btn btn-sm btn-primary">
                            <i class="ri-refresh-line me-1"></i> Limpiar filtros
                        </a>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
