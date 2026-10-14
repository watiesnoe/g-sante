<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 15px 25px; }
        h2 { text-align: center; text-transform: uppercase; margin: 8px 0 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        td { border: 1px solid #999; padding: 6px; vertical-align: top; }
        td.l { width: 30%; background: #f2f2f2; font-weight: bold; }
        .signature { margin-top: 40px; text-align: right; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => $reference->numero])
    <h2>{{ $type === 'reference' ? ($reference->evacuation ? 'Fiche d\'évacuation sanitaire' : 'Fiche de référence') : 'Fiche de contre-référence' }}</h2>
    <table>
        <tr><td class="l">Patient</td><td>{{ mb_strtoupper($reference->patient->nom) }} {{ $reference->patient->prenom }} — {{ $reference->patient->genre === 'F' ? 'F' : 'M' }}, {{ $reference->patient->age }} ans — dossier {{ $reference->patient->numero_dossier }}</td></tr>
        <tr><td class="l">Date</td><td>{{ $reference->date_reference->format('d/m/Y H:i') }}</td></tr>
        <tr><td class="l">Structure d'origine</td><td>{{ $reference->structure_origine }}</td></tr>
        <tr><td class="l">Structure de destination</td><td>{{ $reference->structure_destination }}</td></tr>
        <tr><td class="l">Motif</td><td>{!! nl2br(e($reference->motif)) !!}</td></tr>
        @if($type === 'reference')
            <tr><td class="l">Soins réalisés</td><td>{!! nl2br(e($reference->soins_realises ?? '—')) !!}</td></tr>
            <tr><td class="l">Transport</td><td>{{ \App\Models\Reference::TRANSPORTS[$reference->transport] ?? '—' }}</td></tr>
        @else
            <tr><td class="l">Contre-référence</td><td>{!! nl2br(e($reference->contre_reference)) !!}</td></tr>
            <tr><td class="l">Date de retour</td><td>{{ $reference->contre_reference_at?->format('d/m/Y H:i') }}</td></tr>
        @endif
    </table>
    <div class="signature">Nom, signature et cachet<br><br><strong>{{ $reference->user->name ?? '' }}</strong></div>
    <p style="font-size:9px;color:#777">Modèle de fiche à aligner sur le modèle officiel du district (cahier des charges, section 18).</p>
</body>
</html>
