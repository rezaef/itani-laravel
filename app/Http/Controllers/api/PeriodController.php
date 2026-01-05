<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeriodController extends Controller
{
    private function guard()
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }
        return null;
    }

    /**
     * Catatan perubahan (global access):
     * - Semua user (Admin/Petani) bisa melihat & mengelola data periode.
     * - Kolom user_id tetap diisi (audit siapa pembuat), tapi tidak lagi dipakai untuk filter akses.
     */
    public function index()
    {
        if ($g = $this->guard()) return $g;

        try {
            $rows = DB::table('periods')
                ->select(
                    'periods.id',
                    'periods.user_id',
                    'periods.nama_periode',
                    'periods.tanggal_mulai',
                    'periods.tanggal_selesai',
                    'periods.deskripsi',
                    'periods.status',
                    'periods.created_at',
                    'periods.updated_at',
                )
                // hitung jumlah panen per periode
                ->selectSub(function ($q) {
                    $q->from('harvests')
                      ->selectRaw('COUNT(*)')
                      ->whereColumn('harvests.periode_id', 'periods.id');
                }, 'harvest_count')
                ->orderByDesc('periods.tanggal_mulai')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'DB error (GET)', 'detail' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        if ($g = $this->guard()) return $g;

        $user = auth()->user();

        $input = $request->json()->all();
        if (!is_array($input)) return response()->json(['success' => false, 'error' => 'Invalid JSON'], 400);

        $action = $input['action'] ?? 'create';

        // --- update_status ---
        if ($action === 'update_status') {
            $id = (int)($input['id'] ?? 0);
            $status = (string)($input['status'] ?? 'planning');

            if ($id <= 0 || !in_array($status, ['planning','berjalan','selesai','gagal'], true)) {
                return response()->json(['success'=>false,'error'=>'ID atau status tidak valid'], 400);
            }

            try {
                DB::table('periods')->where('id', $id)->update(['status' => $status]);
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                return response()->json(['success'=>false,'error'=>'DB error (UPDATE_STATUS)','detail'=>$e->getMessage()], 500);
            }
        }

        // --- delete ---
        if ($action === 'delete') {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) return response()->json(['success'=>false,'error'=>'ID tidak valid'], 400);

            try {
                // hapus panen terkait biar DB bersih
                DB::table('harvests')->where('periode_id', $id)->delete();

                DB::table('periods')->where('id', $id)->delete();

                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                return response()->json(['success'=>false,'error'=>'DB error (DELETE)','detail'=>$e->getMessage()], 500);
            }
        }

        // --- create/update ---
        $nama = trim((string)($input['nama_periode'] ?? ''));
        $mulai = $input['tanggal_mulai'] ?? null;
        $selesai = $input['tanggal_selesai'] ?? null;
        $deskripsi = $input['deskripsi'] ?? null;
        $status = (string)($input['status'] ?? 'planning');

        if ($nama === '' || !$mulai) {
            return response()->json(['success'=>false,'error'=>'Nama periode dan tanggal mulai wajib diisi'], 400);
        }
        if ($selesai && $selesai < $mulai) {
            return response()->json(['success'=>false,'error'=>'Tanggal selesai tidak boleh lebih awal dari tanggal mulai'], 400);
        }
        if (!in_array($status, ['planning','berjalan','selesai','gagal'], true)) $status = 'planning';

        try {
            if ($action === 'update') {
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) return response()->json(['success'=>false,'error'=>'ID tidak valid'], 400);

                DB::table('periods')->where('id', $id)->update([
                    'nama_periode' => $nama,
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai ?: null,
                    'deskripsi' => $deskripsi,
                    'status' => $status,
                ]);

                return response()->json(['success' => true, 'mode' => 'update']);
            }

            $id = DB::table('periods')->insertGetId([
                'user_id' => $user?->id, // audit
                'nama_periode' => $nama,
                'tanggal_mulai' => $mulai,
                'tanggal_selesai' => $selesai ?: null,
                'deskripsi' => $deskripsi,
                'status' => $status,
            ]);

            return response()->json(['success' => true, 'mode' => 'create', 'id' => (int)$id]);
        } catch (\Throwable $e) {
            return response()->json(['success'=>false,'error'=>'DB error (SAVE)','detail'=>$e->getMessage()], 500);
        }
    }
}
