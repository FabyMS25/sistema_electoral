<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="50">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-light.png') }}" alt="" height="50">
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
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                
                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('root') }}" class="nav-link {{ request()->routeIs('root') ? 'active' : '' }}">
                        <i class="ri-dashboard-2-line"></i> <span>@lang('translation.dashboards')</span>
                    </a>
                </li>

                {{-- Gestión de Usuarios --}}
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
                        </ul>
                    </div>
                </li>
                @endcan

                {{-- Configuración (Settings) --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') || request()->routeIs('candidates.*') ? 'active' : '' }}" 
                       href="#sidebarApps" 
                       data-bs-toggle="collapse" 
                       role="button"
                       aria-expanded="{{ request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') || request()->routeIs('candidates.*') ? 'true' : 'false' }}" 
                       aria-controls="sidebarApps">
                        <i class="ri-apps-2-line"></i> 
                        <span>@lang('translation.settings')</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('institutions.*') || request()->routeIs('voting-tables.*') || request()->routeIs('candidates.*') ? 'show' : '' }}" 
                         id="sidebarApps">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('institutions.index') }}" 
                                   class="nav-link {{ request()->routeIs('institutions.*') ? 'active' : '' }}">
                                    <i class="ri-building-line"></i>
                                    @lang('translation.list-institutions')
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('voting-tables.index') }}" 
                                   class="nav-link {{ request()->routeIs('voting-tables.*') ? 'active' : '' }}">
                                    <i class="ri-table-line"></i>
                                    @lang('translation.list-voting-tables')
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('candidates.index') }}" 
                                   class="nav-link {{ request()->routeIs('candidates.*') ? 'active' : '' }}">
                                    <i class="ri-user-2-line"></i>
                                    @lang('translation.list-candidates')
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                {{-- Gestión de Votos --}}
                <li class="nav-item">
                    <a href="{{ route('voting-table-votes.index') }}" 
                       class="nav-link {{ request()->routeIs('voting-table-votes.*') ? 'active' : '' }}">
                        <i class="ri-keyboard-fill"></i>
                        <span>@lang('translation.management')</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
        {{-- DEBUG FINAL --}}
{{-- <div style="background: #e0e0e0; padding: 15px; margin: 20px 0; border: 3px solid blue;">
    <h5>DEBUG FINAL:</h5>
    @if(auth()->check())
        <p>✅ Usuario autenticado: {{ auth()->user()->email }}</p>
        <p>✅ Tiene permiso view_users: {{ auth()->user()->hasPermission('view_users') ? 'SI' : 'NO' }}</p>
        <p>✅ Es admin: {{ auth()->user()->hasRole('administrador') ? 'SI' : 'NO' }}</p>
        <p>✅ Total permisos: {{ auth()->user()->permissions->count() }}</p>
        
        @if(auth()->user()->hasPermission('view_users'))
            <p style="color: green; font-weight: bold;">✓ EL MENÚ DEBERÍA SER VISIBLE</p>
        @else
            <p style="color: red; font-weight: bold;">✗ NO TIENE EL PERMISO view_users</p>
        @endif
    @else
        <p>❌ No autenticado</p>
    @endif
</div> --}}
    </div>
    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>