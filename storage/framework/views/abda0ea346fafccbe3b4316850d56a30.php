<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.list-users'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .delegate-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            background-color: #e7f1ff;
            color: #0a5dc2;
            border-radius: 0.25rem;
            margin-right: 0.25rem;
            display: inline-block;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Usuarios
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Gestión de Usuarios
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Usuarios</p>
                            <h4 class="mb-2"><?php echo e($stats['total']); ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-success fw-bold font-size-12 me-2">
                                    <i class="ri-arrow-up-line"></i> <?php echo e($stats['active']); ?> activos
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-user-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Activos Hoy</p>
                            <h4 class="mb-2"><?php echo e($stats['online_today']); ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-primary fw-bold font-size-12 me-2">
                                    <i class="ri-user-star-line"></i> último acceso
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-user-smile-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Delegados</p>
                            <h4 class="mb-2"><?php echo e($stats['delegates']); ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-warning fw-bold font-size-12 me-2">
                                    <i class="ri-user-settings-line"></i> asignaciones
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-user-settings-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Inactivos</p>
                            <h4 class="mb-2"><?php echo e($stats['inactive']); ?></h4>
                            <p class="text-muted mb-0">
                                <span class="text-danger fw-bold font-size-12 me-2">
                                    <i class="ri-user-forbid-line"></i> sin acceso
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-danger rounded-3">
                                <i class="ri-user-forbid-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('users.index')); ?>" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Búsqueda</label>
                                <div class="search-box">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Nombre, email, CI..." value="<?php echo e(request('search')); ?>">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Rol</label>
                                <select name="role" class="form-select">
                                    <option value="">Todos</option>
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($role->name); ?>" <?php echo e(request('role') == $role->name ? 'selected' : ''); ?>>
                                            <?php echo e($role->display_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tipo Delegado</label>
                                <select name="delegate_type" class="form-select">
                                    <option value="">Todos</option>
                                    <?php $__currentLoopData = $delegateTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>" <?php echo e(request('delegate_type') == $value ? 'selected' : ''); ?>>
                                            <?php echo e($label); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Activos</option>
                                    <option value="inactive" <?php echo e(request('status') == 'inactive' ? 'selected' : ''); ?>>Inactivos</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ri-filter-3-line align-middle me-1"></i> Filtrar
                                </button>
                                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-soft-secondary">
                                    <i class="ri-refresh-line align-middle me-1"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Usuarios -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Administración de Usuarios del Sistema</h4>
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_users')): ?>
                    <a href="<?php echo e(route('users.create')); ?>" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Nuevo Usuario
                    </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>CI</th>
                                    <th>Contacto</th>
                                    <th>Roles</th>
                                    <th>Delegaciones</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="<?php echo e($user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg')); ?>"
                                                     alt="avatar" class="avatar-xs rounded-circle" />
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h5 class="fs-14 mb-1"><?php echo e($user->name); ?> <?php echo e($user->last_name); ?></h5>
                                                <p class="text-muted mb-0">ID: #<?php echo e($user->id); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo e($user->id_card ?? 'N/A'); ?></td>
                                    <td>
                                        <p class="mb-0"><?php echo e($user->email); ?></p>
                                        <small class="text-muted"><?php echo e($user->phone ?? 'Sin teléfono'); ?></small>
                                    </td>
                                    <td>
                                        <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge bg-info-subtle text-info"><?php echo e($role->display_name); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </td>
                                    <td>
                                        <?php $__currentLoopData = $user->assignments->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($assignment->institution_id): ?>
                                                <span class="delegate-badge" title="Recinto">
                                                    <i class="ri-building-line"></i> <?php echo e($assignment->institution->code ?? ''); ?>

                                                </span>
                                            <?php elseif($assignment->voting_table_id): ?>
                                                <span class="delegate-badge" title="Mesa <?php echo e($assignment->delegate_type_label); ?>">
                                                    <i class="ri-table-line"></i> Mesa <?php echo e($assignment->votingTable->number ?? ''); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($user->assignments->count() > 2): ?>
                                            <span class="delegate-badge">+<?php echo e($user->assignments->count() - 2); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($user->is_active): ?>
                                            <span class="badge bg-success-subtle text-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($user->last_login_at): ?>
                                            <?php echo e($user->last_login_at->diffForHumans()); ?>

                                        <?php else: ?>
                                            <span class="text-muted">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_users')): ?>
                                            <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-sm btn-info" title="Ver">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('assign_roles')): ?>
                                            <a href="<?php echo e(route('users.assign-roles.form', $user)); ?>" class="btn btn-sm btn-primary" title="Asignar Roles">
                                                <i class="ri-shield-user-line"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_users')): ?>
                                            <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                            <?php endif; ?>

                                            <?php if($user->is_active): ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_users')): ?>
                                                <button class="btn btn-sm btn-danger"
                                                        onclick="confirmDeactivate('<?php echo e($user->id); ?>', '<?php echo e($user->name); ?>')"
                                                        title="Desactivar">
                                                    <i class="ri-user-unfollow-line"></i>
                                                </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_users')): ?>
                                                <a href="<?php echo e(route('users.activate', $user)); ?>" class="btn btn-sm btn-success" title="Activar">
                                                    <i class="ri-user-follow-line"></i>
                                                </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="noresult">
                                            <div class="text-center">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                                </lord-icon>
                                                <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                                <p class="text-muted mb-0">No hay usuarios registrados en el sistema.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <?php echo e($users->appends(request()->query())->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script>
        function confirmDeactivate(userId, userName) {
            Swal.fire({
                title: '¿Desactivar usuario?',
                html: `¿Estás seguro de desactivar a <strong>${userName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/users/${userId}`;
                    form.innerHTML = `
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function() {
            document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/users/index.blade.php ENDPATH**/ ?>