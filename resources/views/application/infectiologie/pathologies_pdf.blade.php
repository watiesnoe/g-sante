<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Atlas des Pathologies Infectieuses</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background-color: #0665d0; color: #fff; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid #0665d0; }
        table.data-table td { padding: 7px; border: 1px solid #dee2e6; vertical-align: top; }
        table.data-table tr:nth-child(even) td { background: #f8f9fa; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 6px; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'PATHO-' . date('Ymd')])

    <h2 style="text-align: center; color: #0665d0; text-transform: uppercase; margin: 10px 0;">Atlas des Pathologies Infectieuses</h2>
    <p style="text-align: center; color: #666; margin: 0 0 10px;">{{ $pathologies->count() }} pathologie(s)</p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Pathologie</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 20%;">Symptômes</th>
                <th style="width: 18%;">Protocole</th>
                <th style="width: 15%;">Traitement principal</th>
                <th style="width: 7%; text-align: center;">Cas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pathologies as $p)
                <tr>
                    <td><strong>{{ $p->nom }}</strong></td>
                    <td>{{ $p->description ?: '-' }}</td>
                    <td>{{ $p->symptomes->pluck('nom')->implode(', ') ?: '-' }}</td>
                    <td>{{ $p->protocole->titre ?? '-' }}</td>
                    <td>{{ $p->protocole->traitement_principal ?? '-' }}</td>
                    <td style="text-align: center;">{{ $p->consultations_count }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align: center;">Aucune pathologie enregistrée.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">G-SANTÉ — Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
