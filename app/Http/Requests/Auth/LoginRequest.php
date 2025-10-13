<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // SEC-02 : compte verrouillé 15 minutes après 5 échecs consécutifs
        $compte = \App\Models\User::where('email', $this->string('email'))->first();
        if ($compte?->verrouille_jusqua && $compte->verrouille_jusqua->isFuture()) {
            throw ValidationException::withMessages([
                'email' => 'Compte verrouillé après plusieurs mots de passe erronés. Réessayez après ' . $compte->verrouille_jusqua->format('H:i') . ' ou contactez l\'administrateur.',
            ]);
        }

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            if ($compte) {
                $echecs = $compte->echecs_connexion + 1;
                $compte->forceFill($echecs >= 5
                    ? ['echecs_connexion' => 0, 'verrouille_jusqua' => now()->addMinutes(15)]
                    : ['echecs_connexion' => $echecs])->saveQuietly();
            }

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if ($compte && ($compte->echecs_connexion || $compte->verrouille_jusqua)) {
            $compte->forceFill(['echecs_connexion' => 0, 'verrouille_jusqua' => null])->saveQuietly();
        }

        $user = Auth::user();
        if ($user->statut !== 'actif') {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Votre compte est inactif ou suspendu. Veuillez contacter l\'administrateur.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
