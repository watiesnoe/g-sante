<?php
$logPath = '/opt/lampp/htdocs/g-sante/storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -100);
    header('Content-Type: text/plain; charset=utf-8');
    echo implode("", $lastLines);
} else {
    echo "Log file not found.\n";
}
