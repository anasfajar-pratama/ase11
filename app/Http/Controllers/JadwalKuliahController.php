<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Ruangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalKuliahController extends Controller
{
    public function index(): View
    {
        $jadwalKuliah = JadwalKuliah::with(['kelas', 'mataKuliah', 'dosen', 'ruangan'])->get();

        return view('jadwal_kuliah.index', compact('jadwalKuliah'));
    }

    public function create(): View
    {
        $kelas = Kelas::all();
        $mataKuliah = MataKuliah::all();
        $dosen = Dosen::all();
        $ruangan = Ruangan::all();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return view('jadwal_kuliah.create', compact('kelas', 'mataKuliah', 'dosen', 'ruangan', 'hari'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_kuliah_id' => ['required', 'exists:mata_kuliah,id'],
            'dosen_id' => ['required', 'exists:dosen,id'],
            'ruangan_id' => ['required', 'exists:ruangan,id'],
            'hari' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $bentrokRuangan = JadwalKuliah::where('ruangan_id', $validated['ruangan_id'])
            ->where('hari', $validated['hari'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokRuangan) {
            return back()
                ->withInput()
                ->withErrors(['ruangan_id' => 'Ruangan sudah terpakai di jadwal tersebut.']);
        }

        $bentrokDosen = JadwalKuliah::where('dosen_id', $validated['dosen_id'])
            ->where('hari', $validated['hari'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokDosen) {
            return back()
                ->withInput()
                ->withErrors(['dosen_id' => 'Dosen sudah mengajar di jadwal tersebut.']);
        }

        $bentrokKelas = JadwalKuliah::where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where(function ($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokKelas) {
            return back()
                ->withInput()
                ->withErrors(['kelas_id' => 'Kelas sudah memiliki jadwal di waktu tersebut.']);
        }

        JadwalKuliah::create($validated);

        return redirect()
            ->route('jadwal-kuliah.index')
            ->with('success', 'Jadwal kuliah berhasil ditambahkan.');
    }

    public function edit(JadwalKuliah $jadwal_kuliah): View
    {
        $kelas = Kelas::all();
        $mataKuliah = MataKuliah::all();
        $dosen = Dosen::all();
        $ruangan = Ruangan::all();
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return view('jadwal_kuliah.edit', compact('jadwal_kuliah', 'kelas', 'mataKuliah', 'dosen', 'ruangan', 'hari'));
    }

    public function update(Request $request, JadwalKuliah $jadwal_kuliah): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_kuliah_id' => ['required', 'exists:mata_kuliah,id'],
            'dosen_id' => ['required', 'exists:dosen,id'],
            'ruangan_id' => ['required', 'exists:ruangan,id'],
            'hari' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $bentrokRuangan = JadwalKuliah::where('ruangan_id', $validated['ruangan_id'])
            ->where('hari', $validated['hari'])
            ->where('id', '!=', $jadwal_kuliah->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokRuangan) {
            return back()
                ->withInput()
                ->withErrors(['ruangan_id' => 'Ruangan sudah terpakai di jadwal tersebut.']);
        }

        $bentrokDosen = JadwalKuliah::where('dosen_id', $validated['dosen_id'])
            ->where('hari', $validated['hari'])
            ->where('id', '!=', $jadwal_kuliah->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokDosen) {
            return back()
                ->withInput()
                ->withErrors(['dosen_id' => 'Dosen sudah mengajar di jadwal tersebut.']);
        }

        $bentrokKelas = JadwalKuliah::where('kelas_id', $validated['kelas_id'])
            ->where('hari', $validated['hari'])
            ->where('id', '!=', $jadwal_kuliah->id)
            ->where(function ($query) use ($validated) {
                $query->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($q) use ($validated) {
                        $q->where('jam_mulai', '<=', $validated['jam_mulai'])
                            ->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokKelas) {
            return back()
                ->withInput()
                ->withErrors(['kelas_id' => 'Kelas sudah memiliki jadwal di waktu tersebut.']);
        }

        $jadwal_kuliah->update($validated);

        return redirect()
            ->route('jadwal-kuliah.index')
            ->with('success', 'Jadwal kuliah berhasil diubah.');
    }

    public function destroy(JadwalKuliah $jadwal_kuliah): RedirectResponse
    {
        $jadwal_kuliah->delete();

        return redirect()
            ->route('jadwal-kuliah.index')
            ->with('success', 'Jadwal kuliah berhasil dihapus.');
    }
}
