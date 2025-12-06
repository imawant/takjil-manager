<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takjil Manager - Masjid An-Nur</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- Icons -->
    <link rel="icon" type="image/png" href="{{ asset('pageicon.png') }}?v=2">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="{{ route('dashboard') }}" class="logo">
                <i class="ri-moon-fill"></i> Takjil Manager
            </a>
            
            <div class="nav-links" id="navLinks">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Kalender</a>
                <a href="{{ route('donations.search') }}" class="{{ request()->routeIs('donations.search') ? 'active' : '' }}">Cari Data</a>
                <a href="{{ route('donations.recap') }}" class="{{ request()->routeIs('donations.recap') ? 'active' : '' }}">Rekap Donatur</a>
                <a href="{{ route('donations.distribution') }}" class="{{ request()->routeIs('donations.distribution') ? 'active' : '' }}">Persebaran Donasi</a>
                @can('petugas')
                <a href="{{ route('donations.flexible') }}" class="{{ request()->routeIs('donations.flexible') ? 'active' : '' }}">Donasi Tanggal Bebas</a>
                @endcan
                @can('admin')
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Kelola Petugas</a>
                <a href="{{ route('activity-logs.index') }}" class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">Log Aktivitas</a>
                @endcan
            </div>

            <div class="nav-actions">
                @auth
                    <div class="user-menu">
                        <span class="user-name"><i class="ri-user-line"></i> {{ Auth::user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="btn-text">Logout</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-text">Login</a>
                @endauth
                

                
                <button class="mobile-toggle" onclick="toggleMenu()">
                    <i class="ri-menu-line"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay" onclick="toggleMenu()"></div>

    <main class="container main-content">
        @if(session('success'))
            <div class="alert success">
                <i class="ri-checkbox-circle-line"></i> {{ session('success') }}
                <button onclick="this.parentElement.remove()" class="close-alert">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button onclick="this.parentElement.remove()" class="close-alert">&times;</button>
            </div>
        @endif
        
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 Masjid An-Nur. Takjil Manager by Imawant.</p>
        </div>
    </footer>

    <!-- Global Donation Modal -->
    @can('petugas')
        @include('components.donation-modal')
    @endcan

    <!-- Global Confirm Modal -->
    @include('components.confirm-modal')

    <script>
        window.canEdit = @json(auth()->check() && auth()->user()->can('petugas'));
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
