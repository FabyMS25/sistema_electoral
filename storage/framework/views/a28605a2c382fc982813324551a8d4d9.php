
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="ds-locality-table">
        <thead class="table-light">
            <tr>
                <th>Localidad</th>
                <th>Municipio</th>
                <th class="text-center">Mesas</th>
                <th class="text-center">Reportadas</th>
                <th style="min-width:120px;">Avance</th>
                <th class="text-center">V. Alcalde</th>
                <th class="text-center">V. Concejal</th>
                <th class="text-center">En Blanco</th>
                <th class="text-center">Nulos</th>
                <th>Líder Alcalde</th>
                <th>Líder Concejal</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $localityStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $locality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $data     = $localityResults[$locality->id] ?? null;
                    $progress = $locality->total_tables > 0
                        ? round(($locality->reported_tables / $locality->total_tables) * 100)
                        : 0;
                    $barColor = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-info' : 'bg-warning');

                    $alcaldeLider  = ($data['alcalde'][0]  ?? null);
                    $concejalLider = ($data['concejal'][0] ?? null);
                ?>
                <tr>
                    <td><strong><?php echo e($locality->name); ?></strong></td>
                    <td><small class="text-muted"><?php echo e($locality->municipality_name); ?></small></td>

                    <td class="text-center"><?php echo e($locality->total_tables); ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?php echo e($locality->reported_tables >= $locality->total_tables && $locality->total_tables > 0 ? 'success' : 'warning text-dark'); ?>">
                            <?php echo e($locality->reported_tables); ?>

                        </span>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-semibold" style="min-width:30px;"><?php echo e($progress); ?>%</span>
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar <?php echo e($barColor); ?>" role="progressbar"
                                     style="width:<?php echo e($progress); ?>%"></div>
                            </div>
                        </div>
                    </td>

                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?php echo e(number_format($data['total_votes_alcalde'] ?? 0)); ?>

                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                            <?php echo e(number_format($data['total_votes_concejal'] ?? 0)); ?>

                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary-subtle text-secondary border">
                            <?php echo e(number_format($data['blank_votes'] ?? 0)); ?>

                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            <?php echo e(number_format($data['null_votes'] ?? 0)); ?>

                        </span>
                    </td>

                    <td>
                        <?php if($alcaldeLider): ?>
                            <div class="d-flex align-items-center gap-1">
                                <?php if($alcaldeLider['party_logo'] ?? false): ?>
                                    <img src="<?php echo e(asset('storage/'.$alcaldeLider['party_logo'])); ?>"
                                         style="width:16px;height:16px;object-fit:contain;" alt="">
                                <?php endif; ?>
                                <span class="small"><?php echo e(Str::limit($alcaldeLider['candidate_name'], 18)); ?></span>
                                <span class="badge bg-success ms-1"><?php echo e($alcaldeLider['percentage']); ?>%</span>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">Sin datos</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if($concejalLider): ?>
                            <div class="d-flex align-items-center gap-1">
                                <?php if($concejalLider['party_logo'] ?? false): ?>
                                    <img src="<?php echo e(asset('storage/'.$concejalLider['party_logo'])); ?>"
                                         style="width:16px;height:16px;object-fit:contain;" alt="">
                                <?php endif; ?>
                                <span class="small"><?php echo e(Str::limit($concejalLider['candidate_name'], 18)); ?></span>
                                <span class="badge bg-info ms-1"><?php echo e($concejalLider['percentage']); ?>%</span>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary">Sin datos</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="ri-map-pin-line fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No hay localidades disponibles</span>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/partials/dashboard-localities-table.blade.php ENDPATH**/ ?>