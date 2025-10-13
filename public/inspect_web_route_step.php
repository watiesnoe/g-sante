<?php
$logFile = '/home/snt/.gemini/antigravity-ide/brain/e5eefc26-5b33-44da-9ced-04c5d3242f92/.system_generated/logs/transcript.jsonl';
$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        if ($data && isset($data['step_index']) && $data['step_index'] === 705) {
            echo "JSON Decode Error: " . json_last_error_msg() . "\n";
            if ($data) {
                echo "Has tool_calls: " . (isset($data['tool_calls']) ? 'yes' : 'no') . "\n";
                foreach ($data['tool_calls'] as $tc) {
                    echo "Tool: " . $tc['name'] . "\n";
                    if (isset($tc['args']['CodeContent'])) {
                        $cc = $tc['args']['CodeContent'];
                        echo "CodeContent Type: " . gettype($cc) . "\n";
                        echo "CodeContent Length: " . strlen($cc) . "\n";
                        echo "CodeContent snippet: " . substr($cc, 0, 100) . "\n...\n" . substr($cc, -100) . "\n";
                    }
                }
            }
            break;
        }
    }
    fclose($handle);
}
