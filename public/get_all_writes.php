<?php
$logFile = '/home/snt/.gemini/antigravity-ide/brain/e5eefc26-5b33-44da-9ced-04c5d3242f92/.system_generated/logs/transcript.jsonl';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        if ($data && isset($data['tool_calls'])) {
            foreach ($data['tool_calls'] as $tc) {
                if (in_array($tc['name'], ['write_to_file', 'replace_file_content', 'multi_replace_file_content'])) {
                    $args = $tc['args'];
                    $target = isset($args['TargetFile']) ? $args['TargetFile'] : '';
                    if (strpos($target, 'Public') !== false || strpos($target, 'Check_') !== false || strpos($target, 'Git_') !== false) {
                        // skip temporary script writes to keep output clean
                        continue;
                    }
                    echo "==================================================\n";
                    echo "STEP: " . $data['step_index'] . " | TOOL: " . $tc['name'] . "\n";
                    echo "TARGET: " . $target . "\n";
                    if (isset($args['CodeContent'])) {
                        echo "CONTENT LENGTH: " . strlen($args['CodeContent']) . "\n";
                        echo substr($args['CodeContent'], 0, 300) . "...\n";
                    }
                    if (isset($args['ReplacementContent'])) {
                        echo "REPLACEMENT CONTENT LENGTH: " . strlen($args['ReplacementContent']) . "\n";
                        echo substr($args['ReplacementContent'], 0, 300) . "...\n";
                    }
                }
            }
        }
    }
    fclose($handle);
}
