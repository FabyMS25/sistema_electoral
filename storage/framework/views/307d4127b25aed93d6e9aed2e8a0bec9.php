

<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-2 px-3">
        <form method="GET" action="<?php echo e(url()->current()); ?>" id="dashboardFilterForm">
            <div class="row g-2 align-items-end">

                
                <?php if($dashboard?->show_election_switcher !== false): ?>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-vote-line me-1"></i>Tipo de Elección
                    </label>
                    <select name="election_type" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                        <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $et): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($et->id); ?>"
                            <?php echo e($selectedElectionType?->id == $et->id ? 'selected' : ''); ?>>
                            <?php echo e($et->short_name ?? $et->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <?php else: ?>
                
                <input type="hidden" name="election_type" value="<?php echo e($selectedElectionType?->id ?? ''); ?>">
                <?php endif; ?>

                
                <?php if($dashboard?->show_category_filter !== false && $typeCategories->count() > 1): ?>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-bar-chart-line me-1"></i>Categoría
                    </label>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php $__currentLoopData = $typeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $code = $tc->electionCategory?->code ?? 'UNK'; ?>
                        <button type="button"
                                class="btn btn-sm <?php echo e($code === $activeCategoryCode ? 'btn-primary' : 'btn-outline-secondary'); ?> category-pill-btn"
                                data-category="<?php echo e($code); ?>"
                                onclick="setCategoryAndSync('<?php echo e($code); ?>')">
                            <?php switch($code):
                                case ('ALC'): ?> <i class="ri-user-star-line me-1"></i> <?php break; ?>
                                <?php case ('CON'): ?> <i class="ri-group-line me-1"></i>      <?php break; ?>
                                <?php case ('GOB'): ?> <i class="ri-government-line me-1"></i> <?php break; ?>
                                <?php default: ?>     <i class="ri-bar-chart-line me-1"></i>
                            <?php endswitch; ?>
                            <?php echo e($tc->electionCategory?->name ?? $code); ?>

                        </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    
                    <input type="hidden" name="category" id="filter-category-input"
                           value="<?php echo e($activeCategoryCode ?? ''); ?>">
                </div>
                <?php else: ?>
                <input type="hidden" name="category" value="<?php echo e($activeCategoryCode ?? ''); ?>">
                <?php endif; ?>

                
                <div class="col-lg-2 col-md-4 col-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-map-2-line me-1"></i>Departamento
                    </label>
                    <select name="department" id="dept-select" class="form-select form-select-sm">
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($dept->id); ?>"
                            <?php echo e($selectedDepartment == $dept->id ? 'selected' : ''); ?>>
                            <?php echo e($dept->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-1 col-md-4 col-6">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-map-pin-2-line me-1"></i>Provincia
                    </label>
                    <select name="province" id="prov-select" class="form-select form-select-sm">
                        <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($prov->id); ?>"
                            <?php echo e($selectedProvince == $prov->id ? 'selected' : ''); ?>>
                            <?php echo e($prov->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-1 col-md-4">
                    <label class="form-label form-label-sm fw-semibold mb-1 text-muted text-uppercase"
                           style="font-size:.68rem;letter-spacing:.04em;">
                        <i class="ri-community-line me-1"></i>Municipio
                    </label>
                    <select name="municipality" id="muni-select" class="form-select form-select-sm"
                            onchange="this.form.submit()">
                        <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $muni): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($muni->id); ?>"
                            <?php echo e($selectedMunicipality == $muni->id ? 'selected' : ''); ?>>
                            <?php echo e($muni->name); ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-lg-2 col-md-12 d-flex align-items-end gap-2 justify-content-end">
                    <span class="text-muted small me-1 d-none d-lg-block text-end lh-1">
                        <?php if($selectedElectionType?->election_date): ?>
                        <span class="d-block fw-semibold text-dark" style="font-size:.75rem;">
                            <?php echo e(\Carbon\Carbon::parse($selectedElectionType->election_date)->format('d/m/Y')); ?>

                        </span>
                        <?php endif; ?>
                        <span id="ds-filter-time"><?php echo e(now()->format('H:i')); ?></span>
                    </span>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="ri-filter-line me-1"></i>Aplicar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            onclick="ElectionDashboard?.refresh()" title="Actualizar datos">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
(function () {
    // ── Category pill: update hidden input + sync content tabs ────────────────
    window.setCategoryAndSync = function (code) {
        // Update hidden input so form submission carries the right category
        const inp = document.getElementById('filter-category-input');
        if (inp) inp.value = code;

        // Highlight the clicked pill
        document.querySelectorAll('.category-pill-btn').forEach(btn => {
            const active = btn.dataset.category === code;
            btn.classList.toggle('btn-primary',          active);
            btn.classList.toggle('btn-outline-secondary', !active);
        });

        // Switch the Bootstrap tab in dashboard-content (no page reload needed)
        const tabLink = document.querySelector(`#categoryTabs [data-category="${code}"]`);
        if (tabLink) {
            const bsTab = bootstrap.Tab.getOrCreateInstance(tabLink);
            bsTab.show();
        }

        // Sync chart pickers (locality + donut) if they exist
        ['locality-category-picker', 'donut-category-picker'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.value = code.toLowerCase();
                el.dispatchEvent(new Event('change'));
            }
        });
    };

    // ── Tab click → sync filter pills back ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#categoryTabs [data-category]').forEach(link => {
            link.addEventListener('shown.bs.tab', e => {
                const code = e.target.dataset.category;
                if (!code) return;
                // Sync pill buttons
                document.querySelectorAll('.category-pill-btn').forEach(btn => {
                    const active = btn.dataset.category === code;
                    btn.classList.toggle('btn-primary',           active);
                    btn.classList.toggle('btn-outline-secondary', !active);
                });
                // Sync hidden input
                const inp = document.getElementById('filter-category-input');
                if (inp) inp.value = code;
            });
        });
    });

    // ── Geography cascade ─────────────────────────────────────────────────────
    document.getElementById('dept-select')?.addEventListener('change', function () {
        fetch(`/api/provinces/${this.value}`)
            .then(r => r.json())
            .then(data => {
                const ps = document.getElementById('prov-select');
                ps.innerHTML = data.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
                return fetch(`/api/municipalities/${data[0]?.id}`);
            })
            .then(r => r?.json())
            .then(data => {
                if (!data) return;
                const ms = document.getElementById('muni-select');
                ms.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
            })
            .catch(console.warn);
    });

    document.getElementById('prov-select')?.addEventListener('change', function () {
        fetch(`/api/municipalities/${this.value}`)
            .then(r => r.json())
            .then(data => {
                const ms = document.getElementById('muni-select');
                ms.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
            })
            .catch(console.warn);
    });
})();
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/partials/dashboard-filters.blade.php ENDPATH**/ ?>