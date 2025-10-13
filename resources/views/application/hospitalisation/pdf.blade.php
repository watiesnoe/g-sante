<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Hospitalisation #{{ $hospitalisation->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2, h4 { margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        .text-end { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
<h2 class="center">Clinique XYZ</h2>
<p class="center"><strong>Facture Hospitalisation N° {{ $hospitalisation->id }}</strong></p>
<hr>

<table>
    <tr>
        <td>
            <strong>Patient :</strong> {{ $hospitalisation->consultation->patient->nom ?? '-' }}
            {{ $hospitalisation->consultation->patient->prenom ?? '' }}<br>
            <strong>Motif :</strong> {{ $hospitalisation->motif ?? '-' }}
        </td>
        <td>
            <strong>Date entrée :</strong> {{ $hospitalisation->date_entree }}<br>
            <strong>Date sortie :</strong> {{ $hospitalisation->date_sortie ?? 'Non sorti' }}<br>
            <strong>Service :</strong> {{ $hospitalisation->service->nom ?? '-' }}
        </td>
    </tr>
</table>

<h4>Liste des paiements</h4>
<table>
    <thead>
    <tr>
        <th>Date</th>
        <th>Mode</th>
        <th>Total</th>
        <th>Reçu</th>
        <th>Restant</th>
        <th>Statut</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($hospitalisation->paiements as $p)
        <tr>
            <td>{{ $p->date_paiement ?? '-' }}</td>
            <td>{{ ucfirst($p->mode_paiement ?? '-') }}</td>
            <td>{{ number_format($p->montant_total, 0, ',', ' ') }}</td>
            <td>{{ number_format($p->montant_recu, 0, ',', ' ') }}</td>
            <td>{{ number_format($p->montant_restant, 0, ',', ' ') }}</td>
            <td>{{ ucfirst($p->statut) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h4 class="text-end" style="margin-top:20px;">
    Total dû : {{ number_format($montant_total, 0, ',', ' ') }} FCFA<br>
    Montant reçu : {{ number_format($montant_recu, 0, ',', ' ') }} FCFA<br>
    <strong>Reste à payer : {{ number_format($montant_restant, 0, ',', ' ') }} FCFA</strong>
</h4>

<p class="center" style="margin-top:40px;">Merci pour votre confiance.</p>
</body>
</html>
