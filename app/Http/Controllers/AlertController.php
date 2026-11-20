<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;

class AlertController extends Controller
{
    public function resolve(Alert $alert, AlertService $alerts): RedirectResponse
    {
        $alerts->resolve($alert);

        return redirect()
            ->route('monitoring.index')
            ->with('success', 'Peringatan "'.$alert->typeLabel().'" ditandai selesai.');
    }
}
