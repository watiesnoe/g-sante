<?php
$dir = '/opt/lampp/htdocs/g-sante/database/seeders';
header('Content-Type: text/plain; charset=utf-8');
echo "database/seeders Permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . ", Owner: " . fileowner($dir) . ", Group: " . filegroup($dir) . "\n";
echo "Is writable by daemon: " . (is_writable($dir) ? 'YES' : 'NO') . "\n";
