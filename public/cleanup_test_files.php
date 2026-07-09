<?php
header('Content-Type: text/plain; charset=utf-8');

$filesToDelete = [
    __DIR__ . '/test_db.php',
    __DIR__ . '/test_db_error.php',
    __DIR__ . '/test_db_ok.php',
    __DIR__ . '/test_ajax_run_final.php',
    __DIR__ . '/test_medicaments_ajax.php',
    __DIR__ . '/run_php_test.php',
    __DIR__ . '/read_laravel_log.php',
    __DIR__ . '/dump_original_views.php',
    __DIR__ . '/restore_medicament_views.php'
];

foreach ($filesToDelete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "Deleted: " . basename($file) . "\n";
        } else {
            echo "Failed to delete: " . basename($file) . "\n";
        }
    } else {
        echo "File does not exist: " . basename($file) . "\n";
    }
}
