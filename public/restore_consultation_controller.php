<?php
$content = file_get_contents('/tmp/temp_show.txt');
if (empty($content)) {
    echo "Source file /tmp/temp_show.txt is empty or does not exist.\n";
    exit;
}

// Replace in create
$target = "\$medicaments = DB::table('medicaments')->select('id', 'nom', 'prix_vente', 'stock')->orderBy('nom')->get();";

$replacement = "\$medicaments = DB::table('medicaments')
            ->select('id', 'nom', 'stock')
            ->addSelect([
                'prix_vente' => DB::table('unites')
                    ->select('prix_vente')
                    ->whereColumn('medicament_id', 'medicaments.id')
                    ->orderBy('is_default', 'desc')
                    ->orderBy('id', 'asc')
                    ->limit(1)
            ])
            ->orderBy('nom')
            ->get();";

$content = str_replace($target, $replacement, $content);

file_put_contents('/opt/lampp/htdocs/g-sante/app/Http/Controllers/ConsultationController.php', $content);
echo "Successfully restored and patched app/Http/Controllers/ConsultationController.php\n";
