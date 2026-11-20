<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Buka atau tutup menu">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-md-block">
                    <a href="{{ route('products.index') }}" class="nav-link">
                        <i class="bi bi-box-seam me-1"></i>
                        Katalog Produk
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                @if(auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="{{ route('monitoring.index') }}" title="Peringatan aktif">
                            <i class="bi bi-bell"></i>
                            @if(($unresolvedAlertCount ?? 0) > 0)
                                <span class="navbar-badge badge text-bg-danger">{{ $unresolvedAlertCount }}</span>
                            @endif
                        </a>
                    </li>
                @endif
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle fs-5 me-1"></i>
                        <span class="d-none d-xl-inline">{{ auth()->user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-semibold">{{ auth()->user()->name }}</div>
                            <div class="small text-secondary">{{ auth()->user()->roleLabel() }}</div>
                            <div class="small text-secondary">{{ auth()->user()->email }}</div>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="{{ route('products.index') }}" class="brand-link">
                <span class="brand-image d-inline-flex align-items-center justify-content-center bg-primary text-white rounded">
                    <i class="bi bi-box-seam"></i>
                </span>
                <span class="brand-text fw-light">Product Manager</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <nav class="mt-2" aria-label="Navigasi utama">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Katalog Produk</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clock-history"></i>
                            <p>Catatan Aktivitas</p>
                        </a>
                    </li>
                    @if(auth()->user()->isAdmin())
                        <li class="nav-item">
                            <a href="{{ route('monitoring.index') }}" class="nav-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-activity"></i>
                                <p>Pantau Performa</p>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="{{ route('help.index') }}" class="nav-link {{ request()->routeIs('help.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-question-circle"></i>
                            <p>Bantuan</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h1 class="mb-0 fs-3">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb float-sm-end mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Beranda</a></li>
                                @yield('breadcrumb')
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="app-content">
            <div class="container-fluid">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Prototype internal</div>
        <strong>{{ config('app.name') }}</strong>
    </footer>
</div>
</body>
</html>
