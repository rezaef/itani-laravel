<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function normalizeRole($role): string
    {
        $r = strtolower(trim((string) $role));

        // support legacy values
        if ($r === 'administrator') $r = 'admin';
        if ($r === 'farmer') $r = 'petani';

        return in_array($r, ['admin', 'petani'], true) ? $r : 'petani';
    }

    private function ensureAdmin(Request $request): void
    {
        $u = auth()->user();
        if (!$u) {
            abort(response()->json(['error' => 'Unauthorized'], 401));
        }

        $role = strtolower((string)($u->role ?? ''));
        if ($role !== 'admin') {
            abort(response()->json(['error' => 'Forbidden (admin only)'], 403));
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $rows = DB::table('users')->select('id','name','username','role','created_at')->orderBy('id')->get();
        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $input = $request->json()->all();

        $name = trim((string)($input['name'] ?? ''));
        $username = trim((string)($input['username'] ?? ''));
        $role = $this->normalizeRole($input['role'] ?? 'petani');
        $password = trim((string)($input['password'] ?? ''));

        if ($name === '' || $username === '') {
            return response()->json(['error' => 'Nama & username wajib diisi'], 400);
        }

        $exists = DB::table('users')->where('username', $username)->exists();
        if ($exists) return response()->json(['error' => 'Username sudah dipakai'], 409);

        $pwdFinal = $password !== '' ? $password : 'password123';

        try {
            DB::table('users')->insert([
                'name' => $name,
                'username' => $username,
                'role' => $role,
                'password_hash' => Hash::make($pwdFinal),
                'created_at' => now(),
            ]);
        } catch (QueryException $e) {
            // Common: role column is ENUM('admin') so inserting 'petani' fails
            return response()->json([
                'error' => 'Gagal membuat user. Pastikan kolom users.role mendukung nilai "petani". Jalankan migration terbaru (ALTER users.role) lalu coba lagi.'
            ], 500);
        }

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $this->ensureAdmin($request);

        $id = (int) $request->query('id', 0);
        if ($id <= 0) return response()->json(['error' => 'ID wajib dikirim'], 400);

        $input = $request->json()->all();

        $name = trim((string)($input['name'] ?? ''));
        $username = trim((string)($input['username'] ?? ''));
        $role = $this->normalizeRole($input['role'] ?? 'petani');
        $password = trim((string)($input['password'] ?? ''));

        if ($name === '' || $username === '') {
            return response()->json(['error' => 'Nama & username wajib diisi'], 400);
        }

        $exists = DB::table('users')->where('username', $username)->where('id','!=',$id)->exists();
        if ($exists) return response()->json(['error' => 'Username sudah dipakai user lain'], 409);

        $upd = ['name' => $name, 'username' => $username, 'role' => $role];
        if ($password !== '') $upd['password_hash'] = Hash::make($password);

        DB::table('users')->where('id', $id)->update($upd);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $this->ensureAdmin($request);

        $id = (int) $request->query('id', 0);
        if ($id <= 0) return response()->json(['error' => 'ID wajib dikirim'], 400);

        DB::table('users')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
