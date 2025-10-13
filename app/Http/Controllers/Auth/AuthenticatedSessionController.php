<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Le super-admin arrive dans l'Administration ; les autres comptes, dans leur établissement.
        // Une page d'accueil mémorisée (« / », tableau de bord) ne prime pas sur cette règle.
        $voulue = $request->session()->get('url.intended');
        if ($voulue && in_array(parse_url($voulue, PHP_URL_PATH) ?: '/', ['/', '/dashboard', '/home'], true)) {
            $request->session()->forget('url.intended');
        }

        return redirect()->intended(self::accueil($request->user()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /** Page d'arrivée après connexion, selon le compte. */
    public static function accueil(?\App\Models\User $user): string
    {
        return $user?->can('etablissements.gerer')
            ? route('plateforme.tableau', absolute: false)
            : route('dashboard', absolute: false);
    }
}
