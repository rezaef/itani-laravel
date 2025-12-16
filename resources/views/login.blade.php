@extends('layouts.auth')

@section('title','Login — ITani')

@section('content')
<div class="auth-card" id="card">
  <div class="p-4 d-flex align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="rounded-4 d-grid place-items-center" style="width:44px;height:44px;background:linear-gradient(135deg, rgba(34,197,94,.18), rgba(99,102,241,.18));border:1px solid var(--border);">
        <i class="bi bi-droplet-half fs-4"></i>
      </div>
      <div>
        <div class="h5 fw-bold mb-0">Login ITani</div>
        <div class="text-muted" style="color:var(--muted)!important">Masuk menggunakan username dan password.</div>
      </div>
    </div>

    <button class="btn btn-ghost" type="button" id="themeBtn" aria-label="Toggle tema">
      <i class="bi bi-moon-stars"></i>
    </button>
  </div>

  <div class="px-4 pb-4">
    <div class="border-top" style="border-color:var(--border)!important"></div>

    <div id="errBox" class="alert alert-danger d-none mt-3" role="alert"></div>

    <form id="loginForm" class="mt-3" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" placeholder="contoh: admin" required />
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" placeholder="••••••••" required />
          <button class="btn btn-outline-secondary" type="button" id="togglePass">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button class="btn btn-success w-100 fw-bold" id="btnLogin" type="submit" style="border-radius:14px;padding:.75rem 1rem;">
        <span class="me-2 d-none" id="spin"><span class="spinner-border spinner-border-sm" aria-hidden="true"></span></span>
        Masuk
      </button>
    </form>

    <div class="mt-3 p-3 rounded-4" style="border:1px dashed var(--border); background:rgba(255,255,255,.45);">
      <div class="d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle mt-1"></i>
        <div class="text-muted" style="color:var(--muted)!important">
          User baru dari menu <b>Kelola User</b> memakai password default
          <span class="badge text-bg-light border" style="border-color:var(--border)!important">password123</span>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const form = document.getElementById('loginForm');
  const errBox = document.getElementById('errBox');
  const btn = document.getElementById('btnLogin');
  const spin = document.getElementById('spin');

  function setLoading(v){
    btn.disabled = v;
    spin.classList.toggle('d-none', !v);
  }
  function showError(msg){
    errBox.textContent = msg || 'Terjadi kesalahan.';
    errBox.classList.remove('d-none');
  }
  function clearError(){
    errBox.classList.add('d-none');
    errBox.textContent = '';
  }

  document.getElementById('togglePass').addEventListener('click', () => {
    const inp = document.getElementById('password');
    const icon = document.querySelector('#togglePass i');
    const isPass = inp.type === 'password';
    inp.type = isPass ? 'text' : 'password';
    icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clearError();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;

    if (!username || !password) return showError('Username dan password wajib diisi.');

    setLoading(true);
    try{
      const res = await fetch('/api/login.php', {
        method:'POST',
        headers:{ 'Content-Type':'application/json' },
        credentials:'same-origin',
        body: JSON.stringify({ username, password })
      });

      let data = null;
      try { data = await res.json(); } catch(_){}

      if (!res.ok) {
        const msg = (data && (data.error || data.message)) || (res.status === 419
          ? 'CSRF terblokir (419).'
          : `Login gagal (${res.status}).`);
        return showError(msg);
      }

      if (!data || data.success !== true) return showError((data && data.error) || 'Login gagal.');

      window.location.href = '/index.php';
    }catch(e){
      showError('Tidak bisa terhubung ke server.');
    }finally{
      setLoading(false);
    }
  });

  // set icon sesuai theme (layout auth sudah applyTheme)
  const t = document.documentElement.getAttribute('data-theme');
  const themeBtn = document.getElementById('themeBtn');
  if (themeBtn) themeBtn.innerHTML = t === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
  themeBtn.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    localStorage.setItem('itani_theme', next);
    location.reload(); // simpel & aman untuk login page
  });
</script>
@endsection
