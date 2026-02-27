<div class="row mb-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row g-3">
                    <div class="col-md-8 d-flex gap-3">
                        <h5 class="card-title mb-0 mt-2">Tipo de Elección: </h5>
                        <form method="GET" action="<?php echo e(url()->current()); ?>">
                            <div class="row">
                            <div class="col-md-8">
                                <select name="election_type" class="form-select" onchange="this.form.submit()">
                                    <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $electionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($electionType->id); ?>" 
                                            <?php echo e($selectedElectionType && $selectedElectionType->id == $electionType->id ? 'selected' : ''); ?>>
                                            <?php echo e($electionType->name); ?> (<?php echo e($electionType->type); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Filtrar Resultados</button>
                            </div>
                            </div>
                        </form>
                    </div>             
                    <div class="col-md-4">
                        <div class="d-flex justify-content-end align-items-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                                <i class="ri-refresh-line"></i> Actualizar ahora
                            </button>
                            <div class="ms-2">
                                <small class="text-muted" id="last-update-time">
                                    Última actualización: <?php echo e(now()->format('H:i:s')); ?>

                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(url()->current()); ?>" class="row g-3 p-1" id="locationFilterForm">
                    <input type="hidden" name="election_type" value="<?php echo e($selectedElectionType ? $selectedElectionType->id : ''); ?>">
                    <div class="col-md-3">
                        <label for="department" class="form-label mb-0">Departamento</label>
                        <select name="department" id="department" class="form-select" onchange="updateProvinces()">
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($department->id); ?>" 
                                    <?php echo e($selectedDepartment == $department->id ? 'selected' : ''); ?>>
                                    <?php echo e($department->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>                    
                    <div class="col-md-3">
                        <label for="province" class="form-label mb-0">Provincia</label>
                        <select name="province" id="province" class="form-select" onchange="updateMunicipalities()">
                            <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($province->id); ?>" 
                                    <?php echo e($selectedProvince == $province->id ? 'selected' : ''); ?>>
                                    <?php echo e($province->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>                    
                    <div class="col-md-3">
                        <label for="municipality" class="form-label mb-0">Municipio</label>
                        <select name="municipality" id="municipality" class="form-select" onchange="this.form.submit()">
                            <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $municipality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($municipality->id); ?>" 
                                    <?php echo e($selectedMunicipality == $municipality->id ? 'selected' : ''); ?>>
                                    <?php echo e($municipality->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>                    
                    <div class="col-md-3 mt-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filtrar Resultados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="loading-indicator" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 9999; background: #fff; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span>Actualizando datos...</span>
    </div>
</div>
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-primary card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Votos Totales</p>
                        <h3 class="text-white mb-0"><span class="counter-value" data-target="<?php echo e($totalVotes); ?>">0</span></h3>
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
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-warning card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Candidato Líder</p>
                        <?php if(count($candidateStats) > 0): ?>
                            <?php $leadingCandidate = reset($candidateStats); ?>
                            <h5 class="text-white mb-0"><?php echo e($leadingCandidate['candidate']->name ?? 'N/A'); ?></h5>
                            <small class="text-white text-opacity-75">
                                <?php echo e(number_format($leadingCandidate['votes'])); ?> votos (<?php echo e($leadingCandidate['percentage']); ?>%)
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
    <div class="col-xl-3 col-md-6">
        <div class="card bg-gradient-info card-animate">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-white text-opacity-75 text-uppercase fw-medium mb-2">Participación</p>
                        <h3 class="text-white mb-0">
                            <span class="counter-value" data-target="<?php echo e($reportedTables > 0 ? round(($totalVotes / ($reportedTables * 300)) * 100, 1) : 0); ?>">0</span>%
                        </h3>
                        <small class="text-white text-opacity-75">estimado por mesa</small>
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
</div>

<!-- Main Visualization Row - Redesigned -->
<div class="row mt-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 flex-grow-1">Resultados por Candidato - Tendencia</h5>
                    <div class="flex-shrink-0">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-primary active" data-chart-view="bars">Barras</button>
                            <button type="button" class="btn btn-sm btn-soft-primary" data-chart-view="line">Línea</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="candidates_trend_chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Distribución 3D</h5>
            </div>
            <div class="card-body">
                <div id="candidates_pie_chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row - New Chart Types -->
<div class="row">
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Análisis por Localidad - Radial</h5>
            </div>
            <div class="card-body">
                <div id="locality_radial_chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Comparativo de Candidatos por Localidad</h5>
            </div>
            <div class="card-body">
                <div id="candidates_heatmap" style="height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Third Row - Advanced Visualizations -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso de Escrutinio - Gauge</h5>
            </div>
            <div class="card-body">
                <div id="progress_gauge" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Participación por Localidad</h5>
            </div>
            <div class="card-body">
                <div id="participation_bubbles" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fourth Row - Detailed Data -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#candidates_tab" role="tab">
                            <i class="ri-user-star-line me-1 align-bottom"></i> Candidatos
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
                    <!-- Candidates Tab -->
                    <div class="tab-pane active" id="candidates_tab" role="tabpanel">
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
                                        $sortedStats = collect($candidateStats)->sortByDesc('votes')->values();
                                        $prevVotes = null;
                                    ?>
                                    <?php $__currentLoopData = $sortedStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                    
                    <!-- Localities Tab -->
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
                                        <th>Votos</th>
                                        <th>Candidato Ganador</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $localityStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $progress = $locality->total_tables > 0 ? round(($locality->reported_tables / $locality->total_tables) * 100) : 0;
                                            $localityData = $localityResults[$locality->id] ?? null;
                                            $winningCandidate = null;
                                            $maxVotes = 0;
                                            
                                            if ($localityData && isset($localityData['candidates'])) {
                                                foreach ($localityData['candidates'] as $candidate) {
                                                    if ($candidate['votes'] > $maxVotes) {
                                                        $maxVotes = $candidate['votes'];
                                                        $winningCandidate = $candidate;
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
                                                <span class="badge bg-light text-dark">
                                                    <?php echo e(number_format($localityData['total_votes'] ?? 0)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <?php if($winningCandidate): ?>
                                                    <span class="badge bg-success">
                                                        <?php echo e($winningCandidate['name']); ?> (<?php echo e($winningCandidate['percentage']); ?>%)
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
                    
                    <!-- Tables Tab -->
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
    <script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/jsvectormap/jsvectormap.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/jsvectormap/maps/quillacollo-merc.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
    
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #405189 0%, #2a3a6b 100%);
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #0ab39c 0%, #078b7a 100%);
        }
        .bg-gradient-warning {
            background: linear-gradient(135deg, #f7b84b 0%, #f5a219 100%);
        }
        .bg-gradient-info {
            background: linear-gradient(135deg, #299cdb 0%, #1b7ab3 100%);
        }
        .table-status .card {
            transition: transform 0.2s;
        }
        .table-status .card:hover {
            transform: translateY(-5px);
        }
    </style>
    
    <script>      
        document.addEventListener('DOMContentLoaded', function() { 
            let refreshInterval = 120000;
            let refreshTimer = null;
            let isRefreshing = false;      
            let charts = {}; 
            
            initializeCharts();
            startAutoRefresh();  
            
            // Chart view toggle
            document.querySelectorAll('[data-chart-view]').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('[data-chart-view]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const view = this.getAttribute('data-chart-view');
                    updateChartView(view);
                });
            });
            
            function initializeCharts() {
                const sortedStats = Object.values(<?php echo json_encode($candidateStats, 15, 512) ?>).sort((a, b) => b.votes - a.votes);
                const candidateNames = sortedStats.map(stat => stat.candidate.name);
                const candidateColors = sortedStats.map(stat => stat.candidate.color || ['#405189', '#0ab39c', '#f7b84b', '#299cdb', '#e66b6b'][Math.floor(Math.random() * 5)]);
                const candidateVotes = sortedStats.map(stat => stat.votes);
                const candidatePercentages = sortedStats.map(stat => stat.percentage);
                var trendOptions = {
                    series: [
                        {
                            name: 'Votos',
                            type: 'column',
                            data: candidateVotes
                        },
                        {
                            name: 'Porcentaje',
                            type: 'line',
                            data: candidatePercentages
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
                    labels: candidateNames,
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
                
                // 2. 3D Pie Chart
                var pieOptions = {
                    series: candidateVotes,
                    chart: {
                        type: 'pie',
                        height: 350,
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800
                        }
                    },
                    labels: candidateNames,
                    colors: candidateColors,
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.globals.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
                        },
                        dropShadow: {
                            enabled: true
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
                            customScale: 1,
                            offsetY: 0,
                            donut: {
                                size: '40%'
                            }
                        }
                    }
                };
                
                charts.pieChart = new ApexCharts(document.querySelector("#candidates_pie_chart"), pieOptions);
                charts.pieChart.render();
                
                // 3. Radial Bar Chart for Localities
                const localityProgress = Object.values(<?php echo json_encode($localityResults, 15, 512) ?>).map(l => {
                    const totalTables = l.total_tables || 0;
                    const reportedTables = l.reported_tables || 0;
                    return totalTables > 0 ? Math.round((reportedTables / totalTables) * 100) : 0;
                });
                
                const localityNames = Object.values(<?php echo json_encode($localityResults, 15, 512) ?>).map(l => l.name).slice(0, 6);
                
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
                
                // 4. Heatmap for Candidates by Locality
                const heatmapData = candidateNames.map((candidate, idx) => {
                    return {
                        name: candidate,
                        data: Object.values(<?php echo json_encode($localityResults, 15, 512) ?>).map(locality => {
                            const candidateData = locality.candidates.find(c => c.name === candidate);
                            return candidateData ? candidateData.votes : 0;
                        })
                    };
                });
                
                var heatmapOptions = {
                    series: heatmapData,
                    chart: {
                        type: 'heatmap',
                        height: 350,
                        toolbar: {
                            show: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    colors: ['#405189'],
                    xaxis: {
                        categories: Object.values(<?php echo json_encode($localityResults, 15, 512) ?>).map(l => l.name),
                        labels: {
                            rotate: -45,
                            rotateAlways: true,
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    title: {
                        text: 'Distribución de Votos por Localidad',
                        align: 'center'
                    },
                    plotOptions: {
                        heatmap: {
                            shadeIntensity: 0.5,
                            radius: 0,
                            useFillColorAsStroke: true,
                            colorScale: {
                                ranges: [
                                    { from: 0, to: 1000, name: 'Bajo', color: '#c2e0ff' },
                                    { from: 1001, to: 5000, name: 'Medio', color: '#8bb5ff' },
                                    { from: 5001, to: 10000, name: 'Alto', color: '#5487ff' },
                                    { from: 10001, to: 50000, name: 'Muy Alto', color: '#2d5ad2' },
                                    { from: 50001, to: 1000000, name: 'Máximo', color: '#0a1e5c' }
                                ]
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val.toLocaleString() + ' votos';
                            }
                        }
                    }
                };
                
                charts.heatmapChart = new ApexCharts(document.querySelector("#candidates_heatmap"), heatmapOptions);
                charts.heatmapChart.render();
                
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
                
                // 6. Bubble Chart for Participation
                const bubbleData = Object.values(<?php echo json_encode($localityResults, 15, 512) ?>).map((locality, index) => {
                    return {
                        x: locality.total_votes || 0,
                        y: locality.total_tables || 0,
                        z: locality.reported_tables || 0,
                        name: locality.name
                    };
                });
                
                var bubbleOptions = {
                    series: [{
                        name: 'Localidades',
                        data: bubbleData
                    }],
                    chart: {
                        type: 'bubble',
                        height: 300,
                        toolbar: {
                            show: true
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    fill: {
                        opacity: 0.8,
                        colors: ['#405189']
                    },
                    title: {
                        text: 'Votos vs Mesas por Localidad',
                        align: 'center'
                    },
                    xaxis: {
                        title: {
                            text: 'Votos Totales'
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toLocaleString();
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Mesas Totales'
                        }
                    },
                    tooltip: {
                        custom: function({ series, seriesIndex, dataPointIndex, w }) {
                            const data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                            return '<div class="p-2">' +
                                '<h6>' + data.name + '</h6>' +
                                '<p class="mb-0">Votos: ' + data.x.toLocaleString() + '</p>' +
                                '<p class="mb-0">Mesas Totales: ' + data.y + '</p>' +
                                '<p class="mb-0">Mesas Reportadas: ' + data.z + '</p>' +
                                '<p class="mb-0">Avance: ' + Math.round((data.z / data.y) * 100) + '%</p>' +
                                '</div>';
                        }
                    }
                };
                
                charts.bubbleChart = new ApexCharts(document.querySelector("#participation_bubbles"), bubbleOptions);
                charts.bubbleChart.render();
            }
            
            function updateChartView(view) {
                if (!charts.trendChart) return;
                
                const sortedStats = Object.values(<?php echo json_encode($candidateStats, 15, 512) ?>).sort((a, b) => b.votes - a.votes);
                const candidateVotes = sortedStats.map(stat => stat.votes);
                const candidatePercentages = sortedStats.map(stat => stat.percentage);
                
                if (view === 'bars') {
                    charts.trendChart.updateOptions({
                        series: [
                            { name: 'Votos', type: 'column', data: candidateVotes },
                            { name: 'Porcentaje', type: 'line', data: candidatePercentages }
                        ]
                    });
                } else {
                    // Line view - show just the line for percentage
                    charts.trendChart.updateOptions({
                        series: [
                            { name: 'Votos', type: 'line', data: candidateVotes },
                            { name: 'Porcentaje', type: 'line', data: candidatePercentages }
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
                // Update counters
                updateCounter('.col-xl-3:first-child .counter-value', data.totalVotes);
                updateCounter('.col-xl-3:nth-child(2) .counter-value', data.reportedTables);
                updateCounter('.col-xl-3:nth-child(4) .counter-value', data.reportedTables > 0 ? Math.round((data.totalVotes / (data.reportedTables * 300)) * 100) : 0);
                
                // Update progress bar
                const progressBar = document.querySelector('.bg-gradient-success .progress-bar');
                if (progressBar) {
                    progressBar.style.width = data.progressPercentage + '%';
                }
                
                // Update leader info
                if (Object.keys(data.candidateStats).length > 0) {
                    const sortedCandidates = Object.values(data.candidateStats).sort((a, b) => b.votes - a.votes);
                    const leadingCandidate = sortedCandidates[0];
                    const leaderContainer = document.querySelector('.bg-gradient-warning');
                    
                    if (leaderContainer) {
                        const nameElement = leaderContainer.querySelector('h5');
                        const detailsElement = leaderContainer.querySelector('small');
                        
                        if (nameElement) {
                            nameElement.textContent = leadingCandidate.candidate.name;
                        }
                        if (detailsElement) {
                            detailsElement.textContent = `${leadingCandidate.votes.toLocaleString()} votos (${leadingCandidate.percentage}%)`;
                        }
                    }
                }
                
                // Update charts
                updateCharts(data);
            }
            
            function updateCounter(selector, value) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    if (element.classList.contains('counter-value')) {
                        element.textContent = typeof value === 'number' ? value.toLocaleString() : value;
                    } else {
                        element.textContent = typeof value === 'number' ? value.toLocaleString() : value;
                    }
                });
            }
            
            function updateCharts(data) {
                if (!charts.trendChart || !charts.pieChart || !charts.radialChart || !charts.heatmapChart || !charts.gaugeChart || !charts.bubbleChart) return;
                
                const sortedStats = Object.values(data.candidateStats).sort((a, b) => b.votes - a.votes);
                const candidateNames = sortedStats.map(stat => stat.candidate.name);
                const candidateColors = sortedStats.map(stat => stat.candidate.color || ['#405189', '#0ab39c', '#f7b84b', '#299cdb', '#e66b6b'][Math.floor(Math.random() * 5)]);
                const candidateVotes = sortedStats.map(stat => stat.votes);
                const candidatePercentages = sortedStats.map(stat => stat.percentage);
                
                // Update trend chart
                charts.trendChart.updateOptions({
                    series: [
                        { name: 'Votos', type: 'column', data: candidateVotes },
                        { name: 'Porcentaje', type: 'line', data: candidatePercentages }
                    ],
                    labels: candidateNames,
                    colors: ['#405189', '#f7b84b']
                });
                
                // Update pie chart
                charts.pieChart.updateOptions({
                    series: candidateVotes,
                    labels: candidateNames,
                    colors: candidateColors
                });
                
                // Update radial chart
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
                
                // Update heatmap
                const heatmapData = candidateNames.map((candidate, idx) => {
                    return {
                        name: candidate,
                        data: Object.values(data.localityResults).map(locality => {
                            const candidateData = locality.candidates.find(c => c.name === candidate);
                            return candidateData ? candidateData.votes : 0;
                        })
                    };
                });
                
                charts.heatmapChart.updateOptions({
                    series: heatmapData,
                    xaxis: {
                        categories: Object.values(data.localityResults).map(l => l.name)
                    }
                });
                
                // Update gauge
                charts.gaugeChart.updateOptions({
                    series: [data.progressPercentage]
                });
                
                // Update bubble chart
                const bubbleData = Object.values(data.localityResults).map((locality, index) => {
                    return {
                        x: locality.total_votes || 0,
                        y: locality.total_tables || 0,
                        z: locality.reported_tables || 0,
                        name: locality.name
                    };
                });
                
                charts.bubbleChart.updateOptions({
                    series: [{
                        name: 'Localidades',
                        data: bubbleData
                    }]
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