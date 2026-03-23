<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Monitor</title>
    {{-- FIX BUG-9: removed defer — Chart.js must be available before async loadMetrics() resolves --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
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
            letter-spacing: .04em;
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
            letter-spacing: .05em;
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

        #loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: #aaa;
            font-size: 13px;
        }

        /* ── VIM TERMINAL ────────────────────────────────────────────────── */
        .vim-wrap {
            margin-bottom: 1.25rem;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #1c1c1c;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .18);
        }

        .vim-tabline {
            background: #1d2021;
            display: flex;
            align-items: stretch;
            height: 28px;
            border-bottom: 1px solid #0d0d0d;
            user-select: none;
        }

        .vim-tab {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 0 16px;
            font-family: ui-monospace, monospace;
            font-size: 11px;
            cursor: default;
            border-right: 1px solid #0d0d0d;
        }

        .vim-tab.active {
            background: #282828;
            color: #ebdbb2;
        }

        .vim-tab.inactive {
            background: #1d2021;
            color: #504945;
        }

        .vim-tab-icon {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #b8bb26;
            flex-shrink: 0;
        }

        .vim-tab-icon.modified {
            background: #fabd2f;
        }

        .vim-spacer {
            flex: 1;
            background: #1d2021;
        }

        .vim-buffer {
            background: #282828;
            display: flex;
            flex-direction: column;
            min-height: 280px;
            max-height: 360px;
            overflow: hidden;
        }

        .vim-lines {
            flex: 1;
            overflow-y: auto;
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #504945 #282828;
        }

        .vim-lines::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .vim-lines::-webkit-scrollbar-track {
            background: #282828;
        }

        .vim-lines::-webkit-scrollbar-thumb {
            background: #504945;
            border-radius: 3px;
        }

        .vim-line {
            display: flex;
            align-items: baseline;
            min-height: 19px;
            font-family: ui-monospace, monospace;
            font-size: 12px;
            line-height: 19px;
            white-space: nowrap;
        }

        .vim-line:hover {
            background: #32302f;
        }

        .vim-line.vim-cursor-line {
            background: #32302f;
        }

        /* FIX BUG-6/7: cursor shown via CSS class — zero DOM rebuild on j/k */
        .vim-cursor {
            display: none;
        }

        .vim-cursor-line .vim-cursor {
            display: inline-block;
        }

        .vim-lnum {
            width: 44px;
            min-width: 44px;
            text-align: right;
            padding-right: 10px;
            color: #504945;
            font-family: ui-monospace, monospace;
            font-size: 11px;
            user-select: none;
            flex-shrink: 0;
        }

        .vim-lnum.current {
            color: #928374;
        }

        /* FIX BUG-8: sign column uses CSS classes — no inline style injection */
        .vim-sign {
            width: 14px;
            min-width: 14px;
            flex-shrink: 0;
            font-size: 10px;
            text-align: center;
        }

        .vim-sign-err {
            color: #fb4934;
        }

        .vim-sign-warn {
            color: #fabd2f;
        }

        .vim-sign-slow {
            color: #83a598;
        }

        .vim-content {
            flex: 1;
            padding: 0 8px 0 4px;
            color: #ebdbb2;
        }

        /* FIX BUG-3: CSS min-width alignment — no more collapsing padEnd() spaces */
        .vim-col {
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: baseline;
        }

        .vc-time {
            min-width: 76px;
        }

        .vc-srv {
            min-width: 96px;
        }

        .vc-meth {
            min-width: 64px;
        }

        .vc-url {
            min-width: 300px;
            max-width: 340px;
        }

        .vc-stat {
            min-width: 36px;
        }

        .vc-rt {
            min-width: 64px;
        }

        .vc-ip {
            min-width: 110px;
        }

        .vim-cursor {
            width: 8px;
            height: 13px;
            background: #ebdbb2;
            margin-right: 1px;
            vertical-align: middle;
            animation: vim-blink 1.1s step-end infinite;
        }

        @keyframes vim-blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }
        }

        /* syntax tokens */
        .vt-header {
            color: #504945;
            font-style: italic;
        }

        .vt-time {
            color: #d3869b;
        }

        .vt-method-get {
            color: #83a598;
        }

        .vt-method-post {
            color: #b8bb26;
        }

        .vt-method-put {
            color: #fabd2f;
        }

        .vt-method-delete {
            color: #fb4934;
        }

        .vt-method-patch {
            color: #8ec07c;
        }

        .vt-url {
            color: #ebdbb2;
        }

        .vt-s2xx {
            color: #b8bb26;
            font-weight: 600;
        }

        .vt-s3xx {
            color: #83a598;
            font-weight: 600;
        }

        .vt-s4xx {
            color: #fabd2f;
            font-weight: 600;
        }

        .vt-s5xx {
            color: #fb4934;
            font-weight: 600;
        }

        .vt-rt-fast {
            color: #b8bb26;
        }

        .vt-rt-mid {
            color: #fabd2f;
        }

        .vt-rt-slow {
            color: #fb4934;
        }

        .vt-ip {
            color: #928374;
        }

        .vt-srv {
            color: #fe8019;
        }

        .vt-sep {
            color: #3c3836;
        }

        .vt-comment {
            color: #665c54;
            font-style: italic;
        }

        .vt-tilde {
            color: #504945;
        }

        .vt-match {
            background: #504945;
            color: #ebdbb2;
            border-radius: 2px;
            padding: 0 1px;
        }

        /* statusline */
        .vim-statusline {
            background: #3c3836;
            display: flex;
            align-items: center;
            height: 22px;
            font-family: ui-monospace, monospace;
            font-size: 11px;
            user-select: none;
        }

        .vim-sl-mode {
            padding: 0 10px;
            height: 100%;
            display: flex;
            align-items: center;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: .03em;
        }

        .vim-sl-mode.normal {
            background: #a89984;
            color: #1d2021;
        }

        .vim-sl-mode.insert {
            background: #b8bb26;
            color: #1d2021;
        }

        .vim-sl-mode.command {
            background: #fabd2f;
            color: #1d2021;
        }

        .vim-sl-mode.search {
            background: #83a598;
            color: #1d2021;
        }

        .vim-sl-sep {
            color: #504945;
            padding: 0 4px;
        }

        .vim-sl-file {
            color: #ebdbb2;
            padding: 0 6px 0 2px;
            flex: 1;
        }

        .vim-sl-hint {
            color: #504945;
            padding: 0 8px;
            font-size: 10px;
        }

        .vim-sl-info {
            color: #928374;
            padding: 0 10px;
        }

        .vim-sl-pos {
            color: #a89984;
            padding: 0 10px;
        }

        .vim-sl-pct {
            color: #928374;
            padding: 0 10px 0 0;
        }

        /* command / search bar */
        .vim-cmdbar {
            background: #1d2021;
            height: 22px;
            display: flex;
            align-items: center;
            font-family: ui-monospace, monospace;
            font-size: 12px;
            border-top: 1px solid #0d0d0d;
        }

        .vim-cmd-prompt {
            color: #ebdbb2;
            padding: 0 0 0 8px;
            min-width: 14px;
            flex-shrink: 0;
        }

        .vim-cmd-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #ebdbb2;
            font-family: ui-monospace, monospace;
            font-size: 12px;
            padding: 0 4px;
            caret-color: #ebdbb2;
        }

        .vim-cmd-input::placeholder {
            color: #504945;
        }

        .vim-cmd-echo {
            flex: 1;
            padding: 0 8px;
            color: #ebdbb2;
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .vim-cmd-echo.err {
            color: #fb4934;
        }

        .vim-cmd-echo.info {
            color: #83a598;
        }

        .vim-cmd-echo.ok {
            color: #b8bb26;
        }

        @media(max-width:700px) {
            .metrics-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .charts-row {
                grid-template-columns: 1fr;
            }

            .vc-ip,
            .vc-rt {
                display: none;
            }
        }

        /* ── BASH TERMINAL ───────────────────────────────────────────────── */
        .bash-wrap {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #1c1c1c;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .18);
        }

        .bash-titlebar {
            background: #2c2c2c;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 14px;
            height: 34px;
            user-select: none;
        }

        .bash-btn {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .bash-btn-close {
            background: #ff5f57;
        }

        .bash-btn-min {
            background: #febc2e;
        }

        .bash-btn-max {
            background: #28c840;
        }

        .bash-titlebar-label {
            flex: 1;
            text-align: center;
            font-family: ui-monospace, monospace;
            font-size: 11px;
            color: #808080;
            margin-right: 34px;
            /* visually centre over the three buttons */
        }

        .bash-output {
            background: #1a1a1a;
            min-height: 220px;
            max-height: 420px;
            overflow-y: auto;
            padding: 10px 14px 4px;
            font-family: ui-monospace, monospace;
            font-size: 12px;
            line-height: 18px;
            scrollbar-width: thin;
            scrollbar-color: #444 #1a1a1a;
        }

        .bash-output::-webkit-scrollbar {
            width: 6px;
        }

        .bash-output::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        .bash-output::-webkit-scrollbar-thumb {
            background: #444;
            border-radius: 3px;
        }

        .bash-entry {
            margin-bottom: 4px;
        }

        .bash-prompt-line {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 0;
            color: #ccc;
        }

        .bash-ps1-user {
            color: #50fa7b;
            font-weight: 600;
        }

        .bash-ps1-at {
            color: #6272a4;
        }

        .bash-ps1-host {
            color: #50fa7b;
            font-weight: 600;
        }

        .bash-ps1-colon {
            color: #6272a4;
        }

        .bash-ps1-path {
            color: #8be9fd;
        }

        .bash-ps1-sign {
            color: #ff79c6;
            font-weight: 600;
        }

        .bash-ps1-cmd {
            color: #f8f8f2;
            margin-left: 6px;
        }

        .bash-result {
            white-space: pre-wrap;
            word-break: break-all;
            color: #f8f8f2;
            margin-top: 1px;
        }

        .bash-result.bash-err {
            color: #ff5555;
        }

        .bash-input-row {
            background: #1a1a1a;
            display: flex;
            align-items: center;
            padding: 6px 14px;
            border-top: 1px solid #2a2a2a;
            gap: 0;
        }

        .bash-input-ps1 {
            font-family: ui-monospace, monospace;
            font-size: 12px;
            white-space: nowrap;
            flex-shrink: 0;
            display: flex;
            align-items: baseline;
            gap: 0;
        }

        .bash-input-field {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: #f8f8f2;
            font-family: ui-monospace, monospace;
            font-size: 12px;
            padding: 0 0 0 6px;
            caret-color: #f8f8f2;
        }

        .bash-input-field::placeholder {
            color: #444;
        }

        .bash-spinner {
            display: none;
            width: 10px;
            height: 10px;
            border: 2px solid #444;
            border-top-color: #50fa7b;
            border-radius: 50%;
            animation: bash-spin .6s linear infinite;
            flex-shrink: 0;
            margin-left: 8px;
        }

        @keyframes bash-spin {
            to {
                transform: rotate(360deg);
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

            <div class="metrics-row" style="margin-top:.5rem;">
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

            <!-- ── VIM TERMINAL ─────────────────────────────────────────────── -->
            <div class="vim-wrap" id="vim-terminal">

                <div class="vim-tabline">
                    <div class="vim-tab active">
                        <span class="vim-tab-icon" id="vim-tab-icon"></span>
                        <span id="vim-tab-name">server-monitor.log</span>
                    </div>
                    <div class="vim-tab inactive">
                        <span class="vim-tab-icon" style="background:#665c54;"></span>
                        <span>[No Name]</span>
                    </div>
                    <div class="vim-spacer"></div>
                    <div
                        style="display:flex;align-items:center;padding:0 12px;font-family:ui-monospace,monospace;font-size:11px;color:#504945;">
                        NVIM</div>
                </div>

                <div class="vim-buffer">
                    <div class="vim-lines" id="vim-lines">
                        <div class="vim-line">
                            <span class="vim-lnum vt-tilde">~</span>
                            <span class="vim-sign"></span>
                            <span class="vim-content vt-comment">" Loading server logs…</span>
                        </div>
                    </div>
                </div>

                <div class="vim-statusline">
                    <div class="vim-sl-mode normal" id="vim-mode-label">NORMAL</div>
                    <span class="vim-sl-sep">│</span>
                    <span class="vim-sl-file" id="vim-sl-file">server-monitor.log</span>
                    <span class="vim-sl-hint">j/k ↕ gg/G :N /search n :filter :ZZ</span>
                    <span class="vim-sl-info" id="vim-sl-info">utf-8[unix]</span>
                    <span class="vim-sl-sep">│</span>
                    <span class="vim-sl-pos" id="vim-sl-pos">1,1</span>
                    <span class="vim-sl-pct" id="vim-sl-pct">Top</span>
                </div>

                <div class="vim-cmdbar">
                    <span class="vim-cmd-prompt" id="vim-cmd-prompt"></span>
                    <input class="vim-cmd-input" id="vim-cmd-input" type="text" autocomplete="off"
                        spellcheck="false" placeholder="hover + press : for commands" style="display:none;">
                    <span class="vim-cmd-echo" id="vim-cmd-echo"></span>
                </div>

            </div>
            <!-- ── /VIM TERMINAL ─────────────────────────────────────────────── -->

            <!-- ── BASH TERMINAL ───────────────────────────────────────────────── -->
            <div class="bash-wrap" id="bash-terminal">

                <div class="bash-titlebar">
                    <span class="bash-btn bash-btn-close"></span>
                    <span class="bash-btn bash-btn-min"></span>
                    <span class="bash-btn bash-btn-max"></span>
                    <span class="bash-titlebar-label" id="bash-title">bash — {{ config('app.name') }}</span>
                </div>

                <div class="bash-output" id="bash-output">
                    {{-- welcome line printed by JS on init --}}
                </div>

                <div class="bash-input-row">
                    <span class="bash-input-ps1" id="bash-ps1">
                        <span class="bash-ps1-user">www</span><span class="bash-ps1-at">@</span><span
                            class="bash-ps1-host">server</span><span class="bash-ps1-colon">:</span><span
                            class="bash-ps1-path" id="bash-cwd-label">~</span><span class="bash-ps1-sign"> $</span>
                    </span>
                    <input class="bash-input-field" id="bash-input" type="text" autocomplete="off"
                        spellcheck="false" placeholder="type a command…">
                    <span class="bash-spinner" id="bash-spinner"></span>
                </div>

            </div>
            <!-- ── /BASH TERMINAL ──────────────────────────────────────────────── -->

        </div>
    </main>

    <script>
        const METRICS_URL = '{{ route('monit.metrics') }}';
        const LOGS_URL = '{{ route('monit.logs') }}';
        const TERMINAL_URL = '{{ route('monit.terminal') }}';
        const CSRF_TOKEN = '{{ csrf_token() }}';

        let donutChart = null,
            lineChart = null;
        let serverColors = {};
        const PALETTE = ['#3266ad', '#639922', '#BA7517', '#c7517a', '#0F6E56', '#534AB7'];
        let currentFilter = 'all',
            currentHours = 24;

        /* ── FIX BUG-2: XSS escape — all server data must go through esc() before innerHTML ── */
        function esc(v) {
            return String(v ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        /* ── VIM STATE ─────────────────────────────────────────────────────────── */
        let vimMode = 'normal';
        let vimCursorRow = 0;
        let vimRows = [];
        // FIX BUG-4: vimCmdBuffer was declared but never used — removed
        let searchTerm = '';

        /* ── VIM COMMANDS ──────────────────────────────────────────────────────── */
        const VIM_CMDS = {
            'q': () => vimEcho('E37: No write since last change (add ! to override)', 'err'),
            'q!': () => vimEcho('"server-monitor.log" [readonly] --100%--', 'info'),
            'wq': () => vimEcho('"server-monitor.log" written', 'info'),
            'w': () => vimEcho('"server-monitor.log" [New] written', 'info'),
            'set number': () => vimEcho('Already set: number', 'info'),
            'set nonumber': () => vimEcho("E21: Cannot make changes, 'modifiable' is off", 'err'),
            'noh': () => {
                searchTerm = '';
                renderVimLines();
                vimEcho('', '');
            },
            'help': () => vimEcho(':filter [all|errors|slow]  :/pat  :N  :noh  :ZZ  :q!', 'info'),
            'vs': () => vimEcho('E36: Not enough room', 'err'),
            'sp': () => vimEcho('E36: Not enough room', 'err'),
            'ZZ': () => vimEcho(`"server-monitor.log"  ${vimRows.length}L, last ${currentHours}h`, 'ok'),
            // :filter — updates currentFilter and reloads logs
            'filter all': () => applyVimFilter('all'),
            'filter errors': () => applyVimFilter('errors'),
            'filter slow': () => applyVimFilter('slow'),
        };

        function resolveCmd(raw) {
            if (VIM_CMDS[raw]) {
                VIM_CMDS[raw]();
                return;
            }

            // :N or :line N — jump to line number
            const lineMatch = raw.match(/^(?:line\s+)?(\d+)$/);
            if (lineMatch) {
                const n = Math.max(0, Math.min(vimRows.length - 1, parseInt(lineMatch[1], 10) - 1));
                moveCursorTo(n);
                vimEcho(`line ${n + 1}`, 'info');
                return;
            }
            // :/pattern — search shorthand from command mode
            const searchMatch = raw.match(/^\/(.+)$/);
            if (searchMatch) {
                doSearch(searchMatch[1]);
                return;
            }

            vimEcho(`E492: Not an editor command: ${raw}`, 'err');
        }

        function applyVimFilter(filter) {
            currentFilter = filter;
            loadLogs();
            vimEcho(`filter: ${filter}`, 'ok');
        }

        function doSearch(term) {
            if (!term) {
                searchTerm = '';
                renderVimLines();
                vimEcho('');
                return;
            }
            searchTerm = term.toLowerCase();
            const start = vimCursorRow;
            for (let i = 1; i <= vimRows.length; i++) {
                const idx = (start + i) % vimRows.length;
                const r = vimRows[idx];
                const hay = [r.url, r.server_id, r.client_ip, String(r.status_code)].join(' ').toLowerCase();
                if (hay.includes(searchTerm)) {
                    moveCursorTo(idx);
                    vimEcho(`/${term}  match ${idx + 1}/${vimRows.length}`, 'info');
                    renderVimLines();
                    return;
                }
            }
            searchTerm = '';
            vimEcho(`E486: Pattern not found: ${term}`, 'err');
        }

        /* ── VIM MODE ──────────────────────────────────────────────────────────── */
        function vimSetMode(m) {
            vimMode = m;
            const label = document.getElementById('vim-mode-label');
            const prompt = document.getElementById('vim-cmd-prompt');
            const input = document.getElementById('vim-cmd-input');
            const echo = document.getElementById('vim-cmd-echo');

            label.className = 'vim-sl-mode ' + m;
            label.textContent = ({
                normal: 'NORMAL',
                insert: 'INSERT',
                command: 'COMMAND',
                search: 'SEARCH'
            } [m] ?? m).toUpperCase();

            if (m === 'command' || m === 'search') {
                prompt.textContent = m === 'search' ? '/' : ':';
                echo.style.display = 'none';
                input.style.display = 'block';
                input.value = '';
                input.focus();
            } else {
                prompt.textContent = '';
                input.style.display = 'none';
                echo.style.display = 'block';
            }
        }

        function vimEcho(msg, type = '') {
            const el = document.getElementById('vim-cmd-echo');
            el.textContent = msg;
            el.className = 'vim-cmd-echo' + (type ? ' ' + type : '');
        }

        /* ── CURSOR ────────────────────────────────────────────────────────────── */
        // FIX BUG-5: always clamp after any data change
        function vimClampCursor() {
            if (!vimRows.length) {
                vimCursorRow = 0;
                return;
            }
            vimCursorRow = Math.max(0, Math.min(vimRows.length - 1, vimCursorRow));
        }

        // FIX BUG-6/7: targeted class toggle — zero DOM rebuild, scroll position preserved
        function moveCursorTo(next) {
            if (!vimRows.length) return;
            next = Math.max(0, Math.min(vimRows.length - 1, next));
            if (next === vimCursorRow) return;

            const container = document.getElementById('vim-lines');
            const lines = container.children;
            const offset = 1; // skip the header line at index 0

            const prevEl = lines[vimCursorRow + offset];
            const nextEl = lines[next + offset];

            if (prevEl) {
                prevEl.classList.remove('vim-cursor-line');
                prevEl.querySelector('.vim-lnum')?.classList.remove('current');
            }
            if (nextEl) {
                nextEl.classList.add('vim-cursor-line');
                nextEl.querySelector('.vim-lnum')?.classList.add('current');
                nextEl.scrollIntoView({
                    block: 'nearest'
                });
            }

            vimCursorRow = next;
            updateVimStatusPos();
        }

        function vimMoveCursor(delta) {
            moveCursorTo(vimCursorRow + delta);
        }

        function updateVimStatusPos() {
            const total = vimRows.length;
            const line = vimCursorRow + 1;
            const pct = !total ? 'All' : line === 1 ? 'Top' : line === total ? 'Bot' : Math.round(line / total * 100) + '%';
            document.getElementById('vim-sl-pos').textContent = `${line},1`;
            document.getElementById('vim-sl-pct').textContent = pct;
            document.getElementById('vim-sl-info').textContent = `${total}L  utf-8[unix]`;
        }

        /* ── KEYBOARD ──────────────────────────────────────────────────────────── */
        let vimFocused = false;
        let ggPending = false;
        document.getElementById('vim-terminal').addEventListener('mouseenter', () => vimFocused = true);
        document.getElementById('vim-terminal').addEventListener('mouseleave', () => {
            vimFocused = false;
        });

        document.addEventListener('keydown', e => {
            if (vimMode === 'command' || vimMode === 'search') return;
            if (!vimFocused) return;
            e.preventDefault();

            const key = e.key;
            if (key === ':') {
                vimSetMode('command');
                return;
            }
            if (key === '/') {
                vimSetMode('search');
                return;
            }
            if (key === 'Escape') {
                searchTerm = '';
                ggPending = false;
                renderVimLines();
                vimEcho('');
                return;
            }
            if (key === 'i' || key === 'a') {
                vimEcho("-- INSERT -- (read-only buffer)", '');
                return;
            }
            if (key === 'j' || key === 'ArrowDown') {
                vimMoveCursor(1);
                return;
            }
            if (key === 'k' || key === 'ArrowUp') {
                vimMoveCursor(-1);
                return;
            }
            if (key === 'G') {
                vimMoveCursor(9999);
                ggPending = false;
                return;
            }
            if (key === 'g') {
                if (ggPending) {
                    vimMoveCursor(-9999);
                    ggPending = false;
                } else {
                    ggPending = true;
                    setTimeout(() => {
                        ggPending = false;
                    }, 500);
                }
                return;
            }
            if (key === 'n' && searchTerm) {
                doSearch(searchTerm);
                return;
            }
            if (key === 'd') {
                vimEcho("E21: Cannot make changes, 'modifiable' is off", 'err');
                return;
            }
            if (key === 'f' && e.ctrlKey) {
                vimMoveCursor(12);
                return;
            }
            if (key === 'b' && e.ctrlKey) {
                vimMoveCursor(-12);
                return;
            }
        });

        document.getElementById('vim-cmd-input').addEventListener('keydown', e => {
            const isSearch = document.getElementById('vim-cmd-prompt').textContent === '/';
            if (e.key === 'Escape') {
                vimSetMode('normal');
                vimEcho('');
                return;
            }
            if (e.key === 'Enter') {
                const raw = e.target.value.trim();
                vimSetMode('normal');
                if (isSearch) doSearch(raw);
                else resolveCmd(raw);
            }
        });

        // live search preview while typing
        document.getElementById('vim-cmd-input').addEventListener('input', e => {
            if (document.getElementById('vim-cmd-prompt').textContent !== '/') return;
            const term = e.target.value.toLowerCase();
            if (!term) return;
            const idx = vimRows.findIndex(r => [r.url, r.server_id, r.client_ip, String(r.status_code)].join(' ')
                .toLowerCase().includes(term)
            );
            if (idx >= 0) {
                vimCursorRow = idx;
                updateVimStatusPos();
                const el = document.getElementById('vim-lines').children[idx + 1];
                if (el) el.scrollIntoView({
                    block: 'nearest'
                });
            }
        });

        /* ── TOKEN HELPERS ─────────────────────────────────────────────────────── */
        function methodClass(m) {
            return {
                GET: 'vt-method-get',
                POST: 'vt-method-post',
                PUT: 'vt-method-put',
                DELETE: 'vt-method-delete',
                PATCH: 'vt-method-patch'
            } [m] ?? 'vt-url';
        }

        function statusVimClass(s) {
            return s < 300 ? 'vt-s2xx' : s < 400 ? 'vt-s3xx' : s < 500 ? 'vt-s4xx' : 'vt-s5xx';
        }

        function rtVimClass(v) {
            return v < 100 ? 'vt-rt-fast' : v < 300 ? 'vt-rt-mid' : 'vt-rt-slow';
        }

        function highlight(text, term) {
            const safe = esc(text);
            if (!term) return safe;
            const re = new RegExp(term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            return safe.replace(re, m => `<mark class="vt-match">${m}</mark>`);
        }

        /* ── RENDER VIM BUFFER (full rebuild only on data change, NOT on cursor move) ── */
        function renderVimLines() {
            const container = document.getElementById('vim-lines');

            if (!vimRows.length) {
                container.innerHTML = `<div class="vim-line">
                    <span class="vim-lnum vt-tilde">~</span>
                    <span class="vim-sign"></span>
                    <span class="vim-content vt-comment">" No log entries in range</span>
                </div>`;
                return;
            }

            const header = `<div class="vim-line">
                <span class="vim-lnum vt-tilde">~</span>
                <span class="vim-sign"></span>
                <span class="vim-content vt-header">
                    <span class="vim-col vc-time">" time&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span class="vt-sep"> │ </span>
                    <span class="vim-col vc-srv">server&nbsp;&nbsp;&nbsp;&nbsp;</span>
                    <span class="vt-sep"> │ </span>
                    <span class="vim-col vc-meth">method&nbsp;</span>
                    <span class="vim-col vc-url">url</span>
                    <span class="vt-sep"> │ </span>
                    <span class="vim-col vc-stat">st</span>
                    <span class="vt-sep"> │ </span>
                    <span class="vim-col vc-rt">resp.time</span>
                    <span class="vt-sep"> │ </span>
                    <span class="vim-col vc-ip">client ip</span>
                </span>
            </div>`;

            const dataLines = vimRows.map((r, i) => {
                const isCursor = i === vimCursorRow;
                const ts = new Date(r.created_at).toLocaleTimeString('en-GB', {
                    hour12: false
                });
                // FIX BUG-2: all user data through esc() / highlight()
                const method = esc(r.method ?? 'GET');
                const url = highlight(r.url ?? '/', searchTerm);
                const srv = highlight(r.server_id ?? '', searchTerm);
                const ip = highlight(r.client_ip ?? '', searchTerm);
                const status = r.status_code;
                const rt = r.response_time;
                // FIX BUG-8: sign uses CSS classes only
                const sign = status >= 500 ? '!' : status >= 400 ? '?' : rt >= 300 ? '»' : '';
                const signCls = status >= 500 ? 'vim-sign-err' : status >= 400 ? 'vim-sign-warn' : 'vim-sign-slow';

                return `<div class="vim-line${isCursor ? ' vim-cursor-line' : ''}">
                    <span class="vim-lnum${isCursor ? ' current' : ''}">${String(i + 1).padStart(4)}</span>
                    <span class="vim-sign ${signCls}">${sign}</span>
                    <span class="vim-content">
                        ${isCursor ? '<span class="vim-cursor"></span>' : ''}
                        <span class="vt-time vim-col vc-time">${esc(ts)}</span>
                        <span class="vt-sep"> │ </span>
                        <span class="vt-srv vim-col vc-srv">${srv}</span>
                        <span class="vt-sep"> │ </span>
                        <span class="${methodClass(r.method ?? 'GET')} vim-col vc-meth">${method}</span>
                        <span class="vt-url vim-col vc-url">${url}</span>
                        <span class="vt-sep"> │ </span>
                        <span class="${statusVimClass(status)} vim-col vc-stat">${status}</span>
                        <span class="vt-sep"> │ </span>
                        <span class="${rtVimClass(rt)} vim-col vc-rt">${rt}ms</span>
                        <span class="vt-sep"> │ </span>
                        <span class="vt-ip vim-col vc-ip">${ip}</span>
                    </span>
                </div>`;
            });

            container.innerHTML = header + dataLines.join('');

            // scroll cursor into view after full rebuild
            container.querySelector('.vim-cursor-line')?.scrollIntoView({
                block: 'nearest'
            });
        }

        function updateVimBuffer(rows) {
            vimRows = rows;
            vimClampCursor(); // FIX BUG-5
            renderVimLines();
            updateVimStatusPos();

            const hasErrors = rows.some(r => r.status_code >= 400);
            document.getElementById('vim-tab-icon').className = 'vim-tab-icon' + (hasErrors ? ' modified' : '');
            document.getElementById('vim-sl-file').textContent = `server-monitor.log [RO]${hasErrors ? ' [+]' : ''}`;
        }

        /* ── METRICS & LOGS ────────────────────────────────────────────────────── */
        document.getElementById('ts-display').textContent = new Date().toLocaleTimeString();

        document.getElementById('hours-select').addEventListener('change', e => {
            currentHours = parseInt(e.target.value, 10);
            loadMetrics();
            loadLogs();
        });

        function statusClass(s) {
            return s < 300 ? 's2xx' : s < 400 ? 's3xx' : s < 500 ? 's4xx' : 's5xx';
        }

        function rtClass(v) {
            return v < 100 ? 'rt-fast' : v < 300 ? 'rt-mid' : 'rt-slow';
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
            } catch {
                document.getElementById('loading').textContent = 'Failed to load metrics.';
            }
        }

        function renderServerCards(servers) {
            const total = servers.reduce((a, [, x]) => a + x.total, 0);
            document.getElementById('servers-row').innerHTML = servers.map(([id, s], i) => {
                const pct = total ? Math.round(s.total / total * 100) : 0;
                return `<div class="server-card">
                    <div class="srv-dot" style="background:${PALETTE[i % PALETTE.length]};"></div>
                    <div>
                        <div class="srv-name">${esc(id)} <span class="srv-pill pill-${i % 5}">${esc(id)}</span></div>
                        <div class="srv-region">avg ${s.avg_rt}ms &nbsp;·&nbsp; ${s.errors} err</div>
                        <div class="prog-row">
                            <span class="prog-label">Load</span>
                            <div class="prog-track"><div class="prog-fill" style="width:${pct}%;background:${PALETTE[i % PALETTE.length]};"></div></div>
                            <span class="ts">${pct}%</span>
                        </div>
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
                return `<span style="display:flex;align-items:center;gap:5px;">
                    <span class="leg-sq" style="background:${serverColors[id]};"></span>${esc(id)} ${pct}%
                </span>`;
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
            const allHours = [...new Set(Object.values(perHour).flatMap(r => r.map(x => x.hour)))].sort();

            document.getElementById('line-legend').innerHTML = servers.map(id =>
                `<span style="display:flex;align-items:center;gap:5px;">
                    <span class="leg-sq" style="background:${serverColors[id]};"></span>${esc(id)}
                </span>`
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

            const gc = '#e5e5e2',
                tc = '#aaa';
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
                updateVimBuffer(rows);
            } catch {
                vimEcho('E484: Cannot open file server-monitor.log', 'err');
            }
        }

        loadMetrics();
        loadLogs();
        setInterval(() => {
            loadMetrics();
            loadLogs();
            document.getElementById('ts-display').textContent = new Date().toLocaleTimeString();
        }, 30000);

        /* ── BASH TERMINAL ─────────────────────────────────────────────────────── */
        (() => {
            const output = document.getElementById('bash-output');
            const input = document.getElementById('bash-input');
            const spinner = document.getElementById('bash-spinner');
            const cwdLabel = document.getElementById('bash-cwd-label');

            let bashCwd = null; // null = server resolves from session
            let bashBusy = false;
            let history = [];
            let historyIdx = -1;

            // ── helpers ──────────────────────────────────────────────────────────

            function escHtml(v) {
                return String(v ?? '')
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function shortPath(abs) {
                // trim the base_path prefix so the prompt stays readable
                return abs ? abs.replace(/.*\/([^/]+\/[^/]+)\/?$/, '…/$1') : '~';
            }

            function updatePrompt(cwd) {
                bashCwd = cwd;
                cwdLabel.textContent = shortPath(cwd);
                cwdLabel.title = cwd ?? '';
            }

            function appendWelcome() {
                const el = document.createElement('div');
                el.className = 'bash-entry';
                el.innerHTML = `<div class="bash-result" style="color:#6272a4;">` +
                    `# Interactive terminal — project root\n` +
                    `# Type any shell command. Use 'clear' to reset output.\n` +
                    `# History: ↑/↓   Clear: Ctrl+L</div>`;
                output.appendChild(el);
            }

            function appendEntry(prompt, cmd, result, isErr) {
                const entry = document.createElement('div');
                entry.className = 'bash-entry';

                const cmdLine = document.createElement('div');
                cmdLine.className = 'bash-prompt-line';
                cmdLine.innerHTML = prompt + `<span class="bash-ps1-cmd">${escHtml(cmd)}</span>`;

                entry.appendChild(cmdLine);

                if (result !== '') {
                    const out = document.createElement('div');
                    out.className = 'bash-result' + (isErr ? ' bash-err' : '');
                    out.textContent = result;
                    entry.appendChild(out);
                }

                output.appendChild(entry);
                output.scrollTop = output.scrollHeight;
            }

            function currentPromptHtml() {
                return document.getElementById('bash-ps1').innerHTML;
            }

            // ── execute ──────────────────────────────────────────────────────────

            async function runCommand(cmd) {
                if (bashBusy) return;

                const trimmed = cmd.trim();

                // client-side clear
                if (trimmed === 'clear' || trimmed === 'cls') {
                    output.innerHTML = '';
                    return;
                }

                if (!trimmed) return;

                // save history
                history.unshift(trimmed);
                if (history.length > 200) history.pop();
                historyIdx = -1;

                const promptSnapshot = currentPromptHtml();

                bashBusy = true;
                input.disabled = true;
                spinner.style.display = 'block';

                try {
                    const res = await fetch(TERMINAL_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            command: trimmed
                        }),
                    });

                    if (!res.ok) {
                        const text = await res.text();
                        appendEntry(promptSnapshot, trimmed, `HTTP ${res.status}: ${text}`, true);
                        return;
                    }

                    const data = await res.json();
                    updatePrompt(data.cwd);
                    appendEntry(promptSnapshot, trimmed, data.output ?? '', data.exit !== 0);

                } catch (err) {
                    appendEntry(promptSnapshot, trimmed, `fetch error: ${err.message}`, true);
                } finally {
                    bashBusy = false;
                    input.disabled = false;
                    spinner.style.display = 'none';
                    input.focus();
                }
            }

            // ── keyboard ─────────────────────────────────────────────────────────

            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    const cmd = input.value;
                    input.value = '';
                    runCommand(cmd);
                    return;
                }

                // history navigation
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (historyIdx < history.length - 1) {
                        historyIdx++;
                        input.value = history[historyIdx];
                        // move caret to end
                        setTimeout(() => input.setSelectionRange(input.value.length, input.value.length), 0);
                    }
                    return;
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (historyIdx > 0) {
                        historyIdx--;
                        input.value = history[historyIdx];
                    } else {
                        historyIdx = -1;
                        input.value = '';
                    }
                    return;
                }

                // Ctrl+L — clear
                if (e.key === 'l' && e.ctrlKey) {
                    e.preventDefault();
                    output.innerHTML = '';
                }

                // Ctrl+C — cancel current input
                if (e.key === 'c' && e.ctrlKey) {
                    appendEntry(currentPromptHtml(), input.value + '^C', '', false);
                    input.value = '';
                    historyIdx = -1;
                }
            });

            // click anywhere on the terminal body to focus input
            document.getElementById('bash-terminal').addEventListener('click', () => input.focus());

            // ── init ─────────────────────────────────────────────────────────────
            appendWelcome();
        })();
    </script>
</body>

</html>
