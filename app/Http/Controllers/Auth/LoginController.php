<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    public function show(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();
        $throttleKey = $this->throttleKey($request);
        $user = User::query()->where('email', $email)->first();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->activityLogs->record(
                ActivityLog::ACTION_LOGIN,
                $user,
                null,
                null,
                ['email' => $email, 'success' => false, 'reason' => 'throttled'],
                'Percobaan masuk melebihi batas untuk email '.$email.'.',
            );

            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Silakan coba lagi dalam 1 menit.',
            ]);
        }

        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            $this->activityLogs->record(
                ActivityLog::ACTION_LOGIN,
                $user,
                null,
                null,
                ['email' => $email, 'success' => false, 'reason' => 'invalid_credentials'],
                'Login gagal untuk email '.$email.'.',
            );

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        $request->user()->update(['last_login_at' => now()]);
        $request->session()->put('auth.last_activity_at', now()->getTimestamp());

        $this->activityLogs->record(
            ActivityLog::ACTION_LOGIN,
            $request->user(),
            null,
            null,
            ['email' => $request->user()->email, 'success' => true],
            'Masuk ke aplikasi.',
        );

        return redirect()
            ->intended(route('products.index'))
            ->with('success', 'Selamat datang, '.$request->user()->name.'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->activityLogs->record(
                ActivityLog::ACTION_LOGOUT,
                $user,
                null,
                null,
                ['success' => true],
                'Keluar dari aplikasi.',
            );
        }

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
