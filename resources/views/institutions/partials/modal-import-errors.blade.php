@if(session('import_errors'))
<div class="modal fade" id="importErrorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">
                    <i class="ri-alert-line me-1"></i>
                    Errores de Importación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(session('success_count'))
                <div class="alert alert-success">
                    <i class="ri-check-line me-1"></i>
                    <strong>{{ session('success_count') }} registros</strong> fueron importados correctamente.
                </div>
                @endif

                <div class="alert alert-warning">
                    <i class="ri-information-line me-1"></i>
                    <strong>Se encontraron errores durante la importación:</strong>
                    <br>
                    <span class="text-muted">Los siguientes registros no pudieron ser procesados correctamente.</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('import_errors') as $index => $error)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="text-danger">
                                        <i class="ri-error-warning-line me-1"></i>
                                        {{ $error }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cerrar
                </button>
                <a href="{{ route('institutions.template') }}" class="btn btn-info">
                    <i class="ri-download-line me-1"></i>Descargar Plantilla
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importErrorModal = new bootstrap.Modal(document.getElementById('importErrorModal'));
    importErrorModal.show();
});
</script>
@endif
