<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu Paiement {{ $commande->reference }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .title-box { text-align: center; margin: 20px 0; background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 5px solid #0665d0; }
        .title-box h2 { margin: 0; color: #0665d0; text-transform: uppercase; font-size: 18px; }
        
        .info-section { margin-bottom: 30px; }
        .info-table { width: 100%; border: none; }
        .info-table td { border: none; padding: 5px 0; vertical-align: top; }
        .info-label { font-weight: bold; color: #666; width: 120px; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th { background-color: #0665d0; color: white; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; border: none; }
        table.data-td { padding: 10px; border-bottom: 1px solid #eee; }
        
        .text-right { text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        
        .totals-section { margin-top: 30px; float: right; width: 300px; background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .total-row { padding: 5px 0; border-bottom: 1px solid #dee2e6; }
        .total-label { font-weight: bold; }
        .total-value { float: right; font-weight: bold; color: #0665d0; font-size: 14px; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'PAY-' . $commande->reference])

    <div class="title-box">
        <h2>Reçu de Paiement Fournisseur</h2>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-label">Fournisseur:</div>
                    <div style="font-size: 13px; font-weight: bold;">{{ $commande->fournisseur->nom ?? 'N/A' }}</div>
                    <div style="color: #666;">Tél: {{ $commande->fournisseur->telephone ?? '' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="info-label" style="width: auto;">Date de Paiement:</div>
                    <div>{{ now()->format('d/m/Y') }}</div>
                    <div class="info-label" style="width: auto; margin-top: 5px;">Référence Commande:</div>
                    <div style="font-weight: bold;">{{ $commande->reference }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th>Désignation des Articles (Rappel)</th>
                <th width="100" class="text-right">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->lignes as $ligne)
                <tr>
                    <td class="data-td">
                        {{ $ligne->medicament->nom ?? '—' }} ({{ $ligne->quantite }} unités)
                    </td>
                    <td class="data-td text-right">
                        {{ number_format($ligne->total, 0, ',', ' ') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <span class="total-label">Montant Total Réglé:</span>
            <span class="total-value">{{ number_format($commande->total, 0, ',', ' ') }} FCFA</span>
        </div>
        <p style="font-size: 10px; margin-top: 10px; color: #666;">Paiement effectué intégralement.</p>
    </div>

    <div style="margin-top: 120px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>La Comptabilité</p>
                    <div style="height: 60px;"></div>
                    <p>_______________________</p>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Le Fournisseur (Acquit)</p>
                    <div style="height: 60px;"></div>
                    <p>_______________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        G-SANTÉ - Reçu de Paiement Officiel | Page 1/1 | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
