<?php
$logFile = '/home/snt/.gemini/antigravity-ide/brain/e5eefc26-5b33-44da-9ced-04c5d3242f92/.system_generated/logs/transcript.jsonl';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$writes = [];
$replacements = [];

$handle = fopen($logFile, 'r');
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $data = json_decode($line, true);
        if ($data && isset($data['tool_calls'])) {
            foreach ($data['tool_calls'] as $tc) {
                $name = $tc['name'];
                $args = $tc['args'];
                $target = isset($args['TargetFile']) ? trim($args['TargetFile'], '"\'') : '';
                
                if (empty($target) || strpos($target, '/opt/lampp/htdocs/g-sante/') === false) {
                    continue;
                }
                
                // Skip debug files
                if (strpos($target, 'public/git_') !== false ||
                    strpos($target, 'public/check_') !== false ||
                    strpos($target, 'public/drop_') !== false ||
                    strpos($target, 'public/run_') !== false ||
                    strpos($target, 'public/php_check') !== false ||
                    strpos($target, 'public/fix_') !== false ||
                    strpos($target, 'public/delete_') !== false ||
                    strpos($target, 'public/restore_') !== false ||
                    strpos($target, 'public/get_') !== false) {
                    continue;
                }
                
                if ($name === 'write_to_file' && isset($args['CodeContent'])) {
                    $writes[$target] = $args['CodeContent'];
                } elseif ($name === 'replace_file_content') {
                    $replacements[$target][] = [
                        'type' => 'single',
                        'target' => $args['TargetContent'],
                        'replacement' => $args['ReplacementContent']
                    ];
                } elseif ($name === 'multi_replace_file_content' && isset($args['ReplacementChunks'])) {
                    $replacements[$target][] = [
                        'type' => 'multi',
                        'chunks' => $args['ReplacementChunks']
                    ];
                }
            }
        }
    }
    fclose($handle);
}

echo "Detected writes: " . count($writes) . "\n";
echo "Detected replacements: " . count($replacements) . "\n\n";

// Restore write_to_file targets
foreach ($writes as $file => $content) {
    $dir = dirname($file);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($file, $content);
    echo "Restored (Write): $file\n";
}

// Restore replacement-only targets by checking out committed and applying replacements
$allModified = [
    '/opt/lampp/htdocs/g-sante/app/Http/Controllers/CommandeController.php',
];

foreach ($allModified as $file) {
    if (isset($writes[$file])) {
        // already handled by write
        continue;
    }
    
    // Get original from git show
    $gitPath = str_replace('/opt/lampp/htdocs/g-sante/', '', $file);
    exec('HOME=/tmp git -C /opt/lampp/htdocs/g-sante show HEAD:' . escapeshellarg($gitPath) . ' 2>&1', $output, $returnVar);
    
    if ($returnVar !== 0) {
        echo "Failed to git show: $file\n";
        continue;
    }
    
    $content = implode("\n", $output);
    
    // Apply replacements chronologically
    if (isset($replacements[$file])) {
        foreach ($replacements[$file] as $rep) {
            if ($rep['type'] === 'single') {
                $targetText = $rep['target'];
                $replaceText = $rep['replacement'];
                
                // Normalise newlines for replacement matching
                $content = str_replace("\r\n", "\n", $content);
                $targetText = str_replace("\r\n", "\n", $targetText);
                $replaceText = str_replace("\r\n", "\n", $replaceText);
                
                if (strpos($content, $targetText) !== false) {
                    $content = str_replace($targetText, $replaceText, $content);
                } else {
                    echo "Warning: Target content not found in $file for single replacement.\n";
                }
            } elseif ($rep['type'] === 'multi') {
                foreach ($rep['chunks'] as $chunk) {
                    $targetText = $chunk['TargetContent'];
                    $replaceText = $chunk['ReplacementContent'];
                    
                    $content = str_replace("\r\n", "\n", $content);
                    $targetText = str_replace("\r\n", "\n", $targetText);
                    $replaceText = str_replace("\r\n", "\n", $replaceText);
                    
                    if (strpos($content, $targetText) !== false) {
                        $content = str_replace($targetText, $replaceText, $content);
                    } else {
                        echo "Warning: Target content not found in $file for chunk replacement.\n";
                    }
                }
            }
        }
    }
    
    file_put_contents($file, $content);
    echo "Restored (Git + Replacements): $file\n";
}
