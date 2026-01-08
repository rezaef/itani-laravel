<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'ITani')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">

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

    /* input */
    .form-control, .form-select, .input-group-text{
      border-color: var(--border) !important;
      background: rgba(255,255,255,.65);
      color: var(--text);
    }

    .form-control:focus, .form-select:focus{
      box-shadow: 0 0 0 .25rem var(--ring);
    }

    /* placeholder follow theme */
    .form-control::placeholder{ color: rgba(100,116,139,.85); opacity: 1; }
    /* base: kalau card kamu pakai id card-xxx */
    #card-temp.warn, #card-humi.warn, #card-ph.warn, #card-n.warn, #card-p.warn, #card-k.warn, #card-ec.warn{
      border: 2px solid #fbbf24;               /* kuning */
      box-shadow: 0 0 0 4px rgba(251,191,36,.18);
    }

    #card-temp.ok, #card-humi.ok, #card-ph.ok, #card-n.ok, #card-p.ok, #card-k.ok, #card-ec.ok{
      border: 2px solid rgba(34,197,94,.55);   /* hijau tipis */
      box-shadow: none;
    }

    #card-temp.danger, #card-humi.danger, #card-ph.danger, #card-n.danger, #card-p.danger, #card-k.danger, #card-ec.danger{
      border: 2px solid #ef4444;               /* merah */
      box-shadow: 0 0 0 4px rgba(239,68,68,.15);
    }

  </style>

  @yield('head')
</head>

<body>
@php
  $u = auth()->user();
  $name = $u?->name ?? 'Admin';
  $username = $u?->username ?? 'admin';
  $role = strtolower((string)($u?->role ?? "petani"));
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
        @if($role === 'admin')
        <li class="nav-item">
          <a class="nav-link {{ str_ends_with($path,'users.php') ? 'active' : '' }}" href="/users.php">Kelola User</a>
        </li>
        @endif
      </ul>

      <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
        @yield('top-right')

        <!-- Notifikasi (ambang batas sensor / error sistem) -->
        <div class="dropdown" id="notifWrap" style="display:none">
          <button class="btn btn-ghost position-relative" id="btnNotif" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi">
            <i class="bi bi-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notifBadge" style="display:none">
              0
              <span class="visually-hidden">unread notifications</span>
            </span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end p-0" style="width: 340px; border-radius: 14px; overflow:hidden" aria-labelledby="btnNotif">
            <li class="px-3 py-2" style="background: rgba(15,23,42,.06)">
              <div class="fw-bold">Notifikasi</div>
              <div class="text-muted" style="font-size:.85rem">Ambang batas sensor, status sistem, dan aktivitas penting.</div>
            </li>
            <li><hr class="dropdown-divider my-0"></li>
            <li>
              <div id="notifList" class="px-2" style="max-height: 320px; overflow:auto">
                <div class="text-muted text-center py-3" style="font-size:.9rem">Belum ada notifikasi.</div>
              </div>
            </li>
            <li><hr class="dropdown-divider my-0"></li>
            <li class="px-2 py-2 d-flex justify-content-between align-items-center">
              <small class="text-muted" id="notifUpdated">–</small>
              <button class="btn btn-sm btn-outline-secondary" id="btnMarkRead">Tandai dibaca</button>
            </li>
          </ul>
        </div>

        <div class="text-end me-2 d-none d-lg-block">
          <div class="text-white fw-semibold" style="font-size:.92rem">{{ $name }}</div>
          <small class="text-white-50">&#64;{{ $username }}</small>
        </div>

        <button class="btn btn-ghost" type="button" id="btnLogout" title="Logout">
          <i class="bi bi-box-arrow-right"></i>
        </button>

      </div>
    </div>
  </div>
</nav>

<div class="container-fluid wrap px-3 px-md-4">
  @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Logout global
  const btnLogout = document.getElementById('btnLogout');
  if (btnLogout) btnLogout.addEventListener('click', async () => {
    try { await fetch('/api/logout.php'); } catch(e){}
    window.location.href = '/login.html';
  });
</script>

<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999" id="toastHost"></div>

<script>
  // ===== Notifikasi global (polling) =====
  const notifWrap = document.getElementById('notifWrap');
  const notifBadge = document.getElementById('notifBadge');
  const notifList = document.getElementById('notifList');
  const notifUpdated = document.getElementById('notifUpdated');
  const btnNotif = document.getElementById('btnNotif');
  const toastHost = document.getElementById('toastHost');


  // id -> last_triggered_at (untuk deteksi notifikasi "kejadian baru" tanpa spam)
  let lastTriggeredMap = new Map();
  let notifInitialized = false;

  function esc(s){ return (s ?? '').toString().replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }

  function fmtTime(v){
    if (!v) return '–';
    const d = new Date(v);
    if (Number.isNaN(d.getTime())) return esc(v);
    return d.toLocaleString('id-ID');
  }

  function levelBadge(level){
    if (level === 'danger') return '<span class="badge text-bg-danger me-2">Danger</span>';
    if (level === 'warning') return '<span class="badge text-bg-warning text-dark me-2">Warning</span>';
    return '<span class="badge text-bg-secondary me-2">Info</span>';
  }

  document.getElementById('btnMarkRead')?.addEventListener('click', async () => {
  // Mark ALL unread (termasuk yang tidak tampil karena ketimpa/limit)
  const res = await fetch('/notifications/mark-all-read', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  });

  const j = await res.json();
  console.log('markAllRead:', j);

  // refresh notif & badge
  await fetchNotifs();
});

  function renderNotifs(items){
    if (!items || items.length === 0){
      notifList.innerHTML = '<div class="text-muted text-center py-3" style="font-size:.9rem">Belum ada notifikasi.</div>';
      return;
    }
    notifList.innerHTML = items.map(n => {
      const isUnread = !n.is_read;
      const bg = isUnread ? 'rgba(34,197,94,.10)' : 'transparent';
      const occ = Number(n.occurrences || 1);
      const occHtml = occ > 1 ? `<span class="badge text-bg-light text-dark ms-2">x${occ}</span>` : '';
      return `
        <div class="px-2 py-2" style="border-bottom:1px solid rgba(15,23,42,.08); background:${bg}; border-radius:12px; margin:.35rem 0;">
          <div class="d-flex align-items-start gap-2">
            <div style="padding-top:2px">${levelBadge(n.level)}</div>
            <div class="flex-grow-1">
              <div class="fw-bold" style="font-size:.92rem">${esc(n.title)}${occHtml}</div>
              <div class="text-muted" style="font-size:.86rem">${esc(n.message)}</div>
              <div class="text-muted" style="font-size:.75rem; margin-top:4px">${fmtTime(n.updated_at || n.created_at)}</div>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }

  function showToast(title, message, level){
    if (!toastHost) return;
    const id = 't' + Math.random().toString(16).slice(2);
    const headerClass = level === 'danger' ? 'text-bg-danger' : (level === 'warning' ? 'text-bg-warning text-dark' : 'text-bg-secondary');
    const html = `
      <div class="toast" id="${id}" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header ${headerClass}">
          <i class="bi bi-bell-fill me-2"></i>
          <strong class="me-auto">${esc(title)}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">${esc(message)}</div>
      </div>
    `;
    toastHost.insertAdjacentHTML('beforeend', html);
    const el = document.getElementById(id);
    const t = new bootstrap.Toast(el, { delay: 4500 });
    t.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  }

  async function fetchNotifs(){
    // kalau belum login, endpoint akan 401 => sembunyikan ikon
    try{
      const res = await fetch('/api/notifications.php?limit=8');
      if (!res.ok) {
        if (notifWrap) notifWrap.style.display = 'none';
        return;
      }
      const data = await res.json();
      if (!data || !data.success) return;

      if (notifWrap) notifWrap.style.display = '';

      const unread = Number(data.unread_count || 0);
      if (unread > 0){
        notifBadge.textContent = unread;
        notifBadge.style.display = '';
      } else {
        notifBadge.style.display = 'none';
      }

      const items = data.items || [];
      renderNotifs(items);
      notifUpdated.textContent = 'Update: ' + new Date().toLocaleString('id-ID');

      // Toast untuk "kejadian baru" berdasarkan last_triggered_at
      for (const n of items){
        const trig = n.last_triggered_at || null;
        if (!trig) continue;

        const prev = lastTriggeredMap.get(n.id);

        // saat pertama kali load, cukup simpan state (jangan spam toast)
        if (!notifInitialized){
          lastTriggeredMap.set(n.id, trig);
          continue;
        }

        if (!n.is_read && prev !== trig){
          showToast(n.title, n.message, n.level);
        }
        lastTriggeredMap.set(n.id, trig);
      }

      notifInitialized = true;

      // sync map: buang id yang sudah tidak ada di batch
      const keep = new Set(items.map(x => x.id));
      for (const id of Array.from(lastTriggeredMap.keys())){
        if (!keep.has(id)) lastTriggeredMap.delete(id);
      }
    }catch(e){
      // ignore
    }
  }

  if (btnNotif){
    btnNotif.addEventListener('shown.bs.dropdown', async () => {
      await fetchNotifs();
    });
  }

  // polling 10 detik (sesuai SRS)
  fetchNotifs();
  setInterval(fetchNotifs, 10000);
</script>

@yield('scripts')
</body>
</html>
