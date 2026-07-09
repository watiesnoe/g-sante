<?php
$dir = '/opt/lampp/htdocs/g-sante/database/migrations';
$files = array_diff(scandir($dir), ['.', '..']);
header('Content-Type: text/plain; charset=utf-8');
echo "Migration files count: " . count($files) . "\n";
foreach ($files as $file) {
    $content = file_get_contents("$dir/$file");
    if (preg_match('/class\s+(\w+)/i', $content, $m)) {
        echo "$file : " . $m[1] . "\n";
    } else {
        echo "$file : (anonymous or no class)\n";
    }
}
