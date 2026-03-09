<?php $__env->startSection('title'); ?>
    Configuración Electoral - Mesa <?php echo e($votingTable->oep_code ?? $votingTable->internal_code); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .config-card {
            border: 1px solid #e9e9ef;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .config-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .election-info {
            background-color: #f8f9fa;
            border-left: 4px solid #0ab39c;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            <a href="<?php echo e(route('voting-tables.index')); ?>">Mesas</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?>
            <a href="<?php echo e(route('voting-tables.show', $votingTable->id)); ?>">
                Mesa <?php echo e($votingTable->oep_code ?? $votingTable->internal_code); ?>

            </a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Configuración Electoral
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="ri-settings-4-line me-1"></i>
                        Configuración por Tipo de Elección
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        <strong>Mesa:</strong> <?php echo e($votingTable->institution->name); ?> - N° <?php echo e($votingTable->number); ?>

                        <?php if($votingTable->letter): ?>
                            (<?php echo e($votingTable->letter); ?>)
                        <?php endif; ?>
                        <br>
                        <small>Configure los datos electorales para cada tipo de elección. La fecha de elección está definida por el tipo de elección y no puede modificarse por mesa.</small>
                    </div>

                    <div class="row">
                        <?php $__empty_1 = true; $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $electionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $config = $votingTable->elections->firstWhere('election_type_id', $electionType->id);
                                $statusColors = [
                                    'configurada' => 'secondary',
                                    'en_espera' => 'info',
                                    'votacion' => 'primary',
                                    'cerrada' => 'warning',
                                    'en_escrutinio' => 'dark',
                                    'escrutada' => 'success',
                                    'observada' => 'danger',
                                    'transmitida' => 'success',
                                    'anulada' => 'dark'
                                ];
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="card config-card">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <?php echo e($electionType->name); ?>

                                            <small class="text-muted d-block"><?php echo e($electionType->short_name ?? ''); ?></small>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        
                                        <div class="election-info">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Fecha de Elección:</small>
                                                    <strong><?php echo e($electionType->election_date ? \Carbon\Carbon::parse($electionType->election_date)->format('d/m/Y') : 'No definida'); ?></strong>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted d-block">Nivel:</small>
                                                    <strong><?php echo e($electionType->level_label ?? $electionType->level); ?></strong>
                                                </div>
                                            </div>
                                        </div>

                                        <form action="<?php echo e(route('voting-tables.election-config.update', $votingTable->id)); ?>"
                                              method="POST" class="election-config-form">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="election_type_id" value="<?php echo e($electionType->id); ?>">

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Recibidas</label>
                                                    <input type="number" name="ballots_received"
                                                           class="form-control"
                                                           value="<?php echo e(old('ballots_received', $config->ballots_received ?? 0)); ?>"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Usadas</label>
                                                    <input type="number" name="ballots_used"
                                                           class="form-control"
                                                           value="<?php echo e(old('ballots_used', $config->ballots_used ?? 0)); ?>"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Sobrantes</label>
                                                    <input type="number" name="ballots_leftover"
                                                           class="form-control"
                                                           value="<?php echo e(old('ballots_leftover', $config->ballots_leftover ?? 0)); ?>"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Deterioradas</label>
                                                    <input type="number" name="ballots_spoiled"
                                                           class="form-control"
                                                           value="<?php echo e(old('ballots_spoiled', $config->ballots_spoiled ?? 0)); ?>"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Total Votantes</label>
                                                    <input type="number" name="total_voters"
                                                           class="form-control"
                                                           value="<?php echo e(old('total_voters', $config->total_voters ?? 0)); ?>"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Estado</label>
                                                    <select name="status" class="form-select" required>
                                                        <?php $__currentLoopData = \App\Models\VotingTable::getStatuses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($value); ?>"
                                                                <?php echo e((old('status', $config->status ?? 'configurada') == $value) ? 'selected' : ''); ?>>
                                                                <?php echo e($label); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Hora Apertura</label>
                                                    <input type="time" name="opening_time"
                                                           class="form-control"
                                                           value="<?php echo e(old('opening_time', $config->opening_time ?? '')); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Hora Cierre</label>
                                                    <input type="time" name="closing_time"
                                                           class="form-control"
                                                           value="<?php echo e(old('closing_time', $config->closing_time ?? '')); ?>">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Observaciones</label>
                                                    <textarea name="observations" class="form-control" rows="2"><?php echo e(old('observations', $config->observations ?? '')); ?></textarea>
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                <?php if($config): ?>
                                                    <span class="badge bg-<?php echo e($statusColors[$config->status] ?? 'secondary'); ?> status-badge me-2">
                                                        Estado actual: <?php echo e(\App\Models\VotingTable::getStatuses()[$config->status] ?? $config->status); ?>

                                                    </span>
                                                <?php endif; ?>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ri-save-line me-1"></i>Guardar Configuración
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="ri-alert-line me-1"></i>
                                    No hay tipos de elección activos en el sistema.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="ri-information-line me-1"></i> Validaciones importantes:</h6>
                                <ul class="mb-0">
                                    <li>La suma de <strong>usadas + sobrantes + deterioradas</strong> debe igualar las <strong>recibidas</strong>.</li>
                                    <li>Las <strong>papeletas recibidas</strong> no pueden ser menores al <strong>total de votantes</strong>.</li>
                                    <li>Los cambios en el <strong>estado</strong> afectan el flujo de trabajo de la mesa para este tipo de elección.</li>
                                    <li>La <strong>fecha de elección</strong> está definida por el tipo de elección y no es editable por mesa.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-end">
                            <a href="<?php echo e(route('voting-tables.show', $votingTable->id)); ?>" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Volver a la Mesa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate ballot consistency
            const forms = document.querySelectorAll('.election-config-form');
            forms.forEach(form => {
                const received = form.querySelector('input[name="ballots_received"]');
                const used = form.querySelector('input[name="ballots_used"]');
                const leftover = form.querySelector('input[name="ballots_leftover"]');
                const spoiled = form.querySelector('input[name="ballots_spoiled"]');

                function validateBallots() {
                    const total = parseInt(used.value || 0) +
                                 parseInt(leftover.value || 0) +
                                 parseInt(spoiled.value || 0);

                    if (total !== parseInt(received.value || 0)) {
                        received.setCustomValidity('La suma de usadas + sobrantes + deterioradas debe igualar las recibidas');
                    } else {
                        received.setCustomValidity('');
                    }
                }

                [received, used, leftover, spoiled].forEach(input => {
                    input.addEventListener('input', validateBallots);
                });

                // Validate that total voters doesn't exceed ballots received
                const totalVoters = form.querySelector('input[name="total_voters"]');

                function validateTotalVoters() {
                    if (parseInt(totalVoters.value || 0) > parseInt(received.value || 0)) {
                        totalVoters.setCustomValidity('El total de votantes no puede ser mayor a las papeletas recibidas');
                    } else {
                        totalVoters.setCustomValidity('');
                    }
                }

                received.addEventListener('input', validateTotalVoters);
                totalVoters.addEventListener('input', validateTotalVoters);
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/election-config.blade.php ENDPATH**/ ?>