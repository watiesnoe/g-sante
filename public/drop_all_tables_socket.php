<?php
try {
    $pdo = new PDO('mysql:dbname=gsante;unix_socket=/opt/lampp/var/mysql/mysql.sock', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Dropping " . count($tables) . " tables in database gsante:\n";
    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table` CASCADE");
        echo "Dropped table: $table\n";
    }
    
    // Also drop views if any
    $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($views as $view) {
        $pdo->exec("DROP VIEW IF EXISTS `$view` CASCADE");
        echo "Dropped view: $view\n";
    }
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    echo "Database gsante has been completely reset via unix socket!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
