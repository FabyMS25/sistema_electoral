<div class="table-responsive table-card mt-3 mb-1">
    <table class="table align-middle table-nowrap" id="customerTable">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                    </div>
                </th>
                <th>
                    <a href="{{ route('candidates.index', array_merge(request()->query(), ['sort' => 'photo', 'direction' => request('sort') == 'photo' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-dark text-decoration-none">
                        Foto
                    </a>
                </th>
                <th>
                    <a href="{{ route('candidates.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-dark text-decoration-none">
                        Nombre
                        @if(request('sort') == 'name')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('candidates.index', array_merge(request()->query(), ['sort' => 'party', 'direction' => request('sort') == 'party' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-dark text-decoration-none">
                        Partido
                        @if(request('sort') == 'party')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>Color</th>
                <th>
                    <a href="{{ route('candidates.index', array_merge(request()->query(), ['sort' => 'election_type_category_id', 'direction' => request('sort') == 'election_type_category_id' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-dark text-decoration-none">
                        Elección / Categoría
                        @if(request('sort') == 'election_type_category_id')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('candidates.index', array_merge(request()->query(), ['sort' => 'type', 'direction' => request('sort') == 'type' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}" 
                       class="text-dark text-decoration-none">
                        Tipo
                        @if(request('sort') == 'type')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            @forelse($candidates as $candidate)
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="selected_ids[]" value="{{ $candidate->id }}">
                        </div>
                    </th>
                    <td class="photo">
                        @if($candidate->photo)
                            <img src="{{ $candidate->photo_url }}" alt="{{ $candidate->name }}" class="avatar-xs rounded-circle">
                        @else
                            <div class="avatar-xs bg-light rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-line text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td class="name">
                        <div class="fw-semibold">{{ $candidate->name }}</div>
                        @if($candidate->list_name)
                            <small class="text-muted">{{ $candidate->list_name }} (Orden: {{ $candidate->list_order }})</small>
                        @endif
                    </td>
                    <td class="party">
                        <div class="d-flex gap-2 align-items-center">
                            @if($candidate->party_logo)
                                <img src="{{ $candidate->party_logo_url }}" alt="{{ $candidate->party }}" class="avatar-xs rounded-circle">
                            @endif
                            <div>
                                <span class="fw-semibold">{{ $candidate->party }}</span>
                                @if($candidate->party_full_name)
                                    <br><small class="text-muted">{{ $candidate->party_full_name }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="color">
                        @if($candidate->color)
                            <div class="color-preview" style="background-color: {{ $candidate->color }}" 
                                 title="{{ $candidate->color }}"></div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="election_category">
                        @if($candidate->electionTypeCategory)
                            <span class="badge bg-primary-subtle text-primary">
                                {{ $candidate->electionTypeCategory->electionCategory->name ?? 'N/A' }}
                            </span>
                            <br>
                            <small class="text-muted">
                                {{ $candidate->electionTypeCategory->electionType->name ?? '' }}
                            </small>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td class="type">
                        <span class="badge 
                            @if($candidate->type === 'candidato') bg-success
                            @elseif($candidate->type === 'blank_votes') bg-warning
                            @elseif($candidate->type === 'null_votes') bg-danger
                            @else bg-secondary @endif">
                            {{ $typeOptions[$candidate->type] ?? $candidate->type }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            @can('view_candidatos')
                            <button type="button" class="btn btn-sm btn-info view-item-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#viewCandidateModal"
                                data-id="{{ $candidate->id }}"
                                data-name="{{ $candidate->name }}"
                                data-party="{{ $candidate->party }}"
                                data-party_full_name="{{ $candidate->party_full_name }}"
                                data-color="{{ $candidate->color }}"
                                data-election_type_category_id="{{ $candidate->election_type_category_id }}"
                                data-election_type="{{ $candidate->electionTypeCategory->electionType->name ?? 'N/A' }}"
                                data-election_category="{{ $candidate->electionTypeCategory->electionCategory->name ?? 'N/A' }}"
                                data-type="{{ $candidate->type }}"
                                data-list_order="{{ $candidate->list_order }}"
                                data-list_name="{{ $candidate->list_name }}"
                                data-department_id="{{ $candidate->department_id }}"
                                data-province_id="{{ $candidate->province_id }}"
                                data-municipality_id="{{ $candidate->municipality_id }}"
                                data-department_name="{{ $candidate->department->name ?? '' }}"
                                data-province_name="{{ $candidate->province->name ?? '' }}"
                                data-municipality_name="{{ $candidate->municipality->name ?? '' }}"
                                data-photo-url="{{ $candidate->photo_url }}"
                                data-party-logo-url="{{ $candidate->party_logo_url }}"
                                data-active="{{ $candidate->active ? '1' : '0' }}"
                                title="Ver detalles">
                                <i class="ri-eye-line"></i>
                            </button>
                            @endcan
                            @can('edit_candidatos')
                            <button type="button" class="btn btn-sm btn-warning edit-item-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#candidateModal"
                                data-id="{{ $candidate->id }}"
                                data-update-url="{{ route('candidates.update', $candidate->id) }}"
                                data-name="{{ $candidate->name }}"
                                data-party="{{ $candidate->party }}"
                                data-party_full_name="{{ $candidate->party_full_name }}"
                                data-color="{{ $candidate->color }}"
                                data-election_type_category_id="{{ $candidate->election_type_category_id }}"
                                data-type="{{ $candidate->type }}"
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
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRecordModal"
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
                    <td colspan="8" class="text-center py-4">
                        <div class="noresult">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                <p class="text-muted mb-0">No hay candidatos que coincidan con los filtros.</p>
                                <a href="{{ route('candidates.index') }}" class="btn btn-primary mt-3">
                                    <i class="ri-refresh-line me-1"></i>Limpiar filtros
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>