<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Bon d'examens - {{ $patient->nom }} {{ $patient->prenom }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
        }

        .header {
            margin-bottom: 20px;
        }

        .patient-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }

        .patient-title {
            font-weight: bold;
            color: #0665d0;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 10px;
        }

        .doc-title {
            text-align: center;
            margin: 20px 0;
        }

        .doc-title h2 {
            margin: 0;
            color: #0665d0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 3px;
            border-bottom: 2px solid #0665d0;
            display: inline-block;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #0665d0;
            color: white;
            padding: 10px;
            text-align: left;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .notes {
            color: #666;
            font-style: italic;
            font-size: 10px;
            margin-top: 4px;
        }

        .statut-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 9px;
            text-transform: uppercase;
        }

        .statut-realise {
            background: #d4edda;
            color: #155724;
        }

        .statut-attente {
            background: #fff3cd;
            color: #856404;
        }

        .footer-sig {
            margin-top: 50px;
            text-align: right;
            padding-right: 50px;
        }

        .footer-sig p {
            margin-bottom: 60px;
            font-weight: bold;
        }

        .page-footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    @include('layouts.pdf_header', [
        'docNumber' => 'EXM-' . date('Ymd') . '-' . $consultation->id,
    ])

    <div class="doc-title">
        <h2>Bon d'Examens</h2>
    </div>

    <div class="patient-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 70%; border: none; padding: 0;">
                    <div class="patient-title">Patient</div>
                    <div style="font-size: 14px; font-weight: bold;">{{ $patient->nom }} {{ $patient->prenom }}</div>
                    <div style="color: #555;">Âge: {{ $patient->age }} | Sexe: {{ $patient->genre }}</div>
                </td>
                <td style="width: 30%; border: none; padding: 0; text-align: right;">
                    <div class="patient-title">Prescripteur</div>
                    <div style="font-weight: bold;">Dr. {{ $medecin->name ?? '-' }}</div>
                    <div style="color: #555;">{{ \Carbon\Carbon::parse($consultation->date_consultation)->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Examen prescrit</th>
                <th width="110" style="text-align: center;">Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($examens as $examen)
                <tr>
                    <td>
                        <div style="font-weight: bold; font-size: 12px;">{{ $examen->examen }}</div>
                        @if ($examen->notes)
                            <div class="notes">{{ $examen->notes }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if ($examen->statut === 'realise')
                            <span class="statut-badge statut-realise">Réalisé</span>
                        @else
                            <span class="statut-badge statut-attente">En attente</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
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
