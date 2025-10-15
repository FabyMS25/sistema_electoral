
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.management'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        .capacity-exceeded {
            background-color: #ffebee !important;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Voting <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Administracion de Votos <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>
    
    <!-- Election Type Selection (if multiple election types exist) -->
    <?php if($electionTypes->count() > 1): ?>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="election_type_selector" class="form-label">Tipo de Elección</label>
                            <select class="form-control" id="election_type_selector" name="election_type_id">
                                <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $electionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($electionType->id); ?>" <?php echo e($currentElectionType->id == $electionType->id ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($electionType->type)); ?> - <?php echo e($electionType->name); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <div class="alert alert-info mb-0">
                                <i class="ri-information-line me-2"></i>
                                Actualmente mostrando datos para: <strong><?php echo e(ucfirst($currentElectionType->type)); ?> - <?php echo e($currentElectionType->name); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Candidate Cards Row -->
    <div class="row">
        <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $stats = $candidateStats[$candidate->id] ?? ['votes' => 0, 'percentage' => 0, 'trend' => 'neutral', 'rank' => 0];
                $votes = $stats['votes'];
                $percentage = $stats['percentage'];
                $trend = $stats['trend'];
                $cardClass = 'info';
                if ($trend === 'up') { 
                    $cardClass = 'success';
                } elseif ($percentage > 20) { 
                    $cardClass = 'primary';
                } elseif ($percentage > 10) { 
                    $cardClass = 'warning';
                } else { 
                    $cardClass = 'danger'; 
                }
            ?>        
            <div class="col-xxl-3 col-md-6" data-candidate-id="<?php echo e($candidate->id); ?>">
                <div class="card card-animate">
                    <div class="card-body <?php echo e($candidate->type === 'blank_votes' || $candidate->type === 'null_votes' ? 'bg-warning-subtle' : ''); ?>">
                        <div class="d-flex mb-3">
                            <div class="flex-grow-1">
                                <?php if($candidate->photo): ?>
                                    <img src="<?php echo e(asset('storage/' . $candidate->photo)); ?>" 
                                        alt="<?php echo e($candidate->name); ?>" 
                                        class="img-fluid rounded-circle border border-3 border-white shadow-sm"
                                        style="width:55px;height:55px;object-fit:cover;">
                                <?php else: ?>
                                    <lord-icon src="https://cdn.lordicon.com/dxjqoygy.json" trigger="loop"
                                        colors="primary:#405189,secondary:#0ab39c" style="width:55px;height:55px"> </lord-icon>
                                <?php endif; ?>
                            </div>
                            <?php if($candidate->type === 'candidato'): ?>
                            <div class="flex-shrink-0 me-2">
                                <?php if($trend === 'up'): ?>
                                    <div class="avatar-xs d-inline-block">
                                        <div class="avatar-title bg-success text-white rounded-circle fs-16">
                                            <i class="ri-arrow-right-up-fill"></i>
                                        </div>
                                    </div>
                                <?php elseif($trend === 'down' && $totalCount > 0): ?>
                                    <div class="avatar-xs d-inline-block">
                                        <div class="avatar-title bg-danger text-white rounded-circle fs-16">
                                            <i class="ri-arrow-right-down-fill"></i>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="avatar-xs d-inline-block">
                                        <div class="avatar-title bg-secondary text-white rounded-circle fs-16">
                                            <i class="ri-subtract-line"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>                       
                            </div>
                            <?php endif; ?>
                            <div class="flex-shrink-0">
                                <span class="badge bg-<?php echo e($cardClass); ?>-subtle text-<?php echo e($cardClass); ?> badge-border">
                                    #<?php echo e($stats['rank'] ?? 'N/A'); ?>

                                </span>
                            </div>
                        </div>
                        <h4 class="mb-2" data-candidate-id="<?php echo e($candidate->id); ?>">
                            <span class="counter-value" data-target="<?php echo e($votes); ?>">0</span>
                            <small class="text-muted fs-13"> votos</small>
                            <small class="text-muted fs-13 badge bg-light"><?php echo e(number_format($percentage, 1)); ?>% del total</small>
                        </h4>
                        <h6 class="text-muted mb-0"><?php echo e($candidate->name); ?></h6>
                        <?php if($candidate->party && $candidate->type === 'candidato'): ?>
                            <p class="text-muted mb-0 fs-12"><?php echo e($candidate->party); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Filter Form -->
    <div class="row align-items-center">
        <form method="GET" action="<?php echo e(route('voting-table-votes.index')); ?>" class="row" id="filter-form">
            <?php if($electionTypes->count() > 1): ?>
                <input type="hidden" name="election_type_id" value="<?php echo e($currentElectionType->id); ?>" id="hidden_election_type_id">
            <?php endif; ?>
            <div class="col-md-4">
                <label for="institution_id" class="form-label text-muted">Filtrar por Institución</label>
                <select class="form-control" data-choices name="institution_id" id="institution_id">
                    <option value="">-- Todas las Instituciones --</option>
                    <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($institution->id); ?>" <?php echo e($institutionId == $institution->id ? 'selected' : ''); ?>>
                        <?php echo e($institution->name); ?> (<?php echo e($institution->code); ?>)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-md-2">
                <div class="mb-1"><label class="form-label">&nbsp;</label></div>
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="<?php echo e(route('voting-table-votes.index')); ?><?php echo e($electionTypes->count() > 1 ? '?election_type_id=' . $currentElectionType->id : ''); ?>" class="btn btn-secondary">Limpiar</a>
            </div>
            <div class="col-md-6">
                <div class="mb-1"><label class="form-label">&nbsp;</label></div>
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    Utilice los campos de entrada para ingresar el conteo de votos o los botones +/- para ajustar. Haga clic en boton <i class="ri-save-line"></i> "Registrar" para guardar.
                </div>
            </div>
        </form>
    </div>
    
    <!-- Main Data Table -->
    <div class="card" id="tableList">
        <div class="card-header">
            <div class="row align-items-center g-3">
                <div class="col-md-3">
                    <h4 class="card-title mb-0">Registro de Votos por mesa</h4>
                </div>
                <div class="col-md-auto ms-auto">
                    <div class="d-flex gap-2">
                        <div class="search-box">
                            <input type="text" class="form-control search" placeholder="Buscar por Institución y/o Mesa...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <button class="btn btn-soft-primary"><i class="ri-equalizer-line align-bottom me-1"></i>
                            Filtrar</button>
                    </div>
                </div>
            </div>
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>                    
            <?php if(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>
        </div>
        <div class="card-body">            
            <div class="table table-responsive table-card">                    
                <table class="table align-middle table-nowrap" id="votingTableList">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="responsivetableCheck">
                                    <label class="form-check-label" for="responsivetableCheck"></label>
                                </div>
                            </th>
                            <th class="sort" data-sort="institution" scope="col">INSTITUCION</th>
                            <th class="sort text-center" data-sort="votingTable" scope="col">MESA</th>
                            <th class="sort text-center" data-sort="status" scope="col">ESTADO</th>
                            <th class="sort text-center" data-sort="registeredCitizens" scope="col">ELECTORES</th>
                            <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th data-sort="candidate-<?php echo e($candidate->id); ?>" scope="col">
                                <div class="d-flex">
                                    <?php if($candidate->photo): ?>
                                        <div class="avatar-xs flex-shrink-0 me-1">
                                            <img src="<?php echo e(asset('storage/' . $candidate->photo)); ?>" 
                                            alt="<?php echo e($candidate->name); ?>" 
                                            class="img-fluid rounded-circle">
                                        </div>
                                    <?php else: ?>
                                        <div class="avatar-xs flex-shrink-0 me-1">
                                            <div class="avatar-title bg-soft-secondary text-secondary rounded-circle">
                                                <i class="ri-user-line"></i>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div style="<?php echo e($candidate->type === 'candidato' ? 'width: 130px;' : 'width: 90px;'); ?>">
                                        <h6 class="fs-13 candidate-name mb-0"><?php echo e($candidate->name); ?></h6>
                                        <?php if($candidate->type === 'candidato' && $candidate->party): ?>
                                        <small class="text-muted fw-medium">(<?php echo e($candidate->party); ?>)</small> 
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <th class="sort text-center" data-sort="totalCount" scope="col">Votos Contados</th>
                            <!-- <th class="sort text-center" data-sort="records" scope="col">Registros</th> -->
                            <th scope="col" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="list form-check-all">
                        <?php $__currentLoopData = $votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $tableVotes = [];
                                $totalVotes = 0;
                                // Only count votes for the current election type
                                foreach ($table->votes as $vote) {
                                    if ($vote->election_type_id == $currentElectionType->id) {
                                        $tableVotes[$vote->candidate_id] = $vote->quantity;
                                        $totalVotes += $vote->quantity;
                                    }
                                }
                                $unregisteredVotes = $table->registered_citizens ? max(0, $table->registered_citizens - $totalVotes) : 0;
                                $isCapacityWarning = $table->registered_citizens && $totalVotes > 0 && $totalVotes < $table->registered_citizens;
                                $isCapacityExceeded = $table->registered_citizens && $totalVotes > $table->registered_citizens;
                            ?>
                            <tr id="table-<?php echo e($table->id); ?>" data-table-id="<?php echo e($table->id); ?>" class="<?php echo e($isCapacityExceeded ? 'capacity-exceeded' : ($isCapacityWarning ? 'capacity-warning' : '')); ?>">
                                <td class="text-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="" id="responsivetableCheck01">
                                        <label class="form-check-label" for="responsivetableCheck01"></label>
                                    </div>
                                </td>
                                <td class="institution"><?php echo e($table->institution->name); ?></td>
                                <td class="votingTable">
                                    <?php echo e($table->code); ?> (<?php echo e($table->number); ?>)
                                    <?php if($table->from_name && $table->to_name): ?>
                                        <small class="from-name fs-10"><br><?php echo e($table->from_name); ?></small> 
                                        <small class="to-name fs-10"><br><?php echo e($table->to_name); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="status text-center">
                                    <?php
                                    $statusClasses = [
                                        'activo' => 'success',
                                        'cerrado' => 'danger',
                                        'pendiente' => 'warning'
                                    ];
                                    $statusLabels = [
                                        'activo' => 'Activo',
                                        'cerrado' => 'Cerrado',
                                        'pendiente' => 'Pendiente'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo e($statusClasses[$table->status]); ?>-subtle text-<?php echo e($statusClasses[$table->status]); ?> status-badge">
                                        <?php echo e($statusLabels[$table->status]); ?>

                                    </span>
                                </td>
                                <td class="registeredCitizens text-center"><?php echo e($table->registered_citizens ?? 'N/A'); ?></td>
                                <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="candidate-<?php echo e($candidate->id); ?> text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <button class="btn btn-sm btn-outline-secondary vote-btn subtract-vote" 
                                                data-table-id="<?php echo e($table->id); ?>" 
                                                data-candidate-id="<?php echo e($candidate->id); ?>"
                                                <?php echo e($table->status === 'cerrado' ? 'disabled' : ''); ?>>
                                            <i class="ri-subtract-line"></i>
                                        </button>
                                        <input type="number" 
                                               class="form-control form-control-sm vote-input mx-1 text-center" 
                                               name="votes[<?php echo e($table->id); ?>][<?php echo e($candidate->id); ?>]" 
                                               value="<?php echo e($tableVotes[$candidate->id] ?? 0); ?>" 
                                               min="0"
                                               data-table-id="<?php echo e($table->id); ?>"
                                               data-candidate-id="<?php echo e($candidate->id); ?>"
                                               <?php echo e($table->status === 'cerrado' ? 'readonly' : ''); ?>>
                                        <button class="btn btn-sm btn-outline-secondary vote-btn add-vote" 
                                                data-table-id="<?php echo e($table->id); ?>" 
                                                data-candidate-id="<?php echo e($candidate->id); ?>"
                                                <?php echo e($table->status === 'cerrado' ? 'disabled' : ''); ?>>
                                            <i class="ri-add-line"></i>
                                        </button>
                                    </div>
                                </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <td class="totalCount text-center">
                                    <span class="total-votes" data-table-id="<?php echo e($table->id); ?>">
                                        <?php echo e($totalVotes); ?>

                                    </span>
                                </td>
                                <!-- <td class="records text-center">
                                    <div class="records-stats">
                                        <div>C: <?php echo e($table->computed_records); ?></div>
                                        <div>A: <?php echo e($table->annulled_records); ?></div>
                                        <div>H: <?php echo e($table->enabled_records); ?></div>
                                    </div>
                                </td> -->
                                <td class="text-center gap-1">
                                    <button type="button" class="btn btn-success p-2 py-1 register-votes" 
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Registrar"
                                        data-table-id="<?php echo e($table->id); ?>" <?php echo e($table->status === 'cerrado' ? 'disabled' : ''); ?>>
                                        <i class="ri-save-line"></i> 
                                    </button>
                                    <button type="button" class="btn btn-outline-primary p-2 py-1 register-close" 
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Cerrar Mesa"
                                        data-table-id="<?php echo e($table->id); ?>" <?php echo e($table->status === 'cerrado' ? 'disabled' : ''); ?>>
                                        <i class="ri-close-circle-fill"></i>                                        
                                    </button>
                                    <?php if($table->status === 'cerrado'): ?>
                                        <div class="text-muted small mt-1">Cerrada</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Totales:</td>
                            <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center fw-bold candidate-<?php echo e($candidate->id); ?>">
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="fs-14 fw-bold mb-1"><?php echo e($candidateTotals[$candidate->id] ?? 0); ?></span>                                    
                                    <?php
                                        $stats = $candidateStats[$candidate->id] ?? ['trend' => 'neutral', 'percentage' => 0];
                                        $trend = $stats['trend'];
                                        $percentage = $stats['percentage'];
                                    ?>                                    
                                    <?php if($totalCount > 0): ?>
                                        <small class="text-muted mb-1"><?php echo e(number_format($percentage, 1)); ?>%</small>
                                        <?php if($candidate->type === 'candidato'): ?>                                    
                                        <?php if($trend === 'up'): ?>
                                            <div class="avatar-xs">
                                                <div class="avatar-title bg-success-subtle text-success rounded-circle fs-12">
                                                    <i class="ri-arrow-right-up-fill"></i>
                                                </div>
                                            </div>
                                        <?php elseif($trend === 'down'): ?>
                                            <div class="avatar-xs">
                                                <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-12">
                                                    <i class="ri-arrow-right-down-fill"></i>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="avatar-xs">
                                                <div class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-12">
                                                    <i class="ri-subtract-line"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">0%</small>
                                        <div class="avatar-xs">
                                            <div class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-12">
                                                <i class="ri-subtract-line"></i>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center fw-bold">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fs-14 fw-bold"><?php echo e($totalCount); ?></span>
                                    <small class="text-muted">votos</small>
                                </div>
                            </td>
                            <td class="text-center fw-bold">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fs-14 fw-bold"><?php echo e($totalRegisteredCitizens); ?></span>
                                    <?php if($totalRegisteredCitizens > 0): ?>
                                        <small class="text-<?php echo e($totalCount > $totalRegisteredCitizens ? 'danger' : ($totalCount < $totalRegisteredCitizens * 0.8 ? 'warning' : 'success')); ?>">
                                            <?php echo e($totalRegisteredCitizens > 0 ? number_format(($totalCount / $totalRegisteredCitizens) * 100, 1) : 0); ?>%
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="gap-1">
                                    <button class="btn btn-sm btn-primary register-all-votes">
                                        <i class="ri-save-line"></i> Registrar Todos
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary register-close-all">
                                        <i class="ri-close-circle-fill me-1"></i> Cerrar Todas
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <?php if($votingTables->isEmpty()): ?>
                <div class="noresult">
                    <div class="text-center">
                        <i class="ri-inbox-line display-4 text-muted"></i>
                        <h5 class="mt-2">No se encontraron Mesas registradas</h5>
                        <p class="text-muted">Intenta seleccionando una institución diferente o verifica si las mesas de votos fueron registrados correctamente.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="d-flex justify-content-end mt-3">
                <div class="pagination-wrap hstack gap-2">
                    <a class="page-item pagination-prev disabled" href="#">
                        Anterior
                    </a>
                    <ul class="pagination listjs-pagination mb-0"></ul>
                    <a class="page-item pagination-next" href="#">
                        Siguiente
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/prismjs/prism.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var votingTableList = new List('tableList', {
                valueNames: ['institution','votingTable','status','records',
                    'totalCount','registeredCitizens',
                    <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        'candidate-<?php echo e($candidate->id); ?>',
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ],
                page: 10,
                pagination: true
            });
            document.querySelector('.search').addEventListener('input', function(e) {
                var searchValue = e.target.value;
                votingTableList.search(searchValue);
            });            
            document.getElementById('institution_id').addEventListener('change', function(e) {
                var institutionId = e.target.value;
                var selectedOption = document.querySelector('option[value="' + institutionId + '"]');
                if (institutionId && selectedOption) {
                    var institutionName = selectedOption.textContent.split(' (')[0];
                    votingTableList.search(institutionName);
                } else {
                    votingTableList.search('');
                }
            });
            <?php if($electionTypes->count() > 1): ?>
            document.getElementById('election_type_selector').addEventListener('change', function(e) {
                var electionTypeId = e.target.value;
                document.getElementById('hidden_election_type_id').value = electionTypeId;
                document.getElementById('filter-form').submit();
            });
            <?php endif; ?>
            document.querySelectorAll(".register-votes").forEach(btn => {
                btn.addEventListener("click", function () {
                    let tableId = this.dataset.tableId;
                    registerTableVotes(tableId, false);
                });
            });            
            document.querySelectorAll(".register-close").forEach(btn => {
                btn.addEventListener("click", function () {
                    let tableId = this.dataset.tableId;
                    if (confirm('¿Está seguro de que desea cerrar esta mesa? No podrá registrar más votos después.')) {
                        registerTableVotes(tableId, true);
                    }
                });
            });
            document.querySelector(".register-all-votes").addEventListener("click", function () {
                if (confirm('¿Está seguro de que desea registrar todos los votos de todas las mesas?')) {
                    registerAllVotes(false);
                }
            });            
            document.querySelector(".register-close-all").addEventListener("click", function () {
                if (confirm('¿Está seguro de que desea registrar todos los votos y cerrar todas las mesas? No podrá registrar más votos después.')) {
                    registerAllVotes(true);
                }
            });
            document.querySelectorAll(".add-vote").forEach(btn => {
                btn.addEventListener("click", function () {
                    let tableId = this.dataset.tableId;
                    let candidateId = this.dataset.candidateId;
                    let input = document.querySelector(`input.vote-input[data-table-id="${tableId}"][data-candidate-id="${candidateId}"]`);
                    if (!input.readOnly) {
                        input.value = parseInt(input.value || 0) + 1;
                        updateTotal(tableId);
                    }
                });
            });            
            document.querySelectorAll(".subtract-vote").forEach(btn => {
                btn.addEventListener("click", function () {
                    let tableId = this.dataset.tableId;
                    let candidateId = this.dataset.candidateId;
                    let input = document.querySelector(`input.vote-input[data-table-id="${tableId}"][data-candidate-id="${candidateId}"]`);
                    if (!input.readOnly) {
                        input.value = Math.max(0, parseInt(input.value || 0) - 1);
                        updateTotal(tableId);
                    }
                });
            });            
            document.querySelectorAll(".vote-input").forEach(input => {
                input.addEventListener('input', function() {
                    let tableId = this.dataset.tableId;
                    updateTotal(tableId);
                });
            });            
            function registerTableVotes(tableId, closeTable = false) {
                let inputs = document.querySelectorAll(`input.vote-input[data-table-id="${tableId}"]`);
                let votes = {};                
                inputs.forEach(input => {
                    votes[input.dataset.candidateId] = parseInt(input.value || 0);
                });                
                let button = document.querySelector(closeTable ? `.register-close[data-table-id="${tableId}"]` : `.register-votes[data-table-id="${tableId}"]`);
                let originalText = button.innerHTML;
                button.innerHTML = '<i class="ri-loader-2-line me-1 spin"></i> Procesando...';
                button.disabled = true;                
                fetch("<?php echo e(route('voting-table-votes.register')); ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
                    },
                    body: JSON.stringify({
                        voting_table_id: tableId,
                        election_type_id: <?php echo e($currentElectionType->id); ?>,
                        votes: votes,
                        close: closeTable
                    })
                })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.success ? 'success' : 'danger', data.message);                    
                    if (data.success) {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    showAlert('danger', 'Error procesando la solicitud. Intente nuevamente.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }            
            function registerAllVotes(closeAll = false) {
                let allTables = {};
                document.querySelectorAll('tr[data-table-id]').forEach(row => {
                    let tableId = row.dataset.tableId;
                    let inputs = row.querySelectorAll('input.vote-input');
                    let votes = {};                    
                    inputs.forEach(input => {
                        votes[input.dataset.candidateId] = parseInt(input.value || 0);
                    });
                    allTables[tableId] = votes;
                });                
                let button = document.querySelector(closeAll ? '.register-close-all' : '.register-all-votes');
                let originalText = button.innerHTML;
                button.innerHTML = '<i class="ri-loader-2-line me-1 spin"></i> Procesando...';
                button.disabled = true;                
                fetch("<?php echo e(route('voting-table-votes.register-all')); ?>", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>"
                    },
                    body: JSON.stringify({
                        election_type_id: <?php echo e($currentElectionType->id); ?>,
                        tables: allTables,
                        close_all: closeAll
                    })
                })
                .then(res => res.json())
                .then(data => {
                    showAlert(data.success ? 'success' : 'danger', data.message);                    
                    if (data.success) {
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(err => {
                    console.error("Error:", err);
                    showAlert('danger', 'Error procesando la solicitud. Intente nuevamente.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }            
            function showAlert(type, message) {
                let alertContainer = document.getElementById("alert-container");
                let div = document.createElement("div");
                div.className = `alert alert-${type} alert-dismissible fade show`;
                div.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                alertContainer.appendChild(div);
                setTimeout(() => {
                    if (div.parentNode) {
                        div.remove();
                    }
                }, 5000);
            }            
            document.querySelectorAll('.counter-value').forEach(counter => {
                let target = parseInt(counter.getAttribute('data-target')) || 0;
                counter.textContent = '0';
                setTimeout(() => animateCounter(counter, target), 500);
            });            
            function updateTotal(tableId) {
                let inputs = document.querySelectorAll(`input.vote-input[data-table-id="${tableId}"]`);
                let total = 0;
                inputs.forEach(i => total += parseInt(i.value || 0));                
                let totalElement = document.querySelector(`.total-votes[data-table-id="${tableId}"]`);
                if (totalElement) {
                    totalElement.textContent = total;
                }                
                let row = document.querySelector(`tr[data-table-id="${tableId}"]`);
                if (row) {
                    let capacityElement = row.querySelector('.registeredCitizens');
                    let capacity = capacityElement ? parseInt(capacityElement.textContent) : null;
                    row.classList.remove('capacity-warning', 'capacity-exceeded');
                    
                    if (capacity && !isNaN(capacity)) {
                        if (total > capacity) {
                            row.classList.add('capacity-exceeded');
                        } else if (total > 0 && total < capacity) {
                            row.classList.add('capacity-warning');
                        }
                    }
                }                
                updateFooterTotals();
                updateCandidateCards();
            }
            
            function updateFooterTotals() {
                let candidateTotals = {};
                let grandTotal = 0;                
                <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                candidateTotals[<?php echo e($candidate->id); ?>] = 0;
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                
                document.querySelectorAll('input.vote-input').forEach(input => {
                    let candidateId = input.dataset.candidateId;
                    let votes = parseInt(input.value || 0);
                    if (candidateTotals.hasOwnProperty(candidateId)) {
                        candidateTotals[candidateId] += votes;
                        grandTotal += votes;
                    }
                });                
                <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                let candidate<?php echo e($candidate->id); ?>Total = candidateTotals[<?php echo e($candidate->id); ?>];
                let candidate<?php echo e($candidate->id); ?>Element = document.querySelector('.candidate-<?php echo e($candidate->id); ?> .fs-14');
                if (candidate<?php echo e($candidate->id); ?>Element) {
                    candidate<?php echo e($candidate->id); ?>Element.textContent = candidate<?php echo e($candidate->id); ?>Total;
                }                
                let candidate<?php echo e($candidate->id); ?>Percentage = grandTotal > 0 ? (candidate<?php echo e($candidate->id); ?>Total / grandTotal) * 100 : 0;
                let candidate<?php echo e($candidate->id); ?>PercentElement = document.querySelector('.candidate-<?php echo e($candidate->id); ?> .text-muted');
                if (candidate<?php echo e($candidate->id); ?>PercentElement) {
                    candidate<?php echo e($candidate->id); ?>PercentElement.textContent = candidate<?php echo e($candidate->id); ?>Percentage.toFixed(1) + '%';
                }                        
                updateCandidateTrend(<?php echo e($candidate->id); ?>, candidate<?php echo e($candidate->id); ?>Total, candidate<?php echo e($candidate->id); ?>Percentage, grandTotal);
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                
                let grandTotalElement = document.querySelector('tfoot .fw-bold .fs-14');
                if (grandTotalElement && grandTotalElement.parentElement.parentElement.classList.contains('text-center')) {
                    grandTotalElement.textContent = grandTotal;
                }        
            }            
            function updateCandidateTrend(candidateId, votes, percentage, totalVotes) {
                let allTotals = [];                
                <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                let candidate<?php echo e($candidate->id); ?>Votes = 0;
                document.querySelectorAll('input.vote-input[data-candidate-id="<?php echo e($candidate->id); ?>"]').forEach(input => {
                    candidate<?php echo e($candidate->id); ?>Votes += parseInt(input.value || 0);
                });
                allTotals.push({id: <?php echo e($candidate->id); ?>, votes: candidate<?php echo e($candidate->id); ?>Votes});
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                
                let maxVotes = Math.max(...allTotals.map(c => c.votes));
                let trend = 'down';                
                if (maxVotes > 0 && votes === maxVotes) {
                    trend = 'up';
                } else if (totalVotes === 0) {
                    trend = 'neutral';
                }                
                let arrowContainer = document.querySelector(`.candidate-${candidateId} .avatar-xs`);
                if (arrowContainer) {
                    let arrowElement = arrowContainer.querySelector('.avatar-title');
                    if (arrowElement) {
                        arrowElement.className = 'avatar-title rounded-circle fs-12';            
                        if (trend === 'up') {
                            arrowElement.classList.add('bg-success-subtle', 'text-success');
                            arrowElement.innerHTML = '<i class="ri-arrow-right-up-fill"></i>';
                        } else if (trend === 'down') {
                            arrowElement.classList.add('bg-danger-subtle', 'text-danger');
                            arrowElement.innerHTML = '<i class="ri-arrow-right-down-fill"></i>';
                        } else {
                            arrowElement.classList.add('bg-secondary-subtle', 'text-secondary');
                            arrowElement.innerHTML = '<i class="ri-subtract-line"></i>';
                        }
                    }
                }
            }
            function updateCandidateCards() {
                let candidateTotals = {};
                let grandTotal = 0;                
                <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                candidateTotals[<?php echo e($candidate->id); ?>] = 0;
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>                
                document.querySelectorAll('input.vote-input').forEach(input => {
                    let candidateId = input.dataset.candidateId;
                    let votes = parseInt(input.value || 0);
                    if (candidateTotals.hasOwnProperty(candidateId)) {
                        candidateTotals[candidateId] += votes;
                        grandTotal += votes;
                    }
                });                
                let sortedCandidates = Object.entries(candidateTotals).sort((a, b) => b[1] - a[1]);
                let maxVotes = sortedCandidates[0] ? sortedCandidates[0][1] : 0;                
                <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                updateCandidateCard(<?php echo e($candidate->id); ?>, candidateTotals[<?php echo e($candidate->id); ?>], grandTotal, maxVotes, sortedCandidates);
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            }
            function updateCandidateCard(candidateId, votes, totalVotes, maxVotes, sortedCandidates) {
                let percentage = totalVotes > 0 ? (votes / totalVotes) * 100 : 0;
                let rank = sortedCandidates.findIndex(([id, v]) => id == candidateId) + 1;
                let trend = 'neutral';                
                if (maxVotes > 0 && votes === maxVotes) {
                    trend = 'up';
                } else if (totalVotes > 0) {
                    trend = 'down';
                }                
                let voteElement = document.querySelector(`[data-candidate-id="${candidateId}"] .counter-value`);
                if (voteElement) {
                    voteElement.textContent = votes;
                    voteElement.setAttribute('data-target', votes);
                }                
                let percentageElement = document.querySelector(`[data-candidate-id="${candidateId}"] .badge`);
                if (percentageElement) {
                    percentageElement.textContent = `${percentage.toFixed(1)}% del total`;
                }
            }
            
            function animateCounter(element, target) {
                let current = parseInt(element.textContent) || 0;
                let increment = target > current ? 1 : -1;
                let steps = Math.abs(target - current);
                let stepTime = Math.max(50, 300 / steps);                
                if (steps === 0) return;              
                let timer = setInterval(() => {
                    current += increment;
                    element.textContent = current;            
                    
                    if (current === target) {
                        clearInterval(timer);
                    }
                }, stepTime);
            }
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes.blade.php ENDPATH**/ ?>