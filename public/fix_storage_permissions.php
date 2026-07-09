<?php
function chmod_recursive($path, $mode) {
    if (!file_exists($path)) return;
    
    $dir = new DirectoryIterator($path);
    foreach ($dir as $item) {
        if ($item->isDot()) continue;
        
        $itemPath = $item->getPathname();
        @chmod($itemPath, $mode);
        if ($item->isDir()) {
            chmod_recursive($itemPath, $mode);
        }
    }
    @chmod($path, $mode);
}

chmod_recursive('/opt/lampp/htdocs/g-sante/storage', 0777);
chmod_recursive('/opt/lampp/htdocs/g-sante/bootstrap/cache', 0777);
echo "Permissions fixed for storage and bootstrap/cache.\n";
