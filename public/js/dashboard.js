/* ============================================================
 * Cooca Dashboard — Analytics Cockpit Renderer
 * Dynamic period filter (AJAX), Chart.js Line & Donut charts,
 * KPI real-time update, table ranking updates, dark/light theme.
 * ============================================================ */

(function () {
    'use strict';

    var A = window.COOCA_DASHBOARD_ANALYTICS;
    var charts = {};
    var currentTrendFilter = 'all'; // 'all', 'omzet', 'profit'

    var PALETTE = [
        '#007AFF', '#34C759', '#FF9500', '#FF3B30', '#AF52DE',
        '#5856D6', '#30B0C7', '#FFCC00', '#0A84FF', '#30D158'
    ];

    function el(sel, root) {
        return (root || document).querySelector(sel);
    }

    function els(sel, root) {
        return Array.prototype.slice.call((root || document).querySelectorAll(sel));
    }

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function plain(v) {
        return Math.round(Number(v) || 0).toLocaleString('id-ID');
    }

    function money(v) {
        return 'Rp ' + plain(v);
    }

    function shortMoney(v) {
        var n = Number(v) || 0;
        var abs = Math.abs(n);
        if (abs >= 1e9) return 'Rp ' + (n / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + 'Mrd';
        if (abs >= 1e6) return 'Rp ' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + 'Jt';
        if (abs >= 1e3) return 'Rp ' + (n / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + 'Rb';
        return money(n);
    }

    function sum(arr) {
        var s = 0;
        for (var i = 0; i < arr.length; i++) s += Number(arr[i]) || 0;
        return s;
    }

    function theme() {
        if (isDark()) {
            return {
                grid: 'rgba(255, 255, 255, 0.08)',
                tick: '#8E8E93',
                tooltipBg: '#2C2C2E',
                tooltipText: '#FFFFFF',
                omzetLine: '#30D158',
                profitLine: '#0A84FF',
            };
        }
        return {
            grid: 'rgba(60, 60, 67, 0.08)',
            tick: 'rgba(60, 60, 67, 0.6)',
            tooltipBg: '#FFFFFF',
            tooltipText: '#000000',
            omzetLine: '#34C759',
            profitLine: '#007AFF',
        };
    }

    /* ---------- State helpers ---------- */

    function box(id) {
        return el('[data-chart="' + id + '"]');
    }

    function setState(id, mode) {
        var b = box(id);
        if (!b) return;
        var sk = el('[data-skeleton]', b);
        var cv = el('canvas.chart-canvas', b);
        var em = el('.chart-empty', b);
        var er = el('.chart-error', b);
        if (sk) sk.style.display = mode === 'loading' ? 'block' : 'none';
        if (cv) cv.style.display = mode === 'ready' ? 'block' : 'none';
        if (em) em.hidden = mode !== 'empty';
        if (er) er.hidden = mode !== 'error';
    }

    function failAll() {
        els('[data-chart]').forEach(function (b) {
            var er = el('.chart-error', b);
            if (er) er.hidden = false;
            var sk = el('[data-skeleton]', b);
            if (sk) sk.style.display = 'none';
            var cv = el('canvas.chart-canvas', b);
            if (cv) cv.style.display = 'none';
            var em = el('.chart-empty', b);
            if (em) em.hidden = true;
        });
    }

    function destroy(id) {
        if (charts[id]) {
            try { charts[id].destroy(); } catch (e) { /* noop */ }
            delete charts[id];
        }
    }

    /* ---------- Chart.js scale & tooltip helpers ---------- */

    function baseScales(t) {
        return {
            x: {
                grid: { display: false },
                border: { display: false },
                ticks: {
                    color: t.tick,
                    maxRotation: 0,
                    autoSkip: true,
                    maxTicksLimit: 14,
                    font: { size: 10, weight: '500', family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif' },
                },
            },
            y: {
                beginAtZero: true,
                grid: { color: t.grid },
                border: { display: false },
                ticks: {
                    color: t.tick,
                    precision: 0,
                    font: { size: 10, weight: '500', family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif' },
                    callback: function (value) {
                        var abs = Math.abs(value);
                        if (abs >= 1e9) return (value / 1e9).toFixed(1) + 'M';
                        if (abs >= 1e6) return (value / 1e6).toFixed(1) + 'Jt';
                        if (abs >= 1e3) return (value / 1e3).toFixed(0) + 'Rb';
                        return value;
                    },
                },
            },
        };
    }

    function tooltip(t, asMoney) {
        return {
            enabled: true,
            backgroundColor: t.tooltipBg,
            titleColor: t.tooltipText,
            bodyColor: t.tooltipText,
            borderColor: t.grid,
            borderWidth: 1,
            displayColors: true,
            boxPadding: 4,
            padding: 8,
            titleFont: { size: 11, weight: '600', family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif' },
            bodyFont: { size: 11, weight: '500', family: '-apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", sans-serif' },
            callbacks: {
                label: function (ctx) {
                    var idx = ctx.dataIndex || 0;
                    var raw = ctx.dataset && ctx.dataset.data ? ctx.dataset.data[idx] : 0;
                    var val = asMoney !== false ? money(raw) : plain(raw);
                    var seriesName = ctx.dataset.label ? ctx.dataset.label + ': ' : '';
                    return ' ' + seriesName + val;
                },
            },
        };
    }

    function legendBottom(t) {
        return {
            position: 'bottom',
            labels: {
                boxWidth: 8,
                boxHeight: 8,
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 10,
                font: { size: 10, weight: '700' },
                color: (t && t.tick) ? t.tick : '#64748b',
            },
        };
    }

    function setDonutCenter(id, caption, value) {
        var wrap = box(id);
        if (!wrap) return;
        var c = wrap.querySelector('[data-donut-center]');
        if (!c) return;
        var totalEl = wrap.querySelector('[data-donut-total]');
        var capEl = c.querySelector('.donut-caption');
        if (totalEl) totalEl.textContent = value;
        if (capEl) capEl.textContent = caption;
        c.hidden = false;
    }

    /* ---------- Renderers ---------- */

    function renderTrend() {
        if (typeof Chart === 'undefined') { setState('trend', 'error'); return; }
        var t = (A && A.trend) || [];
        var labels = [], omzet = [], profit = [];
        for (var i = 0; i < t.length; i++) {
            labels.push(t[i].label);
            omzet.push(Number(t[i].omzet) || 0);
            profit.push(Number(t[i].profit) || 0);
        }
        var hasData = sum(omzet) > 0 || sum(profit) > 0;
        if (!hasData) { setState('trend', 'empty'); return; }

        var palette = theme();
        destroy('trend');

        var canvas = el('canvas.chart-canvas', box('trend'));
        if (!canvas) return;
        var ctx = canvas.getContext('2d');

        // Linear gradient fills (Apple HIG Soft Translucent Sheen)
        var omzetGrad = ctx.createLinearGradient(0, 0, 0, 240);
        omzetGrad.addColorStop(0, isDark() ? 'rgba(48, 209, 88, 0.22)' : 'rgba(52, 199, 89, 0.18)');
        omzetGrad.addColorStop(1, 'rgba(52, 199, 89, 0.0)');

        var profitGrad = ctx.createLinearGradient(0, 0, 0, 240);
        profitGrad.addColorStop(0, isDark() ? 'rgba(10, 132, 255, 0.22)' : 'rgba(0, 122, 255, 0.16)');
        profitGrad.addColorStop(1, 'rgba(0, 122, 255, 0.0)');

        var datasets = [];

        if (currentTrendFilter === 'all' || currentTrendFilter === 'omzet') {
            datasets.push({
                label: 'Omzet Penjualan',
                data: omzet,
                type: 'line',
                borderColor: palette.omzetLine,
                backgroundColor: omzetGrad,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: labels.length <= 24 ? 3.5 : 1.5,
                pointHoverRadius: 6,
                pointBackgroundColor: palette.omzetLine,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
            });
        }

        if (currentTrendFilter === 'all' || currentTrendFilter === 'profit') {
            datasets.push({
                label: 'Laba Kotor (Gross Profit)',
                data: profit,
                type: 'line',
                borderColor: palette.profitLine,
                backgroundColor: profitGrad,
                borderWidth: 2.5,
                fill: true,
                tension: 0.35,
                pointRadius: labels.length <= 24 ? 3.5 : 1.5,
                pointHoverRadius: 6,
                pointBackgroundColor: palette.profitLine,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 1.5,
            });
        }

        charts.trend = new Chart(canvas, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                layout: { padding: { top: 8, right: 10, bottom: 4, left: 2 } },
                scales: baseScales(palette),
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 10,
                            font: { size: 11, weight: '700' },
                            color: palette.tick,
                        },
                    },
                    tooltip: tooltip(palette, true),
                },
            },
        });
        setState('trend', 'ready');
    }

    function renderDonut(id, source, useMoneyCenter) {
        if (typeof Chart === 'undefined') { setState(id, 'error'); return; }
        var rows = (A && A[source]) || [];
        var labels = [], data = [];
        for (var i = 0; i < rows.length; i++) {
            var val = Number(rows[i].value) || 0;
            if (val > 0) { labels.push(rows[i].label); data.push(val); }
        }
        if (data.length === 0) { setState(id, 'empty'); return; }

        var palette = theme();
        destroy(id);
        var colors = [];
        for (var c = 0; c < data.length; c++) colors.push(PALETTE[c % PALETTE.length]);

        var canvas = el('canvas.chart-canvas', box(id));
        if (!canvas) return;

        charts[id] = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: isDark() ? 2 : 1.5,
                    borderColor: isDark() ? '#1C1C1E' : '#ffffff',
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                layout: { padding: { top: 4, bottom: 10 } },
                plugins: {
                    legend: legendBottom(palette),
                    tooltip: tooltip(palette, useMoneyCenter),
                },
            },
        });

        var total = sum(data);
        setDonutCenter(id, useMoneyCenter ? 'Total' : 'Total Transaksi', useMoneyCenter ? shortMoney(total) : plain(total));
        setState(id, 'ready');
    }

    /* ---------- Loading overlay ---------- */

    function showLoading(show) {
        var root = document.getElementById('analytics-cockpit');
        if (!root) return;
        var ov = root.querySelector('[data-loading-overlay]');
        if (ov) ov.hidden = !show;
        if (show) root.classList.add('dash-is-loading');
        else root.classList.remove('dash-is-loading');

        // Toggle skeletons on KPI cards
        els('[data-kpi-value]').forEach(function (node) {
            if (show) {
                node.classList.add('opacity-40');
            } else {
                node.classList.remove('opacity-40');
            }
        });
    }

    /* ---------- Dynamic DOM update from AJAX ---------- */

    function updateDashboardDOM(analytics) {
        if (!analytics) return;

        // 1. Period label & range badge
        var periodBadge = el('[data-analytics-period-badge]');
        if (periodBadge) {
            periodBadge.textContent = (analytics.period_label || 'Periode') + ' · ' +
                (analytics.range ? analytics.range.from + ' → ' + analytics.range.to : '');
        }

        // 2. Granularity subtitle for Trend Chart
        var granBadge = el('[data-trend-granularity]');
        if (granBadge) {
            granBadge.textContent = analytics.granularity === 'hour'
                ? 'Jam Operasional'
                : (analytics.granularity === 'day' ? 'Harian' : 'Bulanan');
        }

        // 3. KPI values
        var kpis = analytics.kpis || {};
        var kpiOmzet = el('[data-kpi="omzet"]');
        if (kpiOmzet) kpiOmzet.textContent = money(kpis.omzet);

        var kpiProfit = el('[data-kpi="profit"]');
        if (kpiProfit) kpiProfit.textContent = money(kpis.profit);

        var kpiMargin = el('[data-kpi="margin_pct"]');
        if (kpiMargin) kpiMargin.textContent = kpis.margin_pct !== null && kpis.margin_pct !== undefined ? kpis.margin_pct + '%' : '—';

        var kpiNet = el('[data-kpi="net"]');
        if (kpiNet) {
            kpiNet.textContent = money(kpis.net);
            if (Number(kpis.net) >= 0) {
                kpiNet.classList.remove('text-[#FF3B30]', 'dark:text-[#FF453A]', 'text-rose-600', 'dark:text-rose-400');
                kpiNet.classList.add('text-[#34C759]', 'dark:text-[#30D158]');
            } else {
                kpiNet.classList.remove('text-[#34C759]', 'dark:text-[#30D158]', 'text-teal-600', 'dark:text-teal-400');
                kpiNet.classList.add('text-[#FF3B30]', 'dark:text-[#FF453A]');
            }
        }

        var kpiTx = el('[data-kpi="transactions"]');
        if (kpiTx) kpiTx.textContent = plain(kpis.transactions);

        var kpiAvg = el('[data-kpi="avg_ticket"]');
        if (kpiAvg) kpiAvg.textContent = money(kpis.avg_ticket);

        var kpiHpp = el('[data-kpi="hpp"]');
        if (kpiHpp) kpiHpp.textContent = money(kpis.hpp);

        var kpiExpenses = el('[data-kpi="expenses"]');
        if (kpiExpenses) kpiExpenses.textContent = money(kpis.expenses);

        var kpiLowStock = el('[data-kpi="low_stock"]');
        if (kpiLowStock) kpiLowStock.textContent = plain(kpis.low_stock);

        var kpiRisk = el('[data-kpi="at_risk_value"]');
        if (kpiRisk) kpiRisk.textContent = money(analytics.at_risk_value || 0);

        var kpiSold = el('[data-kpi="items_sold"]');
        if (kpiSold) kpiSold.textContent = plain(kpis.items_sold || 0);

        // 4. Update Top 10 Products table
        updateProductsTable(analytics.top_products || []);

        // 5. Update Top 15 Materials table
        updateMaterialsTable(analytics.top_materials || []);

        // 6. Update Top 10 Low Stock table
        updateLowStockTable(analytics.low_stock || []);

        // 7. Update Insights
        updateInsightsList(analytics.insights || []);

        // Re-initialize Lucide Icons if available
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            try { window.lucide.createIcons(); } catch (e) { /* noop */ }
        }
    }

    function updateProductsTable(products) {
        var container = el('[data-panel-body="products"]');
        var emptyWrap = el('[data-panel-empty="products"]');
        var countBadge = el('[data-panel-count="products"]');
        if (countBadge) countBadge.textContent = products.length + ' produk';

        if (!container) return;
        if (products.length === 0) {
            container.innerHTML = '';
            if (emptyWrap) emptyWrap.hidden = false;
            return;
        }
        if (emptyWrap) emptyWrap.hidden = true;

        var html = '<table class="dash-tbl w-full text-left text-[13px]">' +
            '<thead><tr class="border-b border-black/5 dark:border-white/10"><th class="w-8">#</th><th>Produk Terlaris</th><th class="text-right">Terjual</th><th class="text-right">Omzet</th><th class="w-16">Porsi</th></tr></thead>' +
            '<tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] tabular-nums">';

        for (var i = 0; i < products.length; i++) {
            var p = products[i];
            var codeHtml = p.code ? '<span class="dash-code">' + escapeHtml(p.code) + '</span>' : '';
            html += '<tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">' +
                '<td class="dash-rank">' + (i + 1) + '</td>' +
                '<td class="font-medium text-black dark:text-white truncate max-w-[150px]" title="' + escapeHtml(p.name) + '">' +
                    escapeHtml(p.name) + codeHtml +
                '</td>' +
                '<td class="text-right text-black/60 dark:text-white/60 font-semibold">' + plain(p.qty) + '</td>' +
                '<td class="text-right text-[#34C759] dark:text-[#30D158] font-semibold">' + money(p.revenue) + '</td>' +
                '<td><div class="pbar" title="' + p.pct + '% dari penjualan tertinggi"><i style="width: ' + p.pct + '%"></i></div></td>' +
                '</tr>';
        }
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function updateMaterialsTable(materials) {
        var container = el('[data-panel-body="materials"]');
        var emptyWrap = el('[data-panel-empty="materials"]');
        var countBadge = el('[data-panel-count="materials"]');
        if (countBadge) countBadge.textContent = materials.length + ' bahan';

        if (!container) return;
        if (materials.length === 0) {
            container.innerHTML = '';
            if (emptyWrap) emptyWrap.hidden = false;
            return;
        }
        if (emptyWrap) emptyWrap.hidden = true;

        var html = '<table class="dash-tbl w-full text-left text-[13px]">' +
            '<thead><tr class="border-b border-black/5 dark:border-white/10"><th class="w-8">#</th><th>Bahan Baku</th><th class="text-right">Jumlah Pakai</th><th class="w-16">Porsi</th></tr></thead>' +
            '<tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] tabular-nums">';

        for (var i = 0; i < materials.length; i++) {
            var m = materials[i];
            html += '<tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">' +
                '<td class="dash-rank">' + (i + 1) + '</td>' +
                '<td class="font-medium text-black dark:text-white truncate max-w-[160px]" title="' + escapeHtml(m.name) + '">' +
                    escapeHtml(m.name) +
                '</td>' +
                '<td class="text-right text-black/70 dark:text-white/70 font-semibold">' +
                    plain(m.qty) + ' <span class="dash-unit">' + escapeHtml(m.unit) + '</span>' +
                '</td>' +
                '<td><div class="pbar" title="' + m.pct + '% dari pemakaian tertinggi"><i style="width: ' + m.pct + '%"></i></div></td>' +
                '</tr>';
        }
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function updateLowStockTable(items) {
        var container = el('[data-panel-body="low_stock"]');
        var emptyWrap = el('[data-panel-empty="low_stock"]');
        var countBadge = el('[data-panel-count="low_stock"]');
        if (countBadge) countBadge.textContent = items.length + ' item';

        if (!container) return;
        if (items.length === 0) {
            container.innerHTML = '';
            if (emptyWrap) emptyWrap.hidden = false;
            return;
        }
        if (emptyWrap) emptyWrap.hidden = true;

        var html = '<table class="dash-tbl w-full text-left text-[13px]">' +
            '<thead><tr class="border-b border-black/5 dark:border-white/10"><th class="w-8">#</th><th>Item</th><th class="text-right">Sisa</th><th>Status</th></tr></thead>' +
            '<tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06] tabular-nums">';

        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            var statusBadge = '';
            if (it.critical) {
                statusBadge = '<span class="badge badge-critical inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30] shrink-0"></span><span>Kritis</span></span>';
            } else if (it.is_low) {
                statusBadge = '<span class="badge badge-low inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#FF9500] shrink-0"></span><span>Menipis</span></span>';
            } else {
                statusBadge = '<span class="badge badge-safe inline-flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#34C759] shrink-0"></span><span>Aman</span></span>';
            }

            var sisaColor = it.critical ? 'text-[#FF3B30] dark:text-[#FF453A] font-semibold' : (it.is_low ? 'text-[#FF9500] dark:text-[#FF9F0A] font-semibold' : 'text-black/70 dark:text-white/70 font-medium');

            html += '<tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">' +
                '<td class="dash-rank">' + (i + 1) + '</td>' +
                '<td class="font-medium text-black dark:text-white truncate max-w-[150px]" title="' + escapeHtml(it.name) + ' (' + escapeHtml(it.kind) + ')">' +
                    escapeHtml(it.name) +
                    '<span class="text-[11px] text-black/40 dark:text-white/40 ml-1">(' + escapeHtml(it.kind) + ')</span>' +
                '</td>' +
                '<td class="text-right ' + sisaColor + '">' +
                    plain(it.remaining) + ' <span class="dash-unit">' + escapeHtml(it.unit) + '</span>' +
                '</td>' +
                '<td>' + statusBadge + '</td>' +
                '</tr>';
        }
        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function updateInsightsList(insights) {
        var container = el('[data-insights-list]');
        var emptyWrap = el('[data-insights-empty]');
        if (!container) return;

        if (insights.length === 0) {
            container.innerHTML = '';
            if (emptyWrap) emptyWrap.hidden = false;
            return;
        }
        if (emptyWrap) emptyWrap.hidden = true;

        var html = '';
        for (var i = 0; i < insights.length; i++) {
            var ins = insights[i];
            var iconName = ins.icon || 'sparkles';
            html += '<div class="insight-chip">' +
                '<i data-lucide="' + iconName + '" class="w-4 h-4 text-[#AF52DE] dark:text-[#BF5AF2] shrink-0"></i>' +
                '<span class="insight-label">' + escapeHtml(ins.label) + '</span>' +
                '<span class="insight-value">' + escapeHtml(ins.value) + '</span>' +
                '</div>';
        }
        container.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /* ---------- AJAX Fetch Analytics ---------- */

    function fetchAnalytics(period, from, to) {
        showLoading(true);

        var url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('period', period);
        if (period === 'custom') {
            if (from) url.searchParams.set('from', from);
            if (to) url.searchParams.set('to', to);
        }
        url.searchParams.set('format', 'json');

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(function (data) {
            if (data && data.analytics) {
                A = data.analytics;
                window.COOCA_DASHBOARD_ANALYTICS = data.analytics;
                updateDashboardDOM(data.analytics);
                renderAll();

                // Update URL in browser history without page reload
                var stateUrl = new URL(window.location.origin + window.location.pathname);
                stateUrl.searchParams.set('period', period);
                if (period === 'custom') {
                    if (from) stateUrl.searchParams.set('from', from);
                    if (to) stateUrl.searchParams.set('to', to);
                }
                window.history.pushState({ period: period, from: from, to: to }, '', stateUrl.toString());
            }
            showLoading(false);
        })
        .catch(function (err) {
            console.error('Cooca Analytics Fetch Error:', err);
            failAll();
            showLoading(false);
        });
    }

    // Expose fetcher globally for Alpine.js / blade buttons
    window.coocaFetchAnalytics = fetchAnalytics;

    /* ---------- Trend Series Toggle (Apple Segmented Control Style) ---------- */

    window.setTrendSeries = function (mode) {
        currentTrendFilter = mode;
        els('[data-trend-btn]').forEach(function (btn) {
            if (btn.getAttribute('data-trend-btn') === mode) {
                btn.classList.add('bg-white', 'dark:bg-[#3A3A3C]', 'text-black', 'dark:text-white', 'shadow-[0_1px_2px_rgba(0,0,0,0.08)]');
                btn.classList.remove('text-black/55', 'dark:text-white/55');
            } else {
                btn.classList.remove('bg-white', 'dark:bg-[#3A3A3C]', 'text-black', 'dark:text-white', 'shadow-[0_1px_2px_rgba(0,0,0,0.08)]');
                btn.classList.add('text-black/55', 'dark:text-white/55');
            }
        });
        renderTrend();
    };

    /* ---------- Main entry ---------- */

    function renderAll() {
        A = window.COOCA_DASHBOARD_ANALYTICS;
        if (!A || typeof A !== 'object') {
            failAll();
            showLoading(false);
            return;
        }
        try {
            renderTrend();
            renderDonut('channel', 'channel_split', true);
            renderDonut('orders', 'order_type_split', false);
            renderDonut('categories', 'category_split', true);
            renderDonut('payment', 'payment_split', true);
            showLoading(false);
        } catch (e) {
            console.error('Chart render error:', e);
            failAll();
            showLoading(false);
        }
    }

    window.addEventListener('cooca:analytics-loading', function () {
        showLoading(true);
    });

    if (typeof Chart === 'undefined') {
        var applyChartError = function () { failAll(); };
        if (window.addEventListener) window.addEventListener('load', applyChartError);
        else applyChartError();
    }

    /* Theme-aware re-render on dark/light toggle */
    var lastDark = isDark();
    if (typeof MutationObserver !== 'undefined') {
        (function () {
            var root = document.documentElement;
            var observer = new MutationObserver(function () {
                if (isDark() !== lastDark) {
                    lastDark = isDark();
                    renderAll();
                }
            });
            observer.observe(root, { attributes: true, attributeFilter: ['class'] });
        })();
    }

    // Initial render on load
    document.addEventListener('DOMContentLoaded', function () {
        renderAll();
    });

    // If script loaded after DOMContentLoaded
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        renderAll();
    }
})();