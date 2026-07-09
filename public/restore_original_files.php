<?php
exec('HOME=/tmp git -C /opt/lampp/htdocs/g-sante checkout -- database/seeders/InfectiologieSeeder.php database/seeders/WhoGuidelinesSeeder.php database/seeders/MedicamentsSeeder.php database/migrations/2025_08_23_074217_create_medicaments_table.php database/migrations/2025_08_20_230313_create_unites_table.php 2>&1', $output, $returnVar);
echo implode("\n", $output) . "\n";
