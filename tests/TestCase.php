<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Désactiver la vérification CSRF dans tous les tests
        // pour éviter les erreurs 419 sur PATCH/PUT/DELETE
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
