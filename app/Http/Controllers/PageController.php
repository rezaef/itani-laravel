<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    private function requireLogin()
    {
        if (!auth()->check()) return redirect('/login.html');
        return null;
    }

    public function periode()
    {
        if ($r = $this->requireLogin()) return $r;
        return view('periode');
    }

    public function users()
    {
        if ($r = $this->requireLogin()) return $r;
        return view('users');
    }

    public function stok()
    {
        if ($r = $this->requireLogin()) return $r;
        return view('stok');
    }
    public function panen()
    {
        if ($r = $this->requireLogin()) return $r;

        // halaman laporan panen: render server-side dulu supaya tabel pasti tampil
        $periodeId = (int) request()->query('periode_id', 0);
        $from = request()->query('from');
        $to = request()->query('to');

        // normalize (YYYY-MM-DD)
        $from = is_string($from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : null;
        $to = is_string($to) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : null;

        $filters = [
            'periode_id' => $periodeId > 0 ? $periodeId : null,
            'from' => $from,
            'to' => $to,
        ];

        $periods = DB::table('periods')
            ->select(['id', 'nama_periode', 'status'])
            ->orderByDesc('id')
            ->get();

        $q = DB::table('harvests')
            ->leftJoin('periods', 'periods.id', '=', 'harvests.periode_id')
            ->select([
                'harvests.id',
                'harvests.periode_id',
                'harvests.tanggal_panen',
                'harvests.jenis_tanaman',
                'harvests.jumlah_panen',
                'harvests.catatan',
                'periods.nama_periode',
                'periods.status',
            ])
            ->orderByDesc('harvests.tanggal_panen')
            ->orderByDesc('harvests.id');

        if ($filters['periode_id']) {
            $q->where('harvests.periode_id', $filters['periode_id']);
        }
        if ($filters['from']) {
            $q->whereDate('harvests.tanggal_panen', '>=', $filters['from']);
        }
        if ($filters['to']) {
            $q->whereDate('harvests.tanggal_panen', '<=', $filters['to']);
        }

        $rows = $q->get();

        $total = 0.0;
        $byCrop = [];
        foreach ($rows as $r) {
            $val = (float)($r->jumlah_panen ?? 0);
            $total += $val;
            $crop = trim((string)($r->jenis_tanaman ?? ''));
            if ($crop === '') $crop = '(tanpa jenis)';
            $byCrop[$crop] = ($byCrop[$crop] ?? 0) + $val;
        }
        arsort($byCrop);

        // CSV url ikut filter agar sama dengan tabel
        $csvParams = array_filter([
            'periode_id' => $filters['periode_id'],
            'from' => $filters['from'],
            'to' => $filters['to'],
        ], fn($v) => !is_null($v) && $v !== '');

        $csvUrl = url('api/harvest_report.csv') . (count($csvParams) ? ('?' . http_build_query($csvParams)) : '');

        return view('panen', [
            'periods' => $periods,
            'rows' => $rows,
            'filters' => $filters,
            'summary' => [
                'count' => $rows->count(),
                'total' => $total,
                'by_crop' => $byCrop,
            ],
            'csvUrl' => $csvUrl,
        ]);
    }
}
