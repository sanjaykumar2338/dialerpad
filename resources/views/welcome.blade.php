<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DialerPad</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    <style>
        :root {
            color-scheme: dark;
            --bg: radial-gradient(circle at 20% 20%, #0ea5e9, #0f172a 45%), radial-gradient(circle at 80% 10%, #22c55e, transparent 35%), #020617;
            --card: rgba(255, 255, 255, 0.05);
            --border: rgba(255, 255, 255, 0.12);
            --text: #e5e7eb;
            --muted: #94a3b8;
            --accent: #22c55e;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem 3rem;
        }
        .shell {
            width: 100%;
            max-width: 1100px;
            background: linear-gradient(145deg, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.6));
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
        }
        .top {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .logo {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #fde68a, #f97316);
            display: grid;
            place-items: center;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0.04em;
        }
        h1 {
            font-size: 2.2rem;
            margin: 0;
            letter-spacing: -0.02em;
        }
        p.lead {
            margin: 0.25rem 0 0;
            color: var(--muted);
        }
        .cta {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .btn {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 0.85rem 1.4rem;
            font-weight: 600;
            text-decoration: none;
            color: var(--text);
            transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        }
        .btn:hover { transform: translateY(-2px); border-color: rgba(255,255,255,0.18); }
        .btn.primary { background: var(--accent); color: #052e16; border: none; }
        .btn.ghost { background: transparent; }
        .grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: 1.5rem;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.25rem;
        }
        .card h3 { margin: 0 0 0.4rem; font-size: 1.05rem; }
        .card p { margin: 0; color: var(--muted); font-size: 0.95rem; line-height: 1.5; }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.75rem;
            background: rgba(34, 197, 94, 0.12);
            border-radius: 999px;
            color: var(--accent);
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .footer {
            margin-top: 2rem;
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
        }
        code {
            font-family: "SFMono-Regular", Menlo, monospace;
            background: rgba(255,255,255,0.05);
            padding: 0.1rem 0.35rem;
            border-radius: 6px;
            color: #cbd5f5;
            border: 1px solid var(--border);
        }
        @media (max-width: 720px) {
            .shell { padding: 1.75rem; }
            h1 { font-size: 1.9rem; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="top">
            <div class="brand">
                <div class="logo">DP</div>
                <div>
                    <h1>DialerPad</h1>
                    <p class="lead">QR-powered calling and eSIM requests in one clean portal.</p>
                </div>
            </div>
            <div class="cta">
                <a class="btn primary" href="{{ route('login') }}">Admin Login</a>
                <a class="btn ghost" href="{{ route('esim.activate.redirect') }}">Request eSIM</a>
            </div>
        </div>

        <div class="pill">Live MVP</div>

        <div class="grid">
            <div class="card">
                <h3>Scan & Call</h3>
                <p>Every call card QR points to a branded dialer at <code>/c/{uuid}</code> with minute tracking and auto-expiry.</p>
            </div>
            <div class="card">
                <h3>Admin Controls</h3>
                <p>Create call cards, export QR packs, review call sessions, and manage eSIM requests from a single dashboard.</p>
            </div>
            <div class="card">
                <h3>eSIM Intake</h3>
                <p>Collect eSIM requests at <code>/esim/activate</code> and process them later—API hookups come next.</p>
            </div>
            <div class="card">
                <h3>Ready to Deploy</h3>
                <p>Docs, storage links, CSV exports, and usage history pages are in place. Next stop: Vultr + SSL.</p>
            </div>
        </div>

        <div class="footer">
            Need access? Contact the admin team for credentials.
        </div>
    </div>
</body>
</html>
