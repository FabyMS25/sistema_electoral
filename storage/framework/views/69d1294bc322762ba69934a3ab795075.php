<?php $__env->startSection('title'); ?>
    Detalle de Usuario
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        .info-label { font-weight: 600; color: #495057; margin-bottom: 0.25rem; }
        .info-value { background-color: #f8f9fa; padding: 0.5rem; border-radius: 0.25rem; margin-bottom: 1rem; }
        .timeline { position: relative; padding-left: 1.5rem; }
        .timeline::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: #e9e9ef; }
        .timeline-item { position: relative; padding-bottom: 1.5rem; }
        .timeline-item::before { content: ''; position: absolute; left: -1.5rem; top: 0.25rem; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: #0ab39c; border: 2px solid #fff; box-shadow: 0 0 0 2px #e9e9ef; }
        .delegate-type-badge { background-color: #e7f1ff; color: #0a5dc2; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Usuarios <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?> <a href="<?php echo e(route('users.index')); ?>">Lista de Usuarios</a> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> <?php echo e($user->name); ?> <?php echo e($user->last_name); ?> <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span>Información del Usuario</span>
                        <div class="btn-group" role="group">
                            <?php if(auth()->user()->allPermissions->contains('name', 'edit_users')): ?>
                            <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-warning btn-sm">
                                <i class="ri-pencil-line align-middle me-1"></i> Editar
                            </a>
                            <?php endif; ?>
                            <?php if($user->id !== auth()->id()): ?>
                                <?php if($user->is_active): ?>
                                    <?php if(auth()->user()->allPermissions->contains('name', 'delete_users')): ?>
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDeactivate('<?php echo e($user->id); ?>', '<?php echo e(addslashes($user->name)); ?>')">
                                        <i class="ri-user-unfollow-line align-middle me-1"></i> Desactivar
                                    </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if(auth()->user()->allPermissions->contains('name', 'activate_users')): ?>
                                    <form method="POST" action="<?php echo e(route('users.activate', $user)); ?>" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ri-user-follow-line align-middle me-1"></i> Activar
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 text-center mb-4">
                            <img src="<?php echo e($user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg')); ?>"
                                 alt="avatar" class="avatar-xl rounded-circle img-thumbnail mb-3">
                            <h5 class="mb-1"><?php echo e($user->name); ?> <?php echo e($user->last_name); ?></h5>
                            <p class="text-muted mb-2">ID: #<?php echo e($user->id); ?></p>
                            <?php if($user->is_active): ?>
                                <span class="badge bg-success-subtle text-success fs-6">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger fs-6">Inactivo</span>
                            <?php endif; ?>
                            <div class="mt-3">
                                <p class="text-muted mb-1">
                                    <i class="ri-time-line align-middle me-1"></i>
                                    Registrado: <?php echo e($user->created_at->format('d/m/Y H:i')); ?>

                                </p>
                                <?php if($user->last_login_at): ?>
                                <p class="text-muted mb-1">
                                    <i class="ri-login-circle-line align-middle me-1"></i>
                                    Último acceso: <?php echo e($user->last_login_at->diffForHumans()); ?>

                                </p>
                                <?php endif; ?>
                                <?php if($user->createdBy): ?>
                                <p class="text-muted mb-0">
                                    <i class="ri-user-add-line align-middle me-1"></i>
                                    Creado por: <?php echo e($user->createdBy->name); ?>

                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-label">Nombre Completo</div>
                                    <div class="info-value"><?php echo e($user->name); ?> <?php echo e($user->last_name); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Carnet de Identidad</div>
                                    <div class="info-value"><?php echo e($user->id_card ?? 'No registrado'); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Correo Electrónico</div>
                                    <div class="info-value"><?php echo e($user->email); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Teléfono</div>
                                    <div class="info-value"><?php echo e($user->phone ?? 'No registrado'); ?></div>
                                </div>
                                <div class="col-12">
                                    <div class="info-label">Dirección</div>
                                    <div class="info-value"><?php echo e($user->address ?? 'No registrada'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php if(auth()->user()->allPermissions->contains('name', 'assign_roles')): ?>
                        <a href="<?php echo e(route('users.assign-roles.form', $user)); ?>" class="btn btn-soft-primary">
                            <i class="ri-shield-user-line align-middle me-1"></i> Asignar Roles
                        </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->allPermissions->contains('name', 'assign_delegates')
                            || auth()->user()->allPermissions->contains('name', 'assign_roles')): ?>
                        <a href="<?php echo e(route('users.assign-roles.form', $user)); ?>" class="btn btn-soft-success">
                            <i class="ri-links-line align-middle me-1"></i> Asignaciones
                        </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->allPermissions->contains('name', 'assign_permissions')): ?>
                        <a href="<?php echo e(route('users.permissions.form', $user)); ?>" class="btn btn-soft-warning">
                            <i class="ri-key-line align-middle me-1"></i> Permisos Directos
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Roles Asignados</h4>
                </div>
                <div class="card-body">
                    <?php if($user->roles->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-nowrap">
                                <thead>
                                    <tr>
                                        <th>Rol</th>
                                        <th>Ámbito</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold"><?php echo e($role->display_name ?? $role->name); ?></span>
                                            <?php if($role->description): ?>
                                            <br><small class="text-muted"><?php echo e($role->description); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            
                                            <?php switch($role->pivot->scope):
                                                case ('global'): ?>
                                                    <span class="badge bg-primary">Global</span>
                                                    <?php break; ?>
                                                <?php case ('recinto'): ?>
                                                    <span class="badge bg-success">Recinto</span>
                                                    <?php break; ?>
                                                <?php case ('mesa'): ?>
                                                    <span class="badge bg-info">Mesa</span>
                                                    <?php break; ?>
                                                <?php default: ?>
                                                    <span class="badge bg-secondary"><?php echo e($role->pivot->scope); ?></span>
                                            <?php endswitch; ?>
                                        </td>
                                        <td>
                                            
                                            <?php if($role->pivot->institution_id): ?>
                                                <?php
                                                    $inst = $user->assignments
                                                        ->where('institution_id', $role->pivot->institution_id)
                                                        ->first()?->institution;
                                                ?>
                                                <small>
                                                    Recinto: <?php echo e($inst?->name ?? '#'.$role->pivot->institution_id); ?>

                                                </small>
                                            <?php endif; ?>
                                            <?php if($role->pivot->voting_table_id): ?>
                                                <?php
                                                    $vt = $user->assignments
                                                        ->where('voting_table_id', $role->pivot->voting_table_id)
                                                        ->first()?->votingTable;
                                                ?>
                                                <small>
                                                    Mesa: <?php echo e($vt ? 'N° '.$vt->number : '#'.$role->pivot->voting_table_id); ?>

                                                </small>
                                            <?php endif; ?>
                                            <?php if($role->pivot->election_type_id): ?>
                                                <?php
                                                    $et = $user->assignments
                                                        ->where('election_type_id', $role->pivot->election_type_id)
                                                        ->first()?->electionType;
                                                ?>
                                                <br><small>
                                                    Elección: <?php echo e($et?->short_name ?? $et?->name ?? '#'.$role->pivot->election_type_id); ?>

                                                </small>
                                            <?php endif; ?>
                                            <?php if(!$role->pivot->institution_id && !$role->pivot->voting_table_id): ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:50px;height:50px">
                            </lord-icon>
                            <p class="text-muted mb-0">No tiene roles asignados</p>
                            <?php if(auth()->user()->allPermissions->contains('name', 'assign_roles')): ?>
                            <a href="<?php echo e(route('users.assign-roles.form', $user)); ?>" class="btn btn-sm btn-primary mt-2">
                                Asignar Roles
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Delegaciones Activas</h4>
                </div>
                <div class="card-body">
                    <?php
                        $activeAssignments = $user->assignments->where('status', 'activo');
                    ?>
                    <?php if($activeAssignments->count() > 0): ?>
                        <div class="timeline">
                            <?php $__currentLoopData = $activeAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php if($assignment->voting_table_id): ?>
                                                <i class="ri-table-line text-info me-1"></i>
                                                Mesa <?php echo e($assignment->votingTable?->number ?? '—'); ?>

                                                (<?php echo e($assignment->votingTable?->institution?->name ?? $assignment->institution?->name ?? '—'); ?>)
                                            <?php else: ?>
                                                <i class="ri-building-line text-success me-1"></i>
                                                <?php echo e($assignment->institution?->name ?? '—'); ?>

                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1">
                                            <span class="delegate-type-badge">
                                                <?php echo e($assignment->delegate_type_label); ?>

                                            </span>
                                        </p>
                                        <p class="text-muted small mb-1">
                                            <i class="ri-calendar-line align-middle"></i>
                                            Desde: <?php echo e($assignment->assignment_date?->format('d/m/Y') ?? 'N/A'); ?>

                                            <?php if($assignment->expiration_date): ?>
                                                | Hasta: <?php echo e($assignment->expiration_date->format('d/m/Y')); ?>

                                            <?php endif; ?>
                                        </p>
                                        <?php if($assignment->credential_number): ?>
                                        <p class="text-muted small mb-0">
                                            <i class="ri-id-card-line align-middle"></i>
                                            Credencial: <?php echo e($assignment->credential_number); ?>

                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(auth()->user()->allPermissions->contains('name', 'assign_delegates')): ?>
                                    <form method="POST"
                                          action="<?php echo e(route('users.remove-assignment', [$user, $assignment])); ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Remover esta asignación?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-soft-danger">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:50px;height:50px">
                            </lord-icon>
                            <p class="text-muted mb-0">No tiene delegaciones activas</p>
                            <?php if(auth()->user()->allPermissions->contains('name', 'assign_delegates')): ?>
                            <a href="<?php echo e(route('users.assign-roles.form', $user)); ?>"
                               class="btn btn-sm btn-success mt-2">
                                Ir a Asignaciones
                            </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Historial de Actividad</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                    <th>Descripción</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $logs = \App\Models\AuditLog::where(function($q) use ($user) {
                                        $q->where('user_id', $user->id)
                                          ->orWhere(function($q2) use ($user) {
                                              $q2->where('model_type', \App\Models\User::class)
                                                 ->where('model_id', $user->id);
                                          });
                                    })->with('user')->latest()->take(10)->get();
                                ?>
                                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="text-nowrap">
                                        <?php echo e(($log->performed_at ?? $log->created_at)?->format('d/m/Y H:i')); ?>

                                    </td>
                                    <td>
                                        
                                        <?php
                                            $badgeColor = match($log->action) {
                                                'created'  => 'success',
                                                'updated'  => 'primary',
                                                'deleted'  => 'danger',
                                                'restored' => 'info',
                                                'reviewed', 'validated' => 'info',
                                                'observed', 'rejected'  => 'warning',
                                                default    => 'secondary',
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo e($badgeColor); ?>"><?php echo e($log->action); ?></span>
                                    </td>
                                    <td><?php echo e($log->description); ?></td>
                                    <td class="text-muted small"><?php echo e($log->ip_address ?? '—'); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay actividad registrada</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script>
    const CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';

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
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/users/${userId}`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = CSRF_TOKEN;

                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/users/show.blade.php ENDPATH**/ ?>