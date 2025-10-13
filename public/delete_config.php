<?php
$files = [
    '/opt/lampp/htdocs/g-sante/bootstrap/cache/config.php',
    '/opt/lampp/htdocs/g-sante/bootstrap/cache/services.php',
    '/opt/lampp/htdocs/g-sante/bootstrap/cache/packages.php',
    '/opt/lampp/htdocs/g-sante/storage/logs/laravel.log'
];

header('Content-Type: text/plain; charset=utf-8');
foreach ($files as $file) {
    if (file_exists($file)) {
        if (@unlink($file)) {
            echo "Successfully deleted: $file\n";
        } else {
            echo "Failed to delete: $file (Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . ", Owner: " . fileowner($file) . ")\n";
        }
    } else {
        echo "File does not exist: $file\n";
    }
}
