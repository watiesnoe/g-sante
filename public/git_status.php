<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "Git Status:\n";
echo shell_exec('git status 2>&1');

echo "\nGit Diff app/Http/Controllers/MedicamentController.php:\n";
echo shell_exec('git diff app/Http/Controllers/MedicamentController.php 2>&1');
