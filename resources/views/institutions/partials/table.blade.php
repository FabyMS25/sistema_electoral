{{-- resources/views/institutions/partials/table.blade.php --}}
<div class="table-responsive table-card mt-3 mb-1">
    <table class="table align-middle table-nowrap" id="customerTable">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                    </div>
                </th>
                <th class="sort" data-sort="institution_code">Código</th>
                <th class="sort" data-sort="institution_name">Recinto</th>
                <th class="sort" data-sort="location">Ubicación</th>
                <th class="sort" data-sort="citizens">Ciudadanos</th>
                <th class="sort" data-sort="mesas">Mesas</th>
                <th class="sort" data-sort="actas">Actas</th>
                <th class="sort" data-sort="status">Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            @forelse($institutions as $institution)
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" 
                                   name="chk_child" value="{{ $institution->id }}">
                        </div>
                    </th>
                    <td class="institution_code">
                        <span class="badge bg-info-subtle text-info">{{ $institution->code }}</span>
                    </td>
                    <td class="institution_name">
                        <div class="d-flex align-items-center">
                            <div>
                                <h5 class="fs-14 mb-1">{{ $institution->name }}</h5>
                                <small class="text-muted">{{ $institution->short_name ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="location" data-location="{{ $institution->locality->municipality->name ?? '' }}">
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
                    <td class="citizens" data-citizens="{{ $institution->registered_citizens ?? 0 }}">
                        <span class="fw-semibold">{{ number_format($institution->registered_citizens ?? 0) }}</span>
                    </td>
                    <td class="mesas" data-mesas="{{ $institution->voting_tables_count ?? 0 }}">
                        <span class="badge bg-primary">{{ $institution->voting_tables_count ?? 0 }}</span>
                    </td>
                    <td class="actas">
                        <div class="d-flex flex-column gap-1">
                            <small class="text-primary">
                                <i class="ri-checkbox-circle-line"></i> C: {{ $institution->total_computed_records ?? 0 }}
                            </small>
                            <small class="text-danger">
                                <i class="ri-close-circle-line"></i> A: {{ $institution->total_annulled_records ?? 0 }}
                            </small>
                            <small class="text-success">
                                <i class="ri-check-line"></i> H: {{ $institution->total_enabled_records ?? 0 }}
                            </small>
                        </div>
                    </td>
                    <td class="status" data-status="{{ $institution->status }}">
                        @if($institution->status == 'activo')
                            <span class="badge bg-success-subtle text-success">Activo</span>
                        @elseif($institution->status == 'inactivo')
                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">Mantenimiento</span>
                        @endif
                        
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
                                <p class="text-muted mb-0">No hay recintos registrados en el sistema.</p>
                                @can('create_recintos')
                                <a href="{{ route('institutions.create') }}" class="btn btn-primary mt-3">
                                    <i class="ri-add-line me-1"></i>Crear Primer Recinto
                                </a>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>