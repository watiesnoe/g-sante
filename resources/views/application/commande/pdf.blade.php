<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande {{ $commande->reference }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .header { margin-bottom: 30px; }
        .title-box { text-align: center; margin: 20px 0; background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 5px solid #0665d0; }
        .title-box h2 { margin: 0; color: #0665d0; text-transform: uppercase; font-size: 18px; }
        
        .info-section { margin-bottom: 30px; }
        .info-table { width: 100%; border: none; }
        .info-table td { border: none; padding: 5px 0; vertical-align: top; }
        .info-label { font-weight: bold; color: #555; width: 120px; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th { background-color: #0665d0; color: white; padding: 10px; text-align: left; font-size: 10px; text-transform: uppercase; border: none; }
        table.main-table td { padding: 10px; border-bottom: 1px solid #eee; }
        table.main-table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .text-right { text-align: right; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .totals-section { margin-top: 30px; float: right; width: 300px; }
        .total-row { padding: 8px 0; border-bottom: 1px solid #eee; display: table; width: 100%; }
        .total-label { display: table-cell; font-weight: bold; }
        .total-value { display: table-cell; text-align: right; font-weight: bold; font-size: 14px; color: #0665d0; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => $commande->reference])

    <div class="title-box">
        <h2>Bon de Commande</h2>
        <span class="badge {{ $commande->statut == 'valide' ? 'badge-success' : 'badge-warning' }}">
            Statut: {{ ucfirst($commande->statut) }}
        </span>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>
                    <div class="info-label">Fournisseur:</div>
                    <div style="font-size: 13px; font-weight: bold;">{{ $commande->fournisseur->nom ?? 'N/A' }}</div>
                    <div style="color: #666;">{{ $commande->fournisseur->adresse ?? '' }}</div>
                    <div style="color: #666;">Tél: {{ $commande->fournisseur->telephone ?? '' }}</div>
                </td>
                <td style="text-align: right;">
                    <div class="info-label" style="width: auto;">Date de Commande:</div>
                    <div>{{ \Carbon\Carbon::parse($commande->date_commande)->format('d/m/Y') }}</div>
                    <div class="info-label" style="width: auto; margin-top: 5px;">Réf:</div>
                    <div style="font-weight: bold;">{{ $commande->reference }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="40">#</th>
                <th>Médicament</th>
                <th width="80" class="text-right">Qté</th>
                <th width="100" class="text-right">P.U (FCFA)</th>
                <th width="120" class="text-right">Total (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->lignes as $ligne)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $ligne->medicament->nom ?? '—' }}</div>
                        <div style="font-size: 9px; color: #888;">{{ $ligne->medicament->famille->nom ?? '' }}</div>
                    </td>
                    <td class="text-right">{{ $ligne->quantite }}</td>
                    <td class="text-right">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($ligne->total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        <div class="total-row">
            <div class="total-label" style="font-size: 16px;">Montant Total:</div>
            <div class="total-value">{{ number_format($commande->total, 0, ',', ' ') }} FCFA</div>
        </div>
    </div>

    <div style="margin-top: 100px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Le Responsable de Commande</p>
                    <div style="height: 60px;"></div>
                    <p>_______________________</p>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Le Fournisseur (Cachet & Signature)</p>
                    <div style="height: 60px;"></div>
                    <p>_______________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        G-SANTÉ - Solution de Gestion Médicale Intégrée | Page 1/1 | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
