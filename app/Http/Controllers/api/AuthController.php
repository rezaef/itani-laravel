<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $input = $request->json()->all();

        $v = Validator::make($input, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => 'Username & password wajib diisi'], 400);
        }

        $username = trim($input['username']);
        $password = (string)$input['password'];

        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password_hash)) {
            return response()->json(['error' => 'Username atau password salah'], 401);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }
}
