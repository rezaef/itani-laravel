<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function guard()
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }
        return null;
    }

    private function filters(Request $request): array
    {
        $periodeId = (int) $request->query('periode_id', 0);
        $from = $request->query('from');
        $to = $request->query('to');

        // normalize (YYYY-MM-DD)
        $from = is_string($from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) ? $from : null;
        $to = is_string($to) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) ? $to : null;

        return [
            'periode_id' => $periodeId > 0 ? $periodeId : null,
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * GET /api/harvest_report.php
     * Output JSON untuk halaman Laporan Hasil Panen (global + filter).
     */
    public function harvest(Request $request)
    {
        if ($g = $this->guard()) return $g;

        $f = $this->filters($request);

        $q = DB::table('harvests')
            ->leftJoin('periods', 'periods.id', '=', 'harvests.periode_id')
            ->select([
                'harvests.id',
                'harvests.periode_id',
                'harvests.tanggal_panen',
                'harvests.jenis_tanaman',
                'harvests.jumlah_panen',
                'harvests.catatan',
                'harvests.created_at',
                'periods.nama_periode',
                'periods.tanggal_mulai',
                'periods.tanggal_selesai',
                'periods.status',
            ])
            ->orderByDesc('harvests.tanggal_panen')
            ->orderByDesc('harvests.id');

        if ($f['periode_id']) {
            $q->where('harvests.periode_id', $f['periode_id']);
        }
        if ($f['from']) {
            $q->whereDate('harvests.tanggal_panen', '>=', $f['from']);
        }
        if ($f['to']) {
            $q->whereDate('harvests.tanggal_panen', '<=', $f['to']);
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

        return response()->json([
            'success' => true,
            'filters' => $f,
            'summary' => [
                'count' => $rows->count(),
                'total_jumlah_panen' => $total,
                'by_crop' => $byCrop,
            ],
            'data' => $rows,
        ]);
    }

    /**
     * GET /api/harvest_report.csv
     * Download CSV laporan panen.
     */
    public function harvestCsv(Request $request): StreamedResponse
    {
        if (!auth()->check()) {
            // untuk download, arahkan balik (biar UX enak)
            return response()->streamDownload(function () {
                echo "Unauthorized";
            }, 'harvest_report.csv', [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $f = $this->filters($request);

        $q = DB::table('harvests')
            ->leftJoin('periods', 'periods.id', '=', 'harvests.periode_id')
            ->select([
                'harvests.tanggal_panen',
                'periods.nama_periode',
                'periods.status',
                'harvests.jenis_tanaman',
                'harvests.jumlah_panen',
                'harvests.catatan',
            ])
            ->orderByDesc('harvests.tanggal_panen')
            ->orderByDesc('harvests.id');

        if ($f['periode_id']) {
            $q->where('harvests.periode_id', $f['periode_id']);
        }
        if ($f['from']) {
            $q->whereDate('harvests.tanggal_panen', '>=', $f['from']);
        }
        if ($f['to']) {
            $q->whereDate('harvests.tanggal_panen', '<=', $f['to']);
        }

        $rows = $q->get();

        $filename = 'harvest_report_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows, $f) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM supaya Excel aman
            fwrite($out, "\xEF\xBB\xBF");

            // meta
            fputcsv($out, ['Laporan Hasil Panen']);
            fputcsv($out, ['Periode ID', $f['periode_id'] ?? 'ALL', 'From', $f['from'] ?? '-', 'To', $f['to'] ?? '-']);
            fputcsv($out, []);

            // header
            fputcsv($out, ['Tanggal Panen', 'Periode', 'Status Periode', 'Jenis Tanaman', 'Jumlah Panen', 'Catatan']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->tanggal_panen,
                    $r->nama_periode,
                    $r->status,
                    $r->jenis_tanaman,
                    $r->jumlah_panen,
                    $r->catatan,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
