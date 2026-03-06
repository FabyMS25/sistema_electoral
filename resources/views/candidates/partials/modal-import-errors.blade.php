@if(session('import_errors'))
<div class="modal fade" id="importErrorsModal" tabindex="-1" aria-labelledby="importErrorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="importErrorsModalLabel">
                    <i class="ri-error-warning-line me-1"></i>
                    Errores de Importación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="ri-information-line me-1"></i>
                    Se encontraron los siguientes errores durante la importación:
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('import_errors') as $index => $error)
                                <tr>
                                    <td class="text-muted">{{ $index + 1 }}</td>
                                    <td class="text-danger">{{ $error }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(session('success'))
                    <div class="alert alert-success mt-3">
                        <i class="ri-check-line me-1"></i>
                        {{ session('success') }}
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cerrar
                </button>
                @if(session('success'))
                    <a href="{{ route('candidates.index') }}" class="btn btn-primary">
                        <i class="ri-refresh-line me-1"></i> Actualizar
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var importErrorsModal = new bootstrap.Modal(document.getElementById('importErrorsModal'));
    importErrorsModal.show();
});
</script>
@endif
