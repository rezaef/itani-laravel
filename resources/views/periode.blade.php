@extends('layouts.app')
@section('title','ITani — Periode Tanam')

@section('content')
  <div class="d-flex align-items-end justify-content-between gap-3 mb-3">
    <div>
      <h3 class="fw-bold mb-1">Periode Tanam</h3>
      <div class="muted">Kelola periode tanam, status, set periode aktif, dan hasil panen per periode.</div>
    </div>
    <button class="btn btn-success fw-bold" id="btnAdd" style="border-radius:14px;padding:.65rem 1rem;">
      <i class="bi bi-plus-lg me-1"></i> Tambah Periode
    </button>
  </div>

  <div class="cardx">
    <div class="card-body">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <div class="d-flex align-items-center gap-2">
          <select class="form-select" id="filterStatus" style="max-width:220px;">
            <option value="">Semua status</option>
            <option value="planning">planning</option>
            <option value="berjalan">berjalan</option>
            <option value="selesai">selesai</option>
            <option value="gagal">gagal</option>
          </select>
          <button class="btn btn-outline-secondary" id="btnReload"><i class="bi bi-arrow-clockwise"></i></button>
        </div>
        <small class="muted" id="infoCount">0 data</small>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Tanggal</th>
              <th>Status</th>
              <th>Aktif</th>
              <th>Panen</th>
              <th style="width:300px;">Aksi</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <tr><td colspan="6" class="text-center muted py-4">Memuat...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Period -->
  <div class="modal fade" id="periodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content" style="border-radius:18px;">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalTitle">Tambah Periode</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger d-none" id="modalErr"></div>

          <input type="hidden" id="pid" />

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama periode</label>
              <input class="form-control" id="nama_periode" placeholder="contoh: Periode Tanam 1" />
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Mulai</label>
              <input class="form-control" id="tanggal_mulai" type="date" />
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Selesai</label>
              <input class="form-control" id="tanggal_selesai" type="date" />
            </div>
            <div class="col-md-12">
              <label class="form-label fw-semibold">Deskripsi</label>
              <textarea class="form-control" id="deskripsi" rows="3" placeholder="Opsional..."></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select" id="status">
                <option value="planning">planning</option>
                <option value="berjalan">berjalan</option>
                <option value="selesai">selesai</option>
                <option value="gagal">gagal</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-success fw-bold" id="btnSave">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Harvest -->
  <div class="modal fade" id="harvestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="border-radius:18px;">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="harvestTitle">Hasil Panen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-danger d-none" id="harvestErr"></div>

          <input type="hidden" id="harvest_periode_id">

          <div id="harvestList" class="d-grid gap-2 mb-3"></div>

          <div class="border rounded-3 p-3">
            <div class="fw-bold mb-2">Tambah Panen</div>
            <div class="row g-2">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Tanggal</label>
                <input class="form-control" id="hp_tanggal" type="date">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Jenis Tanaman</label>
                <input class="form-control" id="hp_jenis" placeholder="okra (opsional)">
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Jumlah</label>
                <input class="form-control" id="hp_jumlah" type="number" step="0.01" placeholder="mis. 2.5">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Catatan</label>
                <input class="form-control" id="hp_catatan" placeholder="opsional">
              </div>
              <div class="col-12 d-flex justify-content-end mt-2">
                <button class="btn btn-success fw-bold" id="btnHarvestSave">
                  <i class="bi bi-plus-lg me-1"></i> Simpan Panen
                </button>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
<script>
  const tbody = document.getElementById('tbody');
  const infoCount = document.getElementById('infoCount');
  const filterStatus = document.getElementById('filterStatus');

  const modalEl = document.getElementById('periodModal');
  const modal = new bootstrap.Modal(modalEl);
  const modalTitle = document.getElementById('modalTitle');
  const modalErr = document.getElementById('modalErr');

  const pid = document.getElementById('pid');
  const nama_periode = document.getElementById('nama_periode');
  const tanggal_mulai = document.getElementById('tanggal_mulai');
  const tanggal_selesai = document.getElementById('tanggal_selesai');
  const deskripsi = document.getElementById('deskripsi');
  const status = document.getElementById('status');

  // Harvest modal
  const harvestModal = new bootstrap.Modal(document.getElementById('harvestModal'));
  const harvestTitle = document.getElementById('harvestTitle');
  const harvestErr = document.getElementById('harvestErr');
  const harvestList = document.getElementById('harvestList');
  const harvest_periode_id = document.getElementById('harvest_periode_id');

  const hp_tanggal = document.getElementById('hp_tanggal');
  const hp_jenis = document.getElementById('hp_jenis');
  const hp_jumlah = document.getElementById('hp_jumlah');
  const hp_catatan = document.getElementById('hp_catatan');
  const btnHarvestSave = document.getElementById('btnHarvestSave');

  let periods = [];
  let currentHarvestPeriod = null;

  function badgeStatus(s){
    const map = { planning:'secondary', berjalan:'success', selesai:'primary', gagal:'danger' };
    const c = map[s] || 'secondary';
    return `<span class="badge text-bg-${c}">${s}</span>`;
  }

  function showModalError(msg){
    modalErr.textContent = msg || 'Terjadi kesalahan.';
    modalErr.classList.remove('d-none');
  }
  function clearModalError(){
    modalErr.classList.add('d-none');
    modalErr.textContent = '';
  }

  function showHarvestError(msg){
    harvestErr.textContent = msg || 'Terjadi kesalahan.';
    harvestErr.classList.remove('d-none');
  }
  function clearHarvestError(){
    harvestErr.classList.add('d-none');
    harvestErr.textContent = '';
  }

  function openAdd(){
    clearModalError();
    modalTitle.textContent = "Tambah Periode";
    pid.value = "";
    nama_periode.value = "";
    tanggal_mulai.value = "";
    tanggal_selesai.value = "";
    deskripsi.value = "";
    status.value = "planning";
    modal.show();
  }

  function openEdit(p){
    clearModalError();
    modalTitle.textContent = "Edit Periode";
    pid.value = p.id;
    nama_periode.value = p.nama_periode || "";
    tanggal_mulai.value = (p.tanggal_mulai || "").slice(0,10);
    tanggal_selesai.value = (p.tanggal_selesai || "").slice(0,10);
    deskripsi.value = p.deskripsi || "";
    status.value = p.status || "planning";
    modal.show();
  }

  async function apiPostPeriods(payload){
    const res = await fetch('/api/periods.php', {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(()=> ({}));
    if (!res.ok || data.success === false) throw new Error(data.error || `Error ${res.status}`);
    return data;
  }

  async function apiGetHarvests(periodeId){
    const res = await fetch(`/api/harvests.php?periode_id=${encodeURIComponent(periodeId)}`);
    const data = await res.json().catch(()=> ({}));
    if (!res.ok || data.success === false) throw new Error(data.error || `Error ${res.status}`);
    return data;
  }

  async function apiPostHarvests(payload){
    const res = await fetch('/api/harvests.php', {
      method:'POST',
      headers:{ 'Content-Type':'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json().catch(()=> ({}));
    if (!res.ok || data.success === false) throw new Error(data.error || `Error ${res.status}`);
    return data;
  }

  function render(){
    const f = filterStatus.value;
    const rows = f ? periods.filter(p => (p.status||'') === f) : periods;

    infoCount.textContent = `${rows.length} data`;

    if (!rows.length){
      tbody.innerHTML = `<tr><td colspan="6" class="text-center muted py-4">Belum ada periode.</td></tr>`;
      return;
    }

    tbody.innerHTML = rows.map(p => {
      const aktif = (Number(p.is_active) === 1)
        ? `<span class="badge text-bg-success">aktif</span>`
        : `<span class="badge text-bg-secondary">-</span>`;

      const range = `${(p.tanggal_mulai||'-').slice(0,10)} → ${(p.tanggal_selesai||'-').slice(0,10)}`;
      const hc = Number(p.harvest_count || 0);

      return `
        <tr>
          <td>
            <div class="fw-bold">${p.nama_periode ?? '-'}</div>
            <div class="muted" style="font-size:.85rem">${p.deskripsi ? p.deskripsi : ''}</div>
          </td>
          <td>${range}</td>
          <td>${badgeStatus(p.status || 'planning')}</td>
          <td>${aktif}</td>
          <td class="text-nowrap">
            <span class="badge text-bg-secondary">${hc} data</span>
            <button class="btn btn-sm btn-outline-success ms-2" data-act="harvest" data-id="${p.id}">
              <i class="bi bi-basket2"></i> Kelola
            </button>
          </td>
          <td class="text-nowrap">
            <button class="btn btn-sm btn-outline-primary" data-act="edit" data-id="${p.id}"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" data-act="del" data-id="${p.id}"><i class="bi bi-trash"></i></button>
            <button class="btn btn-sm btn-outline-success" data-act="active" data-id="${p.id}"><i class="bi bi-check2-circle"></i> Set Aktif</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  async function load(){
    tbody.innerHTML = `<tr><td colspan="6" class="text-center muted py-4">Memuat...</td></tr>`;
    const res = await fetch('/api/periods.php');
    const data = await res.json().catch(()=> ({}));
    periods = (data && data.success && Array.isArray(data.data)) ? data.data : [];
    render();
  }

  function renderHarvest(items){
    harvestList.innerHTML = "";
    if(!items || items.length === 0){
      harvestList.innerHTML = `<div class="text-center muted py-2">Belum ada hasil panen.</div>`;
      return;
    }

    harvestList.innerHTML = items.map(h => {
      const tanggal = (h.tanggal_panen || '-').slice(0,10);
      const jumlah = (h.jumlah_panen ?? '-');
      const jenis = h.jenis_tanaman ? `<div class="muted" style="font-size:.85rem">${h.jenis_tanaman}</div>` : '';
      const cat = h.catatan ? `<div class="muted" style="font-size:.85rem">— ${h.catatan}</div>` : '';

      return `
        <div class="border rounded-3 p-2 d-flex justify-content-between align-items-start gap-2">
          <div>
            <div class="fw-bold">${tanggal} <span class="badge text-bg-success ms-1">${jumlah}</span></div>
            ${jenis}
            ${cat}
          </div>
          <div class="text-nowrap">
            <button class="btn btn-sm btn-outline-danger" data-hdel="${h.id}">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      `;
    }).join('');
  }

  async function openHarvest(periodId){
    clearHarvestError();
    currentHarvestPeriod = periodId;
    harvest_periode_id.value = periodId;

    const p = periods.find(x => Number(x.id) === Number(periodId));
    harvestTitle.textContent = `Hasil Panen — ${p?.nama_periode ?? 'Periode'}`;

    try{
      const data = await apiGetHarvests(periodId);
      renderHarvest(data.data || []);
      harvestModal.show();
    }catch(e){
      showHarvestError(e.message);
      harvestModal.show();
    }
  }

  document.getElementById('btnAdd').addEventListener('click', openAdd);
  document.getElementById('btnReload').addEventListener('click', load);
  filterStatus.addEventListener('change', render);

  document.getElementById('btnSave').addEventListener('click', async () => {
    clearModalError();

    const payload = {
      action: pid.value ? 'update' : 'create',
      id: pid.value ? Number(pid.value) : undefined,
      nama_periode: nama_periode.value.trim(),
      tanggal_mulai: tanggal_mulai.value,
      tanggal_selesai: tanggal_selesai.value || null,
      deskripsi: deskripsi.value.trim() || null,
      status: status.value
    };

    try{
      await apiPostPeriods(payload);
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
    const p = periods.find(x => Number(x.id) === id);

    if (act === 'edit' && p) return openEdit(p);

    if (act === 'del'){
      if (!confirm('Hapus periode ini? (panen terkait juga akan terhapus)')) return;
      try{ await apiPostPeriods({ action:'delete', id }); await load(); }catch(err){ alert(err.message); }
    }

    if (act === 'active'){
      try{ await apiPostPeriods({ action:'set_active', id }); await load(); }catch(err){ alert(err.message); }
    }

    if (act === 'harvest'){
      return openHarvest(id);
    }
  });

  // delete harvest click (delegation)
  harvestList.addEventListener('click', async (e) => {
    const delBtn = e.target.closest('button[data-hdel]');
    if (!delBtn) return;

    const hid = Number(delBtn.getAttribute('data-hdel'));
    if (!confirm('Hapus data panen ini?')) return;

    try{
      await apiPostHarvests({ action:'delete', id: hid });
      await openHarvest(currentHarvestPeriod);
      await load(); // refresh harvest_count
    }catch(err){
      showHarvestError(err.message);
    }
  });

  btnHarvestSave.addEventListener('click', async () => {
    clearHarvestError();

    const periodeId = Number(harvest_periode_id.value || 0);
    const payload = {
      action: 'create',
      periode_id: periodeId,
      tanggal_panen: hp_tanggal.value,
      jenis_tanaman: hp_jenis.value.trim() || null,
      jumlah_panen: hp_jumlah.value,
      catatan: hp_catatan.value.trim() || null
    };

    try{
      await apiPostHarvests(payload);

      // reset form
      hp_tanggal.value = '';
      hp_jenis.value = '';
      hp_jumlah.value = '';
      hp_catatan.value = '';

      await openHarvest(periodeId);
      await load(); // refresh harvest_count
    }catch(err){
      showHarvestError(err.message);
    }
  });

  load();
</script>
@endsection
