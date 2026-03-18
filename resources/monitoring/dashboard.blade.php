<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Monitor</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js" defer></script>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            background: #f8f8f6;
            color: #1a1a18;
            font-size: 14px;
        }

        .mono {
            font-family: ui-monospace, monospace;
        }

        header {
            background: #fff;
            border-bottom: 1px solid #e5e5e2;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 30px;
            height: 30px;
            background: #185FA5;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-icon svg {
            width: 14px;
            height: 14px;
        }

        .logo-text {
            font-size: 14px;
            font-weight: 500;
        }

        .logo-sub {
            font-size: 11px;
            color: #888;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 500;
            padding: 3px 9px;
            border-radius: 20px;
        }

        .badge-ok {
            background: #eaf3de;
            color: #27500a;
        }

        .badge-err {
            background: #fcebeb;
            color: #791f1f;
        }

        .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .dot-ok {
            background: #3b6d11;
        }

        .dot-err {
            background: #a32d2d;
        }

        main {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1.5rem 2rem 3rem;
        }

        .metrics-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 1.25rem;
        }

        .metric-card {
            background: #f1f0eb;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .metric-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }

        .metric-value {
            font-size: 22px;
            font-weight: 500;
            font-family: ui-monospace, monospace;
        }

        .metric-sub {
            font-size: 11px;
            color: #888;
            margin-top: 3px;
        }

        .charts-row {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 12px;
            margin-bottom: 1.25rem;
        }

        .card {
            background: #fff;
            border: 1px solid #e5e5e2;
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }

        .card-title {
            font-size: 11px;
            font-weight: 500;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .servers-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 10px;
            margin-bottom: 1.25rem;
        }

        .server-card {
            background: #fff;
            border: 1px solid #e5e5e2;
            border-radius: 10px;
            padding: 14px 16px;
            display: grid;
            grid-template-columns: 10px 1fr auto;
            gap: 12px;
            align-items: start;
        }

        .srv-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .srv-name {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .srv-region {
            font-size: 11px;
            color: #888;
            font-family: ui-monospace, monospace;
        }

        .prog-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .prog-label {
            font-size: 10px;
            color: #aaa;
            width: 30px;
            flex-shrink: 0;
        }

        .prog-track {
            flex: 1;
            height: 4px;
            background: #e5e5e2;
            border-radius: 2px;
            overflow: hidden;
        }

        .prog-fill {
            height: 100%;
            border-radius: 2px;
        }

        .srv-count {
            font-size: 18px;
            font-weight: 500;
            font-family: ui-monospace, monospace;
        }

        .srv-count-label {
            font-size: 10px;
            color: #888;
        }

        .log-section {
            background: #fff;
            border: 1px solid #e5e5e2;
            border-radius: 10px;
            overflow: hidden;
        }

        .log-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e5e2;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filter-row {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .filter-btn {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 20px;
            border: 1px solid #d0d0cc;
            background: transparent;
            color: #666;
            cursor: pointer;
            font-family: ui-monospace, monospace;
            transition: all 0.12s;
        }

        .filter-btn:hover {
            background: #f1f0eb;
        }

        .filter-btn.active {
            background: #185FA5;
            border-color: #185FA5;
            color: #fff;
        }

        .log-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            table-layout: fixed;
        }

        .log-table th {
            padding: 8px 14px;
            text-align: left;
            font-size: 10px;
            font-weight: 500;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #fafaf8;
            border-bottom: 1px solid #e5e5e2;
        }

        .log-table td {
            padding: 9px 14px;
            border-bottom: 1px solid #f0f0ec;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .log-table tr:last-child td {
            border-bottom: none;
        }

        .log-table tr:hover td {
            background: #fafaf8;
        }

        .s2xx {
            color: #27500a;
            font-family: ui-monospace, monospace;
            font-weight: 500;
        }

        .s3xx {
            color: #0c447c;
            font-family: ui-monospace, monospace;
            font-weight: 500;
        }

        .s4xx {
            color: #633806;
            font-family: ui-monospace, monospace;
            font-weight: 500;
        }

        .s5xx {
            color: #791f1f;
            font-family: ui-monospace, monospace;
            font-weight: 500;
        }

        .rt-fast {
            color: #27500a;
            font-family: ui-monospace, monospace;
        }

        .rt-mid {
            color: #633806;
            font-family: ui-monospace, monospace;
        }

        .rt-slow {
            color: #791f1f;
            font-family: ui-monospace, monospace;
        }

        .url-cell {
            font-family: ui-monospace, monospace;
            color: #666;
            font-size: 11px;
        }

        .ip-cell {
            font-family: ui-monospace, monospace;
            color: #aaa;
            font-size: 11px;
        }

        .srv-pill {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 500;
            font-family: ui-monospace, monospace;
        }

        .pill-0 {
            background: #e6f1fb;
            color: #0c447c;
        }

        .pill-1 {
            background: #eaf3de;
            color: #27500a;
        }

        .pill-2 {
            background: #faeeda;
            color: #633806;
        }

        .pill-3 {
            background: #fbeaf0;
            color: #72243e;
        }

        .pill-4 {
            background: #e1f5ee;
            color: #085041;
        }

        .legend-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 10px;
            font-size: 11px;
            color: #888;
        }

        .leg-sq {
            width: 10px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
            flex-shrink: 0;
        }

        .ts {
            font-size: 11px;
            color: #aaa;
            font-family: ui-monospace, monospace;
        }

        .section-title {
            font-size: 11px;
            font-weight: 500;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0;
        }

        #loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: #aaa;
            font-size: 13px;
        }

        @media(max-width: 700px) {
            .metrics-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <header>
        <div class="logo">
            <div class="logo-icon">
                <svg viewBox="0 0 14 14" fill="none">
                    <rect x="1" y="1" width="5" height="5" rx="1" fill="white" opacity=".9" />
                    <rect x="8" y="1" width="5" height="5" rx="1" fill="white" opacity=".6" />
                    <rect x="1" y="8" width="5" height="5" rx="1" fill="white" opacity=".6" />
                    <rect x="8" y="8" width="5" height="5" rx="1" fill="white" opacity=".9" />
                </svg>
            </div>
            <div>
                <div class="logo-text">Server Monitor</div>
                <div class="logo-sub">{{ config('app.name') }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="badge badge-ok" id="status-badge"><span class="dot dot-ok"></span>Loading…</span>
            <span class="ts" id="ts-display"></span>
            <select id="hours-select"
                style="font-size:11px;padding:4px 8px;border:1px solid #ddd;border-radius:6px;background:#fff;cursor:pointer;">
                <option value="1">Last 1h</option>
                <option value="6">Last 6h</option>
                <option value="24" selected>Last 24h</option>
                <option value="168">Last 7d</option>
            </select>
        </div>
    </header>

    <main>

        <div id="loading">Loading metrics…</div>

        <div id="content" style="display:none;">

            <div class="metrics-row" style="margin-top:0.5rem;">
                <div class="metric-card">
                    <div class="metric-label">Total Requests</div>
                    <div class="metric-value mono" id="m-total">—</div>
                    <div class="metric-sub" id="m-servers">—</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Avg Response</div>
                    <div class="metric-value mono" id="m-avg">—</div>
                    <div class="metric-sub" id="m-p95">—</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Error Rate</div>
                    <div class="metric-value mono" id="m-err-rate">—</div>
                    <div class="metric-sub" id="m-err-count">—</div>
                </div>
                <div class="metric-card">
                    <div class="metric-label">Active Servers</div>
                    <div class="metric-value mono" id="m-active">—</div>
                    <div class="metric-sub">nodes online</div>
                </div>
            </div>

            <div class="servers-row" id="servers-row"></div>

            <div class="charts-row">
                <div class="card">
                    <div class="card-title">Request distribution</div>
                    <div id="donut-legend" class="legend-row"></div>
                    <div style="position:relative;height:180px;"><canvas id="donutChart"></canvas></div>
                </div>
                <div class="card">
                    <div class="card-title">Requests / hour</div>
                    <div id="line-legend" class="legend-row"></div>
                    <div style="position:relative;height:180px;"><canvas id="lineChart"></canvas></div>
                </div>
            </div>

            <div class="log-section">
                <div class="log-header">
                    <span class="section-title">Recent requests</span>
                    <div class="filter-row" id="filter-row">
                        <button class="filter-btn active" data-filter="all">All</button>
                        <button class="filter-btn" data-filter="errors">Errors</button>
                        <button class="filter-btn" data-filter="slow">Slow</button>
                    </div>
                </div>
                <table class="log-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Server</th>
                            <th style="width:65px;">Status</th>
                            <th>URL</th>
                            <th style="width:130px;">Client IP</th>
                            <th style="width:80px;">Resp. Time</th>
                            <th style="width:90px;">Time</th>
                        </tr>
                    </thead>
                    <tbody id="log-body">
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:#aaa;">Loading…</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

    <script>
        const METRICS_URL = '{{ route('monit.metrics') }}';
        const LOGS_URL = '{{ route('monit.logs') }}';

        let donutChart = null;
        let lineChart = null;
        let serverColors = {};
        const PALETTE = ['#3266ad', '#639922', '#BA7517', '#c7517a', '#0F6E56', '#534AB7'];
        let currentFilter = 'all';
        let currentHours = 24;

        document.getElementById('ts-display').textContent = new Date().toLocaleTimeString();

        document.getElementById('hours-select').addEventListener('change', e => {
            currentHours = parseInt(e.target.value);
            loadMetrics();
            loadLogs();
        });

        document.getElementById('filter-row').addEventListener('click', e => {
            const btn = e.target.closest('.filter-btn');
            if (!btn) return;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentFilter = btn.dataset.filter;
            loadLogs();
        });

        function pillClass(server, idx) {
            return `pill-${idx % 5}`;
        }

        function statusClass(s) {
            if (s < 300) return 's2xx';
            if (s < 400) return 's3xx';
            if (s < 500) return 's4xx';
            return 's5xx';
        }

        function rtClass(v) {
            if (v < 100) return 'rt-fast';
            if (v < 300) return 'rt-mid';
            return 'rt-slow';
        }

        function fmt(n) {
            return n >= 1000 ? (n / 1000).toFixed(1) + 'k' : n;
        }

        async function loadMetrics() {
            try {
                const res = await fetch(`${METRICS_URL}?hours=${currentHours}`);
                const data = await res.json();
                const s = data.summary;

                document.getElementById('m-total').textContent = fmt(s.total_requests);
                document.getElementById('m-servers').textContent = `${s.active_servers} server(s) active`;
                document.getElementById('m-avg').textContent = s.avg_response_time + 'ms';
                document.getElementById('m-p95').textContent = `p95: ${s.p95_response_time}ms`;
                document.getElementById('m-err-rate').textContent = s.error_rate + '%';
                document.getElementById('m-err-count').textContent = `${s.error_count} error(s)`;
                document.getElementById('m-active').textContent = s.active_servers;

                const badge = document.getElementById('status-badge');
                if (s.error_rate > 5) {
                    badge.className = 'badge badge-err';
                    badge.innerHTML = '<span class="dot dot-err"></span>Elevated errors';
                } else {
                    badge.className = 'badge badge-ok';
                    badge.innerHTML = '<span class="dot dot-ok"></span>All systems operational';
                }

                const servers = Object.entries(data.per_server);
                servers.forEach(([id], i) => {
                    serverColors[id] = PALETTE[i % PALETTE.length];
                });

                renderServerCards(servers);
                renderDonut(servers, s.total_requests);
                renderLineChart(data.requests_per_hour);

                document.getElementById('loading').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } catch (e) {
                document.getElementById('loading').textContent = 'Failed to load metrics.';
            }
        }

        function renderServerCards(servers) {
            const row = document.getElementById('servers-row');
            row.innerHTML = servers.map(([id, s], i) => {
                const pct = servers.length ? Math.round((s.total / servers.reduce((a, [, x]) => a + x.total, 0)) *
                    100) : 0;
                return `
        <div class="server-card">
            <div class="srv-dot" style="background:${PALETTE[i%PALETTE.length]};"></div>
            <div>
                <div class="srv-name">${id} <span class="srv-pill pill-${i%5}">${id}</span></div>
                <div class="srv-region">avg ${s.avg_rt}ms &nbsp;·&nbsp; ${s.errors} err</div>
                <div class="prog-row"><span class="prog-label">Load</span><div class="prog-track"><div class="prog-fill" style="width:${pct}%;background:${PALETTE[i%PALETTE.length]};"></div></div><span class="ts">${pct}%</span></div>
            </div>
            <div style="text-align:right;">
                <div class="srv-count">${fmt(s.total)}</div>
                <div class="srv-count-label">requests</div>
            </div>
        </div>`;
            }).join('');
        }

        function renderDonut(servers, total) {
            const labels = servers.map(([id]) => id);
            const values = servers.map(([, s]) => s.total);
            const colors = servers.map(([id]) => serverColors[id]);

            document.getElementById('donut-legend').innerHTML = servers.map(([id, s]) => {
                const pct = total > 0 ? Math.round(s.total / total * 100) : 0;
                return `<span style="display:flex;align-items:center;gap:5px;"><span class="leg-sq" style="background:${serverColors[id]};"></span>${id} ${pct}%</span>`;
            }).join('');

            if (donutChart) donutChart.destroy();
            donutChart = new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors,
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.formattedValue}`
                            }
                        }
                    }
                }
            });
        }

        function renderLineChart(perHour) {
            const servers = Object.keys(perHour);
            const allHours = [...new Set(
                Object.values(perHour).flatMap(rows => rows.map(r => r.hour))
            )].sort();

            document.getElementById('line-legend').innerHTML = servers.map(id =>
                `<span style="display:flex;align-items:center;gap:5px;"><span class="leg-sq" style="background:${serverColors[id]};"></span>${id}</span>`
            ).join('');

            const datasets = servers.map(id => ({
                label: id,
                data: allHours.map(h => (perHour[id] || []).find(r => r.hour === h)?.total ?? 0),
                borderColor: serverColors[id],
                backgroundColor: 'transparent',
                borderWidth: 1.5,
                pointRadius: 0,
                tension: 0.4,
            }));

            const gc = '#e5e5e2';
            const tc = '#aaa';

            if (lineChart) lineChart.destroy();
            lineChart = new Chart(document.getElementById('lineChart'), {
                type: 'line',
                data: {
                    labels: allHours.map(h => h.slice(11, 16)),
                    datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: tc,
                                font: {
                                    size: 10
                                },
                                autoSkip: true,
                                maxTicksLimit: 8
                            },
                            grid: {
                                color: gc
                            }
                        },
                        y: {
                            ticks: {
                                color: tc,
                                font: {
                                    size: 10
                                }
                            },
                            grid: {
                                color: gc
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        async function loadLogs() {
            const params = new URLSearchParams();
            if (currentFilter === 'errors') params.set('errors_only', '1');
            if (currentFilter === 'slow') params.set('slow_only', '1');

            try {
                const res = await fetch(`${LOGS_URL}?${params}`);
                const data = await res.json();
                const rows = data.data ?? data;
                const serverIds = Object.keys(serverColors);

                document.getElementById('log-body').innerHTML = rows.length ?
                    rows.map(r => {
                        const idx = serverIds.indexOf(r.server_id);
                        return `<tr>
                    <td><span class="srv-pill pill-${Math.max(0,idx)%5}">${r.server_id}</span></td>
                    <td class="${statusClass(r.status_code)}">${r.status_code}</td>
                    <td class="url-cell">${r.method} ${r.url}</td>
                    <td class="ip-cell">${r.client_ip}</td>
                    <td class="${rtClass(r.response_time)}">${r.response_time}ms</td>
                    <td class="ts">${new Date(r.created_at).toLocaleTimeString()}</td>
                </tr>`;
                    }).join('') :
                    '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#aaa;">No records found.</td></tr>';
            } catch {
                document.getElementById('log-body').innerHTML =
                    '<tr><td colspan="6" style="text-align:center;padding:2rem;color:#aaa;">Failed to load logs.</td></tr>';
            }
        }

        loadMetrics();
        loadLogs();
        setInterval(() => {
            loadMetrics();
            loadLogs();
            document.getElementById('ts-display').textContent = new Date().toLocaleTimeString();
        }, 30000);
    </script>
</body>

</html>
