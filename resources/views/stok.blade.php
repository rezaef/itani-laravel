@extends('layouts.app')

@section('content')
@php
  $auth = auth()->user();
  $sess = session('user');
  $role = $auth->role ?? ($sess['role'] ?? 'Petani');
@endphp

<div class="container-fluid px-3 px-md-4 py-4">
  <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div>
      <h2 class="fw-bold mb-1">Stok Bibit &amp; Pupuk</h2>
      <div class="text-muted">Kelola stok masuk/keluar + riwayat transaksi.</div>
    </div>

    <div class="text-end">
      <span class="badge text-bg-success">Role: {{ $role }}</span>
    </div>
  </div>

  <div class="row g-3">
    {{-- ============ BIBIT ============ --}}
    <div class="col-lg-7">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h5 class="fw-semibold mb-1">Stok Bibit</h5>
              <div class="text-muted small">Endpoint: <code>/api/seed_stock.php</code></div>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-success btn-sm" onclick="openSeedModal()">Tambah Bibit</button>
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Tipe</th>
                  <th class="text-end">Stok</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody id="seedTbody">
                <tr><td colspan="4" class="text-center text-muted py-4">Loading...</td></tr>
              </tbody>
            </table>
          </div>

          <hr class="my-3">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">Transaksi Bibit</div>
            <div class="text-muted small">Restok (+) / Pakai (-).</div>
          </div>

          <form class="row g-2" onsubmit="return submitSeedAdjust(event)">
            <div class="col-md-4">
              <label class="form-label small mb-1">Bibit</label>
              <select class="form-select" id="seedAdjustId" required></select>
            </div>

            <div class="col-md-4">
              <label class="form-label small mb-1">Periode</label>
              <select class="form-select" id="seedAdjustPeriod">
                <option value="">(opsional)</option>
              </select>
            </div>

            <div class="col-md-2">
              <label class="form-label small mb-1">Delta</label>
              <input type="number" class="form-control" id="seedAdjustDelta" value="-1" required>
            </div>

            <div class="col-md-2 d-grid">
              <label class="form-label small mb-1">&nbsp;</label>
              <button class="btn btn-outline-success" type="submit">Simpan</button>
            </div>

            <div class="col-12">
              <label class="form-label small mb-1">Catatan</label>
              <input type="text" class="form-control" id="seedAdjustNotes" placeholder="Contoh: Tanam okra bedeng A / Restok bibit">
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- ============ PUPUK ============ --}}
    <div class="col-lg-5">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
              <h5 class="fw-semibold mb-1">Stok Pupuk</h5>
              <div class="text-muted small">Endpoint: <code>/api/fertilizer_stock.php</code></div>
            </div>

            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-success btn-sm" onclick="openFertModal()">Tambah Pupuk</button>
            </div>
          </div>

          <div class="table-responsive mt-3">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Tipe</th>
                  <th class="text-end">Stok (kg)</th>
                  <th class="text-end">Aksi</th>
                </tr>
              </thead>
              <tbody id="fertTbody">
                <tr><td colspan="4" class="text-center text-muted py-4">Loading...</td></tr>
              </tbody>
            </table>
          </div>

          <hr class="my-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">Transaksi Pupuk</div>
              <div class="text-muted small">Restok (+) / Pakai (-)</div>
            </div>

            <form class="row g-2" onsubmit="return submitFertAdjust(event)">
              <div class="col-md-6">
                <label class="form-label small mb-1">Pupuk</label>
                <select class="form-select" id="fertAdjustId" required></select>
              </div>

              <div class="col-md-3">
                <label class="form-label small mb-1">Delta</label>
                <input type="number" class="form-control" id="fertAdjustDelta" value="1" required>
              </div>

              <div class="col-md-3 d-grid">
                <label class="form-label small mb-1">&nbsp;</label>
                <button class="btn btn-outline-success" type="submit">Simpan</button>
              </div>

              <div class="col-12">
                <label class="form-label small mb-1">Catatan</label>
                <input type="text" class="form-control" id="fertAdjustNotes" placeholder="Contoh: Restok 5kg / Pemupukan bedeng B">
              </div>
            </form>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- =================== MODAL: SEED CREATE/EDIT =================== --}}
<div class="modal fade" id="seedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" onsubmit="return submitSeedModal(event)">
      <div class="modal-header">
        <h5 class="modal-title" id="seedModalTitle">Tambah Bibit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="seedModalId">

        <div class="mb-2">
          <label class="form-label">Nama Bibit</label>
          <input class="form-control" id="seedModalName" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Tipe Bibit</label>
          <input class="form-control" id="seedModalType" placeholder="Sayur/Buah/dll">
        </div>

        <div class="mb-2" id="seedModalQtyWrap">
          <label class="form-label">Stok Awal</label>
          <input type="number" min="0" class="form-control" id="seedModalQty" value="0">
          <div class="form-text">Hanya dipakai saat tambah bibit baru.</div>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- =================== MODAL: FERT CREATE/EDIT =================== --}}
<div class="modal fade" id="fertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" onsubmit="return submitFertModal(event)">
      <div class="modal-header">
        <h5 class="modal-title" id="fertModalTitle">Tambah Pupuk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="fertModalId">

        <div class="mb-2">
          <label class="form-label">Nama Pupuk</label>
          <input class="form-control" id="fertModalName" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Tipe Pupuk</label>
          <input class="form-control" id="fertModalType" placeholder="Padat/Cair">
        </div>

        <div class="mb-2" id="fertModalQtyWrap">
          <label class="form-label">Stok Awal (kg)</label>
          <input type="number" min="0" class="form-control" id="fertModalQty" value="0">
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

{{-- =================== MODAL: LOGS =================== --}}
<div class="modal fade" id="logModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logModalTitle">Riwayat</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>Waktu</th>
                <th>Type</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Periode</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody id="logTbody">
              <tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="text-muted small">Menampilkan 50 transaksi terakhir.</div>
      </div>
    </div>
  </div>
</div>

{{-- ====== CSRF + SCRIPT ====== --}}
<script>
  const ROLE = @json($role);
  const CSRF = @json(csrf_token());

  const API_SEEDS = @json(url('api/seed_stock.php'));
  const API_FERTS = @json(url('api/fertilizer_stock.php'));
  const API_PERIODS = @json(url('api/periods.php'));

  let seedCache = [];
  let fertCache = [];

  function esc(s){ return (s ?? '').toString()
    .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
    .replaceAll('"','&quot;').replaceAll("'","&#039;"); }

  async function apiGet(url){
    const r = await fetch(url, { credentials: 'same-origin' });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'API error');
    return j.data || [];
  }

  async function apiPost(url, payload){
    const r = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': CSRF,
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload)
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'API error');
    return j;
  }

  // ===== LOAD PERIODS (optional) =====
  async function loadPeriods(){
    try {
      const periods = await apiGet(API_PERIODS);
      const el = document.getElementById('seedAdjustPeriod');
      const opts = periods.map(p => `<option value="${p.id}">${esc(p.nama_periode)} (${esc(p.status)})</option>`).join('');
      el.innerHTML = `<option value="">(opsional)</option>` + opts;
    } catch(e){
      // silent: periods belum perlu untuk tampil
    }
  }

  // ===== SEEDS =====
  async function loadSeeds(){
    try {
      seedCache = await apiGet(API_SEEDS);

      const tbody = document.getElementById('seedTbody');
      if (!seedCache.length){
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Belum ada data bibit.</td></tr>`;
      } else {
        tbody.innerHTML = seedCache.map(s => {
          const btnLogs = `<button class="btn btn-outline-secondary btn-sm" onclick="openLogs('seed', ${s.id}, '${esc(s.name)}')">Riwayat</button>`;
          const btnEdit = `<button class="btn btn-outline-primary btn-sm" onclick="openSeedModal(${s.id})">Edit</button>
               <button class="btn btn-outline-danger btn-sm" onclick="deleteSeed(${s.id})">Hapus</button>`;

          // quick IN/OUT
          const btnQuick = `<button class="btn btn-outline-success btn-sm" onclick="quickSeed(${s.id}, 1)">+1</button>`;

          const btnUse = `<button class="btn btn-outline-danger btn-sm" onclick="quickSeed(${s.id}, -1)">-1</button>`;

          return `
            <tr>
              <td class="fw-semibold">${esc(s.name)}</td>
              <td class="text-muted">${esc(s.type || '-')}</td>
              <td class="text-end fw-semibold">${Number(s.qty ?? 0)}</td>
              <td class="text-end">
                <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                  ${btnLogs}
                  ${btnQuick}
                  ${btnUse}
                  ${btnEdit}
                </div>
              </td>
            </tr>
          `;
        }).join('');
      }

      // select adjust
      const sel = document.getElementById('seedAdjustId');
      sel.innerHTML = seedCache.map(s => `<option value="${s.id}">${esc(s.name)}</option>`).join('');
    } catch(e){
document.getElementById('seedTbody').innerHTML =
        `<tr><td colspan="4" class="text-center text-danger py-4">${esc(e.message)}</td></tr>`;
    }
  }

  async function submitSeedAdjust(ev){
    ev.preventDefault();
    const id = Number(document.getElementById('seedAdjustId').value);
    const delta = Number(document.getElementById('seedAdjustDelta').value);
    const notes = document.getElementById('seedAdjustNotes').value;
    const period_id = document.getElementById('seedAdjustPeriod').value
      ? Number(document.getElementById('seedAdjustPeriod').value)
      : null;

    try {
      await apiPost(API_SEEDS, { action:'adjust', id, delta, period_id, notes });
      document.getElementById('seedAdjustNotes').value = '';
      await loadSeeds();
    } catch(e){
      alert(e.message);
    }
    return false;
  }

  async function quickSeed(id, delta){
    try {
      await apiPost(API_SEEDS, { action:'adjust', id:Number(id), delta:Number(delta) });
      await loadSeeds();
    } catch(e){ alert(e.message); }
  }

  // modal seed
  let seedModal, fertModal, logModal;
  function openSeedModal(id=null){
    document.getElementById('seedModalId').value = id || '';
    const wrapQty = document.getElementById('seedModalQtyWrap');

    if (!id){
      document.getElementById('seedModalTitle').textContent = 'Tambah Bibit';
      document.getElementById('seedModalName').value = '';
      document.getElementById('seedModalType').value = '';
      document.getElementById('seedModalQty').value = 0;
      wrapQty.style.display = '';
    } else {
      const s = seedCache.find(x => Number(x.id) === Number(id));
      document.getElementById('seedModalTitle').textContent = 'Edit Bibit';
      document.getElementById('seedModalName').value = s?.name || '';
      document.getElementById('seedModalType').value = s?.type || '';
      wrapQty.style.display = 'none'; // edit tidak set stok awal
    }
    seedModal.show();
  }

  async function submitSeedModal(ev){
    ev.preventDefault();
    const id = document.getElementById('seedModalId').value;

    try {
      if (!id){
        await apiPost(API_SEEDS, {
          action: 'create',
          seed_name: document.getElementById('seedModalName').value,
          seed_type: document.getElementById('seedModalType').value,
          qty: Number(document.getElementById('seedModalQty').value || 0),
        });
      } else {
        await apiPost(API_SEEDS, {
          action: 'update',
          id: Number(id),
          seed_name: document.getElementById('seedModalName').value,
          seed_type: document.getElementById('seedModalType').value,
        });
      }

      seedModal.hide();
      await loadSeeds();
    } catch(e){
      alert(e.message);
    }
    return false;
  }

  async function deleteSeed(id){
    if (!confirm('Hapus bibit ini?')) return;
    try {
      await apiPost(API_SEEDS, { action:'delete', id:Number(id) });
      await loadSeeds();
    } catch(e){ alert(e.message); }
  }

  // ===== FERTS =====
  async function loadFerts(){
    try {
      fertCache = await apiGet(API_FERTS);

      const tbody = document.getElementById('fertTbody');
      if (!fertCache.length){
        tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Belum ada data pupuk.</td></tr>`;
      } else {
        tbody.innerHTML = fertCache.map(f => {
          const btnLogs = `<button class="btn btn-outline-secondary btn-sm" onclick="openLogs('fert', ${f.id}, '${esc(f.name)}')">Riwayat</button>`;
          const btnEdit = `<button class="btn btn-outline-primary btn-sm" onclick="openFertModal(${f.id})">Edit</button>
               <button class="btn btn-outline-danger btn-sm" onclick="deleteFert(${f.id})">Hapus</button>`
            

          return `
            <tr>
              <td class="fw-semibold">${esc(f.name)}</td>
              <td class="text-muted">${esc(f.type || '-')}</td>
              <td class="text-end fw-semibold">${Number(f.qty ?? 0)}</td>
              <td class="text-end">
                <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                  ${btnLogs}
                  ${btnEdit}
                </div>
              </td>
            </tr>
          `;
        }).join('');
      }

      const sel = document.getElementById('fertAdjustId');
      if (sel) sel.innerHTML = fertCache.map(f => `<option value="${f.id}">${esc(f.name)}</option>`).join('');
    } catch(e){
document.getElementById('fertTbody').innerHTML =
        `<tr><td colspan="4" class="text-center text-danger py-4">${esc(e.message)}</td></tr>`;
    }
  }

  async function submitFertAdjust(ev){
    ev.preventDefault();
    const id = Number(document.getElementById('fertAdjustId').value);
    const delta = Number(document.getElementById('fertAdjustDelta').value);
    const notes = document.getElementById('fertAdjustNotes').value;

    try {
      await apiPost(API_FERTS, { action:'adjust', id, delta, notes });
      document.getElementById('fertAdjustNotes').value = '';
      await loadFerts();
    } catch(e){ alert(e.message); }
    return false;
  }

  function openFertModal(id=null){
    document.getElementById('fertModalId').value = id || '';
    const wrapQty = document.getElementById('fertModalQtyWrap');

    if (!id){
      document.getElementById('fertModalTitle').textContent = 'Tambah Pupuk';
      document.getElementById('fertModalName').value = '';
      document.getElementById('fertModalType').value = '';
      document.getElementById('fertModalQty').value = 0;
      wrapQty.style.display = '';
    } else {
      const f = fertCache.find(x => Number(x.id) === Number(id));
      document.getElementById('fertModalTitle').textContent = 'Edit Pupuk';
      document.getElementById('fertModalName').value = f?.name || '';
      document.getElementById('fertModalType').value = f?.type || '';
      wrapQty.style.display = 'none';
    }
    fertModal.show();
  }

  async function submitFertModal(ev){
    ev.preventDefault();
    const id = document.getElementById('fertModalId').value;

    try {
      if (!id){
        await apiPost(API_FERTS, {
          action: 'create',
          fert_name: document.getElementById('fertModalName').value,
          fert_type: document.getElementById('fertModalType').value,
          qty: Number(document.getElementById('fertModalQty').value || 0),
        });
      } else {
        await apiPost(API_FERTS, {
          action: 'update',
          id: Number(id),
          fert_name: document.getElementById('fertModalName').value,
          fert_type: document.getElementById('fertModalType').value,
        });
      }

      fertModal.hide();
      await loadFerts();
    } catch(e){ alert(e.message); }
    return false;
  }

  async function deleteFert(id){
    if (!confirm('Hapus pupuk ini?')) return;
    try {
      await apiPost(API_FERTS, { action:'delete', id:Number(id) });
      await loadFerts();
    } catch(e){ alert(e.message); }
  }

  // ===== LOGS =====
  async function openLogs(kind, id, name){
    document.getElementById('logModalTitle').textContent = `Riwayat: ${name}`;
    document.getElementById('logTbody').innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Loading...</td></tr>`;
    logModal.show();

    try {
      const api = (kind === 'seed') ? API_SEEDS : API_FERTS;
      const res = await apiPost(api, { action:'logs', id:Number(id) });
      const rows = res.data || [];

      if (!rows.length){
        document.getElementById('logTbody').innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>`;
        return;
      }

      document.getElementById('logTbody').innerHTML = rows.map(r => `
        <tr>
          <td class="text-muted small">${esc(r.created_at || '-')}</td>
          <td><span class="badge text-bg-secondary">${esc(r.type)}</span></td>
          <td class="text-end fw-semibold">${Number(r.quantity ?? 0)}</td>
          <td class="text-end text-muted small">${r.period_id ? Number(r.period_id) : '-'}</td>
          <td class="small">${esc(r.notes || '')}</td>
        </tr>
      `).join('');
    } catch(e){
      document.getElementById('logTbody').innerHTML =
        `<tr><td colspan="5" class="text-center text-danger py-4">${esc(e.message)}</td></tr>`;
    }
  }

  document.addEventListener('DOMContentLoaded', async () => {
    seedModal = new bootstrap.Modal(document.getElementById('seedModal'));
    fertModal = new bootstrap.Modal(document.getElementById('fertModal'));
    logModal  = new bootstrap.Modal(document.getElementById('logModal'));

    await loadPeriods();
    await loadSeeds();
    await loadFerts();
  });
</script>
@endsection
