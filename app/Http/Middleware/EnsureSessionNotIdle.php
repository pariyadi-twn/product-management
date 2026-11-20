<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionNotIdle
{
    public function __construct(private readonly ActivityLogService $activityLogs) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('session.idle_timeout', 30);
        $lastActivity = (int) $request->session()->get('auth.last_activity_at', 0);

        if ($lastActivity > 0 && $lastActivity <= now()->subMinutes($timeoutMinutes)->getTimestamp()) {
            $user = $request->user();

            $this->activityLogs->record(
                ActivityLog::ACTION_LOGOUT,
                $user,
                null,
                null,
                ['success' => true, 'reason' => 'idle_timeout'],
                'Sesi berakhir otomatis karena tidak aktif.',
            );

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'Sesi Anda telah berakhir karena tidak aktif. Silakan masuk kembali.');
        }

        $request->session()->put('auth.last_activity_at', now()->getTimestamp());

        return $next($request);
    }
}
