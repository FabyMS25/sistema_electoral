<div class="table-responsive table-card mt-2 mb-1">
    <table class="table align-middle table-nowrap" id="customerTable">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                    </div>
                </th>
                <th>
                    <a href="{{ route('institutions.index', array_merge(request()->query(), ['sort' => 'code', 'direction' => request('sort') == 'code' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                       class="text-dark text-decoration-none">
                        Código
                        @if(request('sort') == 'code')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('institutions.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                       class="text-dark text-decoration-none">
                        Recinto
                        @if(request('sort') == 'name')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>Ubicación</th>
                <th>
                    <a href="{{ route('institutions.index', array_merge(request()->query(), ['sort' => 'registered_citizens', 'direction' => request('sort') == 'registered_citizens' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                       class="text-dark text-decoration-none">
                        Ciudadanos
                        @if(request('sort') == 'registered_citizens')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>Mesas</th>
                <th>Actas</th>
                <th>
                    <a href="{{ route('institutions.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc'])) }}"
                       class="text-dark text-decoration-none">
                        Estado
                        @if(request('sort') == 'status')
                            <i class="ri-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }}-line"></i>
                        @endif
                    </a>
                </th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            @forelse($institutions as $institution)
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="selected_ids[]" value="{{ $institution->id }}">
                        </div>
                    </th>
                    <td>
                        <span class="badge bg-info-subtle text-info">{{ $institution->code }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong>{{ $institution->name }}</strong>
                            @if($institution->short_name)
                                <small class="text-muted">{{ $institution->short_name }}</small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $institution->locality->municipality->name ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">
                                {{ $institution->locality->name ?? '' }}
                                @if($institution->district)
                                    <br>Distrito: {{ $institution->district->name }}
                                @endif
                                @if($institution->zone)
                                    <br>Zona: {{ $institution->zone->name }}
                                @endif
                            </small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">{{ number_format($institution->registered_citizens ?? 0) }}</span>
                            <small class="text-muted">Habilitados</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $institution->voting_tables_count ?? 0 }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <small class="text-primary">
                                <i class="ri-checkbox-circle-line"></i> C: {{ $institution->total_computed_records ?? 0 }}
                            </small>
                            <small class="text-danger">
                                <i class="ri-close-circle-line"></i> A: {{ $institution->total_annulled_records ?? 0 }}
                            </small>
                        </div>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'activo' => 'success',
                                'inactivo' => 'danger',
                                'en_mantenimiento' => 'warning'
                            ];
                            $statusLabels = [
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                                'en_mantenimiento' => 'Mantenimiento'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$institution->status] ?? 'secondary' }}-subtle text-{{ $statusColors[$institution->status] ?? 'secondary' }}">
                            {{ $statusLabels[$institution->status] ?? $institution->status }}
                        </span>
                        @if(!$institution->is_operative)
                            <br><small class="text-warning">No Operativo</small>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            @can('view_recintos')
                            <a href="{{ route('institutions.show', $institution->id) }}"
                               class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="ri-eye-line"></i>
                            </a>
                            @endcan

                            @can('edit_recintos')
                            <a href="{{ route('institutions.edit', $institution->id) }}"
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="ri-pencil-line"></i>
                            </a>
                            @endcan

                            @can('delete_recintos')
                            <button class="btn btn-sm btn-danger remove-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRecordModal"
                                data-id="{{ $institution->id }}"
                                data-name="{{ $institution->name }}"
                                data-delete-url="{{ route('institutions.destroy', $institution->id) }}"
                                title="Eliminar">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="noresult">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                <p class="text-muted mb-0">No hay recintos que coincidan con los filtros.</p>
                                <a href="{{ route('institutions.index') }}" class="btn btn-primary mt-3">
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
