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

    public function index()
    {
        if ($g = $this->guard()) return $g;

        $user = auth()->user();
        $role = $user->role ?? 'Petani';

        try {
            if ($role === 'Admin') {
                $rows = DB::table('periods')
                    ->select('id','user_id','nama_periode','tanggal_mulai','tanggal_selesai','deskripsi','status','created_at','updated_at','is_active')
                    ->orderByDesc('tanggal_mulai')
                    ->get();
            } else {
                $rows = DB::table('periods')
                    ->select('id','user_id','nama_periode','tanggal_mulai','tanggal_selesai','deskripsi','status','created_at','updated_at','is_active')
                    ->where('user_id', $user->id)
                    ->orderByDesc('tanggal_mulai')
                    ->get();
            }

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'DB error (GET)', 'detail' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        if ($g = $this->guard()) return $g;

        $user = auth()->user();
        $role = $user->role ?? 'Petani';

        $input = $request->json()->all();
        if (!is_array($input)) return response()->json(['success' => false, 'error' => 'Invalid JSON'], 400);

        $action = $input['action'] ?? 'create';

        // --- set_active ---
        if ($action === 'set_active') {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) return response()->json(['success'=>false,'error'=>'ID tidak valid'], 400);

            try {
                if ($role === 'Admin') {
                    DB::table('periods')->update(['is_active' => 0]);
                    DB::table('periods')->where('id', $id)->update(['is_active' => 1, 'status' => 'berjalan']);
                } else {
                    DB::table('periods')->where('user_id', $user->id)->update(['is_active' => 0]);
                    DB::table('periods')->where('id', $id)->where('user_id', $user->id)
                        ->update(['is_active' => 1, 'status' => 'berjalan']);
                }
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                return response()->json(['success'=>false,'error'=>'DB error (SET_ACTIVE)','detail'=>$e->getMessage()], 500);
            }
        }

        // --- update_status ---
        if ($action === 'update_status') {
            $id = (int)($input['id'] ?? 0);
            $status = (string)($input['status'] ?? 'planning');

            if ($id <= 0 || !in_array($status, ['planning','berjalan','selesai','gagal'], true)) {
                return response()->json(['success'=>false,'error'=>'ID atau status tidak valid'], 400);
            }

            try {
                $q = DB::table('periods')->where('id', $id);
                if ($role !== 'Admin') $q->where('user_id', $user->id);
                $q->update(['status' => $status]);

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
                $q = DB::table('periods')->where('id', $id);
                if ($role !== 'Admin') $q->where('user_id', $user->id);
                $q->delete();

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

                $q = DB::table('periods')->where('id', $id);
                if ($role !== 'Admin') $q->where('user_id', $user->id);

                $q->update([
                    'nama_periode' => $nama,
                    'tanggal_mulai' => $mulai,
                    'tanggal_selesai' => $selesai ?: null,
                    'deskripsi' => $deskripsi,
                    'status' => $status,
                ]);

                return response()->json(['success' => true, 'mode' => 'update']);
            }

            $id = DB::table('periods')->insertGetId([
                'user_id' => $user->id,
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
