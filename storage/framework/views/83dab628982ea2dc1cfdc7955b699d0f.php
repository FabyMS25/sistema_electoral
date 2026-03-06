<div class="modal fade" id="candidateModal" tabindex="-1" aria-labelledby="candidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light p-3">
                <h5 class="modal-title" id="candidateModalLabel">
                    <i class="ri-user-star-line me-1"></i>
                    <span id="modalTitleText">Agregar Nuevo Candidato</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
            </div>

            <form id="candidateForm" method="POST" class="tablelist-form" autocomplete="off" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="method_field" name="_method" value="">
                <input type="hidden" id="form-method" name="_method" value="POST">
                <input type="hidden" id="candidate_id" name="id">

                <div class="modal-body">
                    <!-- Información Básica -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="name-field" class="form-label">
                                    Nombre Completo <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name-field" name="name" class="form-control"
                                    placeholder="Ej: Juan Pérez González" value="<?php echo e(old('name')); ?>" required maxlength="255" />
                                <div class="invalid-feedback" id="name-error">El nombre es obligatorio y debe tener máximo 255 caracteres.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Partido -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party-field" class="form-label">
                                    Sigla del Partido <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="party-field" name="party" class="form-control"
                                    placeholder="Ej: MAS, UNE, CC" value="<?php echo e(old('party')); ?>" required maxlength="50" />
                                <div class="invalid-feedback" id="party-error">La sigla del partido es obligatoria.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party_full_name-field" class="form-label">
                                    Nombre Completo del Partido
                                </label>
                                <input type="text" id="party_full_name-field" name="party_full_name" class="form-control"
                                    placeholder="Nombre completo del partido político" value="<?php echo e(old('party_full_name')); ?>" maxlength="255" />
                                <div class="invalid-feedback" id="party-full-name-error">El nombre no puede exceder los 255 caracteres.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Lista -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="list_name-field" class="form-label">
                                    Nombre de la Lista
                                </label>
                                <input type="text" id="list_name-field" name="list_name" class="form-control"
                                    placeholder="Ej: Lista 1, Frente Amplio, Juntos" value="<?php echo e(old('list_name')); ?>" maxlength="255" />
                                <div class="invalid-feedback" id="list-name-error">El nombre no puede exceder los 255 caracteres.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="list_order-field" class="form-label">
                                    Orden en la Lista
                                </label>
                                <input type="number" id="list_order-field" name="list_order" class="form-control"
                                    placeholder="Ej: 1, 2, 3" value="<?php echo e(old('list_order')); ?>" min="1" step="1" />
                                <div class="invalid-feedback" id="list-order-error">El orden debe ser un número positivo.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Color y Categoría de Elección -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color-field" class="form-label">
                                    Color Representativo
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="color" class="form-control form-control-color w-25 me-2"
                                        id="color-field" name="color" value="<?php echo e(old('color', '#1b8af8')); ?>"
                                        style="height: 38px; padding: 2px;" title="Seleccione un color" />
                                    <input type="text" class="form-control" id="color-hex"
                                        value="<?php echo e(old('color', '#1b8af8')); ?>" placeholder="#RRGGBB"
                                        pattern="^#[0-9A-Fa-f]{6}$" maxlength="7" />
                                </div>
                                <small class="text-muted">Formato hexadecimal: #RRGGBB</small>
                                <div class="invalid-feedback" id="color-error">Seleccione un color válido en formato hexadecimal (#RRGGBB).</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="election_type_category_id-field" class="form-label">
                                    Elección / Categoría <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" name="election_type_category_id" id="election_type_category_id-field" required>
                                    <option value="">Seleccione una combinación</option>
                                    <?php $__currentLoopData = $electionTypeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($etc->id); ?>"
                                            data-election-type="<?php echo e($etc->electionType->name); ?>"
                                            data-category="<?php echo e($etc->electionCategory->name); ?>"
                                            data-code="<?php echo e($etc->electionCategory->code); ?>"
                                            data-ballot-order="<?php echo e($etc->ballot_order); ?>"
                                            data-votes-per-person="<?php echo e($etc->votes_per_person); ?>"
                                            <?php echo e(old('election_type_category_id', $candidate->election_type_category_id ?? '') == $etc->id ? 'selected' : ''); ?>>
                                            <?php echo e($etc->electionType->name); ?> - <?php echo e($etc->electionCategory->name); ?>

                                            (<?php echo e($etc->electionCategory->code); ?>) - Franja <?php echo e($etc->ballot_order); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted" id="selected-category-info"></small>
                                <div class="invalid-feedback" id="election-type-category-error">Seleccione una combinación de elección y categoría.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Categoría (readonly) -->
                    <div class="row" id="category-info-row" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Código:</small>
                                        <span class="fw-semibold" id="info-category-code">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Franja:</small>
                                        <span class="fw-semibold" id="info-ballot-order">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block">Votos por persona:</small>
                                        <span class="fw-semibold" id="info-votes-per-person">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación Geográfica -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="department_id-field" class="form-label">Departamento</label>
                                <select class="form-select <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        name="department_id" id="department_id-field">
                                    <option value="">Seleccione un departamento</option>
                                    <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($department->id); ?>"
                                            <?php echo e(old('department_id', $candidate->department_id ?? '') == $department->id ? 'selected' : ''); ?>>
                                            <?php echo e($department->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <small class="text-muted">Opcional - para candidatos departamentales, provinciales o municipales</small>
                                <?php $__errorArgs = ['department_id'];
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
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="province_id-field" class="form-label">Provincia</label>
                                <select class="form-select <?php $__errorArgs = ['province_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        name="province_id" id="province_id-field" disabled>
                                    <option value="">Primero seleccione un departamento</option>
                                    <?php if(isset($provinces) && count($provinces) > 0): ?>
                                        <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($province->id); ?>"
                                                <?php echo e(old('province_id', $candidate->province_id ?? '') == $province->id ? 'selected' : ''); ?>>
                                                <?php echo e($province->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                                <?php $__errorArgs = ['province_id'];
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
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="municipality_id-field" class="form-label">Municipio</label>
                                <select class="form-select <?php $__errorArgs = ['municipality_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        name="municipality_id" id="municipality_id-field" disabled>
                                    <option value="">Primero seleccione una provincia</option>
                                    <?php if(isset($municipalities) && count($municipalities) > 0): ?>
                                        <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $municipality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($municipality->id); ?>"
                                                <?php echo e(old('municipality_id', $candidate->municipality_id ?? '') == $municipality->id ? 'selected' : ''); ?>>
                                                <?php echo e($municipality->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </select>
                                <?php $__errorArgs = ['municipality_id'];
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
                        </div>
                    </div>

                    <!-- Estado Activo/Inactivo (solo visible en edición) -->
                    <div class="row" id="active-status-row" style="display: none;">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active-field" name="active" value="1" checked>
                                    <label class="form-check-label" for="active-field">Candidato Activo</label>
                                </div>
                                <small class="text-muted">Si está inactivo, no aparecerá en las listas</small>
                            </div>
                        </div>
                    </div>

                    <!-- Imágenes -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo-field" class="form-label">Foto del Candidato</label>
                                <input type="file" id="photo-field" name="photo" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg,image/gif" />
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Máximo 2MB</small>
                                <div class="invalid-feedback" id="photo-error"></div>
                                <div class="image-preview-container mt-2" id="photo-preview-container">
                                    <img id="photo-preview" class="image-preview img-thumbnail" src="" alt="Vista previa" style="display: none; max-height: 100px;">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="party_logo-field" class="form-label">Logo del Partido</label>
                                <input type="file" id="party_logo-field" name="party_logo" class="form-control"
                                    accept="image/jpeg,image/png,image/jpg,image/gif" />
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Máximo 2MB</small>
                                <div class="invalid-feedback" id="party-logo-error"></div>
                                <div class="image-preview-container mt-2" id="party-logo-preview-container">
                                    <img id="party-logo-preview" class="image-preview img-thumbnail" src="" alt="Vista previa" style="display: none; max-height: 100px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="save-btn">
                            <i class="ri-save-line me-1"></i> Guardar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show category info when selection changes
    const categorySelect = document.getElementById('election_type_category_id-field');
    const categoryInfoRow = document.getElementById('category-info-row');
    const infoCode = document.getElementById('info-category-code');
    const infoBallot = document.getElementById('info-ballot-order');
    const infoVotes = document.getElementById('info-votes-per-person');

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (this.value) {
                const code = selectedOption.dataset.code || '-';
                const ballotOrder = selectedOption.dataset.ballotOrder || '-';
                const votesPerPerson = selectedOption.dataset.votesPerPerson || '1';

                infoCode.textContent = code;
                infoBallot.textContent = ballotOrder;
                infoVotes.textContent = votesPerPerson;
                categoryInfoRow.style.display = 'block';
            } else {
                categoryInfoRow.style.display = 'none';
            }
        });
        if (categorySelect.value) {
            categorySelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
<?php /**PATH D:\_Mine\corporate\resources\views/candidates/partials/modal-form.blade.php ENDPATH**/ ?>