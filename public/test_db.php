<?php
header('Content-Type: text/plain; charset=utf-8');
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->boot();
var_dump(app('db'));
