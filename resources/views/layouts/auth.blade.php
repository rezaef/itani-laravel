<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title','Login — ITani')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#f6f7fb; --bg2:#eef2ff; --card:rgba(255,255,255,.85);
      --text:#0f172a; --muted:#64748b; --border:rgba(15,23,42,.08);
      --shadow:0 18px 60px rgba(0,0,0,.14);
      --ring:rgba(99,102,241,.25);
    }
    [data-theme="dark"]{
      --bg1:#0b1220; --bg2:#0b1b2c; --card:rgba(15,23,42,.72);
      --text:#e5e7eb; --muted:#94a3b8; --border:rgba(255,255,255,.10);
      --shadow:0 18px 60px rgba(0,0,0,.35);
      --ring:rgba(34,197,94,.20);
    }
    body{
      min-height:100vh;
      background:
        radial-gradient(900px 600px at 15% 20%, var(--bg2), transparent 60%),
        radial-gradient(700px 500px at 85% 15%, rgba(34,197,94,.12), transparent 55%),
        radial-gradient(800px 600px at 50% 95%, rgba(99,102,241,.12), transparent 55%),
        linear-gradient(180deg, var(--bg1), var(--bg1));
      color:var(--text);
      font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    }
    .auth-shell{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem 1rem; }
    .auth-card{
      width:100%; max-width:460px;
      background:var(--card);
      border:1px solid var(--border);
      border-radius:22px;
      box-shadow:var(--shadow);
      backdrop-filter: blur(10px);
      overflow:hidden;
    }
    .btn-ghost{
      border-radius: 12px;
      border: 1px solid var(--border);
      background: transparent;
      color: var(--text);
      padding: .5rem .65rem;
    }
    .btn-ghost:hover{ background: rgba(255,255,255,.08); }

    .form-control, .input-group-text{
      border-color: var(--border) !important;
      background: rgba(255,255,255,.65);
      color: var(--text);
    }
    [data-theme="dark"] .form-control,
    [data-theme="dark"] .input-group-text{
      background: rgba(2,6,23,.35);
      color: var(--text);
    }
    .form-control:focus{ box-shadow: 0 0 0 .25rem var(--ring); }

    .form-control::placeholder{ color: rgba(100,116,139,.85); opacity:1; }
    [data-theme="dark"] .form-control::placeholder{ color: rgba(148,163,184,.85); }
  </style>
  @yield('head')
</head>

<body>
  <div class="auth-shell">
    @yield('content')
  </div>

  <script>
    // Theme global (login)
    const root = document.documentElement;
    function applyTheme(t){
      root.setAttribute('data-theme', t);
      localStorage.setItem('itani_theme', t);
      const btn = document.getElementById('themeBtn');
      if (btn) btn.innerHTML = t === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
    }
    const saved = localStorage.getItem('itani_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    applyTheme(saved || (prefersDark ? 'dark' : 'light'));
  </script>

  @yield('scripts')
</body>
</html>
