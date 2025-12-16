@extends('layouts.app')
@section('title','ITani — Kelola User')

@section('content')
  <div class="d-flex align-items-end justify-content-between gap-3 mb-3">
    <div>
      <h3 class="fw-bold mb-1">Kelola User</h3>
      <div class="muted">Tambah, ubah, dan hapus user. User baru default password: <b>password123</b></div>
    </div>
    <button class="btn btn-success fw-bold" id="btnAdd" style="border-radius:14px;padding:.65rem 1rem;">
      <i class="bi bi-plus-lg me-1"></i> Tambah User
    </button>
  </div>

  <div class="cardx">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
        <div class="input-group" style="max-width:360px;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input class="form-control" id="q" placeholder="Cari nama / username..." />
        </div>
        <button class="btn btn-outline-secondary" id="btnReload"><i class="bi bi-arrow-clockwise"></i></button>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Username</th>
              <th>Role</th>
              <th>Created</th>
              <th style="width:160px;">Aksi</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <tr><td colspan="5" class="text-center muted py-4">Memuat...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="border-radius:18px;">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalTitle">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger d-none" id="modalErr"></div>

          <input type="hidden" id="uid" />

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama</label>
              <input class="form-control" id="name" placeholder="Nama lengkap" />
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Username</label>
              <input class="form-control" id="username" placeholder="username" />
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Role</label>
              <select class="form-select" id="role">
                <option value="Admin">Admin</option>
                <option value="Petani">Petani</option>
                <option value="Viewer">Viewer</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Password (opsional saat edit)</label>
              <input class="form-control" id="password" placeholder="Kosongkan jika tidak diubah" />
            </div>
          </div>

          <div class="mt-3 muted" style="font-size:.9rem">
            Catatan: saat tambah user, bila password kosong maka otomatis diset ke <b>password123</b>.
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-success fw-bold" id="btnSave">Simpan</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const tbody = document.getElementById('tbody');
  const q = document.getElementById('q');

  const modalEl = document.getElementById('userModal');
  const modal = new bootstrap.Modal(modalEl);
  const modalTitle = document.getElementById('modalTitle');
  const modalErr = document.getElementById('modalErr');

  const uid = document.getElementById('uid');
  const name = document.getElementById('name');
  const username = document.getElementById('username');
  const role = document.getElementById('role');
  const password = document.getElementById('password');

  let users = [];

  function showModalError(msg){
    modalErr.textContent = msg || 'Terjadi kesalahan.';
    modalErr.classList.remove('d-none');
  }
  function clearModalError(){
    modalErr.classList.add('d-none');
    modalErr.textContent = '';
  }

  function openAdd(){
    clearModalError();
    modalTitle.textContent = "Tambah User";
    uid.value = "";
    name.value = "";
    username.value = "";
    role.value = "Viewer";
    password.value = "";
    modal.show();
  }

  function openEdit(u){
    clearModalError();
    modalTitle.textContent = "Edit User";
    uid.value = u.id;
    name.value = u.name || "";
    username.value = u.username || "";
    role.value = u.role || "Viewer";
    password.value = "";
    modal.show();
  }

  function esc(s){ return String(s ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;'); }

  function render(){
    const keyword = q.value.trim().toLowerCase();
    const rows = keyword
      ? users.filter(u => (u.name||'').toLowerCase().includes(keyword) || (u.username||'').toLowerCase().includes(keyword))
      : users;

    if (!rows.length){
      tbody.innerHTML = `<tr><td colspan="5" class="text-center muted py-4">Tidak ada data user.</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(u => `
      <tr>
        <td class="fw-bold">${esc(u.name)}</td>
        <td>@${esc(u.username)}</td>
        <td><span class="badge text-bg-secondary">${esc(u.role)}</span></td>
        <td class="muted" style="font-size:.9rem">${esc(u.created_at || '-')}</td>
        <td class="text-nowrap">
          <button class="btn btn-sm btn-outline-primary" data-act="edit" data-id="${u.id}"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" data-act="del" data-id="${u.id}"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    `).join('');
  }

  async function load(){
    tbody.innerHTML = `<tr><td colspan="5" class="text-center muted py-4">Memuat...</td></tr>`;
    const res = await fetch('/api/users.php');
    users = await res.json().catch(()=> []);
    if (!Array.isArray(users)) users = [];
    render();
  }

  document.getElementById('btnAdd').addEventListener('click', openAdd);
  document.getElementById('btnReload').addEventListener('click', load);
  q.addEventListener('input', render);

  document.getElementById('btnSave').addEventListener('click', async () => {
    clearModalError();
    const isEdit = !!uid.value;

    const payload = {
      name: name.value.trim(),
      username: username.value.trim(),
      role: role.value,
      password: password.value.trim()
    };

    if (!payload.name || !payload.username) return showModalError('Nama dan username wajib diisi.');

    try{
      if (!isEdit){
        const res = await fetch('/api/users.php', {
          method:'POST',
          headers:{ 'Content-Type':'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data.error || `Error ${res.status}`);
      } else {
        const res = await fetch('/api/users.php?id=' + encodeURIComponent(uid.value), {
          method:'PUT',
          headers:{ 'Content-Type':'application/json' },
          body: JSON.stringify(payload)
        });
        const data = await res.json().catch(()=> ({}));
        if (!res.ok) throw new Error(data.error || `Error ${res.status}`);
      }

      modal.hide();
      await load();
    }catch(e){
      showModalError(e.message);
    }
  });

  tbody.addEventListener('click', async (e) => {
    const btn = e.target.closest('button[data-act]');
    if (!btn) return;

    const act = btn.getAttribute('data-act');
    const id = Number(btn.getAttribute('data-id'));
    const u = users.find(x => Number(x.id) === id);

    if (act === 'edit' && u) return openEdit(u);

    if (act === 'del'){
      if (!confirm('Hapus user ini?')) return;
      const res = await fetch('/api/users.php?id=' + encodeURIComponent(id), { method:'DELETE' });
      const data = await res.json().catch(()=> ({}));
      if (!res.ok) return alert(data.error || `Error ${res.status}`);
      load();
    }
  });

  load();
</script>
@endsection
