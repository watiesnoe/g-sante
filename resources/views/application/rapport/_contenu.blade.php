@php
    $f = fn($v) => number_format((float) $v, 0, ',', ' ');
    $tableau = function (string $titre, array $valeurs, bool $montant = false) use ($f) {
        $html = '<h4 style="margin:14px 0 6px">' . e($titre) . '</h4>';
        if (!$valeurs) return $html . '<p class="text-muted">Aucune donnée.</p>';
        $html .= '<table class="table table-sm table-bordered" style="width:100%;border-collapse:collapse"><tbody>';
        foreach ($valeurs as $libelle => $valeur) {
            $html .= '<tr><td>' . e($libelle) . '</td><td style="text-align:right;width:30%">' . ($montant ? $f($valeur) . ' F' : e($valeur)) . '</td></tr>';
        }
        return $html . '</tbody></table>';
    };
    $statuts = collect($rapport['activite']['passages_par_statut'])->mapWithKeys(fn($v, $k) => [\App\Models\Passage::STATUTS[$k] ?? $k => $v])->all();
@endphp
<div class="row">
    <div class="col-lg-6">
        {!! $tableau('Activité', ['Passages' => $rapport['activite']['passages'], 'Consultations' => $rapport['activite']['consultations']]) !!}
        {!! $tableau('Passages par statut', $statuts) !!}
        {!! $tableau('Consultations par médecin', $rapport['activite']['par_medecin']) !!}
        {!! $tableau('Consultations par type d\'activité', $rapport['activite']['par_type']) !!}
        {!! $tableau('Diagnostics les plus fréquents', $rapport['activite']['diagnostics']) !!}
    </div>
    <div class="col-lg-6">
        {!! $tableau('État de facturation', ['Total facturé' => $rapport['facturation']['total'], 'Part des patients' => $rapport['facturation']['part_patient'], 'Part des organismes' => $rapport['facturation']['part_assurance']], true) !!}
        <p class="small text-muted">{{ $rapport['facturation']['tickets'] }} ticket(s) non annulé(s).</p>
        {!! $tableau('Encaissements par mode (entrées)', collect($rapport['caisse']['par_mode'])->mapWithKeys(fn($m, $k) => [\App\Models\CaisseMouvement::MODES[$k] ?? $k => $m['entrees']])->all(), true) !!}
        {!! $tableau('Caisse : solde net par jour', $rapport['caisse']['par_jour'], true) !!}
        {!! $tableau('Dépenses payées par catégorie', $rapport['depenses'], true) !!}
    </div>
</div>
<h4 style="margin:14px 0 6px">Analytique par service (recettes facturées − dépenses payées)</h4>
@if(empty($rapport['analytique']))
    <p class="text-muted">Aucune donnée.</p>
@else
    <table class="table table-sm table-bordered" style="width:100%;border-collapse:collapse">
        <thead><tr><th>Service</th><th style="text-align:right">Recettes</th><th style="text-align:right">Dépenses</th><th style="text-align:right">Résultat</th></tr></thead>
        <tbody>
            @foreach($rapport['analytique'] as $service => $a)
                <tr><td>{{ $service }}</td><td style="text-align:right">{{ $f($a['recettes']) }} F</td><td style="text-align:right">{{ $f($a['depenses']) }} F</td><td style="text-align:right">{{ $f($a['resultat']) }} F</td></tr>
            @endforeach
        </tbody>
    </table>
@endif
