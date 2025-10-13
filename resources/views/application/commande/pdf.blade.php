<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande {{ $commande->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
<h2>FICHE DE COMMANDE - {{ $commande->reference }}</h2>

<p><strong>Fournisseur :</strong> {{ $commande->fournisseur->nom ?? 'N/A' }}</p>
<p><strong>Date de commande :</strong> {{ $commande->date_commande }}</p>
<p><strong>Statut :</strong> {{ ucfirst($commande->statut) }}</p>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Médicament</th>
        <th>Quantité</th>
        <th>Prix Unitaire</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($commande->lignes as $ligne)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $ligne->medicament->nom ?? '—' }}</td>
            <td>{{ $ligne->quantite }}</td>
            <td class="text-right">{{ number_format($ligne->prix_unitaire, 2, ',', ' ') }}</td>
            <td class="text-right">{{ number_format($ligne->total, 2, ',', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="4" class="text-right">Montant Total</th>
        <th class="text-right">{{ number_format($commande->montant_total, 2, ',', ' ') }} FCFA</th>
    </tr>
    </tfoot>
</table>

<p style="margin-top:30px; text-align:center;">
    <em>Document généré le {{ now()->format('d/m/Y à H:i') }}</em>
</p>
</body>
</html>
