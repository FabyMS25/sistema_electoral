{{-- resources/views/institutions/partials/stats-cards.blade.php --}}
@php
    $totalInstitutions = $institutions->total();
    $totalCitizens = $institutions->sum('registered_citizens');
    $totalComputed = $institutions->sum('total_computed_records');
    $totalActive = $institutions->where('status', 'activo')->count();
    $totalOperative = $institutions->where('is_operative', true)->count();
@endphp

<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Total Recintos</h6>
                        <h3 class="mb-0 text-white">{{ number_format($totalInstitutions) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-building-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Ciudadanos Habilitados</h6>
                        <h3 class="mb-0 text-white">{{ number_format($totalCitizens) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-group-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Recintos Activos</h6>
                        <h3 class="mb-0 text-white">{{ number_format($totalActive) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-checkbox-circle-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Actas Computadas</h6>
                        <h3 class="mb-0 text-white">{{ number_format($totalComputed) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-file-text-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>