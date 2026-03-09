

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="ri-file-upload-line me-1"></i>
                    Importar Mesas de Votación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="<?php echo e(route('voting-tables.import')); ?>" method="POST"
                  enctype="multipart/form-data">
                <?php echo csrf_field(); ?>

                <div class="modal-body">

                    
                    <div class="alert alert-info mb-3">
                        <div class="fw-semibold mb-2">
                            <i class="ri-list-check me-1"></i>
                            Columnas de la plantilla (reconocidas por nombre — el orden no importa):
                        </div>

                        <ul class="nav nav-tabs mb-2" id="importTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-table-tab"
                                        data-bs-toggle="tab" data-bs-target="#tab-table"
                                        type="button" role="tab">
                                    <i class="ri-table-line me-1"></i>Mesa (obligatorio)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-election-tab"
                                        data-bs-toggle="tab" data-bs-target="#tab-election"
                                        type="button" role="tab">
                                    <i class="ri-bar-chart-line me-1"></i>Electoral (opcional)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-delegates-tab"
                                        data-bs-toggle="tab" data-bs-target="#tab-delegates"
                                        type="button" role="tab">
                                    <i class="ri-group-line me-1"></i>Delegados (opcional)
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content bg-white rounded border p-2">

                            
                            <div class="tab-pane fade show active" id="tab-table" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Columna en la plantilla</th>
                                                <th>Obligatorio</th>
                                                <th>Descripción</th>
                                                <th>Ejemplo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-warning">
                                                <td><code>N° Mesa</code></td>
                                                <td><span class="badge bg-danger">Sí</span></td>
                                                <td>Número de la mesa (entero ≥ 1)</td>
                                                <td>1</td>
                                            </tr>
                                            <tr class="table-warning">
                                                <td><code>Recinto</code> o <code>Código Recinto</code></td>
                                                <td><span class="badge bg-danger">Sí</span></td>
                                                <td>Nombre exacto o código del recinto registrado en el sistema</td>
                                                <td>UNIDAD EDUCATIVA ADELA ZAMUDIO</td>
                                            </tr>
                                            <tr>
                                                <td><code>Código OEP</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Se genera automáticamente si se deja vacío</td>
                                                <td>REC-QUI-001-1A</td>
                                            </tr>
                                            <tr>
                                                <td><code>Código Interno</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Se genera automáticamente si se deja vacío</td>
                                                <td>REC-QUI-001-M01</td>
                                            </tr>
                                            <tr>
                                                <td><code>Letra</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Un carácter alfabético (ej. A, B)</td>
                                                <td>A</td>
                                            </tr>
                                            <tr>
                                                <td><code>Tipo</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td><code>mixta</code> | <code>masculina</code> | <code>femenina</code></td>
                                                <td>mixta</td>
                                            </tr>
                                            <tr>
                                                <td><code>Votantes Esperados</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Total de ciudadanos habilitados según padrón</td>
                                                <td>350</td>
                                            </tr>
                                            <tr>
                                                <td><code>Rango Desde (Apellido)</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Primer apellido del rango de votantes</td>
                                                <td>ACOSTA</td>
                                            </tr>
                                            <tr>
                                                <td><code>Rango Hasta (Apellido)</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Último apellido del rango de votantes</td>
                                                <td>ZEBALLOS</td>
                                            </tr>
                                            <tr>
                                                <td><code>Observaciones</code></td>
                                                <td><span class="badge bg-secondary">No</span></td>
                                                <td>Observaciones generales de la mesa</td>
                                                <td>Mesa accesible para discapacitados</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="alert alert-success mt-2 mb-0 py-2">
                                    <i class="ri-information-line me-1"></i>
                                    <small>
                                        <strong>No hay columna "Tipo Elección".</strong>
                                        La mesa queda habilitada automáticamente para
                                        <em>todos los tipos de elección activos</em> en el sistema.
                                    </small>
                                </div>
                            </div>

                            
                            <div class="tab-pane fade" id="tab-election" role="tabpanel">
                                <p class="text-muted small mb-2">
                                    Estos valores se aplican a <strong>todos</strong> los tipos de elección activos
                                    en <code>voting_table_elections</code>. Si se deja vacío, los valores por defecto son 0 / configurada.
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Columna</th>
                                                <th>Descripción</th>
                                                <th>Valores válidos / formato</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>Papeletas Recibidas</code></td>
                                                <td>Total de papeletas entregadas a la mesa</td>
                                                <td>Entero ≥ 0</td>
                                            </tr>
                                            <tr>
                                                <td><code>Papeletas Deterioradas</code></td>
                                                <td>Papeletas dañadas / inutilizadas</td>
                                                <td>Entero ≥ 0</td>
                                            </tr>
                                            <tr>
                                                <td><code>Total Votantes</code></td>
                                                <td>Votantes que efectivamente sufragaron</td>
                                                <td>Entero ≥ 0</td>
                                            </tr>
                                            <tr>
                                                <td><code>Estado</code></td>
                                                <td>Estado actual de la mesa en este tipo de elección</td>
                                                <td>
                                                    <code>configurada</code> · <code>en espera</code> ·
                                                    <code>votacion</code> · <code>cerrada</code> ·<br>
                                                    <code>en escrutinio</code> · <code>escrutada</code> ·
                                                    <code>observada</code> · <code>transmitida</code> · <code>anulada</code>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><code>Hora Apertura</code></td>
                                                <td>Hora de apertura de la mesa</td>
                                                <td>HH:MM (ej: 08:00)</td>
                                            </tr>
                                            <tr>
                                                <td><code>Hora Cierre</code></td>
                                                <td>Hora de cierre de la mesa</td>
                                                <td>HH:MM (ej: 17:00)</td>
                                            </tr>
                                            <tr>
                                                <td><code>Fecha Elección</code></td>
                                                <td>Fecha del acto electoral</td>
                                                <td>DD/MM/AAAA (ej: 22/03/2026)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            
                            <div class="tab-pane fade" id="tab-delegates" role="tabpanel">
                                <p class="text-muted small mb-2">
                                    Ingrese el <strong>correo electrónico</strong> o el <strong>carnet de identidad</strong>
                                    del usuario registrado en el sistema. Si no se encuentra, el campo queda vacío.
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr><th>Columna</th><th>Rol</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><code>Presidente</code></td><td>Presidente de mesa</td></tr>
                                            <tr><td><code>Secretario</code></td><td>Secretario de mesa</td></tr>
                                            <tr><td><code>Vocal 1</code></td><td>Vocal 1</td></tr>
                                            <tr><td><code>Vocal 2</code></td><td>Vocal 2</td></tr>
                                            <tr><td><code>Vocal 3</code></td><td>Vocal 3</td></tr>
                                            <tr><td><code>Vocal 4</code></td><td>Vocal 4 (opcional)</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>

                    
                    <div class="alert alert-success d-flex align-items-start gap-2 py-2">
                        <i class="ri-lightbulb-line fs-5 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Consejo:</strong> Descargue la plantilla oficial — incluye filas de ejemplo
                            con instrucciones en el pie.
                            <br>
                            <a href="<?php echo e(route('voting-tables.template')); ?>"
                               class="btn btn-sm btn-outline-success mt-2">
                                <i class="ri-file-download-line me-1"></i>
                                Descargar plantilla XLSX
                            </a>
                        </div>
                    </div>

                    
                    <div class="mb-1">
                        <label for="import_file_vt" class="form-label fw-semibold">
                            Archivo XLSX / XLS / CSV <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="import_file_vt"
                               name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">
                            Máximo 5 MB · Se recomienda XLSX para evitar problemas de codificación.
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-upload-2-line me-1"></i>Importar Mesas
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/partials/modal-import.blade.php ENDPATH**/ ?>