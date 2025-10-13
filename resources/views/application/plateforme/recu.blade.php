<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $paiement->numero }}</title>
    <style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; color: #1d2733; max-width: 720px; margin: 24px auto; padding: 0 16px; font-size: 14px; }
        h1 { font-size: 20px; margin: 0; } .muted { color: #6b7684; }
        .entete { display: flex; justify-content: space-between; border-bottom: 3px solid #0b5d8f; padding-bottom: 10px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; } th, td { text-align: left; padding: 8px; border-bottom: 1px solid #dde3e9; }
        .total { font-size: 18px; font-weight: 700; } .imprimer { margin-top: 20px; } @media print { .imprimer { display: none; } }
    </style>
</head>
<body>
    @php $a = $paiement->abonnement; @endphp
    <div class="entete">
        <div><h1>Reçu de paiement</h1><div class="muted">Abonnement à la plateforme G-SANTÉ</div></div>
        <div style="text-align:right"><strong>N° {{ $paiement->numero }}</strong><div class="muted">{{ $paiement->paye_le->format('d/m/Y') }}</div></div>
    </div>
    <p><strong>Établissement :</strong> {{ $a->etablissement->nom }}{{ $a->etablissement->district ? ' — ' . $a->etablissement->district : '' }}</p>
    <table>
        <tr><th>Formule</th><td>{{ $a->formule->nom }}</td></tr>
        <tr><th>Période couverte</th><td>du {{ $paiement->periode_debut->format('d/m/Y') }} au {{ $paiement->periode_fin->format('d/m/Y') }}</td></tr>
        <tr><th>Mode de paiement</th><td>{{ \App\Models\PaiementAbonnement::MODES[$paiement->mode] ?? $paiement->mode }}{{ $paiement->reference ? ' — réf. ' . $paiement->reference : '' }}</td></tr>
        <tr><th>Montant reçu</th><td class="total">{{ number_format((float) $paiement->montant, 0, ',', ' ') }} XOF</td></tr>
    </table>
    <p class="muted">Enregistré par {{ $paiement->user->name ?? '—' }} le {{ $paiement->created_at->format('d/m/Y à H:i') }}.</p>
    <button class="imprimer" onclick="window.print()">Imprimer</button>
</body>
</html>
