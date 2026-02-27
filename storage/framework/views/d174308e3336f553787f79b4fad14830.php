<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="50">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="50">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                
                
                <li class="nav-item">
                    <a href="<?php echo e(route('root')); ?>" class="nav-link <?php echo e(request()->routeIs('root') ? 'active' : ''); ?>">
                        <i class="ri-dashboard-2-line"></i> <span><?php echo app('translator')->get('translation.dashboards'); ?></span>
                    </a>
                </li>

                
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_users')): ?>
                
                <li class="nav-item">
                    <a class="nav-link menu-link <?php echo e(request()->routeIs('users.*') ? 'active' : ''); ?>" 
                       href="#sidebarUsers" 
                       data-bs-toggle="collapse" 
                       role="button"
                       aria-expanded="<?php echo e(request()->routeIs('users.*') ? 'true' : 'false'); ?>" 
                       aria-controls="sidebarUsers">
                        <i class="ri-user-settings-line"></i> 
                        <span><?php echo app('translator')->get('translation.users'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(request()->routeIs('users.*') ? 'show' : ''); ?>" 
                         id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('users.index')); ?>" 
                                   class="nav-link <?php echo e(request()->routeIs('users.index') ? 'active' : ''); ?>">
                                    <i class="ri-list-check"></i>
                                    <?php echo app('translator')->get('translation.list-users'); ?>
                                </a>
                            </li>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_users')): ?>
                            <li class="nav-item">
                                <a href="<?php echo e(route('users.create')); ?>" 
                                   class="nav-link <?php echo e(request()->routeIs('users.create') ? 'active' : ''); ?>">
                                    <i class="ri-add-circle-line"></i>
                                    <?php echo app('translator')->get('translation.create-user'); ?>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                
                <li class="nav-item">
                    <a class="nav-link menu-link <?php echo e(request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') || request()->routeIs('candidates.*') ? 'active' : ''); ?>" 
                       href="#sidebarApps" 
                       data-bs-toggle="collapse" 
                       role="button"
                       aria-expanded="<?php echo e(request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') || request()->routeIs('candidates.*') ? 'true' : 'false'); ?>" 
                       aria-controls="sidebarApps">
                        <i class="ri-apps-2-line"></i> 
                        <span><?php echo app('translator')->get('translation.settings'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown <?php echo e(request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') || request()->routeIs('candidates.*') ? 'show' : ''); ?>" 
                         id="sidebarApps">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="<?php echo e(route('institutions.index')); ?>" 
                                   class="nav-link <?php echo e(request()->routeIs('institutions.*') ? 'active' : ''); ?>">
                                    <i class="ri-building-line"></i>
                                    <?php echo app('translator')->get('translation.list-institutions'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('voting-tables.index')); ?>" 
                                   class="nav-link <?php echo e(request()->routeIs('voting-tables.*') ? 'active' : ''); ?>">
                                    <i class="ri-table-line"></i>
                                    <?php echo app('translator')->get('translation.list-voting-tables'); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('candidates.index')); ?>" 
                                   class="nav-link <?php echo e(request()->routeIs('candidates.*') ? 'active' : ''); ?>">
                                    <i class="ri-user-2-line"></i>
                                    <?php echo app('translator')->get('translation.list-candidates'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                
                <li class="nav-item">
                    <a href="<?php echo e(route('voting-table-votes.index')); ?>" 
                       class="nav-link <?php echo e(request()->routeIs('voting-table-votes.*') ? 'active' : ''); ?>">
                        <i class="ri-keyboard-fill"></i>
                        <span><?php echo app('translator')->get('translation.management'); ?></span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
        

    </div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div><?php /**PATH D:\_Mine\corporate\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>