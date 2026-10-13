<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Suivi de cohorte</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #666; margin-bottom: 10px; }
        .kpi { margin-bottom: 10px; }
        .kpi span { display: inline-block; margin-right: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #eef2f7; }
    </style>
</head>
<body>
    <h1>Suivi de cohorte - Traitements infectieux</h1>
    <div class="meta">
        Exercice {{ $annee }}
        @if($maladieFiltre) · Pathologie : {{ $maladieFiltre->nom }} @endif
        · Édité le {{ now()->format('d/m/Y H:i') }}
    </div>
    <div class="kpi">
        <span><b>Total cas :</b> {{ $totalCas }}</span>
        <span><b>En cours :</b> {{ $enCours }}</span>
        <span><b>Guéris :</b> {{ $gueris }}</span>
        <span><b>Perdus de vue :</b> {{ $perdusDeVue }}</span>
        <span><b>Taux d'atteinte :</b> {{ $tauxAtteinte !== null ? $tauxAtteinte.'% ('.$atteinte.'/'.$evalues.' évalués)' : '—' }}</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Patient</th><th>Pathologie</th><th>Protocole</th><th>Début</th>
                <th>Dernier contact</th><th>Prochain RDV</th><th>Évolution</th><th>Statut</th>
            </tr>
        </thead>
        <tbody>
        @foreach($cohorte as $c)
            <tr>
                <td>{{ $c['patient']->nom }} {{ $c['patient']->prenom }} ({{ $c['patient']->age }} ans)</td>
                <td>{{ $c['maladie']->nom }}</td>
                <td>{{ $c['protocole']->titre }}</td>
                <td>{{ $c['debut']->format('d/m/Y') }}</td>
                <td>{{ $c['dernier_contact']->format('d/m/Y') }}</td>
                <td>{{ $c['prochain_rdv']?->format('d/m/Y H:i') ?? '—' }}</td>
                <td>{{ $c['evolution'] ?? 'Non évalué' }}</td>
                <td>{{ $c['statut'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
