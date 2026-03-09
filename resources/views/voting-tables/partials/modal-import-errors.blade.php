{{-- resources/views/voting-tables/partials/modal-import-errors.blade.php --}}
{{--
  Shown automatically when the import controller returns errors or partial success.
  Sessions used:
    import_errors   → array of ❌/⏭️ messages
    import_warnings → array of ⚠️ messages (optional)
    success_count   → int — rows that were actually inserted/updated
--}}
@if(session('import_errors') || session('import_warnings'))
<div class="modal fade" id="importErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header {{ session('success_count') ? 'bg-warning' : 'bg-danger' }}">
                <h5 class="modal-title text-white">
                    <i class="ri-alert-line me-1"></i>
                    {{ session('success_count') ? 'Importación con advertencias' : 'Errores de Importación' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                {{-- Success count --}}
                @if(session('success_count'))
                    <div class="alert alert-success d-flex gap-2">
                        <i class="ri-check-double-line fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>{{ session('success_count') }} mesa(s)</strong> importadas correctamente.
                            <br>
                            <small class="text-muted">
                                Se creó automáticamente un registro en <code>voting_table_elections</code>
                                para cada tipo de elección activo.
                            </small>
                        </div>
                    </div>
                @endif

                {{-- Errors --}}
                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-danger d-flex gap-2">
                        <i class="ri-error-warning-line fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>{{ count(session('import_errors')) }} fila(s) con errores</strong>
                            no pudieron procesarse:
                        </div>
                    </div>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Descripción del error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('import_errors') as $index => $error)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td class="text-danger">
                                            <i class="ri-error-warning-line me-1"></i>{{ $error }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Warnings --}}
                @if(session('import_warnings') && count(session('import_warnings')) > 0)
                    <div class="alert alert-warning d-flex gap-2">
                        <i class="ri-information-line fs-5 flex-shrink-0"></i>
                        <strong>{{ count(session('import_warnings')) }} aviso(s):</strong>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th>Aviso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(session('import_warnings') as $index => $warning)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td class="text-warning-emphasis">
                                            <i class="ri-information-line me-1"></i>{{ $warning }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Help hint --}}
                <div class="alert alert-light border mt-2 mb-0 py-2">
                    <i class="ri-lightbulb-line me-1 text-warning"></i>
                    <small>
                        Descargue la <strong>plantilla oficial</strong> para ver el formato correcto de cada columna.
                        Los <strong>recintos</strong> deben coincidir con el <em>nombre exacto</em>
                        o el <em>código</em> del sistema.
                    </small>
                </div>

            </div>

            <div class="modal-footer">
                <a href="{{ route('voting-tables.template') }}" class="btn btn-outline-info">
                    <i class="ri-download-line me-1"></i>Descargar Plantilla
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('importErrorModal');
    if (el) new bootstrap.Modal(el).show();
});
</script>
@endif
