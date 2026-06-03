<?php

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(); // La page d'accueil redirige vers /login
});
