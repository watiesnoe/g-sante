<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Espace patient : jeton CSRF vérifié comme ailleurs, mais sans cookie XSRF-TOKEN.
 * Les pages n'en ont pas besoin (formulaires classiques), et ce cookie, partagé par
 * le navigateur avec l'application interne sur une même machine, la perturberait.
 */
class CsrfEspacePatient extends ValidateCsrfToken
{
    protected $addHttpCookie = false;
}
