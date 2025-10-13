<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=gsante', 'root', '');
    $stmt = $pdo->query('DESCRIBE ordonnance_medicaments');
    header('Content-Type: text/plain; charset=utf-8');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
