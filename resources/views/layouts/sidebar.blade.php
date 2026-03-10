<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('root') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/lofo_elections.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/lofo_elections_large.png') }}" alt="" height="50">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('root') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/lofo_elections.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/llofo_elections_large.png') }}" alt="" height="50">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                @can('view_dashboard')
                <li class="nav-item">
                    <a href="{{ route('root') }}" class="nav-link {{ request()->routeIs('root') ? 'active' : '' }}">
                        <i class="ri-dashboard-2-line"></i>
                        <span>@lang('translation.dashboards')</span>
                    </a>
                </li>
                @endcan
                @can('view_votes')
                <li class="nav-item">
                    <a href="{{ route('voting-table-votes.index') }}"
                       class="nav-link {{ request()->routeIs('voting-table-votes.*') ? 'active' : '' }}">
                        <i class="ri-keyboard-fill"></i>
                        <span>Gestión de Votos</span>
                    </a>
                </li>
                @endcan
                @can('view_candidates')
                <li class="nav-item">
                    <a href="{{ route('candidates.index') }}"
                       class="nav-link {{ request()->routeIs('candidates.*') ? 'active' : '' }}">
                        <i class="ri-user-2-line"></i>
                        <span>Candidatos</span>
                    </a>
                </li>
                @endcan
                @can('view_recintos')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') ? 'active' : '' }}"
                       href="#sidebarRecintos"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="{{ request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarRecintos">
                        <i class="ri-building-line"></i>
                        <span>Recintos y Mesas</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') ? 'show' : '' }}"
                         id="sidebarRecintos">
                        <ul class="nav nav-sm flex-column">
                            @can('view_recintos')
                            <li class="nav-item">
                                <a href="{{ route('institutions.index') }}"
                                   class="nav-link {{ request()->routeIs('institutions.*') ? 'active' : '' }}">
                                    <i class="ri-building-2-line"></i>
                                    Recintos
                                </a>
                            </li>
                            @endcan
                            @can('view_mesas')
                            <li class="nav-item">
                                <a href="{{ route('voting-tables.index') }}"
                                   class="nav-link {{ request()->routeIs('voting-tables.*') ? 'active' : '' }}">
                                    <i class="ri-table-line"></i>
                                    Mesas de Votación
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan
                @can('view_users')
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                       href="#sidebarUsers"
                       data-bs-toggle="collapse"
                       role="button"
                       aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}"
                       aria-controls="sidebarUsers">
                        <i class="ri-user-settings-line"></i>
                        <span>@lang('translation.users')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('users.*') ? 'show' : '' }}"
                         id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}"
                                   class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                                    <i class="ri-list-check"></i>
                                    @lang('translation.list-users')
                                </a>
                            </li>
                            @can('create_users')
                            <li class="nav-item">
                                <a href="{{ route('users.create') }}"
                                   class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}">
                                    <i class="ri-add-circle-line"></i>
                                    @lang('translation.create-user')
                                </a>
                            </li>
                            @endcan
                            @can('assign_roles')
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}?role=delegado_recinto"
                                   class="nav-link">
                                    <i class="ri-shield-user-line"></i>
                                    Delegados
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan
                {{-- @can('view_audit_logs')
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="ri-history-line"></i>
                        <span>Auditoría</span>
                    </a>
                </li>
                @endcan --}}
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
