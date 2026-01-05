<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        $role = strtolower((string)(auth()->user()->role ?? ''));
        if ($role !== 'admin') return redirect('/index.php');

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
        return view('panen');
    }
}
