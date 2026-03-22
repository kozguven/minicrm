<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini CRM') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f3ef;
            --panel: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --accent: #0f766e;
            --accent-strong: #115e59;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #fbf8f5 0%, var(--bg) 100%);
            color: var(--text);
        }
        .shell {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(10px);
        }
        .brand {
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .content {
            flex: 1;
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
        }
        .card {
            width: min(100%, 420px);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 20px 60px rgba(17, 24, 39, 0.08);
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        .button:hover { background: var(--accent-strong); }
        .muted { color: var(--muted); }
        .stack > * + * { margin-top: 1rem; }
        .error {
            margin: 0 0 1rem;
            color: #b91c1c;
            font-size: 0.95rem;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.85rem 0.95rem;
            font: inherit;
            background: #fff;
        }
        input:focus {
            outline: 2px solid color-mix(in srgb, var(--accent) 30%, white);
            border-color: var(--accent);
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <div class="brand">{{ config('app.name', 'Mini CRM') }}</div>
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button" type="submit">Çıkış Yap</button>
                </form>
            @endauth
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
