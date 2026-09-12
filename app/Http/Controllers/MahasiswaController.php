<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


class MahasiswaController extends Controller
{
    public function index(): View
    {
        $mahasiswa = Mahasiswa::all();

        return view('mahasiswa.index', compact('mahasiswa'));
    }
}
