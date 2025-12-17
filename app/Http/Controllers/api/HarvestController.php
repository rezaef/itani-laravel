<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HarvestController extends Controller
{
    public function index(Request $request)
    {
        $periodeId = (int) $request->query('periode_id', 0);
        if ($periodeId <= 0) {
            return response()->json(['success' => false, 'error' => 'periode_id wajib'], 400);
        }

        try {
            $rows = DB::table('harvests')
                ->select('id','periode_id','tanggal_panen','jenis_tanaman','jumlah_panen','catatan','created_at')
                ->where('periode_id', $periodeId)
                ->orderByDesc('tanggal_panen')
                ->orderByDesc('id')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'DB error (HARVEST GET)',
                'detail'  => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $input = $request->json()->all();
        if (!is_array($input)) {
            return response()->json(['success' => false, 'error' => 'Invalid JSON'], 400);
        }

        $action = $input['action'] ?? 'create';

        // delete
        if ($action === 'delete') {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) return response()->json(['success'=>false,'error'=>'ID tidak valid'], 400);

            try {
                DB::table('harvests')->where('id', $id)->delete();
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                return response()->json(['success'=>false,'error'=>'DB error (HARVEST DELETE)','detail'=>$e->getMessage()], 500);
            }
        }

        // create
        $periodeId = (int)($input['periode_id'] ?? 0);
        $tanggal   = $input['tanggal_panen'] ?? null;
        $jenis     = trim((string)($input['jenis_tanaman'] ?? ''));
        $jumlah    = $input['jumlah_panen'] ?? null;
        $catatan   = isset($input['catatan']) ? trim((string)$input['catatan']) : null;

        // ✅ karena DB: jenis_tanaman NOT NULL
        if ($periodeId <= 0 || !$tanggal || $jenis === '' || $jumlah === null || $jumlah === '') {
            return response()->json([
                'success'=>false,
                'error'=>'periode_id, tanggal_panen, jenis_tanaman, jumlah_panen wajib'
            ], 400);
        }

        try {
            // pastikan periode ada
            $exists = DB::table('periods')->where('id', $periodeId)->exists();
            if (!$exists) {
                return response()->json(['success'=>false,'error'=>'Periode tidak ditemukan'], 404);
            }

            // ✅ jangan kirim updated_at (kolom tidak ada)
            // ✅ created_at punya default current_timestamp(), jadi boleh di-skip
            $id = DB::table('harvests')->insertGetId([
                'periode_id'    => $periodeId,
                'tanggal_panen' => $tanggal,
                'jenis_tanaman' => $jenis,
                'jumlah_panen'  => $jumlah,
                'catatan'       => ($catatan !== '' ? $catatan : null),
            ]);

            return response()->json(['success' => true, 'id' => (int)$id], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'DB error (HARVEST CREATE)',
                'detail'  => $e->getMessage()
            ], 500);
        }
    }
}
