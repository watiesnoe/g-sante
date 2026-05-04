<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture Hospitalisation #{{ $hospitalisation->id }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        
        .section-title { background: #0665d0; color: white; padding: 8px 12px; font-weight: bold; text-transform: uppercase; margin: 20px 0 10px; border-radius: 4px; font-size: 10px; }
        
        .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .grid-table td { border: 1px solid #eee; padding: 10px; vertical-align: top; }
        .label { font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .value { font-size: 11px; color: #000; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background-color: #f8f9fa; color: #333; padding: 10px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid #dee2e6; }
        table.data-table td { padding: 10px; border: 1px solid #dee2e6; }
        
        .totals-box { margin-top: 30px; float: right; width: 300px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; }
        .total-row { padding: 5px 0; border-bottom: 1px solid #eee; }
        .total-row:last-child { border: none; }
        .total-label { font-weight: bold; color: #666; }
        .total-value { float: right; font-weight: bold; color: #0665d0; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'HOSP-' . date('Ymd') . '-' . $hospitalisation->id])

    <h2 style="text-align: center; color: #0665d0; text-transform: uppercase; margin: 10px 0;">Facture d'Hospitalisation</h2>

    <div class="section-title">Informations Patient & Séjour</div>
    <table class="grid-table">
        <tr>
            <td style="width: 50%;">
                <span class="label">Patient</span>
                <span class="value" style="font-size: 13px; font-weight: bold;">
                    {{ $hospitalisation->consultation->patient->nom ?? '-' }} {{ $hospitalisation->consultation->patient->prenom ?? '' }}
                </span>
                <span class="label" style="margin-top: 10px;">Service</span>
                <span class="value">{{ $hospitalisation->service->nom ?? '-' }}</span>
            </td>
            <td style="width: 50%;">
                <span class="label">Date d'entrée</span>
                <span class="value">{{ \Carbon\Carbon::parse($hospitalisation->date_entree)->format('d/m/Y') }}</span>
                <span class="label" style="margin-top: 10px;">Date de sortie</span>
                <span class="value">{{ $hospitalisation->date_sortie ? \Carbon\Carbon::parse($hospitalisation->date_sortie)->format('d/m/Y') : 'En cours' }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Historique des Paiements</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Mode</th>
                <th style="text-align: right;">Total dû</th>
                <th style="text-align: right;">Montant versé</th>
                <th style="text-align: right;">Reste</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hospitalisation->paiements as $p)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($p->mode_paiement ?? '-') }}</td>
                    <td style="text-align: right;">{{ number_format($p->montant_total, 0, ',', ' ') }}</td>
                    <td style="text-align: right;">{{ number_format($p->montant_recu, 0, ',', ' ') }}</td>
                    <td style="text-align: right;">{{ number_format($p->montant_restant, 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Aucun paiement enregistré</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totals-box">
        <div class="total-row">
            <span class="total-label">Total Général:</span>
            <span class="total-value">{{ number_format($montant_total, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total Versé:</span>
            <span class="total-value">{{ number_format($montant_recu, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="total-row" style="margin-top: 10px; border-top: 2px solid #0665d0; padding-top: 10px;">
            <span class="total-label" style="font-size: 13px; color: #d9534f;">Reste à payer:</span>
            <span class="total-value" style="font-size: 14px; color: #d9534f;">{{ number_format($montant_restant, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    <div class="footer">
        G-SANTÉ - Facturation Hospitalière | Page 1/1 | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
