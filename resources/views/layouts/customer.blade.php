<!DOCTYPE html>
<html lang="id">
<head>
    @PwaHead
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Oemah Kopi - Aplikasi Pemesanan">
    <meta name="author" content="Oemah Kopi">
    
    <title>{{ $title ?? 'Pemesanan' }} - Oemah Kopi</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/remixicon/remixicon.css') }}" />
    
    <!-- Page CSS -->
    @yield('style')

</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
        
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Empty for now, could add menu items in the future -->
                </ul>
                
                <div class="navbar-text">
                    <i class="ri-phone-line me-1"></i> Layanan Pelanggan: <strong>0812-3456-7890</strong>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero section (optional, shown on homepage) -->
    @if(isset($showHero) && $showHero)
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4">Selamat Datang di Oemah Kopi</h1>
            <p class="lead">Nikmati berbagai menu kopi pilihan dengan suasana yang nyaman</p>
            <a href="#menu" class="btn btn-lg btn-light order-now-btn mt-3">Lihat Menu</a>
        </div>
    </div>
    @endif

    <!-- Main content -->
    <main class="oemah-kopi-bg py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; {{ date('Y') }} Oemah Kopi. Hak Cipta Dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>
                        <a href="#" class="text-decoration-none me-2"><i class="ri-instagram-line"></i></a>
                        <a href="#" class="text-decoration-none me-2"><i class="ri-facebook-circle-line"></i></a>
                        <a href="#" class="text-decoration-none"><i class="ri-twitter-line"></i></a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    @RegisterServiceWorkerScript
    <!-- Core JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    
    <!-- Sweet Alert (for notifications) -->
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    
    <!-- Theme switcher -->
    <script>
        // Check for dark mode preference
        const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        // Set theme based on browser preference initially
        if (darkModeMediaQuery.matches) {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        } else {
            document.documentElement.setAttribute('data-bs-theme', 'light');
        }
        
        // Listen for changes in theme preference
        darkModeMediaQuery.addEventListener('change', e => {
            if (e.matches) {
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        });
    </script>
    
    <!-- Display flash messages -->
    @if(session('success'))
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#696cff'
        });
    </script>
    @endif
    
    @if(session('error'))
    <script>
        Swal.fire({
            title: 'Gagal!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonColor: '#ff3e1d'
        });
    </script>
    @endif
    
    <!-- Page JS -->
    @yield('script')
</body>
</html>