<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query('SHOW DATABASES');
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: text/plain; charset=utf-8');
    echo "Databases:\n";
    print_r($dbs);
    
    foreach ($dbs as $db) {
        if ($db === 'information_schema' || $db === 'performance_schema' || $db === 'mysql' || $db === 'sys') continue;
        $pdo->exec("USE `$db`");
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        echo "Database $db has " . count($tables) . " tables:\n";
        print_r($tables);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
