{{-- resources/views/candidates/partials/modal-import.blade.php --}}
<div class="modal fade" id="importModal" tabindex="-1"
     aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="ri-file-upload-line me-1"></i>
                    Importar Candidatos desde CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <form action="{{ route('candidates.import') }}" method="POST"
                  enctype="multipart/form-data">
                @csrf

                <div class="modal-body">

                    {{-- ── Column reference ── --}}
                    <div class="alert alert-info mb-3">
                        <div class="fw-semibold mb-2">
                            <i class="ri-list-check me-1"></i>
                            Columnas requeridas en el CSV:
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 bg-white">
                                <thead class="table-light">
                                    <tr>
                                        <th>Columna</th>
                                        <th>Obligatorio</th>
                                        <th>Descripción</th>
                                        <th>Ejemplo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>nombre</code></td>
                                        <td><span class="badge bg-danger">Sí</span></td>
                                        <td>Nombre completo del candidato</td>
                                        <td>Juan Pérez</td>
                                    </tr>
                                    <tr>
                                        <td><code>partido</code></td>
                                        <td><span class="badge bg-danger">Sí</span></td>
                                        <td>Sigla del partido</td>
                                        <td>MAS</td>
                                    </tr>
                                    <tr>
                                        <td><code>nombre_completo_partido</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Nombre oficial del partido</td>
                                        <td>Movimiento Al Socialismo</td>
                                    </tr>
                                    <tr>
                                        <td><code>color</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Color hexadecimal</td>
                                        <td>#1b8af8</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><code>tipo_eleccion</code></td>
                                        <td><span class="badge bg-danger">Sí</span></td>
                                        <td>
                                            Nombre exacto del tipo de elección
                                            <br><small class="text-muted">Ver hoja de referencia en la plantilla</small>
                                        </td>
                                        <td class="font-monospace small">Elecciones Subnacionales 2026</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><code>codigo_categoria</code></td>
                                        <td><span class="badge bg-danger">Sí</span></td>
                                        <td>
                                            Código de categoría
                                            <br><small class="text-muted">GOB · AST · ASP · ALC · CON</small>
                                        </td>
                                        <td><code>ALC</code></td>
                                    </tr>
                                    <tr>
                                        <td><code>orden_lista</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Número de orden en la lista</td>
                                        <td>1</td>
                                    </tr>
                                    <tr>
                                        <td><code>nombre_lista</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Nombre de la lista</td>
                                        <td>Lista Única</td>
                                    </tr>
                                    <tr>
                                        <td><code>departamento</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>
                                            Nombre del departamento
                                            <br><small class="text-muted">Ver hoja de referencia en la plantilla</small>
                                        </td>
                                        <td>Cochabamba</td>
                                    </tr>
                                    <tr>
                                        <td><code>provincia</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Nombre de la provincia</td>
                                        <td>Quillacollo</td>
                                    </tr>
                                    <tr>
                                        <td><code>municipio</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Nombre del municipio</td>
                                        <td>Quillacollo</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ── Tip: use the template ── --}}
                    <div class="alert alert-success d-flex align-items-start gap-2 py-2">
                        <i class="ri-lightbulb-line fs-5 flex-shrink-0 mt-1"></i>
                        <div>
                            <strong>Consejo:</strong> Descargue la plantilla — incluye un ejemplo
                            y una hoja de referencia con todos los valores válidos de
                            <code>tipo_eleccion</code>, <code>codigo_categoria</code> y
                            <code>departamento</code> de su base de datos.
                            <br>
                            <a href="{{ route('candidates.template') }}"
                               class="btn btn-sm btn-outline-success mt-2">
                                <i class="ri-file-download-line me-1"></i>
                                Descargar plantilla CSV
                            </a>
                        </div>
                    </div>

                    {{-- ── File input ── --}}
                    <div class="mb-2">
                        <label for="import_file" class="form-label fw-semibold">
                            Archivo CSV <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="import_file"
                               name="import_file" accept=".csv,.txt" required>
                        <small class="text-muted">
                            Máximo 5 MB · Codificación UTF-8 recomendada para tildes y ñ
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-upload-2-line me-1"></i> Importar
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
