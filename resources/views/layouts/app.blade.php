<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'ITani')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#f6f7fb; --bg2:#eef2ff; --card:rgba(255,255,255,.88);
      --text:#0f172a; --muted:#64748b; --border:rgba(15,23,42,.10);
      --shadow:0 18px 60px rgba(0,0,0,.10);
      --ring:rgba(34,197,94,.25);
      --nav:#15803d;
      --chip:rgba(255,255,255,.16);
      --chipB:rgba(255,255,255,.18);
    }
    [data-theme="dark"]{
      --bg1:#0b1220; --bg2:#0b1b2c; --card:rgba(15,23,42,.75);
      --text:#e5e7eb; --muted:#94a3b8; --border:rgba(255,255,255,.10);
      --shadow:0 18px 60px rgba(0,0,0,.35);
      --ring:rgba(34,197,94,.20);
      --nav:#0f5132;
      --chip:rgba(255,255,255,.10);
      --chipB:rgba(255,255,255,.16);
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

    .navx{
      background: linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,0)) , var(--nav);
      box-shadow: 0 10px 30px rgba(0,0,0,.18);
      position: sticky; top:0; z-index: 50;
      border-bottom: 1px solid rgba(255,255,255,.12);
    }
    .nav-link{ font-weight: 700; opacity:.92; }
    .nav-link.active{
      background: rgba(255,255,255,.16);
      border-radius: 999px;
      padding-inline: .9rem;
    }

    .wrap{ padding: 1.5rem 0 2.2rem; }

    .cardx{
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 18px;
      box-shadow: var(--shadow);
    }
    .cardx .card-body{ padding: 1.15rem 1.2rem; }

    .muted{ color: var(--muted); }

    .chip{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.35rem .7rem; border-radius:999px;
      background: var(--chip); color:#fff;
      border: 1px solid var(--chipB);
      font-size:.82rem; font-weight:800;
    }
    .dot{ width:9px; height:9px; border-radius:99px; display:inline-block; }
    .dot.on{ background:#22c55e; } .dot.off{ background:#ef4444; }

    .btn-ghost{
      border-radius: 12px;
      border: 1px solid rgba(255,255,255,.22);
      background: rgba(255,255,255,.12);
      color:#fff;
      padding:.45rem .6rem;
    }
    .btn-ghost:hover{ background: rgba(255,255,255,.18); }

    .avatar{
      width:40px; height:40px; border-radius:999px;
      display:grid; place-items:center; font-weight:900;
      background: rgba(255,255,255,.18);
      border: 1px solid rgba(255,255,255,.22);
      color:#fff;
    }

    /* input */
    .form-control, .form-select, .input-group-text{
      border-color: var(--border) !important;
      background: rgba(255,255,255,.65);
      color: var(--text);
    }
    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select,
    [data-theme="dark"] .input-group-text{
      background: rgba(2,6,23,.35);
      color: var(--text);
    }
    .form-control:focus, .form-select:focus{
      box-shadow: 0 0 0 .25rem var(--ring);
    }

    /* placeholder follow theme */
    .form-control::placeholder{ color: rgba(100,116,139,.85); opacity: 1; }
    [data-theme="dark"] .form-control::placeholder{ color: rgba(148,163,184,.85); }
  </style>

  @yield('head')
</head>

<body>
@php
  $u = auth()->user();
  $name = $u?->name ?? 'Admin';
  $username = $u?->username ?? 'admin';
  $initial = strtoupper(mb_substr($name, 0, 1));
  $path = request()->path();
@endphp

<nav class="navbar navbar-expand-lg navbar-dark navx">
  <div class="container-fluid px-3 px-md-4">
    <a class="navbar-brand fw-bold" href="/index.php">ITani</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navMain">
      <ul class="navbar-nav gap-1">
        <li class="nav-item">
          <a class="nav-link {{ str_ends_with($path,'index.php') ? 'active' : '' }}" href="/index.php">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_ends_with($path,'periode.php') ? 'active' : '' }}" href="/periode.php">Periode Tanam</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_ends_with($path,'panen.php') ? 'active' : '' }}" href="/panen.php">Hasil Panen</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_ends_with($path,'stok.php') ? 'active' : '' }}" href="/stok.php">Stok Bibit &amp; Pupuk</a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ str_ends_with($path,'users.php') ? 'active' : '' }}" href="/users.php">Kelola User</a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
        @yield('top-right')

        <div class="text-end me-2 d-none d-lg-block">
          <div class="text-white fw-semibold" style="font-size:.92rem">{{ $name }}</div>
          <small class="text-white-50">&#64;{{ $username }}</small>
        </div>

        <button class="btn btn-ghost" type="button" id="themeBtn" title="Toggle tema">
          <i class="bi bi-moon-stars"></i>
        </button>

        <button class="btn btn-ghost" type="button" id="btnLogout" title="Logout">
          <i class="bi bi-box-arrow-right"></i>
        </button>

        <div class="avatar">{{ $initial }}</div>
      </div>
    </div>
  </div>
</nav>

<div class="container-fluid wrap px-3 px-md-4">
  @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Theme global (dipakai semua page)
  const root = document.documentElement;
  const themeBtn = document.getElementById('themeBtn');

  function applyTheme(t){
    root.setAttribute('data-theme', t);
    localStorage.setItem('itani_theme', t);
    if (themeBtn) themeBtn.innerHTML = t === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
  }

  const saved = localStorage.getItem('itani_theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  applyTheme(saved || (prefersDark ? 'dark' : 'light'));

  if (themeBtn) themeBtn.addEventListener('click', () => applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));

  // Logout global
  const btnLogout = document.getElementById('btnLogout');
  if (btnLogout) btnLogout.addEventListener('click', async () => {
    try { await fetch('/api/logout.php'); } catch(e){}
    window.location.href = '/login.html';
  });
</script>

@yield('scripts')
</body>
</html>
