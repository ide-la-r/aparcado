<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        /*
         * `validated()` y no `all()`: así lo único que llega al modelo es lo que
         * pasó por las reglas. La contraseña la cifra el cast `hashed` del propio
         * modelo, y `verified_at` se queda a nulo —la cuenta existe, pero hasta que
         * alguien mire los papeles no se puede publicar ni reservar—.
         */
        $user = User::create($request->validated());

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', '¡Bienvenido a Aparcado, '.$user->name.'!');
    }
}
