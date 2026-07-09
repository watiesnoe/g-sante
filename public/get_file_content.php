<?php
$step = isset($_GET['step']) ? (int)$_GET['step'] : 0;
$logFile = '/home/snt/.gemini/antigravity-ide/brain/e5eefc26-5b33-44da-9ced-04c5d3242f92/.system_generated/logs/transcript.jsonl';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        if ($data && isset($data['step_index']) && $data['step_index'] === $step) {
            header('Content-Type: text/plain; charset=utf-8');
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    echo "Step $step not found.\n";
    fclose($handle);
}
