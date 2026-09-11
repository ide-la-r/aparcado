<?php

namespace App\Http\Controllers;

use App\Models\Province;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'provinces' => Province::query()->orderBy('name')->get(),
        ]);
    }
}
