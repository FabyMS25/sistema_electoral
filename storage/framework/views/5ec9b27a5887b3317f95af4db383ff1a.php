

<?php $__env->startSection('title'); ?>
    <?php echo e($institution->name); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/leaflet/leaflet.css')); ?>" rel="stylesheet" />
    <style>
        #map {
            height: 300px;
            border-radius: 0.25rem;
        }
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
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            <a href="<?php echo e(route('institutions.index')); ?>">Recintos</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            <?php echo e($institution->name); ?>

        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-building-line me-1"></i>
                        Detalles del Recinto
                    </h4>
                    <div>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_recintos')): ?>
                        <button class="btn btn-warning btn-sm" onclick="window.location.href='<?php echo e(route('institutions.edit', $institution->id)); ?>'">
                            <i class="ri-pencil-line me-1"></i>Editar
                        </button>
                        <?php endif; ?>
                        <a href="<?php echo e(route('institutions.index')); ?>" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">Información Básica</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Código:</td>
                                        <td class="info-value">
                                            <span class="badge bg-info-subtle text-info fs-6"><?php echo e($institution->code); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Nombre:</td>
                                        <td class="info-value"><?php echo e($institution->name); ?></td>
                                    </tr>
                                    <?php if($institution->short_name): ?>
                                    <tr>
                                        <td class="info-label">Nombre Corto:</td>
                                        <td class="info-value"><?php echo e($institution->short_name); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="info-label">Dirección:</td>
                                        <td class="info-value"><?php echo e($institution->address ?? 'No especificada'); ?></td>
                                    </tr>
                                    <?php if($institution->reference): ?>
                                    <tr>
                                        <td class="info-label">Referencia:</td>
                                        <td class="info-value"><?php echo e($institution->reference); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="info-label">Estado:</td>
                                        <td class="info-value">
                                            <?php if($institution->status == 'activo'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php elseif($institution->status == 'inactivo'): ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Mantenimiento</span>
                                            <?php endif; ?>
                                            
                                            <?php if($institution->is_operative): ?>
                                                <span class="badge bg-info ms-1">Operativo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary ms-1">No Operativo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="info-box">
                                <h5 class="mb-3">Contacto</h5>
                                <table class="table table-borderless">
                                    <?php if($institution->phone): ?>
                                    <tr>
                                        <td class="info-label">Teléfono:</td>
                                        <td class="info-value"><?php echo e($institution->phone); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($institution->email): ?>
                                    <tr>
                                        <td class="info-label">Email:</td>
                                        <td class="info-value"><?php echo e($institution->email); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($institution->responsible_name): ?>
                                    <tr>
                                        <td class="info-label">Responsable:</td>
                                        <td class="info-value"><?php echo e($institution->responsible_name); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>

                        <!-- Ubicación Geográfica -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">Ubicación Geográfica</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Departamento:</td>
                                        <td class="info-value"><?php echo e($institution->locality->municipality->province->department->name); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Provincia:</td>
                                        <td class="info-value"><?php echo e($institution->locality->municipality->province->name); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Municipio:</td>
                                        <td class="info-value"><?php echo e($institution->locality->municipality->name); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Localidad:</td>
                                        <td class="info-value"><?php echo e($institution->locality->name); ?></td>
                                    </tr>
                                    <?php if($institution->district): ?>
                                    <tr>
                                        <td class="info-label">Distrito:</td>
                                        <td class="info-value"><?php echo e($institution->district->name); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if($institution->zone): ?>
                                    <tr>
                                        <td class="info-label">Zona:</td>
                                        <td class="info-value"><?php echo e($institution->zone->name); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>

                            <?php if($institution->latitude && $institution->longitude): ?>
                            <div class="info-box">
                                <h5 class="mb-3">Ubicación en Mapa</h5>
                                <div id="map"></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Datos Electorales -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">Datos Electorales</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card bg-primary-subtle">
                                            <div class="card-body text-center">
                                                <h6>Ciudadanos Habilitados</h6>
                                                <h3><?php echo e(number_format($institution->registered_citizens ?? 0)); ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success-subtle">
                                            <div class="card-body text-center">
                                                <h6>Actas Computadas</h6>
                                                <h3><?php echo e(number_format($institution->total_computed_records ?? 0)); ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-danger-subtle">
                                            <div class="card-body text-center">
                                                <h6>Actas Anuladas</h6>
                                                <h3><?php echo e(number_format($institution->total_annulled_records ?? 0)); ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning-subtle">
                                            <div class="card-body text-center">
                                                <h6>Actas Habilitadas</h6>
                                                <h3><?php echo e(number_format($institution->total_enabled_records ?? 0)); ?></h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mesas de Votación -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">Mesas de Votación (<?php echo e($institution->votingTables->count()); ?>)</h5>
                                <?php if($institution->votingTables->count() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>N° Mesa</th>
                                                    <th>Código</th>
                                                    <th>Ciudadanos</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $__currentLoopData = $institution->votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($table->number); ?></td>
                                                    <td><?php echo e($table->code); ?></td>
                                                    <td><?php echo e($table->registered_citizens ?? 'N/A'); ?></td>
                                                    <td>
                                                        <?php if($table->status == 'activo'): ?>
                                                            <span class="badge bg-success">Activo</span>
                                                        <?php elseif($table->status == 'cerrado'): ?>
                                                            <span class="badge bg-secondary">Cerrado</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo e(route('voting-tables.show', $table->id)); ?>" class="btn btn-sm btn-info">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay mesas de votación registradas en este recinto.</p>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_mesas')): ?>
                                    <a href="<?php echo e(route('voting-tables.create', ['institution_id' => $institution->id])); ?>" class="btn btn-sm btn-primary">
                                        <i class="ri-add-line me-1"></i>Agregar Mesa
                                    </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if($institution->observations): ?>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">Observaciones</h5>
                                <p><?php echo e($institution->observations); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <?php if($institution->latitude && $institution->longitude): ?>
    <script src="<?php echo e(URL::asset('build/libs/leaflet/leaflet.js')); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([<?php echo e($institution->latitude); ?>, <?php echo e($institution->longitude); ?>], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            L.marker([<?php echo e($institution->latitude); ?>, <?php echo e($institution->longitude); ?>])
                .addTo(map)
                .bindPopup('<b><?php echo e($institution->name); ?></b><br><?php echo e($institution->address); ?>');
        });
    </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/show.blade.php ENDPATH**/ ?>