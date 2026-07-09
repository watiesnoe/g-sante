<?php
$dir = '/opt/lampp/htdocs/g-sante/database/seeders/data';
$files = array_diff(scandir($dir), ['.', '..']);
header('Content-Type: text/plain; charset=utf-8');
foreach ($files as $file) {
    $path = "$dir/$file";
    echo "$file: Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . ", Owner: " . fileowner($path) . ", Group: " . filegroup($path) . "\n";
}
