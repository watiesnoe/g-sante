<?php
$file = isset($_GET['file']) ? $_GET['file'] : '';
if (empty($file)) {
    echo "No file specified.\n";
    exit;
}

exec('git -c safe.directory="*" -C /opt/lampp/htdocs/g-sante show HEAD:' . escapeshellarg($file) . ' 2>&1', $output, $returnVar);
file_put_contents('/tmp/temp_show.txt', implode("\n", $output));
echo "Written HEAD:$file to /tmp/temp_show.txt (Return code: $returnVar)\n";
