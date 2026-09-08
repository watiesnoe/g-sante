<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Réception {{ $reception->reference_reception }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        .header { margin-bottom: 30px; }
        .title-box { text-align: center; margin: 20px 0; background: #f0f7ff; padding: 12px; border-radius: 8px; border-left: 5px solid #2c7fb8; }
        .title-box h2 { margin: 0; color: #2c7fb8; text-transform: uppercase; font-size: 18px; }
        
        .info-section { margin-bottom: 25px; }
        .info-table { width: 100%; border: none; }
        .info-table td { border: none; padding: 4px 0; vertical-align: top; }
        .info-label { font-weight: bold; color: #555; }
        
        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th { background-color: #2c7fb8; color: white; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; border: none; }
        table.main-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        table.main-table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background: #d4edda; color: #155724; }
        
        .totals-section { margin-top: 25px; float: right; width: 300px; }
        .total-row { padding: 6px 0; border-bottom: 1px solid #eee; display: table; width: 100%; }
        .total-label { display: table-cell; font-weight: bold; }
        .total-value { display: table-cell; text-align: right; font-weight: bold; font-size: 13px; color: #2c7fb8; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => $reception->reference_reception])

    <div class="title-box">
        <h2>Bon de Réception</h2>
        <span class="badge badge-success">
            Réf: {{ $reception->reference_reception }}
        </span>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="info-label">Fournisseur:</div>
                    <div style="font-size: 13px; font-weight: bold;">{{ $reception->fournisseur->nom ?? $reception->commande->fournisseur->nom ?? 'N/A' }}</div>
                    <div style="color: #666;">{{ $reception->fournisseur->adresse ?? '' }}</div>
                    <div style="color: #666;">Tél: {{ $reception->fournisseur->telephone ?? '' }}</div>
                </td>
                <td width="50%" style="text-align: right;">
                    <div><strong>Date de Réception:</strong> {{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}</div>
                    <div style="margin-top: 4px;"><strong>Commande Liée:</strong> {{ $reception->commande->reference ?? 'CMD-'.$reception->commande_id }}</div>
                    @if($reception->user)
                    <div style="margin-top: 4px;"><strong>Réceptionné par:</strong> {{ $reception->user->name }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @php
        $lignesRecues = $reception->lignes->filter(fn($l) => $l->quantite_recue > 0);
        $totalRecu = 0;
        $totalMontant = 0;
    @endphp

    <table class="main-table">
        <thead>
            <tr>
                <th width="25">#</th>
                <th>Médicament</th>
                <th width="65">Unité</th>
                <th width="65">N° Lot</th>
                <th width="75">Péremption</th>
                <th width="55" class="text-center">Qte Cmd</th>
                <th width="55" class="text-center">Qte Reçue</th>
                <th width="80" class="text-right">P.U (FCFA)</th>
                <th width="90" class="text-right">Total (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lignesRecues as $ligne)
                @php
                    $totalLigne = $ligne->quantite_recue * $ligne->prix_unitaire;
                    $totalRecu += $ligne->quantite_recue;
                    $totalMontant += $totalLigne;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="font-weight: bold;">{{ $ligne->medicament->nom ?? '—' }}</td>
                    <td>{{ $ligne->unite->nom ?? '-' }}</td>
                    <td>{{ $ligne->lot ?? '-' }}</td>
                    <td>{{ $ligne->date_peremption ? \Carbon\Carbon::parse($ligne->date_peremption)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $ligne->quantite_commandee }}</td>
                    <td class="text-center" style="font-weight: bold; color: #155724;">{{ $ligne->quantite_recue }}</td>
                    <td class="text-right">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ number_format($totalLigne, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #999;">Aucun produit reçu enregistré.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($lignesRecues->count() > 0)
    <div class="totals-section">
        <div class="total-row">
            <div class="total-label">Total Articles Reçus:</div>
            <div class="total-value" style="font-size: 13px;">{{ number_format($totalRecu, 0, ',', ' ') }}</div>
        </div>
        <div class="total-row">
            <div class="total-label" style="font-size: 14px;">Montant Total:</div>
            <div class="total-value" style="font-size: 14px;">{{ number_format($totalMontant, 0, ',', ' ') }} FCFA</div>
        </div>
    </div>
    @endif

    <div style="margin-top: 100px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Le Réceptionnaire (Nom & Signature)</p>
                    <div style="height: 50px;"></div>
                    <p>_______________________</p>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Le Livreur / Fournisseur (Signature)</p>
                    <div style="height: 50px;"></div>
                    <p>_______________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        G-SANTÉ - Solution de Gestion Médicale Intégrée | Bon de Réception | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
