<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>DialerPad</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            color-scheme: dark;
            --bg: radial-gradient(circle at 20% 20%, #0ea5e9, #0f172a 45%), radial-gradient(circle at 80% 10%, #22c55e, transparent 35%), #020617;
            --card: rgba(255, 255, 255, 0.04);
            --border: rgba(255, 255, 255, 0.08);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --accent: #22c55e;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }
        .auth-shell {
            width: 100%;
            max-width: 440px;
            background: linear-gradient(145deg, rgba(15,23,42,0.85), rgba(15,23,42,0.7));
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
            justify-content: center;
        }
        .logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #fde68a, #f97316);
            display: grid;
            place-items: center;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        .brand-title {
            font-size: 1.2rem;
            margin: 0;
            letter-spacing: 0.02em;
        }
        .brand-sub {
            margin: 0;
            color: var(--muted);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="brand">
            <div class="logo">DP</div>
            <div>
                <p class="brand-title">DialerPad Admin</p>
                <p class="brand-sub">Secure workspace</p>
            </div>
        </div>
        {{ $slot }}
    </div>
</body>
</html>
