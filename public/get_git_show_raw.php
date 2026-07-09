<?php
exec('git -c safe.directory="*" -C /opt/lampp/htdocs/g-sante show HEAD:app/Http/Controllers/ConsultationController.php 2>&1', $output, $returnVar);
header('Content-Type: text/plain; charset=utf-8');
echo "Return Var: $returnVar\n";
echo "Lines: " . count($output) . "\n\n";
echo implode("\n", $output);
