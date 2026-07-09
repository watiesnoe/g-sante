<?php
exec('HOME=/tmp git -C /opt/lampp/htdocs/g-sante diff app/Models/Medicament.php 2>&1', $output);
echo implode("\n", $output) . "\n";
