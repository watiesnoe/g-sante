<?php
header('Content-Type: text/plain; charset=utf-8');
$cmd = 'HOME=/tmp git -C /opt/lampp/htdocs/g-sante checkout -- resources/views/application/medicament/index.blade.php resources/views/application/medicament/create.blade.php resources/views/application/medicament/show.blade.php 2>&1';
exec($cmd, $output, $returnVar);
echo "Command: $cmd\n";
echo "Return Code: $returnVar\n";
echo "Output:\n" . implode("\n", $output) . "\n";
