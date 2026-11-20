<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            Log::warning('Percobaan login melebihi batas.', [
                'email' => $request->string('email')->toString(),
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam 1 menit.',
            ]);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            Log::warning('Login gagal.', [
                'email' => $request->string('email')->toString(),
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $request->user()->update(['last_login_at' => now()]);

        Log::info('Login berhasil.', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->intended(route('products.index'))
            ->with('success', 'Selamat datang, '.$request->user()->name.'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Log::info('Logout.', [
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Anda telah keluar dari aplikasi.');
    }

    private function throttleKey(LoginRequest $request): string
    {
        return Str::transliterate(Str::lower($request->string('email')->toString()).'|'.$request->ip());
    }
}
