<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->boot();
var_dump(app('db'));
