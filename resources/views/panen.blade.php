@extends('layouts.app')
@section('title','ITani — Laporan Hasil Panen')

@section('content')
@php
  /** @var \Illuminate\Support\Collection $periods */
  /** @var \Illuminate\Support\Collection $rows */
  $filters = $filters ?? ['periode_id'=>null,'from'=>null,'to'=>null];
  $summary = $summary ?? ['count'=>0,'total'=>0,'by_crop'=>[]];

  $fmt = function ($n) {
    $x = is_numeric($n) ? (float)$n : 0.0;
    // max 2 decimal, trim trailing zeros
    $s = number_format($x, 2, '.', '');
    $s = rtrim(rtrim($s, '0'), '.');
    return $s === '' ? '0' : $s;
  };
@endphp

<div class="d-flex align-items-end justify-content-between mb-3">
  <div>
    <h3 class="fw-bold mb-1">Laporan Hasil Panen</h3>
    <div class="muted">Rekap global panen (Admin &amp; Petani) dengan filter periode dan rentang tanggal.</div>
  </div>

  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="{{ $csvUrl ?? url('api/harvest_report.csv') }}" role="button">
      <i class="bi bi-filetype-csv me-1"></i> Download CSV
    </a>
    <a class="btn btn-success fw-bold" href="{{ url('/panen.php') }}" role="button">
      <i class="bi bi-arrow-repeat me-1"></i> Refresh
    </a>
  </div>
</div>

<div class="cardx mb-3">
  <div class="card-body">
    <form class="row g-3 align-items-end" method="GET" action="{{ url('/panen.php') }}">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Periode</label>
        <select class="form-select" name="periode_id">
          <option value="">Semua Periode</option>
          @foreach(($periods ?? []) as $p)
            @php
              $label = trim(($p->nama_periode ?? '-') . ' (' . ($p->status ?? '-') . ')');
              $selected = (string)($filters['periode_id'] ?? '') === (string)($p->id ?? '');
            @endphp
            <option value="{{ $p->id }}" @selected($selected)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Dari tanggal</label>
        <input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}">
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">Sampai tanggal</label>
        <input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}">
      </div>

      <div class="col-md-2 d-grid">
        <button class="btn btn-primary fw-bold" type="submit">
          <i class="bi bi-funnel me-1"></i> Terapkan
        </button>
      </div>

      <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <div class="badge text-bg-light border">{{ (int)($summary['count'] ?? 0) }} data</div>
          <div class="badge text-bg-light border">Total: {{ $fmt($summary['total'] ?? 0) }}</div>
          <div class="text-muted small">Jumlah panen mengikuti kolom <span class="fw-semibold">jumlah_panen</span> pada tabel harvests.</div>
        </div>
        @if(!empty($summary['by_crop']))
          <div class="mt-2">
            @foreach(($summary['by_crop'] ?? []) as $crop => $val)
              <span class="badge text-bg-light border me-1 mb-1">{{ $crop }}: <span class="fw-semibold">{{ $fmt($val) }}</span></span>
            @endforeach
          </div>
        @endif
      </div>
    </form>
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
        <tbody>
          @if(($rows ?? collect())->count() === 0)
            <tr>
              <td colspan="5" class="text-center muted py-4">Belum ada data panen pada filter ini.</td>
            </tr>
          @else
            @foreach($rows as $r)
              <tr>
                <td class="text-muted">{{ $r->tanggal_panen ?? '-' }}</td>
                <td>
                  <div class="fw-semibold">{{ $r->nama_periode ?? '-' }}</div>
                  <div class="text-muted small">Status: {{ $r->status ?? '-' }}</div>
                </td>
                <td>{{ $r->jenis_tanaman ?? '-' }}</td>
                <td class="text-end fw-semibold">{{ $fmt($r->jumlah_panen ?? 0) }}</td>
                <td class="text-muted">{{ $r->catatan ?? '' }}</td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
