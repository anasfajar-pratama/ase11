<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dosen</title>
    <style>
        @include('partials.style')
    </style>
</head>
<body>
    <h1>Data Dosen</h1>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    @include('partials.nav')

    @role('admin')
        <p>
            <a href="{{ route('dosen.create') }}" class="btn">Tambah Dosen</a>
        </p>
    @endrole

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIDN</th>
                <th>Nama</th>
                <th>Bidang Keahlian</th>
                <th>Email</th>
                <th>No. Telepon</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dosen as $dsn)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $dsn->nidn }}</td>
                    <td>{{ $dsn->nama }}</td>
                    <td>{{ $dsn->bidang_keahlian }}</td>
                    <td>{{ $dsn->email }}</td>
                    <td>{{ $dsn->no_telepon }}</td>
                    <td>
                        @role('admin')
                            <a href="{{ route('dosen.edit', $dsn->id) }}" class="btn">Ubah</a>

                            <form
                                action="{{ route('dosen.destroy', $dsn->id) }}"
                                method="POST"
                                style="display: inline"
                                onsubmit="return confirm('Yakin ingin menghapus data dosen ini?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        @else
                            <span>-</span>
                        @endrole
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data dosen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
