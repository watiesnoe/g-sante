<?php
$dir = new DirectoryIterator(__DIR__.'/app/Http/Controllers');
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'php') {
        $file = $fileinfo->getPathname();
        $content = file_get_contents($file);
        
        // This regex aims to match the addColumn('actions' block and standardizes the buttons if it can identify edit and delete
        // Given complexity, let's just inspect the first few matches
    }
}
