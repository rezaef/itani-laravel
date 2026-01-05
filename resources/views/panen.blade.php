@extends('layouts.app')
@section('title','ITani — Laporan Hasil Panen')

@section('content')
<div class="d-flex align-items-end justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-1">Laporan Hasil Panen</h3>
    <div class="muted">Rekap global panen (Admin &amp; Petani) dengan filter periode dan rentang tanggal.</div>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" id="btnCsv" href="#" role="button">
      <i class="bi bi-filetype-csv me-1"></i> Download CSV
    </a>
    <button class="btn btn-success fw-bold" id="btnReload">
      <i class="bi bi-arrow-repeat me-1"></i> Refresh
    </button>
  </div>
</div>

<div class="cardx mb-3">
  <div class="card-body">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Periode</label>
        <select class="form-select" id="fPeriode">
          <option value="">Semua Periode</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Dari tanggal</label>
        <input type="date" class="form-control" id="fFrom">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Sampai tanggal</label>
        <input type="date" class="form-control" id="fTo">
      </div>

      <div class="col-md-2 d-grid">
        <button class="btn btn-primary fw-bold" id="btnApply">
          <i class="bi bi-funnel me-1"></i> Terapkan
        </button>
      </div>

      <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <div class="badge text-bg-light border" id="sumCount">0 data</div>
          <div class="badge text-bg-light border" id="sumTotal">Total: 0</div>
          <div class="text-muted small">Jumlah panen mengikuti kolom <span class="fw-semibold">jumlah_panen</span> pada tabel harvests.</div>
        </div>
        <div class="mt-2" id="sumByCrop"></div>
      </div>
    </div>
  </div>
</div>

<div class="cardx">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:130px;">Tanggal</th>
            <th>Periode</th>
            <th>Jenis Tanaman</th>
            <th class="text-end" style="width:160px;">Jumlah</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr>
            <td colspan="5" class="text-center muted py-4">Memuat...</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
  const TBODY = document.getElementById('tbody');
  const F_PERIODE = document.getElementById('fPeriode');
  const F_FROM = document.getElementById('fFrom');
  const F_TO = document.getElementById('fTo');

  const SUM_COUNT = document.getElementById('sumCount');
  const SUM_TOTAL = document.getElementById('sumTotal');
  const SUM_BY_CROP = document.getElementById('sumByCrop');

  const BTN_CSV = document.getElementById('btnCsv');
  const BTN_APPLY = document.getElementById('btnApply');
  const BTN_RELOAD = document.getElementById('btnReload');

  const API_PERIODS = @json(url('api/periods.php'));
  const API_REPORT  = @json(url('api/harvest_report.php'));
  const API_CSV     = @json(url('api/harvest_report.csv'));

  function esc(s){
    return (s ?? '').toString()
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'","&#039;");
  }

  function qs(params){
    const u = new URLSearchParams();
    for (const [k,v] of Object.entries(params)) {
      if (v === null || v === undefined || v === '') continue;
      u.set(k, String(v));
    }
    const s = u.toString();
    return s ? ('?' + s) : '';
  }

  function fmtNum(n){
    const x = Number(n ?? 0);
    if (Number.isNaN(x)) return '0';
    // tampil 2 desimal kalau ada pecahan
    return (Math.round(x*100) / 100).toString();
  }

  async function apiGet(url){
    const r = await fetch(url, { credentials: 'same-origin' });
    const j = await r.json();
    if (!j.success) throw new Error(j.error || 'API error');
    return j;
  }

  async function loadPeriods(){
    try {
      const j = await apiGet(API_PERIODS);
      const data = j.data || [];

      const opts = data.map(p => {
        const label = `${p.nama_periode} (${p.status})`;
        return `<option value="${p.id}">${esc(label)}</option>`;
      }).join('');

      F_PERIODE.innerHTML = `<option value="">Semua Periode</option>` + opts;
    } catch(e) {
      // silent: kalau gagal, tetap bisa report global
      console.warn(e);
    }
  }

  function getFilters(){
    return {
      periode_id: F_PERIODE.value,
      from: F_FROM.value,
      to: F_TO.value,
    };
  }

  function setCsvLink(filters){
    BTN_CSV.href = API_CSV + qs(filters);
  }

  function renderSummary(summary){
    const count = Number(summary?.count ?? 0);
    const total = summary?.total_jumlah_panen ?? 0;

    SUM_COUNT.textContent = `${count} data`;
    SUM_TOTAL.textContent = `Total: ${fmtNum(total)}`;

    const byCrop = summary?.by_crop || {};
    const entries = Object.entries(byCrop);

    if (!entries.length){
      SUM_BY_CROP.innerHTML = '';
      return;
    }

    SUM_BY_CROP.innerHTML = entries.map(([k,v]) => {
      return `<span class="badge text-bg-light border me-1 mb-1">${esc(k)}: <span class="fw-semibold">${fmtNum(v)}</span></span>`;
    }).join('');
  }

  function renderTable(rows){
    if (!rows || !rows.length) {
      TBODY.innerHTML = `<tr><td colspan="5" class="text-center muted py-4">Belum ada data panen pada filter ini.</td></tr>`;
      return;
    }

    TBODY.innerHTML = rows.map(r => {
      const tanggal = r.tanggal_panen || '-';
      const periode = r.nama_periode ? `${r.nama_periode} (${r.status || '-'})` : '-';
      const jenis = r.jenis_tanaman || '-';
      const jumlah = fmtNum(r.jumlah_panen);
      const catatan = r.catatan || '';

      return `
        <tr>
          <td class="text-muted">${esc(tanggal)}</td>
          <td>
            <div class="fw-semibold">${esc(r.nama_periode || '-')}</div>
            <div class="text-muted small">Status: ${esc(r.status || '-')}</div>
          </td>
          <td>${esc(jenis)}</td>
          <td class="text-end fw-semibold">${esc(jumlah)}</td>
          <td class="text-muted">${esc(catatan)}</td>
        </tr>
      `;
    }).join('');
  }

  async function loadReport(){
    const filters = getFilters();
    setCsvLink(filters);

    TBODY.innerHTML = `<tr><td colspan="5" class="text-center muted py-4">Memuat...</td></tr>`;

    try {
      const j = await apiGet(API_REPORT + qs(filters));
      renderSummary(j.summary);
      renderTable(j.data);
    } catch(e) {
      TBODY.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">${esc(e.message)}</td></tr>`;
      renderSummary({count:0,total_jumlah_panen:0,by_crop:{}});
    }
  }

  BTN_APPLY.addEventListener('click', loadReport);
  BTN_RELOAD.addEventListener('click', loadReport);
  F_PERIODE.addEventListener('change', loadReport);

  // init
  (async function(){
    await loadPeriods();
    setCsvLink(getFilters());
    await loadReport();
  })();
</script>
@endsection
