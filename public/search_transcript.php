<?php
$logFile = '/home/snt/.gemini/antigravity-ide/brain/e5eefc26-5b33-44da-9ced-04c5d3242f92/.system_generated/logs/transcript.jsonl';
if (!file_exists($logFile)) {
    echo "Log file not found at: $logFile\n";
    exit;
}

$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, '2025_08_23_074218_create_unites_table.php') !== false) {
            echo $line . "\n";
        }
    }
    fclose($handle);
} else {
    echo "Failed to open log file.\n";
}
