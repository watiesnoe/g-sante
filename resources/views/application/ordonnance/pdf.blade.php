<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ordonnance - {{ $patient->nom_patient }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.6; }
        .header { margin-bottom: 20px; }
        
        .patient-box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 25px; }
        .patient-title { font-weight: bold; color: #0665d0; margin-bottom: 5px; text-transform: uppercase; font-size: 10px; }
        
        .doc-title { text-align: center; margin: 20px 0; }
        .doc-title h2 { margin: 0; color: #0665d0; font-size: 22px; text-transform: uppercase; letter-spacing: 3px; border-bottom: 2px solid #0665d0; display: inline-block; padding-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #0665d0; color: white; padding: 10px; text-align: left; text-transform: uppercase; font-size: 10px; }
        td { padding: 12px 10px; border-bottom: 1px solid #eee; vertical-align: top; }
        
        .posologie { color: #666; font-style: italic; font-size: 10px; margin-top: 4px; }
        .total-row { background-color: #f8f9fa; font-weight: bold; font-size: 12px; }
        
        .footer-sig { margin-top: 50px; text-align: right; padding-right: 50px; }
        .footer-sig p { margin-bottom: 60px; font-weight: bold; }
        
        .page-footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'ORD-' . date('Ymd') . '-' . ($ordonnance->id ?? $patient->id ?? '0')])

    <div class="doc-title">
        <h2>Ordonnance Médicale</h2>
    </div>

    <div class="patient-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 70%; border: none; padding: 0;">
                    <div class="patient-title">Patient</div>
                    <div style="font-size: 14px; font-weight: bold;">{{ $patient->nom_patient }} {{ $patient->prenom_patient }}</div>
                    <div style="color: #555;">Âge: {{ $patient->age_patient }} | Sexe: {{ $patient->genre }}</div>
                </td>
                <td style="width: 30%; border: none; padding: 0; text-align: right;">
                    <div class="patient-title">Prescripteur</div>
                    <div style="font-weight: bold;">Dr. {{ $patient->nom_medecin }} {{ $patient->prenom_medecin }}</div>
                    <div style="color: #555;">Médecin Généraliste</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Médicament & Posologie</th>
                <th width="100" style="text-align: right;">Prix (Estimation)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medicaments as $med)
                <tr>
                    <td>
                        <div style="font-weight: bold; font-size: 12px;">{{ $med->nom }}</div>
                        <div class="posologie">{{ $med->pivot->posologie }}</div>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        @if(empty($med->pivot->statut_vente) || $med->pivot->statut_vente === 'non_disponible')
                            <span style="color: #999;">-</span>
                        @else
                            {{ number_format(($med->prix_vente ?? 0) * ($med->pivot->quantite ?? 1), 0, ',', ' ') }} FCFA
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td style="text-align: right;">TOTAL ESTIMÉ</td>
                <td style="text-align: right; color: #0665d0;">{{ number_format($totale, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-sig">
        <p>Cachet et Signature du Médecin</p>
        <div style="border-bottom: 1px solid #333; width: 200px; display: inline-block;"></div>
    </div>

    <div class="page-footer">
        G-SANTÉ - La santé à portée de main | Document officiel | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
