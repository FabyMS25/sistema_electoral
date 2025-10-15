
<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.list-managers'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Tables
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Gestores de Mesas
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Gestores de Mesas</h4>
                </div>
                
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>
                    
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="listjs-table" id="managerList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                        id="create-btn" data-bs-target="#showModal"><i
                                            class="ri-add-line align-bottom me-1"></i> Agregar</button>
                                    <button class="btn btn-soft-danger" onClick="deleteMultiple()"><i
                                            class="ri-delete-bin-2-line"></i></button>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control search" placeholder="Buscar...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-card mt-3 mb-1">
                            <table class="table align-middle table-nowrap" id="customerTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </div>
                                        </th>
                                        <th class="sort" data-sort="name">Nombre</th>
                                        <th class="sort" data-sort="id_card">Cédula</th>
                                        <th class="sort" data-sort="role">Rol</th>
                                        <th class="sort" data-sort="email">Email</th>
                                        <th class="sort" data-sort="voting_table">Mesa de Votación</th>
                                        <th class="sort" data-sort="institution">Institución</th>
                                        <th class="sort actions-column" data-sort="action">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    <?php $__currentLoopData = $managers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manager): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="chk_child" value="<?php echo e($manager->id); ?>">
                                                </div>
                                            </th>
                                            <td class="name"><?php echo e($manager->name); ?></td>
                                            <td class="id_card"><?php echo e($manager->id_card ?? 'N/A'); ?></td>
                                            <td class="role">
                                                <?php
                                                    $roleClasses = [
                                                        'presidente' => 'primary',
                                                        'secretario' => 'info',
                                                        'escrutador' => 'secondary'
                                                    ];
                                                    $roleLabels = [
                                                        'presidente' => 'Presidente',
                                                        'secretario' => 'Secretario',
                                                        'escrutador' => 'Escrutador'
                                                    ];
                                                ?>
                                                <span class="badge bg-<?php echo e($roleClasses[$manager->role]); ?>-subtle text-<?php echo e($roleClasses[$manager->role]); ?>">
                                                    <?php echo e($roleLabels[$manager->role]); ?>

                                                </span>
                                            </td>
                                            <td class="email"><?php echo e($manager->user->email ?? 'N/A'); ?></td>
                                            <td class="voting_table">
                                                <span class="badge bg-info-subtle text-info">
                                                    <?php echo e($manager->votingTable->code ?? 'N/A'); ?>

                                                </span>
                                            </td>
                                            <td class="institution">
                                                <?php echo e($manager->votingTable->institution->name ?? 'N/A'); ?>

                                                <?php if($manager->votingTable->institution->locality ?? false): ?>
                                                    <br><small class="text-muted"><?php echo e($manager->votingTable->institution->locality->name); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <div class="edit">
                                                        <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-id="<?php echo e($manager->id); ?>"
                                                            data-name="<?php echo e($manager->name); ?>"
                                                            data-id_card="<?php echo e($manager->id_card); ?>"
                                                            data-role="<?php echo e($manager->role); ?>"
                                                            data-voting_table_id="<?php echo e($manager->voting_table_id); ?>"
                                                            data-institution_id="<?php echo e($manager->votingTable->institution_id ?? ''); ?>"
                                                            data-email="<?php echo e($manager->user->email ?? ''); ?>"
                                                            data-update-url="<?php echo e(route('managers.update', $manager->id)); ?>">
                                                            Editar
                                                        </button>
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"
                                                            data-id="<?php echo e($manager->id); ?>"
                                                            data-delete-url="<?php echo e(route('managers.destroy', $manager->id)); ?>">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            
                            <?php if($managers->isEmpty()): ?>
                                <div class="noresult">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                        </lord-icon>
                                        <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                        <p class="text-muted mb-0">No hay gestores registrados en el sistema.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end">
                            <div class="pagination-wrap hstack gap-2">
                                <a class="page-item pagination-prev disabled" href="javascript:void(0);">
                                    Anterior
                                </a>
                                <ul class="pagination listjs-pagination mb-0"></ul>
                                <a class="page-item pagination-next" href="javascript:void(0);">
                                    Siguiente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">Agregar Nuevo Gestor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="managerForm" method="POST" class="tablelist-form" autocomplete="off">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="method_field" name="_method" value="">
                    <input type="hidden" id="manager_id" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name-field" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="name-field" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                placeholder="Ingrese el nombre completo" value="<?php echo e(old('name')); ?>" required />
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="id_card-field" class="form-label">Número de Identificación</label>
                            <input type="text" id="id_card-field" name="id_card" class="form-control <?php $__errorArgs = ['id_card'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                placeholder="Ingrese el número de identificación" value="<?php echo e(old('id_card')); ?>" />
                            <?php $__errorArgs = ['id_card'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="email-field" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email-field" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                placeholder="Ingrese el correo electrónico" value="<?php echo e(old('email')); ?>" required />
                            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="password-field" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" id="password-field" name="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                placeholder="Ingrese la contraseña" required />
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">Por favor ingrese una contraseña.</div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation-field" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" id="password_confirmation-field" name="password_confirmation" class="form-control" 
                                placeholder="Confirme la contraseña" required />
                        </div>

                        <div class="mb-3">
                            <label for="role-field" class="form-label">Rol <span class="text-danger">*</span></label>
                            <select class="form-control <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="role" id="role-field" required>
                                <option value="">Seleccione un rol</option>
                                <option value="presidente" <?php echo e(old('role') == 'presidente' ? 'selected' : ''); ?>>Presidente</option>
                                <option value="secretario" <?php echo e(old('role') == 'secretario' ? 'selected' : ''); ?>>Secretario</option>
                                <option value="escrutador" <?php echo e(old('role') == 'escrutador' ? 'selected' : ''); ?>>Escrutador</option>
                            </select>
                            <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">Por favor seleccione un rol.</div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="institution-field" class="form-label">Institución <span class="text-danger">*</span></label>
                            <select class="form-control <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="institution_id" id="institution-field" required>
                                <option value="">Seleccione una institución</option>
                                <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($institution->id); ?>" <?php echo e(old('institution_id') == $institution->id ? 'selected' : ''); ?>>
                                        <?php echo e($institution->name); ?> 
                                        <?php if($institution->locality): ?>
                                            - <?php echo e($institution->locality->name); ?>

                                        <?php endif; ?>
                                        <?php if($institution->district): ?>
                                            , <?php echo e($institution->district->name); ?>

                                        <?php endif; ?>
                                        <?php if($institution->zone): ?>
                                            , Zona <?php echo e($institution->zone->name); ?>

                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">Por favor seleccione una institución.</div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="mb-3">
                            <label for="voting_table-field" class="form-label">Mesa de Votación <span class="text-danger">*</span></label>
                            <select class="form-control <?php $__errorArgs = ['voting_table_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="voting_table_id" id="voting_table-field" required>
                                <option value="">Primero seleccione una institución</option>
                            </select>
                            <?php $__errorArgs = ['voting_table_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">Por favor seleccione una mesa de votación.</div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" id="save-btn">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                            colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>¿Está seguro?</h4>
                            <p class="text-muted mx-4 mb-0">¿Está seguro de que desea eliminar este gestor?</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn w-sm btn-danger">Sí, eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end modal -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/prismjs/prism.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.js/list.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/list.pagination.js/list.pagination.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const institutionSelect = new Choices('#institution-field', {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
            });
            const votingTableSelect = new Choices('#voting_table-field', {
                searchEnabled: true,
                itemSelectText: '',
                shouldSort: false,
            });
            const roleSelect = new Choices('#role-field', {
                searchEnabled: false,
                itemSelectText: '',
                shouldSort: false,
            });

            document.getElementById('institution-field').addEventListener('change', function() {
                const institutionId = this.value;
                const votingTableField = document.getElementById('voting_table-field');
                
                if (institutionId) {
                    fetch(`/managers/voting-tables/${institutionId}`)
                        .then(response => response.json())
                        .then(data => {
                            votingTableField.innerHTML = '<option value="">Seleccione una mesa de votación</option>';
                            
                            data.forEach(votingTable => {
                                const option = document.createElement('option');
                                option.value = votingTable.id;
                                option.textContent = `${votingTable.code} - Mesa ${votingTable.number}`;
                                votingTableField.appendChild(option);
                            });
                            
                            votingTableSelect.destroy();
                            votingTableSelect.init();
                        })
                        .catch(error => {
                            console.error('Error loading voting tables:', error);
                            votingTableField.innerHTML = '<option value="">Error al cargar mesas</option>';
                        });
                } else {
                    votingTableField.innerHTML = '<option value="">Primero seleccione una institución</option>';
                    votingTableSelect.destroy();
                    votingTableSelect.init();
                }
            });

            var options = {valueNames: ['name', 'id_card', 'role', 'email', 'voting_table', 'institution']};
            var managerList = new List('managerList', options).on('updated', function(list) {
                attachEditEventListeners();
                attachDeleteEventListeners();
            });

            document.getElementById('checkAll').addEventListener('change', function() {
                var checkboxes = document.querySelectorAll('input[name="chk_child"]');
                for (var i = 0; i < checkboxes.length; i++) {
                    checkboxes[i].checked = this.checked;
                }
            });

            document.getElementById('create-btn').addEventListener('click', function() {
                document.getElementById('exampleModalLabel').textContent = 'Agregar Nuevo Gestor';
                document.getElementById('managerForm').action = "<?php echo e(route('managers.store')); ?>";
                document.getElementById('method_field').value = '';            
                document.getElementById('managerForm').reset();
                document.getElementById('manager_id').value = '';
                document.getElementById('password-field').required = true;
                document.getElementById('password_confirmation-field').required = true;
                document.getElementById('save-btn').textContent = 'Guardar';                
                institutionSelect.setChoiceByValue('');
                votingTableSelect.setChoiceByValue('');
                roleSelect.setChoiceByValue('presidente');
                
                clearValidationErrors();
            });

            function attachEditEventListeners() {
                document.querySelectorAll('.edit-item-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        const id_card = this.getAttribute('data-id_card');
                        const role = this.getAttribute('data-role');
                        const voting_table_id = this.getAttribute('data-voting_table_id');
                        const institution_id = this.getAttribute('data-institution_id');
                        const email = this.getAttribute('data-email');
                        const updateUrl = this.getAttribute('data-update-url');
                        
                        document.getElementById('exampleModalLabel').textContent = 'Editar Gestor';
                        document.getElementById('managerForm').action = updateUrl;
                        document.getElementById('method_field').value = 'PUT';
                        document.getElementById('manager_id').value = id;
                        document.getElementById('name-field').value = name;
                        document.getElementById('id_card-field').value = id_card;
                        document.getElementById('email-field').value = email;
                        document.getElementById('password-field').required = false;
                        document.getElementById('password_confirmation-field').required = false;
                        
                        institutionSelect.setChoiceByValue(institution_id);
                        roleSelect.setChoiceByValue(role);
                        
                        if (institution_id) {
                            fetch(`/managers/voting-tables/${institution_id}`)
                                .then(response => response.json())
                                .then(data => {
                                    const votingTableField = document.getElementById('voting_table-field');
                                    votingTableField.innerHTML = '<option value="">Seleccione una mesa de votación</option>';
                                    
                                    data.forEach(votingTable => {
                                        const option = document.createElement('option');
                                        option.value = votingTable.id;
                                        option.textContent = `${votingTable.code} - Mesa ${votingTable.number}`;
                                        if (votingTable.id == voting_table_id) {
                                            option.selected = true;
                                        }
                                        votingTableField.appendChild(option);
                                    });
                                    
                                    votingTableSelect.destroy();
                                    votingTableSelect.init();
                                    votingTableSelect.setChoiceByValue(voting_table_id);
                                });
                        }
                        
                        document.getElementById('save-btn').textContent = 'Actualizar';
                        clearValidationErrors();
                    });
                });
            }

            function attachDeleteEventListeners() {
                document.querySelectorAll('.remove-item-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const deleteUrl = this.getAttribute('data-delete-url');
                        document.getElementById('deleteForm').action = deleteUrl;
                    });
                });
            }

            attachEditEventListeners();
            attachDeleteEventListeners();

            const form = document.getElementById('managerForm');
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                const requiredFields = [
                    'name', 'email', 'role', 'institution_id', 'voting_table_id'
                ];
                
                requiredFields.forEach(field => {
                    const element = document.getElementById(field + '-field');
                    if (!element.value.trim()) {
                        element.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        element.classList.remove('is-invalid');
                    }
                });
                
                if (!document.getElementById('manager_id').value) {
                    const password = document.getElementById('password-field');
                    const passwordConfirmation = document.getElementById('password_confirmation-field');
                    
                    if (!password.value) {
                        password.classList.add('is-invalid');
                        isValid = false;
                    }
                    
                    if (password.value !== passwordConfirmation.value) {
                        passwordConfirmation.classList.add('is-invalid');
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
            
            document.getElementById('showModal').addEventListener('hidden.bs.modal', function () {
                clearValidationErrors();
            });
            
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });
            }
        });
        
        function deleteMultiple() {
            alert('Función de eliminar múltiple - por implementar');
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/tables-managers.blade.php ENDPATH**/ ?>