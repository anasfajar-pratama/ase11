<nav class="main-nav">
    <div class="nav-links">
        <a href="{{ route('mahasiswa.index') }}">Mahasiswa</a>
        <a href="{{ route('dosen.index') }}">Dosen</a>
        <a href="{{ route('mata-kuliah.index') }}">Mata Kuliah</a>
        <a href="{{ route('ruangan.index') }}">Ruangan</a>
        <a href="{{ route('kelas.index') }}">Kelas</a>
        <a href="{{ route('jadwal-kuliah.index') }}">Jadwal Kuliah</a>
    </div>

    @auth
        <div class="nav-user">
            <span class="user-info">
                {{ auth()->user()->name }}
                ({{ auth()->user()->getRoleNames()->implode(', ') }})
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    @endauth
</nav>
