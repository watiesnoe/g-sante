<?php
header('Content-Type: text/plain; charset=utf-8');
$files = [
    'resources/views/application/medicament/index.blade.php' => '/tmp/orig_index.blade.php',
    'resources/views/application/medicament/create.blade.php' => '/tmp/orig_create.blade.php',
    'resources/views/application/medicament/show.blade.php' => '/tmp/orig_show.blade.php'
];

foreach ($files as $repoPath => $tmpPath) {
    $cmd = "HOME=/tmp git -C /opt/lampp/htdocs/g-sante show HEAD:$repoPath > " . escapeshellarg($tmpPath) . " 2>&1";
    exec($cmd, $output, $returnVar);
    echo "Dumped HEAD:$repoPath to $tmpPath (Return: $returnVar)\n";
}
