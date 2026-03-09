<?php $__env->startSection('title'); ?>
    Mesa <?php echo e($votingTable->oep_code ?? $votingTable->internal_code); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.css')); ?>" rel="stylesheet" />
    <style>
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0ab39c;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        .delegate-card {
            transition: transform 0.2s;
            border: 1px solid #e9e9ef;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }
        .delegate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .delegate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }
        .code-badge {
            font-family: monospace;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            <a href="<?php echo e(route('voting-tables.index')); ?>">Mesas</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Mesa <?php echo e($votingTable->oep_code ?? $votingTable->internal_code); ?>

        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <?php
        $latestElection = $votingTable->elections->sortByDesc('updated_at')->first();
        $status = $latestElection?->status ?? 'configurada';
        $totalVoters = $votingTable->elections->sum('total_voters');
        $ballotsReceived = $votingTable->elections->sum('ballots_received');
        $ballotsUsed = $votingTable->elections->sum('ballots_used');
        $ballotsLeftover = $votingTable->elections->sum('ballots_leftover');
        $ballotsSpoiled = $votingTable->elections->sum('ballots_spoiled');

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

        $statusLabels = [
            'configurada' => 'Configurada',
            'en_espera' => 'En Espera',
            'votacion' => 'Votación',
            'cerrada' => 'Cerrada',
            'en_escrutinio' => 'En Escrutinio',
            'escrutada' => 'Escrutada',
            'observada' => 'Observada',
            'transmitida' => 'Transmitida',
            'anulada' => 'Anulada'
        ];
    ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Detalles de la Mesa de Votación
                    </h4>
                    <div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_mesas')): ?>
                        <a href="<?php echo e(route('voting-tables.edit', $votingTable->id)); ?>" class="btn btn-warning btn-sm">
                            <i class="ri-pencil-line me-1"></i>Editar
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo e(route('voting-tables.election-config', $votingTable->id)); ?>" class="btn btn-info btn-sm">
                            <i class="ri-settings-4-line me-1"></i>Configuración Electoral
                        </a>
                        <a href="<?php echo e(route('voting-tables.index')); ?>" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Estado y Progreso -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <h5 class="mb-3">Estado de la Mesa</h5>
                                <div class="text-center">
                                    <span class="badge bg-<?php echo e($statusColors[$status] ?? 'secondary'); ?> status-badge">
                                        <?php echo e($statusLabels[$status] ?? $status); ?>

                                    </span>
                                </div>
                                <?php if($status == 'observada'): ?>
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <i class="ri-alert-line me-1"></i>
                                        Esta mesa tiene observaciones pendientes
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <h5 class="mb-3">Progreso de Votación</h5>
                                <?php
                                    $progress = $votingTable->expected_voters > 0
                                        ? round(($totalVoters / $votingTable->expected_voters) * 100, 1)
                                        : 0;
                                ?>
                                <h2 class="text-center mb-3"><?php echo e($progress); ?>%</h2>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?php echo e($progress); ?>%;"
                                         aria-valuenow="<?php echo e($progress); ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100"></div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <strong><?php echo e(number_format($totalVoters)); ?></strong>
                                        <small class="d-block text-muted">Votaron</small>
                                    </div>
                                    <div class="col-6">
                                        <strong><?php echo e(number_format($votingTable->expected_voters - $totalVoters)); ?></strong>
                                        <small class="d-block text-muted">Faltan</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-box">
                                <h5 class="mb-3">Cómputo de Papeletas</h5>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-primary"><?php echo e(number_format($ballotsReceived)); ?></h4>
                                        <small class="text-muted">Recibidas</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-success"><?php echo e(number_format($ballotsUsed)); ?></h4>
                                        <small class="text-muted">Usadas</small>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <h4 class="text-warning"><?php echo e(number_format($ballotsLeftover)); ?></h4>
                                        <small class="text-muted">Sobrantes</small>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <h4 class="text-danger"><?php echo e(number_format($ballotsSpoiled)); ?></h4>
                                        <small class="text-muted">Deterioradas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-information-line me-1"></i>
                                    Información Básica
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Código OEP:</td>
                                        <td class="info-value">
                                            <span class="badge bg-primary code-badge"><?php echo e($votingTable->oep_code ?? 'N/A'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Código Interno:</td>
                                        <td class="info-value">
                                            <span class="badge bg-info code-badge"><?php echo e($votingTable->internal_code ?? 'N/A'); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Número de Mesa:</td>
                                        <td class="info-value">
                                            <strong><?php echo e($votingTable->number); ?></strong>
                                            <?php if($votingTable->letter): ?>
                                                <span class="badge bg-secondary ms-1">Letra <?php echo e($votingTable->letter); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Tipo:</td>
                                        <td class="info-value">
                                            <?php
                                                $typeLabels = [
                                                    'mixta' => 'Mixta',
                                                    'masculina' => 'Masculina',
                                                    'femenina' => 'Femenina'
                                                ];
                                            ?>
                                            <?php echo e($typeLabels[$votingTable->type] ?? $votingTable->type); ?>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Recinto:</td>
                                        <td class="info-value">
                                            <strong><?php echo e($votingTable->institution->name ?? 'N/A'); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e($votingTable->institution->code ?? ''); ?></small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Municipio:</td>
                                        <td class="info-value"><?php echo e($votingTable->institution->municipality->name ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Tipo de Elección:</td>
                                        <td class="info-value"><?php echo e($votingTable->electionType?->name ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Fecha Elección:</td>
                                        <td class="info-value">
                                            <?php echo e($latestElection && $latestElection->election_date ? \Carbon\Carbon::parse($latestElection->election_date)->format('d/m/Y') : 'No definida'); ?>

                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Horarios -->
                            <div class="info-box mt-3">
                                <h5 class="mb-3">
                                    <i class="ri-time-line me-1"></i>
                                    Horarios
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Hora de Apertura:</td>
                                        <td class="info-value"><?php echo e($latestElection && $latestElection->opening_time ? \Carbon\Carbon::parse($latestElection->opening_time)->format('H:i') : 'No registrada'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Hora de Cierre:</td>
                                        <td class="info-value"><?php echo e($latestElection && $latestElection->closing_time ? \Carbon\Carbon::parse($latestElection->closing_time)->format('H:i') : 'No registrada'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Rango de Votantes -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-group-line me-1"></i>
                                    Rango de Votantes
                                </h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-primary-subtle">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Desde</h6>
                                                <h5><?php echo e($votingTable->voter_range_start_name ?? 'N/A'); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-info-subtle">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Hasta</h6>
                                                <h5><?php echo e($votingTable->voter_range_end_name ?? 'N/A'); ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <strong>Total Habilitados: <?php echo e(number_format($votingTable->expected_voters)); ?></strong>
                                </div>
                            </div>

                            <!-- Acta Electoral -->
                            <div class="info-box mt-3">
                                <h5 class="mb-3">
                                    <i class="ri-file-copy-line me-1"></i>
                                    Resumen Electoral
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Papeletas Recibidas:</td>
                                        <td class="info-value"><?php echo e(number_format($ballotsReceived)); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Papeletas Usadas:</td>
                                        <td class="info-value"><?php echo e(number_format($ballotsUsed)); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Papeletas Sobrantes:</td>
                                        <td class="info-value"><?php echo e(number_format($ballotsLeftover)); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Papeletas Deterioradas:</td>
                                        <td class="info-value"><?php echo e(number_format($ballotsSpoiled)); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Delegados de Mesa -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-user-star-line me-1"></i>
                                    Delegados de Mesa
                                </h5>
                                <div class="row">
                                    <?php
                                        $delegates = [
                                            'president' => ['label' => 'Presidente', 'color' => 'primary'],
                                            'secretary' => ['label' => 'Secretario', 'color' => 'success'],
                                            'vocal1' => ['label' => 'Vocal 1', 'color' => 'info'],
                                            'vocal2' => ['label' => 'Vocal 2', 'color' => 'warning'],
                                            'vocal3' => ['label' => 'Vocal 3', 'color' => 'secondary'],
                                        ];
                                    ?>

                                    <?php $__currentLoopData = $delegates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relation => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $delegate = $votingTable->$relation;
                                        ?>
                                        <div class="col-md-4 col-lg-2 mb-3">
                                            <div class="delegate-card">
                                                <?php if($delegate): ?>
                                                    <div class="delegate-avatar bg-<?php echo e($info['color']); ?>">
                                                        <?php echo e(strtoupper(substr($delegate->name, 0, 1))); ?><?php echo e(strtoupper(substr($delegate->last_name ?? '', 0, 1))); ?>

                                                    </div>
                                                    <h6 class="mb-1"><?php echo e($delegate->name); ?> <?php echo e($delegate->last_name); ?></h6>
                                                    <small class="text-muted d-block"><?php echo e($info['label']); ?></small>
                                                    <small class="text-muted"><?php echo e($delegate->email); ?></small>
                                                <?php else: ?>
                                                    <div class="delegate-avatar bg-light text-muted">
                                                        <i class="ri-user-line fs-2"></i>
                                                    </div>
                                                    <h6 class="mb-1 text-muted">No asignado</h6>
                                                    <small class="text-muted d-block"><?php echo e($info['label']); ?></small>
                                                    <small class="text-muted">Disponible</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <?php if($votingTable->vocal4): ?>
                                    <div class="col-md-4 col-lg-2 mb-3">
                                        <div class="delegate-card">
                                            <div class="delegate-avatar bg-dark">
                                                <?php echo e(strtoupper(substr($votingTable->vocal4->name, 0, 1))); ?><?php echo e(strtoupper(substr($votingTable->vocal4->last_name ?? '', 0, 1))); ?>

                                            </div>
                                            <h6 class="mb-1"><?php echo e($votingTable->vocal4->name); ?> <?php echo e($votingTable->vocal4->last_name); ?></h6>
                                            <small class="text-muted d-block">Vocal 4</small>
                                            <small class="text-muted"><?php echo e($votingTable->vocal4->email); ?></small>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_mesas')): ?>
                                <div class="text-end mt-3">
                                    <a href="<?php echo e(route('voting-tables.assign-delegates', $votingTable->id)); ?>" class="btn btn-sm btn-primary">
                                        <i class="ri-user-add-line me-1"></i>Asignar Delegados
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <?php if($votingTable->observations): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-chat-1-line me-1"></i>
                                    Observaciones
                                </h5>
                                <p class="mb-0"><?php echo e($votingTable->observations); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Auditoría -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box bg-light">
                                <h5 class="mb-3">
                                    <i class="ri-history-line me-1"></i>
                                    Información de Auditoría
                                </h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Creado por:</small>
                                        <strong><?php echo e($votingTable->createdBy->name ?? 'Sistema'); ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Fecha creación:</small>
                                        <strong><?php echo e($votingTable->created_at?->format('d/m/Y H:i') ?? 'N/A'); ?></strong>
                                    </div>
                                    <?php if($votingTable->updatedBy): ?>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Actualizado por:</small>
                                        <strong><?php echo e($votingTable->updatedBy->name); ?></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Fecha actualización:</small>
                                        <strong><?php echo e($votingTable->updated_at?->format('d/m/Y H:i') ?? 'N/A'); ?></strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/apexcharts/apexcharts.min.js')); ?>"></script>
    <?php if($votingTable->votes->count() > 0): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                series: [{
                    data: [
                        <?php $__currentLoopData = $votingTable->votes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($vote->quantity); ?>,
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    ]
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                xaxis: {
                    categories: [
                        <?php $__currentLoopData = $votingTable->votes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            '<?php echo e($vote->candidate->name ?? "N/A"); ?>',
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    ],
                },
                colors: ['#0ab39c'],
                title: {
                    text: 'Resultados por Candidato',
                    align: 'center',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold',
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#results-chart"), options);
            chart.render();
        });
    </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/show.blade.php ENDPATH**/ ?>