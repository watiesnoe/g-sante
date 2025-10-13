<?php
$file = isset($_GET['file']) ? $_GET['file'] : '';
if (empty($file)) {
    echo "No file specified.\n";
    exit;
}

exec('HOME=/tmp git -C /opt/lampp/htdocs/g-sante show HEAD:' . escapeshellarg($file) . ' 2>&1', $output, $returnVar);
header('Content-Type: text/plain; charset=utf-8');
echo implode("\n", $output) . "\n";
