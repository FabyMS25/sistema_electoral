@extends('layouts.master')
@section('title')
    @lang('translation.dashboards')
@endsection

@section('content')
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
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
@endsection

@section('script')
    @yield('dashboard-scripts')
    @auth
    <script>
    document.getElementById('toggleDashboardBtn')?.addEventListener('click', function () {
        const btn         = this;
        const url         = btn.dataset.toggleUrl;
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
            console.error('Toggle error:', err);
            btn.innerHTML = originalHtml;
            alert('Error al cambiar el estado del dashboard: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
        });
    });
    </script>
    @endauth
@endsection
