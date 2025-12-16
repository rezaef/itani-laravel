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
        return view('users');
    }

    public function stok()
    {
        if ($r = $this->requireLogin()) return $r;
        return view('stok');
    }
}
