<?php

function refactorActionsInPhpFiles($path) {
    if (is_dir($path)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isDir()) continue;
            
            $content = file_get_contents($file->getPathname());
            $originalContent = $content;
            
            // Refactoring Blade buttons: "btn btn-info btn-sm", "btn btn-warning btn-sm", "btn btn-danger btn-sm"
            // Usually found inside <a> tags or <button> tags. We want to convert them to mostly icon buttons
            // Wait, standardizes to simple icon links and buttons.
            
            // Standard edit icon link:
            // original: <a href="{{ route('xyz.edit', $x) }}" class="btn btn-sm btn-warning"><i class="fa fa-pencil-alt"></i></a>
            // new: <a href="{{ route('xyz.edit', $x) }}" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a>
            
            // Standard show icon link:
            // original: <a href="{{ route('xyz.show', $x) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
            // new: <a href="{{ route('xyz.show', $x) }}" class="btn-sm" title="Détails"><i class="fa fa-eye text-primary"></i></a>
            
            // Standard delete icon button:
            // original: <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
            // new: <button type="submit" class="btn-sm border-0 bg-transparent" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>
            // and add p-0 m-0 to the form if d-inline is used.
            
            // Regular expressions:
            
            // 1. DÉTAILS (View)
            $content = preg_replace_callback('/<a\s+href="([^"]+)"\s+class="btn\s+btn-sm\s+(?:btn-info|btn-primary|btn-outline-info)"[^>]*>.*?<i class="f[as]\s+fa-eye[^"]*"><\/i>(?:.*?Détails)?.*?<\/a>/is', function($m) {
                return '<a href="'.$m[1].'" class="btn-sm" title="Détails"><i class="fa fa-eye text-primary"></i></a>';
            }, $content);
            
            // 2. MODIFIER (Edit)
            $content = preg_replace_callback('/<a\s+href="([^"]+)"\s+class="btn\s+btn-sm\s+(?:btn-warning|btn-outline-warning)"[^>]*>.*?<i class="f[as]\s+(?:fa-pencil-alt|fa-edit)[^"]*"><\/i>(?:.*?Modifier)?.*?<\/a>/is', function($m) {
                return '<a href="'.$m[1].'" class="btn-sm" title="Modifier"><i class="fa fa-pencil-alt text-info"></i></a>';
            }, $content);
            
            // 3. SUPPRIMER (Delete) - for PHP/Blade button tags inside forms
            $content = preg_replace_callback('/<form([^>]+action="[^"]+"[^>]*)>(.*?)<button\s+type="submit"\s+class="btn\s+btn-sm\s+btn-danger"[^>]*>.*?<i class="f[as]\s+fa-trash[^"]*"><\/i>.*?<\/button>(.*?)<\/form>/is', function($m) {
                // Ensure form has inline style classes
                $formTag = $m[1];
                if (!preg_match('/class="/', $formTag)) {
                    $formTag .= ' class="d-inline m-0 p-0"';
                } else if (!preg_match('/d-inline/', $formTag)) {
                    $formTag = preg_replace('/class="/', 'class="d-inline m-0 p-0 ', $formTag);
                }
                
                // Add confirm
                if (!preg_match('/onsubmit="/', $formTag)) {
                    $formTag .= ' onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer ceci ?\');"';
                }
                
                $button = '<button type="submit" class="btn-sm border-0 bg-transparent" title="Supprimer"><i class="fa fa-trash text-danger"></i></button>';
                
                return '<form'.$formTag.'>' . $m[2] . $button . $m[3] . '</form>';
            }, $content);

            // Now, handle the Datatables strings generated in Controllers. 
            // In Controllers, they use single quotes or double quotes concatenated.
            // Example:
            // $btn .= '<a href="'.route('patients.edit',$patient->id).'" class="btn btn-warning btn-sm"><i class="fa fa-pencil-alt"></i></a>';
            
            // Because of varying string concat styles, replacing the raw HTML strings is better.
            
            // Edit button string replacements:
            $content = preg_replace('/class="btn\s+btn-sm\s+btn-warning"/i', 'class="btn-sm"', $content);
            $content = preg_replace('/class="btn\s+btn-warning\s+btn-sm"/i', 'class="btn-sm"', $content);
            $content = preg_replace('/class="fa\s+fa-pencil-alt"/i', 'class="fa fa-pencil-alt text-info"', $content);
            $content = preg_replace('/class="fa\s+fa-edit"/i', 'class="fa fa-edit text-info"', $content);
            
            // Delete button string replacements:
            $content = preg_replace('/class="btn\s+btn-sm\s+btn-danger"/i', 'class="btn-sm border-0 bg-transparent"', $content);
            $content = preg_replace('/class="btn\s+btn-danger\s+btn-sm"/i', 'class="btn-sm border-0 bg-transparent"', $content);
            $content = preg_replace('/class="fa\s+fa-trash-alt"/i', 'class="fa fa-trash text-danger"', $content);
            $content = str_replace('class="fa fa-trash"', 'class="fa fa-trash text-danger"', $content);
            $content = str_replace('class="fa fa-trash text-danger text-danger"', 'class="fa fa-trash text-danger"', $content); // prevent double

            // Form string replacements inside strings (controllers)
            $content = preg_replace('/<form action="([^"]+)" method="POST" style="display:inline-block;"/i', '<form action="$1" method="POST" class="d-inline m-0 p-0"', $content);
            $content = str_replace('style="display:inline"', 'class="d-inline m-0 p-0"', $content);
            
            if ($content !== $originalContent) {
                file_put_contents($file->getPathname(), $content);
                echo "Modifié: " . $file->getPathname() . "\n";
            }
        }
    }
}

echo "Modification des contrôleurs...\n";
refactorActionsInPhpFiles(__DIR__.'/app/Http/Controllers');

echo "Modification des vues...\n";
refactorActionsInPhpFiles(__DIR__.'/resources/views/application');

echo "Terminé.\n";
