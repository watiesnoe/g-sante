<?php
$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : 'list';
// Prevent arbitrary command injection by whitelisting artisan prefixes
if (preg_match('/^[a-z:_\- ]+$/i', $cmd)) {
    $fullCmd = 'php /opt/lampp/htdocs/g-sante/artisan ' . $cmd . ' 2>&1';
    exec($fullCmd, $output, $returnVar);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Command: $fullCmd\n";
    echo "Return Code: $returnVar\n\n";
    echo implode("\n", $output);
} else {
    echo "Invalid command format.\n";
}
