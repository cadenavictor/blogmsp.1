<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Admin' }} - Blog MSP</title>
        <style>
            :root {
                color-scheme: light;
                --admin-bg: #f6f7f9;
                --admin-panel: #ffffff;
                --admin-panel-muted: #f0f3f6;
                --admin-text: #18202c;
                --admin-muted: #697386;
                --admin-line: #d7dde5;
                --admin-accent: #0f766e;
                --admin-accent-dark: #115e59;
                --admin-danger: #b42318;
                --admin-warning-bg: #fff7ed;
                --admin-success-bg: #ecfdf3;
                --admin-error-bg: #fef3f2;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: var(--admin-bg);
                color: var(--admin-text);
                font-family: Arial, Helvetica, sans-serif;
                font-size: 14px;
                line-height: 1.45;
            }

            a {
                color: var(--admin-accent-dark);
                text-decoration-thickness: 1px;
                text-underline-offset: 3px;
            }

            button,
            input,
            select,
            textarea {
                font: inherit;
            }

            button,
            .button {
                align-items: center;
                background: var(--admin-accent);
                border: 1px solid var(--admin-accent);
                border-radius: 6px;
                color: #fff;
                cursor: pointer;
                display: inline-flex;
                font-weight: 700;
                gap: 6px;
                min-height: 34px;
                padding: 7px 11px;
                text-decoration: none;
            }

            button.secondary,
            .button.secondary {
                background: var(--admin-panel);
                border-color: var(--admin-line);
                color: var(--admin-text);
            }

            button.danger {
                background: transparent;
                border-color: var(--admin-line);
                color: var(--admin-danger);
            }

            .admin-shell {
                min-height: 100vh;
            }

            .admin-layout {
                display: grid;
                grid-template-columns: 220px minmax(0, 1fr);
                min-height: 100vh;
            }

            .admin-sidebar {
                background: #111827;
                color: #d1d5db;
                padding: 18px 14px;
            }

            .admin-brand {
                color: #fff;
                display: block;
                font-size: 1.05rem;
                font-weight: 700;
                margin: 0 0 18px;
                text-decoration: none;
            }

            .admin-nav {
                display: grid;
                gap: 4px;
            }

            .admin-nav a {
                border-radius: 6px;
                color: #d1d5db;
                padding: 8px 10px;
                text-decoration: none;
            }

            .admin-nav a[aria-current="page"],
            .admin-nav a:hover {
                background: #1f2937;
                color: #fff;
            }

            .admin-main {
                min-width: 0;
            }

            .admin-topbar {
                align-items: center;
                background: var(--admin-panel);
                border-bottom: 1px solid var(--admin-line);
                display: flex;
                gap: 18px;
                justify-content: space-between;
                padding: 16px 22px;
            }

            .admin-eyebrow {
                color: var(--admin-muted);
                font-size: .78rem;
                font-weight: 700;
                letter-spacing: .08em;
                margin: 0 0 4px;
                text-transform: uppercase;
            }

            .admin-topbar h1 {
                font-size: 1.5rem;
                line-height: 1.2;
                margin: 0;
            }

            .admin-actions {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-end;
            }

            .logout-form {
                margin: 0;
            }

            .admin-content {
                padding: 22px;
            }

            .stack {
                display: grid;
                gap: 18px;
            }

            .panel {
                background: var(--admin-panel);
                border: 1px solid var(--admin-line);
                border-radius: 8px;
                padding: 16px;
            }

            .panel-header {
                align-items: flex-start;
                display: flex;
                gap: 14px;
                justify-content: space-between;
                margin-bottom: 12px;
            }

            .panel-header h2,
            .panel h2 {
                font-size: 1rem;
                margin: 0;
            }

            .muted {
                color: var(--admin-muted);
            }

            .metric-grid {
                display: grid;
                gap: 12px;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .metric {
                background: var(--admin-panel);
                border: 1px solid var(--admin-line);
                border-radius: 8px;
                padding: 14px;
            }

            .metric span {
                color: var(--admin-muted);
                display: block;
                font-size: .78rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .metric strong {
                display: block;
                font-size: 1.8rem;
                line-height: 1.1;
                margin-top: 6px;
            }

            .admin-table-wrap {
                overflow-x: auto;
            }

            .admin-table {
                border-collapse: collapse;
                min-width: 680px;
                width: 100%;
            }

            .admin-table th,
            .admin-table td {
                border-bottom: 1px solid var(--admin-line);
                padding: 9px 10px;
                text-align: left;
                vertical-align: top;
            }

            .admin-table th {
                background: var(--admin-panel-muted);
                color: var(--admin-muted);
                font-size: .78rem;
                text-transform: uppercase;
            }

            .admin-table tr:last-child td {
                border-bottom: 0;
            }

            .table-actions {
                align-items: center;
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .table-actions form {
                margin: 0;
            }

            .filters,
            .admin-form {
                display: grid;
                gap: 14px;
            }

            .filters {
                align-items: end;
                grid-template-columns: minmax(180px, 2fr) repeat(3, minmax(150px, 1fr)) auto;
            }

            .form-grid {
                display: grid;
                gap: 14px;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .field,
            .check-field {
                display: grid;
                gap: 6px;
            }

            .field > span,
            fieldset legend {
                color: var(--admin-muted);
                font-size: .82rem;
                font-weight: 700;
            }

            input[type="text"],
            input[type="url"],
            input[type="datetime-local"],
            input:not([type]),
            select,
            textarea {
                background: #fff;
                border: 1px solid var(--admin-line);
                border-radius: 6px;
                color: var(--admin-text);
                min-height: 36px;
                padding: 8px 10px;
                width: 100%;
            }

            textarea {
                min-height: 120px;
                resize: vertical;
            }

            .span-2 {
                grid-column: 1 / -1;
            }

            fieldset {
                border: 1px solid var(--admin-line);
                border-radius: 8px;
                margin: 0;
                padding: 12px;
            }

            .check-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 16px;
            }

            .check-field {
                align-items: center;
                display: flex;
            }

            .alert {
                border: 1px solid var(--admin-line);
                border-radius: 8px;
                margin-bottom: 16px;
                padding: 10px 12px;
            }

            .alert.success {
                background: var(--admin-success-bg);
            }

            .alert.error {
                background: var(--admin-error-bg);
                border-color: #fecdca;
            }

            .empty {
                background: var(--admin-panel-muted);
                border-radius: 8px;
                color: var(--admin-muted);
                margin: 0;
                padding: 14px;
            }

            .pagination {
                margin-top: 14px;
            }

            .pagination nav > div:first-child {
                display: none;
            }

            .pagination nav > div:last-child {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }

            @media (max-width: 920px) {
                .admin-layout {
                    grid-template-columns: 1fr;
                }

                .admin-sidebar {
                    position: static;
                }

                .admin-nav {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }

                .metric-grid,
                .filters,
                .form-grid {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media (max-width: 640px) {
                .admin-topbar {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .admin-actions {
                    justify-content: flex-start;
                    width: 100%;
                }

                .admin-nav,
                .metric-grid,
                .filters,
                .form-grid {
                    grid-template-columns: 1fr;
                }

                .admin-content {
                    padding: 16px;
                }
            }
        </style>
    </head>
    <body class="admin-shell">
        <div class="admin-layout">
            <aside class="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">Blog MSP Admin</a>
                <nav class="admin-nav" aria-label="Administracao">
                    <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Painel</a>
                    <a href="{{ route('admin.posts.index') }}" @if (request()->routeIs('admin.posts.*')) aria-current="page" @endif>Posts</a>
                    <a href="{{ route('admin.scripts.index') }}" @if (request()->routeIs('admin.scripts.*')) aria-current="page" @endif>Scripts</a>
                    <a href="{{ route('admin.indexnow.index') }}" @if (request()->routeIs('admin.indexnow.*')) aria-current="page" @endif>IndexNow</a>
                </nav>
            </aside>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div>
                        <p class="admin-eyebrow">Administracao</p>
                        <h1>{{ $title ?? 'Admin' }}</h1>
                    </div>
                    <div class="admin-actions">
                        @yield('page-actions')
                        <form class="logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="secondary" type="submit">Sair</button>
                        </form>
                    </div>
                </header>

                <main class="admin-content">
                    @if (session('status'))
                        <p class="alert success" role="status">{{ session('status') }}</p>
                    @endif

                    @if (session('error'))
                        <p class="alert error" role="alert">{{ session('error') }}</p>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
