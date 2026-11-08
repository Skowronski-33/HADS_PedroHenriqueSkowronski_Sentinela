<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    private const MAX_TENTATIVAS = 5;

    private const BLOQUEIO_SEGUNDOS = 60;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $throttleKey = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_TENTATIVAS)) {
            $segundos = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors(['email' => "Muitas tentativas de login. Tente novamente em {$segundos} segundo(s)."])
                ->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);

            if (! Auth::user()->ativo) {
                Auth::logout();

                return back()
                    ->withErrors(['email' => 'Este usuário está inativo. Procure um administrador.'])
                    ->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, self::BLOQUEIO_SEGUNDOS);

        return back()
            ->withErrors(['email' => 'Credenciais inválidas.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }
}
