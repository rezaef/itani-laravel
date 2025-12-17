@extends('layouts.auth')

@section('title','Login — ITani')

@section('content')
<div class="auth-card" id="card">
  <div class="p-4 d-flex align-items-center justify-content-between gap-3">
    <div class="d-flex align-items-center gap-3">
      <div class="brand-mark" aria-hidden="true">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
          <defs>
            <linearGradient id="itaniG" x1="3" y1="3" x2="21" y2="21">
              <stop stop-color="#22c55e"/>
              <stop offset="1" stop-color="#6366f1"/>
            </linearGradient>
          </defs>

          <!-- bentuk daun -->
          <path d="M20 4c-6.5.6-11.6 3.7-14.4 8.6C4.4 14.8 4 16.6 4 18c0 1.1.9 2 2 2
                  1.4 0 3.2-.4 5.4-1.6C16.3 15.6 19.4 10.5 20 4Z"
                fill="url(#itaniG)" opacity="0.95"/>

          <!-- urat daun -->
          <path d="M7.2 16.8c3.4-3.5 7.2-6.1 11.2-7.8"
                stroke="rgba(255,255,255,.90)" stroke-width="1.7" stroke-linecap="round"/>

          <!-- tetes air kecil -->
          <path d="M8.2 10.1c.8 1 .9 1.7.9 2.1a1.6 1.6 0 1 1-3.2 0c0-.4.1-1.1.9-2.1.3-.4.5-.7.7-1 .2.3.4.6.7 1Z"
                fill="rgba(255,255,255,.85)"/>
        </svg>
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

    <div class="mt-3 p-3 hint-box">
      <div class="d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle mt-1"></i>
        <div>
          User baru dari menu <b>Kelola User</b> memakai password default
          <span class="hint-pill">password123</span>
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
