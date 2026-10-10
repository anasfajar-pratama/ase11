<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(): View
    {
        $mahasiswa = Mahasiswa::with('kelas')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('mahasiswa.index', compact('mahasiswa'));
    }

    public function create(): View
    {
        $kelas = Kelas::all();

        return view('mahasiswa.create', compact('kelas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mahasiswa', 'nim'),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'jurusan' => ['required', 'string', 'max:100'],
            'angkatan' => ['required', 'integer', 'min:1900', 'max:2200'],
            'email' => ['nullable', 'email', 'max:100'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
        ]);

        Mahasiswa::create($validated);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        $kelas = Kelas::all();

        return view('mahasiswa.edit', compact('mahasiswa', 'kelas'));
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => [
                'required',
                'string',
                'max:20',
                Rule::unique('mahasiswa', 'nim')->ignore($mahasiswa->id),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'jurusan' => ['required', 'string', 'max:100'],
            'angkatan' => ['required', 'integer', 'min:1900', 'max:2200'],
            'email' => ['nullable', 'email', 'max:100'],
            'kelas_id' => ['nullable', 'exists:kelas,id'],
        ]);

        $mahasiswa->update($validated);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diubah.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->delete();

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}