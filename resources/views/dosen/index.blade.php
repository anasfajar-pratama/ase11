<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dosen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h1 {
            margin-bottom: 10px;
        }

        a {
            color: #155eef;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .success {
            padding: 10px;
            background-color: #e8f7e8;
            color: #176b17;
        }

        form {
            display: inline;
        }

        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Data Dosen</h1>

    @if (session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    <p>
        <a href="{{ route('dosen.create') }}">Tambah Dosen</a>
        |
        <a href="{{ route('mahasiswa.index') }}">Data Mahasiswa</a>
    </p>

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
                        <a href="{{ route('dosen.edit', $dsn->id) }}">Ubah</a>

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