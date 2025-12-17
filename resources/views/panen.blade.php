@extends('layouts.app')
@section('title','ITani — Hasil Panen')

@section('content')
<div class="d-flex align-items-end justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-1">Hasil Panen</h3>
    <div class="muted">Kelola data hasil panen tanaman.</div>
  </div>

  <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalAdd">
    <i class="bi bi-plus-lg me-1"></i> Tambah Panen
  </button>
</div>

<div class="cardx">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Nama Tanaman</th>
            <th>Tanggal Panen</th>
            <th>Jumlah (kg)</th>
            <th>Grade</th>
            <th style="width:140px;">Aksi</th>
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

{{-- ================= MODAL TAMBAH ================= --}}
<div class="modal fade" id="modalAdd" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="fw-bold">Tambah Hasil Panen</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nama Tanaman</label>
          <input class="form-control" id="add_nama">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tanggal Panen</label>
          <input type="date" class="form-control" id="add_tanggal">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Jumlah (kg)</label>
          <input type="number" step="0.01" class="form-control" id="add_jumlah">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Grade</label>
          <select class="form-select" id="add_grade">
            <option>A</option><option>B</option><option>C</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-success fw-bold" id="btnSaveAdd">Simpan</button>
      </div>
    </div>
  </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="fw-bold">Edit Hasil Panen</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <input type="hidden" id="edit_id">

      <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nama Tanaman</label>
          <input class="form-control" id="edit_nama">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Tanggal Panen</label>
          <input type="date" class="form-control" id="edit_tanggal">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Jumlah (kg)</label>
          <input type="number" step="0.01" class="form-control" id="edit_jumlah">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Grade</label>
          <select class="form-select" id="edit_grade">
            <option>A</option><option>B</option><option>C</option>
          </select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary fw-bold" id="btnSaveEdit">Update</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const tbody = document.getElementById('tbody');
const modalAdd = new bootstrap.Modal('#modalAdd');
const modalEdit = new bootstrap.Modal('#modalEdit');
let dataPanen = [];

/* ================= LOAD ================= */
async function loadData(){
  const res = await fetch('/api/harvests.php');
  const json = await res.json();
  dataPanen = json.data || [];
  render();
}

/* ================= RENDER ================= */
function render(){
  if(!dataPanen.length){
    tbody.innerHTML = `<tr><td colspan="5" class="text-center muted py-4">Belum ada data</td></tr>`;
    return;
  }

  tbody.innerHTML = dataPanen.map(d => `
    <tr data-id="${d.id}">
      <td>${d.nama_tanaman}</td>
      <td>${d.tanggal_panen}</td>
      <td>${d.jumlah_kg}</td>
      <td><span class="badge bg-success">${d.grade}</span></td>
      <td>
        <button class="btn btn-sm btn-outline-primary" data-act="edit">
          <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger" data-act="del">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    </tr>
  `).join('');
}

/* ================= TAMBAH ================= */
document.getElementById('btnSaveAdd').onclick = async ()=>{
  await fetch('/api/harvests.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({
      action:'create',
      nama_tanaman:add_nama.value,
      tanggal_panen:add_tanggal.value,
      jumlah_kg:add_jumlah.value,
      grade:add_grade.value
    })
  });
  modalAdd.hide();
  loadData();
};

/* ================= EDIT & DELETE ================= */
tbody.onclick = async (e)=>{
  const btn = e.target.closest('button');
  if(!btn) return;
  const tr = btn.closest('tr');
  const id = tr.dataset.id;

  const row = dataPanen.find(d => d.id == id);

  if(btn.dataset.act === 'edit'){
    edit_id.value = row.id;
    edit_nama.value = row.nama_tanaman;
    edit_tanggal.value = row.tanggal_panen;
    edit_jumlah.value = row.jumlah_kg;
    edit_grade.value = row.grade;
    modalEdit.show();
  }

  if(btn.dataset.act === 'del'){
    if(!confirm('Hapus data panen ini?')) return;
    await fetch('/api/harvests.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({action:'delete', id})
    });
    loadData();
  }
};

/* ================= UPDATE ================= */
document.getElementById('btnSaveEdit').onclick = async ()=>{
  await fetch('/api/harvests.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({
      action:'update',
      id: edit_id.value,
      nama_tanaman:edit_nama.value,
      tanggal_panen:edit_tanggal.value,
      jumlah_kg:edit_jumlah.value,
      grade:edit_grade.value
    })
  });
  modalEdit.hide();
  loadData();
};

loadData();
</script>
@endsection
