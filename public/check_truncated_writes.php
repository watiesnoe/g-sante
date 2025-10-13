<?php
$logFile = '/home/snt/.gemini/antigravity-ide/brain/e5eefc26-5b33-44da-9ced-04c5d3242f92/.system_generated/logs/transcript.jsonl';
$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'truncated') !== false) {
            $data = json_decode($line, true);
            if ($data) {
                echo "Step " . $data['step_index'] . " contains 'truncated' in content/tool_calls.\n";
                if (isset($data['tool_calls'])) {
                    foreach ($data['tool_calls'] as $tc) {
                        $target = isset($tc['args']['TargetFile']) ? $tc['args']['TargetFile'] : '';
                        $cc = isset($tc['args']['CodeContent']) ? $tc['args']['CodeContent'] : '';
                        $rc = isset($tc['args']['ReplacementContent']) ? $tc['args']['ReplacementContent'] : '';
                        if (strpos($cc, 'truncated') !== false || strpos($rc, 'truncated') !== false) {
                            echo "  Tool Call target: $target contains truncated!\n";
                        }
                    }
                }
            }
        }
    }
    fclose($handle);
}
