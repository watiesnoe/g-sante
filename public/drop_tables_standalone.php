<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

try {
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'gsante',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]);
    $capsule->setAsGlobal();
    
    $conn = $capsule->getConnection();
    
    $conn->statement('SET FOREIGN_KEY_CHECKS = 0');
    
    $tables = $conn->select('SHOW TABLES');
    echo "Dropping " . count($tables) . " tables:\n";
    
    foreach ($tables as $tableObj) {
        $table = current((array)$tableObj);
        $conn->statement("DROP TABLE IF EXISTS `$table` CASCADE");
        echo "Dropped table: $table\n";
    }
    
    $conn->statement('SET FOREIGN_KEY_CHECKS = 1');
    echo "Database completely reset via standalone script!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
