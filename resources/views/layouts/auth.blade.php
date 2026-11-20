<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page bg-body-secondary">
<main class="login-box">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header text-center py-4">
            <div class="h3 mb-1">
                <i class="bi bi-box-seam text-primary me-2"></i>{{ config('app.name') }}
            </div>
            <div class="text-secondary">Masuk untuk mengelola data produk</div>
        </div>
        <div class="card-body">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>
    <p class="text-center text-secondary small mt-3">Modul internal · Akses hanya untuk pengguna berwenang</p>
</main>
</body>
</html>
