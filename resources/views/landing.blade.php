@extends('layouts.master-without-nav')
{{-- @extends('layouts.minimal') --}}
@section('title', 'Centro de Monitoreo')
@section('css')
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="layout-wrapper">
        <nav class="navbar navbar-expand-lg bg-dark" id="navbar">
                <div class="container">
                    <a class="navbar-brand" href="index">
                        <img src="{{ URL::asset('build/images/logo_elections_large.png') }}" class="card-logo card-logo-dark" alt="logo dark"
                            height="50">
                        <img src="{{ URL::asset('build/images/logo_elections_large.png') }}" class="card-logo card-logo-light" alt="logo light"
                            height="50">
                    </a>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                            <li class="nav-item">
                                <a class="nav-link active" href="#hero">Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#reviews">Reviews</a>
                            </li>
                        </ul>
                        <div class="">
                            @auth
                                <a href="/" class="btn btn-primary">Admin Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary">Ingresar</a>
                            @endauth
                        </div>
                    </div>
                    <button class="navbar-toggler py-0 fs-20 text-body" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="mdi mdi-menu"></i>
                    </button>

                </div>
        </nav>
        <div class="vertical-overlay" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent.show"></div>
        <div class="row justify-content-center mt-2">
            <div class="col-lg-11">
            <div class="card p-2 pb-0 mb-0">
                <div class='card-header'>
                    <div>
                        <h5 class="card-title mb-0">Panel de Resultados Electorales</h5>
                        <p class="text-muted mb-0">Última actualización: {{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                    @auth
                    <div class="dashboard-controls">
                        <button id="toggleDashboardBtn"
                                class="btn {{ $dashboard->is_public ? 'btn-warning' : 'btn-success' }}"
                                data-current-status="{{ $dashboard->is_public ? 'true' : 'false' }}"
                                data-toggle-url="{{ route('dashboard.toggle') }}">
                            <i class="{{ $dashboard->is_public ? 'ri-lock-unlock-fill' : 'ri-rotate-lock-fill' }}"
                            id="toggleIcon"></i>
                            <span id="toggleText">
                                {{ $dashboard->is_public ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard' }}
                            </span>
                        </button>
                    </div>
                    @endauth
                </div>
            </div>
            @include('partials.dashboard-content')
            </div>
        </div>
        <footer class="custom-footer bg-dark py-3 position-relative">
            <div class="container">
                <div class="row text-center text-sm-start align-items-center mt-2">
                    <div class="footer-inner">
                        <span>
                            © {{ date('Y') }} Sistema de Procesamiento Electoral
                        </span>
                        <span>
                            Plataforma de análisis y consolidación de datos
                        </span>
                    </div>
                </div>
            </div>
        </footer>
        {{-- <button onclick="topFunction()" class="btn btn-danger btn-icon landing-back-top" id="back-to-top">
            <i class="ri-arrow-up-line"></i>
        </button> --}}
    </div>
@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    {{-- ❌ removed: landing.init.js requires #back-to-top which is commented out --}}
    @yield('dashboard-scripts')

    @auth
    <script>
    document.getElementById('toggleDashboardBtn')?.addEventListener('click', function () {
        const btn          = this;
        const url          = btn.dataset.toggleUrl;
        const originalHtml = btn.innerHTML;

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Procesando...';

        fetch(url, {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message ?? 'Error desconocido');
            const nowPublic = data.is_public;
            btn.dataset.currentStatus = nowPublic ? 'true' : 'false';
            btn.className  = 'btn ' + (nowPublic ? 'btn-warning' : 'btn-success');
            btn.innerHTML  = `<i class="${nowPublic ? 'ri-lock-unlock-fill' : 'ri-rotate-lock-fill'}" id="toggleIcon"></i> `
                           + `<span id="toggleText">${nowPublic ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard'}</span>`;
        })
        .catch(err => {
            btn.innerHTML = originalHtml;
            alert('Error: ' + err.message);
        })
        .finally(() => { btn.disabled = false; });
    });
    </script>
    @endauth
@endsection
