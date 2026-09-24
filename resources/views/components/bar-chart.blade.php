@props(['title', 'labels' => [], 'values' => [], 'prefix' => '', 'suffix' => ''])

@php
    $max = count($values) > 0 ? max($values) : 0;
    $hasData = $max > 0;
    $dataCount = count($values);
@endphp

<div class="card-surface p-6 relative overflow-hidden">
    <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full bg-gradient-to-br from-orange-500/10 to-amber-500/10 blur-2xl pointer-events-none"></div>
    @if($title)
    <div class="relative flex items-center gap-3 mb-6">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 flex items-center justify-center shadow-sm shrink-0">
            <i class="ri-bar-chart-grouped-line text-white"></i>
        </div>
        <h3 class="font-black text-slate-900 dark:text-white text-sm tracking-tight">{{ $title }}</h3>
        <span class="ml-auto hidden sm:inline-flex items-center gap-1 text-[11px] font-bold tracking-widest uppercase text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full">{{ $dataCount }} data</span>
    </div>
    @endif

    @if(!$hasData)
        <div class="relative py-10 text-center">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-orange-50 dark:bg-orange-500/10 text-orange-500"><i class="ri-line-chart-line text-2xl"></i></span>
            <p class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-200">Belum ada data</p>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Grafik akan muncul setelah ada transaksi.</p>
        </div>
    @else
        <div class="relative h-52">
            <canvas
                class="kosmanager-bar-chart"
                data-chart-labels='@json($labels)'
                data-chart-values='@json($values)'
                data-chart-prefix="{{ $prefix }}"
                data-chart-suffix="{{ $suffix }}"
                role="img"
                aria-label="{{ $title }}"
            ></canvas>
        </div>
        <div class="mt-4 h-px bg-gradient-to-r from-transparent via-slate-100 dark:via-slate-800 to-transparent"></div>
    @endif
</div>

@push('scripts')
@if($hasData)
<script>
(function () {
    if (window.__kosManagerChartInitializerRegistered) {
        return;
    }

    window.__kosManagerChartInitializerRegistered = true;

    var chartInstances = new WeakMap();

    function parseJsonAttribute(canvas, attributeName, fallback) {
        var raw = canvas.getAttribute(attributeName);
        if (!raw) return fallback;
        try {
            return JSON.parse(raw);
        } catch (e) {
            console.warn('[KosManager] Invalid JSON in ' + attributeName, e);
            return fallback;
        }
    }

    function formatNumber(value) {
        var number = Number(value);
        if (!Number.isFinite(number)) return '0';
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function initChart(canvas) {
        if (!(canvas instanceof HTMLCanvasElement)) return;
        if (chartInstances.has(canvas)) return;
        if (!window.Chart) {
            console.warn('[KosManager] Chart.js is not available.');
            return;
        }

        var labels = parseJsonAttribute(canvas, 'data-chart-labels', []);
        var values = parseJsonAttribute(canvas, 'data-chart-values', []);
        var prefix = canvas.getAttribute('data-chart-prefix') || '';
        var suffix = canvas.getAttribute('data-chart-suffix') || '';

        if (!Array.isArray(labels) || !Array.isArray(values)) {
            console.warn('[KosManager] Chart data must be arrays.', canvas);
            return;
        }

        if (labels.length === 0 || values.length === 0) return;
        if (labels.length !== values.length) {
            console.warn('[KosManager] Chart labels/values length mismatch.', canvas);
            return;
        }

        var isDarkMode = document.documentElement.classList.contains('dark');
        var textColor = isDarkMode ? '#cbd5e1' : '#475569';
        var gridColor = isDarkMode ? '#334155' : '#e2e8f0';

        var ctx = canvas.getContext('2d');
        if (!ctx) {
            console.warn('[KosManager] Unable to acquire canvas context.', canvas);
            return;
        }

        var chart = new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: function(context) {
                        var index = context.dataIndex;
                        var value = context.dataset.data[index];
                        var maxValue = Math.max.apply(null, context.dataset.data);
                        var chartCanvas = context.chart.canvas;
                        var isDark = document.documentElement.classList.contains('dark');

                        if (value === maxValue && value > 0) {
                            var grad = ctx.createLinearGradient(0, 0, 0, canvas.height);
                            grad.addColorStop(0, isDark ? '#fb923c' : '#fb923c');
                            grad.addColorStop(1, isDark ? '#ea580c' : '#f97316');
                            return grad;
                        }
                        var grad = ctx.createLinearGradient(0, 0, 0, canvas.height);
                        grad.addColorStop(0, isDark ? '#fb923c' : '#fb923c');
                        grad.addColorStop(1, isDark ? '#c2410c' : '#f97316');
                        return grad;
                    },
                    borderRadius: 4,
                    borderSkipped: false,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 800,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDarkMode ? '#1e293b' : '#0f172a',
                        titleColor: '#f8fafc',
                        bodyColor: '#f8fafc',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        titleFont: { weight: 'bold', size: 12 },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                var value = context.raw;
                                return prefix + formatNumber(value) + suffix;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: textColor,
                            font: { size: 11, weight: '600' }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        border: { display: false },
                        ticks: {
                            color: textColor,
                            font: { size: 11 },
                            callback: function(value) {
                                return prefix + formatNumber(value) + suffix;
                            }
                        }
                    }
                }
            }
        });

        chartInstances.set(canvas, chart);
    }

    function initAllCharts() {
        document.querySelectorAll('.kosmanager-bar-chart').forEach(initChart);
    }

    function boot() {
        initAllCharts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
</script>
@endif
@endpush
