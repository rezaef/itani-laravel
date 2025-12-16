<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WateringLogController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 20);
        if ($limit < 1 || $limit > 100) $limit = 20;

        try {
            $rows = DB::table('watering_logs')
                ->select('id', 'log_time', 'source', 'action', 'duration_seconds', 'notes')
                ->orderByDesc('log_time')
                ->limit($limit)
                ->get();

            return response()->json($rows);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'DB error (GET)',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $input = $request->json()->all();
        if (!is_array($input)) {
            return response()->json(['error' => 'JSON tidak valid'], 400);
        }

        if (empty($input['source']) || empty($input['action'])) {
            return response()->json(['error' => 'Field source & action wajib diisi'], 400);
        }

        $source = (string) $input['source']; // "manual" / "otomatis"
        $action = (string) $input['action']; // "ON" / "OFF"

        $duration = array_key_exists('duration_seconds', $input) && $input['duration_seconds'] !== null
            ? (int) $input['duration_seconds']
            : null;

        $notes = array_key_exists('notes', $input) ? $input['notes'] : null;

        try {
            $id = DB::table('watering_logs')->insertGetId([
                'source' => $source,
                'action' => $action,
                'duration_seconds' => $duration,
                'notes' => $notes,
            ]);


            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'DB error (POST)',
                'detail' => $e->getMessage()
            ], 500);
        }
    }

    public function pumpStatusLatest()
    {
        try {
            $row = DB::table('watering_logs')
                ->select('action', 'source', 'log_time', 'notes')
                ->where('notes', 'like', 'Perintah%')
                ->orderByDesc('log_time')
                ->first();

            if (!$row) {
                return response()->json(['exists' => false]);
            }

            return response()->json([
                'exists' => true,
                'action' => $row->action,
                'source' => $row->source,
                'log_time' => $row->log_time,
                'notes' => $row->notes,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'exists' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
