@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <form method="POST" action="{{ route('login.store') }}" class="needs-validation" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="nama@perusahaan.co.id"
                    autocomplete="email"
                    required
                    autofocus
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Masukkan kata sandi"
                    autocomplete="current-password"
                    required
                >
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
            </button>
        </div>
    </form>
@endsection
