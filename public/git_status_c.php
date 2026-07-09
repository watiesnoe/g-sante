<?php
exec('git -c safe.directory="*" -C /opt/lampp/htdocs/g-sante status 2>&1', $output, $returnVar);
echo "Git Status:\n" . implode("\n", $output) . "\n\n";

exec('git -c safe.directory="*" -C /opt/lampp/htdocs/g-sante diff --stat 2>&1', $outputDiff, $returnVarDiff);
echo "Git Diff Stat:\n" . implode("\n", $outputDiff) . "\n";
