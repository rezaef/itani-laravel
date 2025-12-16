@extends('layouts.app')
@section('title','ITani — Stok Bibit & Pupuk')

@section('content')
  <div class="mb-3">
    <h3 class="fw-bold mb-1">Stok Bibit &amp; Pupuk</h3>
    <div class="muted">Halaman ini siap kamu sambungkan ke fitur stok (bibit & pupuk).</div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="cardx">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="fw-bold">Stok Bibit</div>
              <div class="muted">CRUD + histori masuk/keluar (next).</div>
            </div>
            <i class="bi bi-basket2 fs-3"></i>
          </div>
          <hr style="border-color:var(--border)">
          <div class="muted">Status: <span class="badge text-bg-secondary">Belum diaktifkan</span></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="cardx">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="fw-bold">Stok Pupuk</div>
              <div class="muted">CRUD + histori + reminder batas minimum (next).</div>
            </div>
            <i class="bi bi-droplet fs-3"></i>
          </div>
          <hr style="border-color:var(--border)">
          <div class="muted">Status: <span class="badge text-bg-secondary">Belum diaktifkan</span></div>
        </div>
      </div>
    </div>
  </div>
@endsection
