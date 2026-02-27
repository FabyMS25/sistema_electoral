{{-- resources/views/institutions/partials/actions-bar.blade.php --}}
<div class="row g-4 mb-3">
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2">
            @can('create_recintos')
            <a href="{{ route('institutions.create') }}" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Agregar Recinto
            </a>
            @endcan
            
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-download-line align-bottom me-1"></i> Excel
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('institutions.export') }}">
                            <i class="ri-file-excel-line me-2"></i> Exportar Datos
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('institutions.template') }}">
                            <i class="ri-file-download-line me-2"></i> Descargar Plantilla
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-file-upload-line me-2"></i> Importar Datos
                        </a>
                    </li>
                </ul>
            </div>
            
            <button class="btn btn-soft-danger" id="delete-multiple-btn" onclick="deleteMultiple()" style="display:none;">
                <i class="ri-delete-bin-2-line me-1"></i>
                Eliminar Seleccionados
            </button>
        </div>
    </div>
    
    <div class="col-sm">
        <div class="d-flex justify-content-sm-end">
            <form method="GET" action="{{ route('institutions.index') }}" class="d-flex">
                <div class="search-box me-2">
                    <input type="text" name="search" class="form-control search" 
                           placeholder="Buscar recinto..." value="{{ request('search') }}">
                    <i class="ri-search-line search-icon"></i>
                </div>
                <button type="submit" class="btn btn-soft-primary">
                    <i class="ri-search-line"></i>
                </button>
            </form>
        </div>
    </div>
</div>