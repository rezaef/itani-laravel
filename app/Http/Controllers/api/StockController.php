<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    private function sessionUser(Request $request): array
    {
        $authUser = auth()->user();
        if ($authUser) {
            return [
                'id'   => $authUser->id,
                'role' => $authUser->role ?? 'Petani',
            ];
        }

        $u = $request->session()->get('user');
        if (is_array($u)) {
            return [
                'id'   => (int)($u['id'] ?? 0),
                'role' => $u['role'] ?? 'Petani',
            ];
        }

        abort(response()->json(['success' => false, 'error' => 'Unauthorized'], 401));
    }

    /**
     * Catatan perubahan (global access):
     * - Admin/Petani tidak dibedakan pada modul stok.
     * - Hanya modul kelola user yang dibatasi Admin.
     */

    // ===================== SEED =====================
    public function seeds(Request $request)
    {
        $user = $this->sessionUser($request);

        if ($request->isMethod('get')) {
            $rows = DB::table('seed_stock')
                ->select([
                    'id',
                    'seed_name as name',
                    'seed_type as type',
                    'stock_in',
                    'stock_out',
                    'stock_remaining as qty',
                    'updated_at',
                ])
                ->orderBy('seed_name')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        }

        $action = $request->input('action');

        if ($action === 'create') {
            $data = $request->validate([
                'seed_name' => 'required|string|max:120',
                'seed_type' => 'nullable|string|max:120',
                'qty'       => 'nullable|integer|min:0',
            ]);

            $qty = (int)($data['qty'] ?? 0);

            DB::table('seed_stock')->insert([
                'seed_name'        => $data['seed_name'],
                'seed_type'        => $data['seed_type'] ?? null,
                'stock_in'         => $qty,
                'stock_out'        => 0,
                'stock_remaining'  => $qty,
                'updated_at'       => now(),
            ]);

            return response()->json(['success' => true]);
        }

        if ($action === 'update') {
            $data = $request->validate([
                'id'        => 'required|integer|min:1',
                'seed_name' => 'required|string|max:120',
                'seed_type' => 'nullable|string|max:120',
            ]);

            DB::table('seed_stock')
                ->where('id', $data['id'])
                ->update([
                    'seed_name'  => $data['seed_name'],
                    'seed_type'  => $data['seed_type'] ?? null,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true]);
        }

        if ($action === 'delete') {
            $data = $request->validate(['id' => 'required|integer|min:1']);
            DB::table('seed_stock')->where('id', $data['id'])->delete();
            return response()->json(['success' => true]);
        }

        // transaksi IN/OUT + update stok atomik
        if ($action === 'adjust') {
            $data = $request->validate([
                'id'        => 'required|integer|min:1',
                'delta'     => 'required|integer|not_in:0',  // + untuk IN, - untuk OUT
                'period_id' => 'nullable|integer|min:1',
                'notes'     => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($data, $user) {
                $row = DB::table('seed_stock')
                    ->where('id', $data['id'])
                    ->lockForUpdate()
                    ->first();

                if (!$row) {
                    abort(response()->json(['success' => false, 'error' => 'Bibit tidak ditemukan'], 404));
                }

                $delta = (int)$data['delta'];
                $currentRemaining = (int)$row->stock_remaining;
                $newRemaining = $currentRemaining + $delta;

                if ($newRemaining < 0) {
                    abort(response()->json(['success' => false, 'error' => 'Stok tidak cukup (tidak boleh minus)'], 400));
                }

                $type = $delta > 0 ? 'IN' : 'OUT';
                $qtyAbs = abs($delta);

                // update seed_stock (IN: stock_in++, OUT: stock_out++)
                $upd = [
                    'stock_remaining' => $newRemaining,
                    'updated_at' => now(),
                ];

                if ($delta > 0) {
                    $upd['stock_in'] = (int)$row->stock_in + $qtyAbs;
                } else {
                    $upd['stock_out'] = (int)$row->stock_out + $qtyAbs;
                }

                DB::table('seed_stock')->where('id', $data['id'])->update($upd);

                // log transaksi
                DB::table('seed_stock_transactions')->insert([
                    'seed_stock_id' => $data['id'],
                    'period_id'     => $data['period_id'] ?? null,
                    'user_id'       => $user['id'] ?: null,
                    'type'          => $type,
                    'quantity'      => $qtyAbs,
                    'notes'         => $data['notes'] ?? null,
                    'created_at'    => now(),
                ]);
            });

            return response()->json(['success' => true]);
        }

        if ($action === 'logs') {
            $data = $request->validate(['id' => 'required|integer|min:1']);

            $logs = DB::table('seed_stock_transactions')
                ->where('seed_stock_id', $data['id'])
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            return response()->json(['success' => true, 'data' => $logs]);
        }

        return response()->json(['success' => false, 'error' => 'Action tidak dikenal'], 400);
    }

    // ===================== FERTILIZER =====================
    public function fertilizers(Request $request)
    {
        $user = $this->sessionUser($request);

        if ($request->isMethod('get')) {
            $rows = DB::table('fertilizer_stock')
                ->select([
                    'id',
                    'fert_name as name',
                    'fert_type as type',
                    'stock_in_kg',
                    'stock_out_kg',
                    'stock_remaining_kg as qty',
                    'updated_at',
                ])
                ->orderBy('fert_name')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        }

        $action = $request->input('action');

        if ($action === 'create') {
            $data = $request->validate([
                'fert_name' => 'required|string|max:120',
                'fert_type' => 'nullable|string|max:120',
                'qty'       => 'nullable|integer|min:0',
            ]);

            $qty = (int)($data['qty'] ?? 0);

            DB::table('fertilizer_stock')->insert([
                'fert_name'           => $data['fert_name'],
                'fert_type'           => $data['fert_type'] ?? null,
                'stock_in_kg'         => $qty,
                'stock_out_kg'        => 0,
                'stock_remaining_kg'  => $qty,
                'updated_at'          => now(),
            ]);

            return response()->json(['success' => true]);
        }

        if ($action === 'update') {
            $data = $request->validate([
                'id'        => 'required|integer|min:1',
                'fert_name' => 'required|string|max:120',
                'fert_type' => 'nullable|string|max:120',
            ]);

            DB::table('fertilizer_stock')
                ->where('id', $data['id'])
                ->update([
                    'fert_name'  => $data['fert_name'],
                    'fert_type'  => $data['fert_type'] ?? null,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true]);
        }

        if ($action === 'delete') {
            $data = $request->validate(['id' => 'required|integer|min:1']);
            DB::table('fertilizer_stock')->where('id', $data['id'])->delete();
            return response()->json(['success' => true]);
        }

        if ($action === 'adjust') {
            $data = $request->validate([
                'id'        => 'required|integer|min:1',
                'delta'     => 'required|integer|not_in:0',
                'period_id' => 'nullable|integer|min:1',
                'notes'     => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($data, $user) {
                $row = DB::table('fertilizer_stock')
                    ->where('id', $data['id'])
                    ->lockForUpdate()
                    ->first();

                if (!$row) {
                    abort(response()->json(['success' => false, 'error' => 'Pupuk tidak ditemukan'], 404));
                }

                $delta = (int)$data['delta'];
                $currentRemaining = (int)$row->stock_remaining_kg;
                $newRemaining = $currentRemaining + $delta;

                if ($newRemaining < 0) {
                    abort(response()->json(['success' => false, 'error' => 'Stok tidak cukup (tidak boleh minus)'], 400));
                }

                $type = $delta > 0 ? 'IN' : 'OUT';
                $qtyAbs = abs($delta);

                $upd = [
                    'stock_remaining_kg' => $newRemaining,
                    'updated_at' => now(),
                ];

                if ($delta > 0) {
                    $upd['stock_in_kg'] = (int)$row->stock_in_kg + $qtyAbs;
                } else {
                    $upd['stock_out_kg'] = (int)$row->stock_out_kg + $qtyAbs;
                }

                DB::table('fertilizer_stock')->where('id', $data['id'])->update($upd);

                DB::table('fertilizer_stock_transactions')->insert([
                    'fertilizer_stock_id' => $data['id'],
                    'period_id'           => $data['period_id'] ?? null,
                    'user_id'             => $user['id'] ?: null,
                    'type'                => $type,
                    'quantity'            => $qtyAbs,
                    'notes'               => $data['notes'] ?? null,
                    'created_at'          => now(),
                ]);
            });

            return response()->json(['success' => true]);
        }

        if ($action === 'logs') {
            $data = $request->validate(['id' => 'required|integer|min:1']);

            $logs = DB::table('fertilizer_stock_transactions')
                ->where('fertilizer_stock_id', $data['id'])
                ->orderByDesc('id')
                ->limit(50)
                ->get();

            return response()->json(['success' => true, 'data' => $logs]);
        }

        return response()->json(['success' => false, 'error' => 'Action tidak dikenal'], 400);
    }
}
