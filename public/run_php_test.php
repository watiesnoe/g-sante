<?php
header('Content-Type: text/plain; charset=utf-8');
exec('/opt/lampp/bin/php /opt/lampp/htdocs/g-sante/public/test_medicaments_ajax.php 2>&1', $output, $returnVar);
echo "Return Var: $returnVar\n";
echo implode("\n", $output) . "\n";
