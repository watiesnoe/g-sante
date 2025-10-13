<?php
exec('HOME=/tmp git -C /opt/lampp/htdocs/g-sante show HEAD:database/seeders/MedicamentsSeeder.php 2>&1', $output, $returnVar);
if ($returnVar === 0) {
    file_put_contents('/opt/lampp/htdocs/g-sante/database/seeders/MedicamentsSeeder.php', implode("\n", $output));
    echo "Restored original MedicamentsSeeder.php\n";
} else {
    echo "Failed to restore: " . implode("\n", $output) . "\n";
}
