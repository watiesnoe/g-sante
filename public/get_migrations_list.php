<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gsante', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query('SELECT * FROM migrations');
    header('Content-Type: text/plain; charset=utf-8');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
