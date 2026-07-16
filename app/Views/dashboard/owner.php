<?php

/**
 * @var string                       $nama
 * @var list<array<string, mixed>>   $cards
 * @var list<array<string, mixed>>   $recentOrders
 * @var list<array<string, mixed>>   $chartData
 * @var array<string, array{label: string}> $chartConfig
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Beranda<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Beranda Pemilik<?= $this->endSection() ?>

<?= $this->section('banner_title') ?>Selamat Datang, Pemilik 👋<?= $this->endSection() ?>
<?= $this->section('banner_subtitle') ?>Ringkasan performa bisnis Z'Plack · <?= esc(date('d F Y')) ?><?= $this->endSection() ?>

<?= $this->section('styles') ?>
<?= view('partials/admin_data_table_styles') ?>
<style>
    .owner-chart-card {
        background: #fff;
        border-color: #f1f5f9;
    }

    .owner-chart-title {
        color: #051747;
    }

    html[data-theme="dark"] .owner-chart-card {
        background: #09090B;
        border-color: #27272A;
    }

    html[data-theme="dark"] .owner-chart-title {
        color: #FAFAFA;
    }

    .owner-chart-container {
        --color-primary: #8B5CF6;
        --color-secondary: #38BDF8;
        --color-tertiary: #34D399;
        --color-cetak_digital: var(--color-primary);
        --color-cetak_offset: var(--color-secondary);
        --color-media_promosi: var(--color-tertiary);
        --color-border: #E2E8F0;
        --color-muted-foreground: #64748B;
        --color-foreground: #051747;
        --owner-chart-grid: rgba(15, 23, 42, 0.08);
        --owner-chart-tick: #64748B;
        --owner-chart-tooltip-bg: #fff;
        --owner-chart-tooltip-shadow: 0 4px 24px rgba(15, 23, 42, 0.12);
        min-height: 200px;
        width: 100%;
    }

    html[data-theme="dark"] .owner-chart-container {
        --color-border: #27272A;
        --color-muted-foreground: #A1A1AA;
        --color-foreground: #FAFAFA;
        --owner-chart-grid: rgba(255, 255, 255, 0.06);
        --owner-chart-tick: #71717A;
        --owner-chart-tooltip-bg: #18181B;
        --owner-chart-tooltip-shadow: 0 4px 24px rgba(0, 0, 0, 0.35);
    }

    .owner-chart-tooltip {
        position: absolute;
        z-index: 50;
        pointer-events: none;
        min-width: 8rem;
        border-radius: 8px;
        border: 1px solid var(--color-border);
        background: var(--owner-chart-tooltip-bg);
        padding: 10px 12px;
        color: var(--color-foreground);
        font-size: 12px;
        box-shadow: var(--owner-chart-tooltip-shadow);
        opacity: 0;
        transition: opacity 0.15s ease;
    }

    .owner-chart-tooltip.is-visible {
        opacity: 1;
    }

    .owner-chart-tooltip-label {
        font-weight: 500;
        margin-bottom: 8px;
    }

    .owner-chart-tooltip-items {
        display: grid;
        gap: 8px;
    }

    .owner-chart-tooltip-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .owner-chart-tooltip-indicator {
        width: 0;
        height: 10px;
        border-left: 1.5px dashed;
        background: transparent;
        flex-shrink: 0;
    }

    .owner-chart-tooltip-name {
        flex: 1;
        color: var(--color-muted-foreground);
    }

    .owner-chart-tooltip-value {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?= view('dashboard/_partials/summary_cards', ['cards' => $cards]) ?>

<div class="owner-chart-card rounded-xl overflow-hidden shadow-sm border mb-6">
    <div class="px-6 pt-6 pb-2">
        <h3 class="text-sm font-medium owner-chart-title">Pesanan 7 Hari Terakhir</h3>
    </div>
    <div class="px-2 pb-6 sm:px-6 relative owner-chart-container" id="ownerChartContainer">
        <canvas id="ordersChart" height="120"></canvas>
        <div id="ownerChartTooltip" class="owner-chart-tooltip" role="tooltip" aria-hidden="true"></div>
    </div>
</div>

<?= view('dashboard/_partials/recent_orders_table', [
    'orders'            => $recentOrders,
    'variant'           => 'owner',
    'sectionTitle'      => 'Pesanan Terbaru',
    'searchId'          => 'ownerDashboardSearch',
    'tbodyId'           => 'ownerDashboardBody',
    'searchPlaceholder' => 'Cari kode, pelanggan, produk...',
    'entriesId'         => 'ownerEntriesSelect',
    'entriesInfoId'     => 'ownerEntriesInfo',
    'paginationId'      => 'ownerTablePagination',
    'prevPageId'        => 'ownerPrevPageBtn',
    'nextPageId'        => 'ownerNextPageBtn',
    'pageInfoId'        => 'ownerPageInfo',
]) ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.adminDataTableConfig = {
        searchId: 'ownerDashboardSearch',
        tbodyId: 'ownerDashboardBody',
        emptyFilterRowId: 'emptyFilterRow',
        entriesId: 'ownerEntriesSelect',
        entriesInfoId: 'ownerEntriesInfo',
        paginationId: 'ownerTablePagination',
        prevPageId: 'ownerPrevPageBtn',
        nextPageId: 'ownerNextPageBtn',
        pageInfoId: 'ownerPageInfo',
        enableSort: true,
    };
</script>
<?= view('partials/admin_data_table_scripts') ?>
<script>
(function () {
    const chartData = <?= json_encode($chartData ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const chartConfig = <?= json_encode($chartConfig ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const seriesKeys = ['cetak_digital', 'cetak_offset', 'media_promosi'];
    const colorVars = {
        cetak_digital: '--color-cetak_digital',
        cetak_offset: '--color-cetak_offset',
        media_promosi: '--color-media_promosi',
    };

    const ordersChartEl = document.getElementById('ordersChart');
    const chartContainer = document.getElementById('ownerChartContainer');
    const tooltipEl = document.getElementById('ownerChartTooltip');

    if (!ordersChartEl || !chartContainer || chartData.length === 0) {
        return;
    }

    function cssVar(name) {
        return getComputedStyle(chartContainer).getPropertyValue(name).trim();
    }

    function chartThemeColors() {
        return {
            grid: cssVar('--owner-chart-grid'),
            tick: cssVar('--owner-chart-tick'),
        };
    }

    function datasetColors() {
        return seriesKeys.map(function (key) {
            return cssVar(colorVars[key]);
        });
    }

    function shortDayLabel(day) {
        return String(day || '').slice(0, 3);
    }

    function buildTooltipHtml(title, items) {
        const rows = items.map(function (item) {
            return '<div class="owner-chart-tooltip-row">'
                + '<div class="owner-chart-tooltip-indicator" style="border-color:' + item.color + '"></div>'
                + '<span class="owner-chart-tooltip-name">' + item.label + '</span>'
                + '<span class="owner-chart-tooltip-value">' + item.value + '</span>'
                + '</div>';
        }).join('');

        return '<div class="owner-chart-tooltip-label">' + title + '</div>'
            + '<div class="owner-chart-tooltip-items">' + rows + '</div>';
    }

    function externalTooltipHandler(context) {
        const tooltip = context.tooltip;
        if (!tooltipEl) {
            return;
        }

        if (tooltip.opacity === 0) {
            tooltipEl.classList.remove('is-visible');
            tooltipEl.setAttribute('aria-hidden', 'true');
            return;
        }

        const dataIndex = tooltip.dataPoints[0].dataIndex;
        const row = chartData[dataIndex] || {};
        const items = seriesKeys.map(function (key) {
            return {
                label: (chartConfig[key] && chartConfig[key].label) ? chartConfig[key].label : key,
                value: row[key] || 0,
                color: cssVar(colorVars[key]),
            };
        });

        tooltipEl.innerHTML = buildTooltipHtml(row.dayFull || row.day || '', items);
        tooltipEl.classList.add('is-visible');
        tooltipEl.setAttribute('aria-hidden', 'false');

        const canvasRect = ordersChartEl.getBoundingClientRect();
        const containerRect = chartContainer.getBoundingClientRect();
        const left = canvasRect.left - containerRect.left + tooltip.caretX + 12;
        const top = canvasRect.top - containerRect.top + tooltip.caretY - 12;

        tooltipEl.style.left = left + 'px';
        tooltipEl.style.top = top + 'px';
    }

    const labels = chartData.map(function (row) {
        return shortDayLabel(row.day);
    });

    const themeColors = chartThemeColors();
    const barColors = datasetColors();

    const datasets = seriesKeys.map(function (key, index) {
        const color = barColors[index] || cssVar(colorVars[key]);
        return {
            label: (chartConfig[key] && chartConfig[key].label) ? chartConfig[key].label : key,
            data: chartData.map(function (row) {
                return row[key] || 0;
            }),
            backgroundColor: color,
            hoverBackgroundColor: color,
            borderRadius: 4,
            borderSkipped: false,
            maxBarThickness: 30,
        };
    });

    const chart = new Chart(ordersChartEl.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            datasets: {
                bar: {
                    categoryPercentage: 0.55,
                    barPercentage: 0.9,
                },
            },
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: false,
                    external: externalTooltipHandler,
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: {
                        display: false,
                    },
                    grid: {
                        color: themeColors.grid,
                        drawTicks: false,
                    },
                    ticks: {
                        display: false,
                        stepSize: 1,
                    },
                },
                x: {
                    border: {
                        display: false,
                    },
                    grid: {
                        display: false,
                    },
                    ticks: {
                        color: themeColors.tick,
                        padding: 10,
                        font: {
                            size: 12,
                        },
                    },
                },
            },
        },
    });

    function applyOwnerChartTheme() {
        const nextTheme = chartThemeColors();
        const nextColors = datasetColors();

        chart.options.scales.y.grid.color = nextTheme.grid;
        chart.options.scales.x.ticks.color = nextTheme.tick;

        chart.data.datasets.forEach(function (dataset, index) {
            const color = nextColors[index] || dataset.backgroundColor;
            dataset.backgroundColor = color;
            dataset.hoverBackgroundColor = color;
        });

        chart.update('none');
    }

    const themeObserver = new MutationObserver(function () {
        applyOwnerChartTheme();
    });
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme'],
    });
})();
</script>
<?= $this->endSection() ?>
