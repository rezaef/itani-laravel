<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $rows = DB::table('users')->select('id','name','username','role','created_at')->orderBy('id')->get();
        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $input = $request->json()->all();

        $name = trim((string)($input['name'] ?? ''));
        $username = trim((string)($input['username'] ?? ''));
        $role = trim((string)($input['role'] ?? 'Viewer'));

        if ($name === '' || $username === '') {
            return response()->json(['error' => 'Nama & username wajib diisi'], 400);
        }

        $exists = DB::table('users')->where('username', $username)->exists();
        if ($exists) return response()->json(['error' => 'Username sudah dipakai'], 409);

        DB::table('users')->insert([
            'name' => $name,
            'username' => $username,
            'role' => $role,
            'password_hash' => Hash::make('password123'),
            'created_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) return response()->json(['error' => 'ID wajib dikirim'], 400);

        $input = $request->json()->all();

        $name = trim((string)($input['name'] ?? ''));
        $username = trim((string)($input['username'] ?? ''));
        $role = trim((string)($input['role'] ?? 'Viewer'));
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
        $id = (int) $request->query('id', 0);
        if ($id <= 0) return response()->json(['error' => 'ID wajib dikirim'], 400);

        DB::table('users')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}
