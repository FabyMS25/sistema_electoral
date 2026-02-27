{{-- resources/views/voting-tables/partials/table.blade.php --}}
<div class="table-responsive table-card mt-3 mb-1">
    <table class="table align-middle table-nowrap">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll">
                    </div>
                </th>
                <th>Institución</th>
                <th>Código</th>
                <th>N° Mesa</th>
                <th>Electores</th>
                <th>Votaron</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            @forelse($votingTables as $table)
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="chk_child" value="{{ $table->id }}">
                        </div>
                    </th>
                    <td>
                        <div class="d-flex flex-column">
                            <strong>{{ $table->institution->name ?? 'N/A' }}</strong>
                            <small class="text-muted">{{ $table->institution->code ?? '' }}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info">{{ $table->code }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $table->number }}</span>
                        @if($table->letter)
                            <span class="badge bg-secondary">{{ $table->letter }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">{{ number_format($table->registered_citizens) }}</span>
                            <small class="text-muted">Habilitados</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold">{{ number_format($table->voted_citizens) }}</span>
                            <small class="text-muted">{{ $table->progress_percentage }}%</small>
                        </div>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'pendiente' => 'warning',
                                'en_proceso' => 'info',
                                'cerrado' => 'secondary',
                                'en_computo' => 'primary',
                                'computado' => 'success',
                                'observado' => 'danger',
                                'anulado' => 'dark'
                            ];
                            $statusLabels = [
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'cerrado' => 'Cerrado',
                                'en_computo' => 'En Cómputo',
                                'computado' => 'Computado',
                                'observado' => 'Observado',
                                'anulado' => 'Anulado'
                            ];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$table->status] }}-subtle text-{{ $statusColors[$table->status] }}">
                            {{ $statusLabels[$table->status] ?? $table->status }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            @can('view_mesas')
                            <a href="{{ route('voting-tables.show', $table->id) }}" 
                               class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="ri-eye-line"></i>
                            </a>
                            @endcan
                            
                            @can('edit_mesas')
                            <a href="{{ route('voting-tables.edit', $table->id) }}" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="ri-pencil-line"></i>
                            </a>
                            @endcan
                            
                            @can('delete_mesas')
                            <button class="btn btn-sm btn-danger remove-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRecordModal"
                                data-id="{{ $table->id }}"
                                data-code="{{ $table->code }}"
                                data-delete-url="{{ route('voting-tables.destroy', $table->id) }}"
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
                                <p class="text-muted mb-0">No hay mesas de votación registradas en el sistema.</p>
                                @can('create_mesas')
                                <a href="{{ route('voting-tables.create') }}" class="btn btn-primary mt-3">
                                    <i class="ri-add-line me-1"></i>Crear Primera Mesa
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