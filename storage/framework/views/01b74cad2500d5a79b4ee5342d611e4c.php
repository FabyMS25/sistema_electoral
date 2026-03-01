<?php echo $__env->make('partials.dashboard-filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div id="loading-indicator" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 9999; background: #fff; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span>Actualizando datos...</span>
    </div>
</div>
<div class="row">
    <!-- Tarjeta de Votos Totales - Alcalde -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-primary card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Votos Alcalde</p>
                        <h3 class="text-white mb-0"><span class="counter-value" data-target="<?php echo e($totalVotesAlcalde); ?>">0</span></h3>
                        <small class="text-white text-opacity-75">Emitidos en vivo</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2">
                            <i class="ri-bar-chart-2-line text-white"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-white text-primary me-2"><?php echo e($progressPercentage); ?>%</span>
                        <span class="text-white text-opacity-75">mesas reportadas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Votos Totales - Concejal -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-info card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Votos Concejal</p>
                        <h3 class="text-white mb-0"><span class="counter-value" data-target="<?php echo e($totalVotesConcejal); ?>">0</span></h3>
                        <small class="text-white text-opacity-75">Emitidos en vivo</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2">
                            <i class="ri-bar-chart-grouped-line text-white"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-white text-info me-2"><?php echo e($progressPercentage); ?>%</span>
                        <span class="text-white text-opacity-75">mesas reportadas</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Mesas Reportadas -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-success card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Mesas</p>
                        <h3 class="text-white mb-0">
                            <span class="counter-value" data-target="<?php echo e($reportedTables); ?>">0</span>/<span class="fs-5"><?php echo e($totalTables); ?></span>
                        </h3>
                        <small class="text-white text-opacity-75">Reportadas</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2">
                            <i class="ri-table-line text-white"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress bg-white bg-opacity-25" style="height: 6px;">
                        <div class="progress-bar bg-white" role="progressbar" style="width: <?php echo e($progressPercentage); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Candidato Líder (Alcalde) -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-warning card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Líder Alcalde</p>
                        <?php if(count($alcaldeStats) > 0): ?>
                            <?php 
                                $sortedAlcalde = collect($alcaldeStats)->sortByDesc('votes')->first();
                                $leadingAlcalde = $sortedAlcalde;
                            ?>
                            <h5 class="text-white mb-0"><?php echo e($leadingAlcalde['candidate']->name ?? 'N/A'); ?></h5>
                            <small class="text-white text-opacity-75">
                                <?php echo e(number_format($leadingAlcalde['votes'])); ?> votos (<?php echo e($leadingAlcalde['percentage']); ?>%)
                            </small>
                        <?php else: ?>
                            <h5 class="text-white mb-0">Sin votos</h5>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2">
                            <i class="ri-crown-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda fila: Líder Concejal y Participación -->
    <div class="col-xl-3 col-md-6 mt-3">
        <div class="card bg-gradient-purple card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Líder Concejal</p>
                        <?php if(count($concejalStats) > 0): ?>
                            <?php 
                                $sortedConcejal = collect($concejalStats)->sortByDesc('votes')->first();
                                $leadingConcejal = $sortedConcejal;
                            ?>
                            <h5 class="text-white mb-0"><?php echo e($leadingConcejal['candidate']->name ?? 'N/A'); ?></h5>
                            <small class="text-white text-opacity-75">
                                <?php echo e(number_format($leadingConcejal['votes'])); ?> votos (<?php echo e($leadingConcejal['percentage']); ?>%)
                            </small>
                        <?php else: ?>
                            <h5 class="text-white mb-0">Sin votos</h5>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2">
                            <i class="ri-group-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mt-3">
        <div class="card bg-gradient-pink card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Participación</p>
                        <h3 class="text-white mb-0">
                            <span class="counter-value" data-target="<?php echo e($reportedTables > 0 ? round(($totalVotesAlcalde / ($reportedTables * 300)) * 100, 1) : 0); ?>">0</span>%
                        </h3>
                        <small class="text-white text-opacity-75">promedio por mesa</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2">
                            <i class="ri-pie-chart-line text-white"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tendencia de Votos - Alcalde</h5>
            </div>
            <div class="card-body">
                <div id="candidates_trend_chart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución Alcalde</h5>
            </div>
            <div class="card-body">
                <div id="candidates_pie_chart_alcalde"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución Concejal</h5>
            </div>
            <div class="card-body">
                <div id="candidates_pie_chart_concejal"></div>
            </div>
        </div>
    </div>
</div>
<!-- Main Visualization Row - Redesigned -->
<div class="row mt-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Alcalde - Tendencia</h5>
                    <div class="flex-shrink-0">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-primary active" data-chart-view="bars-alcalde">Barras</button>
                            <button type="button" class="btn btn-sm btn-soft-primary" data-chart-view="line-alcalde">Línea</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="candidates_trend_chart_alcalde" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Concejal - Tendencia</h5>
                    <div class="flex-shrink-0">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-primary active" data-chart-view="bars-concejal">Barras</button>
                            <button type="button" class="btn btn-sm btn-soft-primary" data-chart-view="line-concejal">Línea</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="candidates_trend_chart_concejal" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row - Pie Charts -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución Alcalde</h5>
            </div>
            <div class="card-body">
                <div id="candidates_pie_chart_alcalde" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución Concejal</h5>
            </div>
            <div class="card-body">
                <div id="candidates_pie_chart_concejal" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Third Row - Localities Analysis -->
<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso por Localidad</h5>
            </div>
            <div class="card-body">
                <div id="locality_radial_chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Mapa de Calor - Alcalde por Localidad</h5>
            </div>
            <div class="card-body">
                <div id="candidates_heatmap_alcalde" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fourth Row - Additional Heatmap for Concejal -->
<div class="row">
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Mapa de Calor - Concejal por Localidad</h5>
            </div>
            <div class="card-body">
                <div id="candidates_heatmap_concejal" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso General</h5>
            </div>
            <div class="card-body">
                <div id="progress_gauge" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fifth Row - Detailed Data Tabs -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#alcalde_tab" role="tab">
                            <i class="ri-user-star-line me-1 align-bottom"></i> Alcalde
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#concejal_tab" role="tab">
                            <i class="ri-user-star-line me-1 align-bottom"></i> Concejal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#localities_tab" role="tab">
                            <i class="ri-map-pin-line me-1 align-bottom"></i> Localidades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tables_tab" role="tab">
                            <i class="ri-table-line me-1 align-bottom"></i> Estado de Mesas
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Alcalde Tab -->
                    <div class="tab-pane active" id="alcalde_tab" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Posición</th>
                                        <th>Candidato</th>
                                        <th>Partido</th>
                                        <th>Votos</th>
                                        <th>Porcentaje</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $sortedAlcalde = collect($alcaldeStats)->sortByDesc('votes')->values();
                                        $prevVotes = null;
                                    ?>
                                    <?php $__currentLoopData = $sortedAlcalde; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php 
                                            $candidate = $stats['candidate'];
                                            $difference = $prevVotes !== null ? $prevVotes - $stats['votes'] : 0;
                                            $prevVotes = $stats['votes'];
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?php echo e($index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'secondary'))); ?> rounded-pill fs-12">
                                                    #<?php echo e($index + 1); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if($candidate->photo): ?>
                                                        <img src="<?php echo e(asset('storage/' . $candidate->photo)); ?>" 
                                                             alt="<?php echo e($candidate->name); ?>" 
                                                             class="rounded-circle avatar-sm me-2"
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-2">
                                                            <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                                <?php echo e(substr($candidate->name, 0, 1)); ?>

                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo e($candidate->name); ?></h6>
                                                        <small class="text-muted"><?php echo e($candidate->party); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark"><?php echo e($candidate->party); ?></span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0"><?php echo e(number_format($stats['votes'])); ?></h6>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold"><?php echo e($stats['percentage']); ?>%</span>
                                                    <div class="progress" style="width: 80px; height: 6px;">
                                                        <div class="progress-bar bg-<?php echo e($index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'secondary'))); ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo e($stats['percentage']); ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($index > 0): ?>
                                                    <span class="text-muted">
                                                        <i class="ri-arrow-down-line text-danger"></i>
                                                        <?php echo e(number_format($difference)); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-success">
                                                        <i class="ri-arrow-up-line"></i> Líder
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Concejal Tab -->
                    <div class="tab-pane" id="concejal_tab" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Posición</th>
                                        <th>Candidato</th>
                                        <th>Partido</th>
                                        <th>Votos</th>
                                        <th>Porcentaje</th>
                                        <th>Diferencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $sortedConcejal = collect($concejalStats)->sortByDesc('votes')->values();
                                        $prevVotes = null;
                                    ?>
                                    <?php $__currentLoopData = $sortedConcejal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php 
                                            $candidate = $stats['candidate'];
                                            $difference = $prevVotes !== null ? $prevVotes - $stats['votes'] : 0;
                                            $prevVotes = $stats['votes'];
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-<?php echo e($index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'secondary'))); ?> rounded-pill fs-12">
                                                    #<?php echo e($index + 1); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if($candidate->photo): ?>
                                                        <img src="<?php echo e(asset('storage/' . $candidate->photo)); ?>" 
                                                             alt="<?php echo e($candidate->name); ?>" 
                                                             class="rounded-circle avatar-sm me-2"
                                                             style="width: 32px; height: 32px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="avatar-sm me-2">
                                                            <span class="avatar-title rounded-circle bg-soft-info text-info">
                                                                <?php echo e(substr($candidate->name, 0, 1)); ?>

                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-0"><?php echo e($candidate->name); ?></h6>
                                                        <small class="text-muted"><?php echo e($candidate->party); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark"><?php echo e($candidate->party); ?></span>
                                            </td>
                                            <td>
                                                <h6 class="mb-0"><?php echo e(number_format($stats['votes'])); ?></h6>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold"><?php echo e($stats['percentage']); ?>%</span>
                                                    <div class="progress" style="width: 80px; height: 6px;">
                                                        <div class="progress-bar bg-<?php echo e($index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'secondary'))); ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo e($stats['percentage']); ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($index > 0): ?>
                                                    <span class="text-muted">
                                                        <i class="ri-arrow-down-line text-danger"></i>
                                                        <?php echo e(number_format($difference)); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-success">
                                                        <i class="ri-arrow-up-line"></i> Líder
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Localities Tab (actualizado para mostrar ambas categorías) -->
                    <div class="tab-pane" id="localities_tab" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Localidad</th>
                                        <th>Municipio</th>
                                        <th>Mesas</th>
                                        <th>Reportadas</th>
                                        <th>Avance</th>
                                        <th>Votos Alcalde</th>
                                        <th>Votos Concejal</th>
                                        <th>Ganador Alcalde</th>
                                        <th>Ganador Concejal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $localityStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $progress = $locality->total_tables > 0 ? round(($locality->reported_tables / $locality->total_tables) * 100) : 0;
                                            $localityData = $localityResults[$locality->id] ?? null;
                                            
                                            // Ganador Alcalde
                                            $winningAlcalde = null;
                                            $maxVotesAlcalde = 0;
                                            
                                            // Ganador Concejal
                                            $winningConcejal = null;
                                            $maxVotesConcejal = 0;
                                            
                                            if ($localityData && isset($localityData['candidates'])) {
                                                // Alcalde (ALC)
                                                if (isset($localityData['candidates']['ALC'])) {
                                                    foreach ($localityData['candidates']['ALC'] as $candidate) {
                                                        if ($candidate['votes'] > $maxVotesAlcalde) {
                                                            $maxVotesAlcalde = $candidate['votes'];
                                                            $winningAlcalde = $candidate;
                                                        }
                                                    }
                                                }
                                                
                                                // Concejal (CON)
                                                if (isset($localityData['candidates']['CON'])) {
                                                    foreach ($localityData['candidates']['CON'] as $candidate) {
                                                        if ($candidate['votes'] > $maxVotesConcejal) {
                                                            $maxVotesConcejal = $candidate['votes'];
                                                            $winningConcejal = $candidate;
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                        <tr>
                                            <td><strong><?php echo e($locality->name); ?></strong></td>
                                            <td><?php echo e($locality->municipality_name); ?></td>
                                            <td><?php echo e($locality->total_tables); ?></td>
                                            <td><?php echo e($locality->reported_tables); ?></td>
                                            <td style="width: 150px;">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold"><?php echo e($progress); ?>%</span>
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar bg-info" role="progressbar" 
                                                             style="width: <?php echo e($progress); ?>%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo e(number_format($localityData['total_votes_alcalde'] ?? 0)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo e(number_format($localityData['total_votes_concejal'] ?? 0)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <?php if($winningAlcalde): ?>
                                                    <span class="badge bg-success">
                                                        <?php echo e($winningAlcalde['name']); ?> (<?php echo e($winningAlcalde['percentage']); ?>%)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin datos</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($winningConcejal): ?>
                                                    <span class="badge bg-info">
                                                        <?php echo e($winningConcejal['name']); ?> (<?php echo e($winningConcejal['percentage']); ?>%)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Sin datos</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tables Tab (sin cambios) -->
                    <div class="tab-pane" id="tables_tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border shadow-none">
                                    <div class="card-body text-center">
                                        <div class="avatar-md mx-auto mb-3">
                                            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                                                <i class="ri-table-line"></i>
                                            </span>
                                        </div>
                                        <h4 class="mb-1"><?php echo e($totalTables); ?></h4>
                                        <p class="text-muted mb-0">Total de Mesas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border shadow-none">
                                    <div class="card-body text-center">
                                        <div class="avatar-md mx-auto mb-3">
                                            <span class="avatar-title bg-success-subtle text-success rounded-circle fs-1">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </span>
                                        </div>
                                        <h4 class="mb-1"><?php echo e($reportedTables); ?></h4>
                                        <p class="text-muted mb-0">Mesas Reportadas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border shadow-none">
                                    <div class="card-body text-center">
                                        <div class="avatar-md mx-auto mb-3">
                                            <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-1">
                                                <i class="ri-time-line"></i>
                                            </span>
                                        </div>
                                        <h4 class="mb-1"><?php echo e($totalTables - $reportedTables); ?></h4>
                                        <p class="text-muted mb-0">Mesas Pendientes</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso por Localidad</h5>
            </div>
            <div class="card-body">
                <div id="locality_radial_chart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso General</h5>
            </div>
            <div class="card-body">
                <div id="progress_gauge"></div>
            </div>
        </div>
    </div>
</div>


<div class="auto-refresh-controls" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary" onclick="refreshDashboard()" title="Actualizar ahora">
            <i class="ri-refresh-line"></i>
        </button>
        <button class="btn btn-outline-success" onclick="startAutoRefresh()" title="Iniciar auto-actualización">
            <i class="ri-play-line"></i>
        </button>
        <button class="btn btn-outline-secondary" onclick="stopAutoRefresh()" title="Pausar auto-actualización">
            <i class="ri-pause-line"></i>
        </button>
    </div>
    <div class="mt-1">
        <small class="text-muted">Auto: 2 min</small>
    </div>
    <div id="refresh-status" class="mt-1">
        <small class="text-success">● Activo</small>
    </div>
</div>

<?php $__env->startSection('dashboard-scripts'); ?>
    <style>
        .bg-gradient-primary { background: linear-gradient(135deg, #405189 0%, #2a3a6b 100%); }
        .bg-gradient-success { background: linear-gradient(135deg, #0ab39c 0%, #078b7a 100%); }
        .bg-gradient-warning { background: linear-gradient(135deg, #f7b84b 0%, #f5a219 100%); }
        .bg-gradient-info { background: linear-gradient(135deg, #299cdb 0%, #1b7ab3 100%); }
        .bg-gradient-purple { background: linear-gradient(135deg, #9b59b6 0%, #6c3483 100%); }
        .bg-gradient-pink { background: linear-gradient(135deg, #fd6e8a 0%, #f5496b 100%); }
        .table-status .card { transition: transform 0.2s; }
        .table-status .card:hover { transform: translateY(-5px); }
    </style>
    <script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/jsvectormap/jsvectormap.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/jsvectormap/maps/quillacollo-merc.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
    <script>      
        document.addEventListener('DOMContentLoaded', function() { 
            let refreshInterval = 120000;
            let refreshTimer = null;
            let isRefreshing = false;      
            let charts = {};            
            initializeCharts();
            startAutoRefresh();  
            document.querySelectorAll('[data-chart-view]').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('[data-chart-view]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');                    
                    const view = this.getAttribute('data-chart-view');
                    updateChartView(view);
                });
            });
            
            function initializeCharts() {
                // ===== DATOS DE ALCALDE =====
                const sortedAlcaldeStats = Object.values(<?php echo json_encode($alcaldeStats, 15, 512) ?>).sort((a, b) => b.votes - a.votes);
                const alcaldeNames = sortedAlcaldeStats.map(stat => stat.candidate.name);
                const alcaldeColors = sortedAlcaldeStats.map(stat => stat.candidate.color || '#405189');
                const alcaldeVotes = sortedAlcaldeStats.map(stat => stat.votes);
                const alcaldePercentages = sortedAlcaldeStats.map(stat => stat.percentage);                
                // ===== DATOS DE CONCEJAL =====
                const sortedConcejalStats = Object.values(<?php echo json_encode($concejalStats, 15, 512) ?>).sort((a, b) => b.votes - a.votes);
                const concejalNames = sortedConcejalStats.map(stat => stat.candidate.name);
                const concejalColors = sortedConcejalStats.map(stat => stat.candidate.color || '#299cdb');
                const concejalVotes = sortedConcejalStats.map(stat => stat.votes);
                const concejalPercentages = sortedConcejalStats.map(stat => stat.percentage);                
                // ===== DATOS DE LOCALIDADES =====
                const localityData = Object.values(<?php echo json_encode($localityResults, 15, 512) ?>);
                const localityNames = localityData.map(l => l.name).slice(0, 6);                
                // 1. Trend Chart (Alcalde por defecto)
                var trendOptions = {
                    series: [
                        {
                            name: 'Votos Alcalde',
                            type: 'column',
                            data: alcaldeVotes
                        },
                        {
                            name: 'Porcentaje',
                            type: 'line',
                            data: alcaldePercentages
                        }
                    ],
                    chart: {
                        height: 350,
                        type: 'line',
                        toolbar: {
                            show: true
                        }
                    },
                    stroke: {
                        width: [0, 4]
                    },
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [1],
                        formatter: function(val) {
                            return val + '%';
                        }
                    },
                    labels: alcaldeNames,
                    xaxis: {
                        type: 'category'
                    },
                    yaxis: [
                        {
                            title: {
                                text: 'Votos'
                            }
                        },
                        {
                            opposite: true,
                            title: {
                                text: 'Porcentaje'
                            },
                            max: 100,
                            labels: {
                                formatter: function(val) {
                                    return val + '%';
                                }
                            }
                        }
                    ],
                    colors: ['#405189', '#f7b84b'],
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: function(val, { seriesIndex }) {
                                if (seriesIndex === 0) return val.toLocaleString() + ' votos';
                                return val + '%';
                            }
                        }
                    }
                };
                
                charts.trendChart = new ApexCharts(document.querySelector("#candidates_trend_chart"), trendOptions);
                charts.trendChart.render();
                
                // 2. Pie Chart Alcalde
                var pieOptionsAlcalde = {
                    series: alcaldeVotes,
                    chart: {
                        type: 'pie',
                        height: 350,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    labels: alcaldeNames,
                    colors: alcaldeColors,
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        text: 'Alcalde',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.globals.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString() + ' votos';
                            }
                        }
                    },
                    plotOptions: {
                        pie: {
                            expandOnClick: true,
                            donut: {
                                size: '40%'
                            }
                        }
                    }
                };
                
                charts.pieChartAlcalde = new ApexCharts(document.querySelector("#candidates_pie_chart_alcalde"), pieOptionsAlcalde);
                charts.pieChartAlcalde.render();
                
                // 3. Pie Chart Concejal
                var pieOptionsConcejal = {
                    series: concejalVotes,
                    chart: {
                        type: 'pie',
                        height: 350,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    labels: concejalNames,
                    colors: concejalColors,
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        text: 'Concejal',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 600
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.globals.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString() + ' votos';
                            }
                        }
                    },
                    plotOptions: {
                        pie: {
                            expandOnClick: true,
                            donut: {
                                size: '40%'
                            }
                        }
                    }
                };
                
                charts.pieChartConcejal = new ApexCharts(document.querySelector("#candidates_pie_chart_concejal"), pieOptionsConcejal);
                charts.pieChartConcejal.render();
                
                // 4. Radial Bar Chart for Localities
                const localityProgress = localityData.map(l => {
                    const totalTables = l.total_tables || 0;
                    const reportedTables = l.reported_tables || 0;
                    return totalTables > 0 ? Math.round((reportedTables / totalTables) * 100) : 0;
                });                
                var radialOptions = {
                    series: localityProgress.slice(0, 6),
                    chart: {
                        height: 350,
                        type: 'radialBar',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            dataLabels: {
                                name: {
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    offsetY: -10
                                },
                                value: {
                                    fontSize: '16px',
                                    fontWeight: 500,
                                    offsetY: 5,
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Promedio',
                                    formatter: function(w) {
                                        const avg = w.globals.series.reduce((a, b) => a + b, 0) / w.globals.series.length;
                                        return Math.round(avg) + '%';
                                    }
                                }
                            }
                        }
                    },
                    labels: localityNames,
                    colors: ['#0ab39c', '#405189', '#f7b84b', '#299cdb', '#e66b6b', '#9b59b6'],
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + '% reportado';
                            }
                        }
                    }
                };
                
                charts.radialChart = new ApexCharts(document.querySelector("#locality_radial_chart"), radialOptions);
                charts.radialChart.render();                
                // 5. Progress Gauge
                var gaugeOptions = {
                    series: [<?php echo e($progressPercentage); ?>],
                    chart: {
                        type: 'radialBar',
                        height: 300,
                        offsetY: -20,
                        sparkline: {
                            enabled: true
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -90,
                            endAngle: 90,
                            track: {
                                background: "#e7e7e7",
                                strokeWidth: '97%',
                                margin: 5,
                                dropShadow: {
                                    enabled: true,
                                    top: 2,
                                    left: 0,
                                    opacity: 0.1,
                                    blur: 2
                                }
                            },
                            dataLabels: {
                                name: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    offsetY: -10
                                },
                                value: {
                                    show: true,
                                    fontSize: '26px',
                                    fontWeight: 700,
                                    offsetY: -25,
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Progreso',
                                    fontSize: '14px',
                                    fontWeight: 400,
                                    formatter: function(w) {
                                        return w.globals.series[0] + '%';
                                    }
                                }
                            }
                        }
                    },
                    labels: ['Mesas Reportadas'],
                    colors: ['#0ab39c'],
                    tooltip: {
                        enabled: false
                    }
                };                
                charts.gaugeChart = new ApexCharts(document.querySelector("#progress_gauge"), gaugeOptions);
                charts.gaugeChart.render();
            }
            
            function updateChartView(view) {
                if (!charts.trendChart) return;                
                const sortedAlcaldeStats = Object.values(<?php echo json_encode($alcaldeStats, 15, 512) ?>).sort((a, b) => b.votes - a.votes);
                const alcaldeVotes = sortedAlcaldeStats.map(stat => stat.votes);
                const alcaldePercentages = sortedAlcaldeStats.map(stat => stat.percentage);
                if (view === 'bars') {
                    charts.trendChart.updateOptions({
                        series: [
                            { name: 'Votos Alcalde', type: 'column', data: alcaldeVotes },
                            { name: 'Porcentaje', type: 'line', data: alcaldePercentages }
                        ]
                    });
                } else {
                    charts.trendChart.updateOptions({
                        series: [
                            { name: 'Votos Alcalde', type: 'line', data: alcaldeVotes },
                            { name: 'Porcentaje', type: 'line', data: alcaldePercentages }
                        ]
                    });
                }
            }
            
            function refreshDashboard() {
                if (isRefreshing) return;                
                isRefreshing = true;
                showLoadingIndicator();
                const electionType = document.querySelector('select[name="election_type"]').value;
                const department = document.querySelector('select[name="department"]').value;
                const province = document.querySelector('select[name="province"]').value;
                const municipality = document.querySelector('select[name="municipality"]').value;
                fetch(`/refresh-dashboard?election_type=${electionType}&department=${department}&province=${province}&municipality=${municipality}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateDashboard(data.data);
                            updateLastUpdateTime();
                        }
                        isRefreshing = false;
                        hideLoadingIndicator();
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        isRefreshing = false;
                        hideLoadingIndicator();
                    });
            }
            
            function updateDashboard(data) {
                updateCounter('.bg-gradient-primary .counter-value', data.totalVotesAlcalde);
                updateCounter('.bg-gradient-info .counter-value', data.totalVotesConcejal);
                updateCounter('.bg-gradient-success .counter-value', data.reportedTables);
                const progressBar = document.querySelector('.bg-gradient-success .progress-bar');
                if (progressBar) {
                    progressBar.style.width = data.progressPercentage + '%';
                }
                if (Object.keys(data.alcaldeStats).length > 0) {
                    const sortedAlcalde = Object.values(data.alcaldeStats).sort((a, b) => b.votes - a.votes);
                    const leadingAlcalde = sortedAlcalde[0];
                    const alcaldeContainer = document.querySelector('.bg-gradient-warning');   
                    if (alcaldeContainer) {
                        const nameElement = alcaldeContainer.querySelector('h5');
                        const detailsElement = alcaldeContainer.querySelector('small');
                        if (nameElement) {
                            nameElement.textContent = leadingAlcalde.candidate.name;
                        }
                        if (detailsElement) {
                            detailsElement.textContent = `${leadingAlcalde.votes.toLocaleString()} votos (${leadingAlcalde.percentage}%)`;
                        }
                    }
                }
                if (Object.keys(data.concejalStats).length > 0) {
                    const sortedConcejal = Object.values(data.concejalStats).sort((a, b) => b.votes - a.votes);
                    const leadingConcejal = sortedConcejal[0];
                    const concejalContainer = document.querySelector('.bg-gradient-purple');   
                    if (concejalContainer) {
                        const nameElement = concejalContainer.querySelector('h5');
                        const detailsElement = concejalContainer.querySelector('small');
                        if (nameElement) {
                            nameElement.textContent = leadingConcejal.candidate.name;
                        }
                        if (detailsElement) {
                            detailsElement.textContent = `${leadingConcejal.votes.toLocaleString()} votos (${leadingConcejal.percentage}%)`;
                        }
                    }
                }
                const participationElement = document.querySelector('.bg-gradient-pink .counter-value');
                if (participationElement) {
                    const participation = data.reportedTables > 0 
                        ? Math.round((data.totalVotesAlcalde / (data.reportedTables * 300)) * 100) 
                        : 0;
                    participationElement.textContent = participation;
                }
                updateCharts(data);
            }
            
            function updateCounter(selector, value) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    element.textContent = typeof value === 'number' ? value.toLocaleString() : value;
                });
            }
            
            function updateCharts(data) {
                if (!charts.trendChart || !charts.pieChartAlcalde || !charts.pieChartConcejal || !charts.radialChart || !charts.gaugeChart) return;
                const sortedAlcalde = Object.values(data.alcaldeStats).sort((a, b) => b.votes - a.votes);
                const alcaldeNames = sortedAlcalde.map(stat => stat.candidate.name);
                const alcaldeColors = sortedAlcalde.map(stat => stat.candidate.color || '#405189');
                const alcaldeVotes = sortedAlcalde.map(stat => stat.votes);
                const alcaldePercentages = sortedAlcalde.map(stat => stat.percentage);
                const sortedConcejal = Object.values(data.concejalStats).sort((a, b) => b.votes - a.votes);
                const concejalVotes = sortedConcejal.map(stat => stat.votes);
                charts.trendChart.updateOptions({
                    series: [
                        { name: 'Votos Alcalde', type: 'column', data: alcaldeVotes },
                        { name: 'Porcentaje', type: 'line', data: alcaldePercentages }
                    ],
                    labels: alcaldeNames
                });
                charts.pieChartAlcalde.updateOptions({
                    series: alcaldeVotes,
                    labels: alcaldeNames,
                    colors: alcaldeColors
                });
                charts.pieChartConcejal.updateOptions({
                    series: concejalVotes,
                    labels: sortedConcejal.map(stat => stat.candidate.name),
                    colors: sortedConcejal.map(stat => stat.candidate.color || '#299cdb')
                });
                const localityProgress = Object.values(data.localityResults).map(l => {
                    const totalTables = l.total_tables || 0;
                    const reportedTables = l.reported_tables || 0;
                    return totalTables > 0 ? Math.round((reportedTables / totalTables) * 100) : 0;
                });
                const localityNames = Object.values(data.localityResults).map(l => l.name).slice(0, 6);
                charts.radialChart.updateOptions({
                    series: localityProgress.slice(0, 6),
                    labels: localityNames
                });
                charts.gaugeChart.updateOptions({
                    series: [data.progressPercentage]
                });
            }
            
            function showLoadingIndicator() {
                document.getElementById('loading-indicator').style.display = 'block';
            }
            
            function hideLoadingIndicator() {
                document.getElementById('loading-indicator').style.display = 'none';
            }
            
            function updateLastUpdateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                document.getElementById('last-update-time').textContent = `Última actualización: ${timeString}`;
            }
            
            function startAutoRefresh() {
                if (refreshTimer) clearInterval(refreshTimer);
                refreshTimer = setInterval(refreshDashboard, refreshInterval);
                document.getElementById('refresh-status').innerHTML = '<small class="text-success">● Activo</small>';
            }
            
            function stopAutoRefresh() {
                if (refreshTimer) {
                    clearInterval(refreshTimer);
                    refreshTimer = null;
                }
                document.getElementById('refresh-status').innerHTML = '<small class="text-secondary">○ Pausado</small>';
            }
            
            window.refreshDashboard = refreshDashboard;
            window.startAutoRefresh = startAutoRefresh;
            window.stopAutoRefresh = stopAutoRefresh;
        });
    </script>
<?php $__env->stopSection(); ?><?php /**PATH D:\_Mine\corporate\resources\views/partials/dashboard-content.blade.php ENDPATH**/ ?>