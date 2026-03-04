
<!-- Cargar ApexCharts directamente desde CDN (más confiable) -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando dashboard...');

    // Verificar que ApexCharts está disponible
    if (typeof ApexCharts === 'undefined') {
        console.error('❌ ApexCharts no está cargado');

        // Mostrar mensaje de error
        const containers = document.querySelectorAll('#candidates_chart, #party_distribution_chart');
        containers.forEach(container => {
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-warning text-center p-4">
                        <i class="ri-alert-line fs-1"></i>
                        <h4 class="mt-2">Error al cargar los gráficos</h4>
                        <p class="mb-0">No se pudo cargar la librería de gráficos.</p>
                    </div>
                `;
            }
        });
        return;
    }

    console.log('✅ ApexCharts cargado correctamente');

    let refreshTimer = null;
    let isRefreshing = false;
    let charts = {};

    // Obtener datos de PHP
    const candidateStats = <?php echo json_encode($candidateStats ?? [], 15, 512) ?>;
    const totalVotes = <?php echo e($totalVotes ?? 0); ?>;

    console.log('📊 Datos recibidos:', { candidateStats, totalVotes });

    if (Object.keys(candidateStats).length === 0) {
        console.warn('⚠️ No hay datos de candidatos');
        return;
    }

    try {
        // Preparar datos
        const sortedStats = Object.values(candidateStats).sort((a, b) => b.votes - a.votes);
        const candidateNames = sortedStats.map(stat => {
            const name = stat.candidate?.name || 'Sin nombre';
            return name.length > 20 ? name.substring(0, 18) + '...' : name;
        });
        const candidateColors = sortedStats.map(stat => stat.candidate?.color || '#3b5de7');
        const candidateVotes = sortedStats.map(stat => stat.votes || 0);

        console.log('📊 Datos procesados:', { candidateNames, candidateVotes });

        // Gráfico de barras
        const barContainer = document.querySelector("#candidates_chart");
        if (barContainer) {
            const barOptions = {
                series: [{ name: 'Votos', data: candidateVotes }],
                chart: { type: 'bar', height: 350, toolbar: { show: true } },
                plotOptions: { bar: { distributed: true, borderRadius: 4 } },
                xaxis: {
                    categories: candidateNames,
                    labels: { rotate: -45, trim: true, style: { fontSize: '11px' } }
                },
                colors: candidateColors,
                tooltip: { y: { formatter: val => val.toLocaleString() + ' votos' } }
            };
            charts.candidateChart = new ApexCharts(barContainer, barOptions);
            charts.candidateChart.render();
            console.log('✅ Gráfico de barras creado');
        }

        // Gráfico de donut
        const donutContainer = document.querySelector("#party_distribution_chart");
        if (donutContainer && candidateVotes.length > 0) {
            const donutOptions = {
                series: candidateVotes,
                labels: candidateNames,
                colors: candidateColors,
                chart: { type: 'donut', height: 300 },
                legend: { position: 'bottom' },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString()
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: val => val.toLocaleString() + ' votos' } }
            };
            charts.partyChart = new ApexCharts(donutContainer, donutOptions);
            charts.partyChart.render();
            console.log('✅ Gráfico de donut creado');
        }

    } catch (error) {
        console.error('❌ Error creando gráficos:', error);
    }

    // Funciones de auto-refresh
    window.refreshDashboard = function() {
        if (isRefreshing) return;
        isRefreshing = true;

        const electionType = document.querySelector('select[name="election_type"]')?.value || '';
        const url = `/refresh-dashboard?election_type=${electionType}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
                isRefreshing = false;
            })
            .catch(error => {
                console.error('Error:', error);
                isRefreshing = false;
            });
    };

    window.startAutoRefresh = function() {
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(refreshDashboard, 120000);
        const status = document.getElementById('refresh-status');
        if (status) status.innerHTML = '<small class="text-success">● Activo</small>';
    };

    window.stopAutoRefresh = function() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
        const status = document.getElementById('refresh-status');
        if (status) status.innerHTML = '<small class="text-secondary">○ Pausado</small>';
    };

    // Iniciar auto-refresh
    startAutoRefresh();
});
</script>
<?php /**PATH D:\_Mine\corporate\resources\views/partials/dashboard-scripts.blade.php ENDPATH**/ ?>