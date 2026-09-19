<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DosenController extends Controller
{
    public function index(): View
    {
        $dosen = Dosen::all();

        return view('dosen.index', compact('dosen'));
    }

    public function create(): View
    {
        return view('dosen.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nidn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nidn'),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'bidang_keahlian' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ]);

        Dosen::create($validated);

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil ditambahkan.');
    }

    public function edit(Dosen $dosen): View
    {
        return view('dosen.edit', compact('dosen'));
    }

    public function update(Request $request, Dosen $dosen): RedirectResponse
    {
        $validated = $request->validate([
            'nidn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('dosen', 'nidn')->ignore($dosen->id),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'bidang_keahlian' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
        ]);

        $dosen->update($validated);

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil diubah.');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        $dosen->delete();

        return redirect()
            ->route('dosen.index')
            ->with('success', 'Data dosen berhasil dihapus.');
    }
}