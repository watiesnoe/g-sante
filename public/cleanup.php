<?php
$files = [
    __DIR__ . '/check_mysql_port.php',
    __DIR__ . '/check_permissions.php',
    __DIR__ . '/check_seed_status.php',
    __DIR__ . '/check_parent_perms.php',
    __DIR__ . '/delete_config.php',
    __DIR__ . '/drop_all_tables_now.php',
    __DIR__ . '/drop_all_tables_socket.php',
    __DIR__ . '/drop_tables_standalone.php',
    __DIR__ . '/get_migrations_list.php',
    __DIR__ . '/list_databases.php',
    __DIR__ . '/list_migration_files.php',
    __DIR__ . '/read_laravel_log.php',
    __DIR__ . '/run_artisan.php',
    __DIR__ . '/show_db_tables.php',
    __DIR__ . '/restore_consultation_controller.php',
    __DIR__ . '/restore_all_files.php'
];

header('Content-Type: text/plain; charset=utf-8');
foreach ($files as $file) {
    if (file_exists($file)) {
        if (@unlink($file)) {
            echo "Deleted: " . basename($file) . "\n";
        } else {
            echo "Failed to delete: " . basename($file) . "\n";
        }
    }
}
@unlink(__FILE__);
echo "Cleanup completed successfully!\n";
