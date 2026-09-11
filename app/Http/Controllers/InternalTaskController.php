<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * Lo que en un servidor normal haría el cron. Aquí lo llama GitHub Actions una
 * vez al día, y de paso despierta el contenedor dormido.
 */
class InternalTaskController extends Controller
{
    public function closeBookings(): JsonResponse
    {
        Artisan::call('aparcado:close-bookings');

        return response()->json(['ok' => true, 'output' => trim(Artisan::output())]);
    }
}
