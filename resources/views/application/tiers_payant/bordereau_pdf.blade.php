<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px; }
        th { background: #eee; }
        .droite { text-align: right; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => $bordereau->numero])
    <h3 style="text-align:center">Bordereau de prise en charge — {{ $bordereau->assurance->nom }}</h3>
    <p>Période du {{ $bordereau->periode_du->format('d/m/Y') }} au {{ $bordereau->periode_au->format('d/m/Y') }}</p>
    <table>
        <thead><tr><th>Date</th><th>Patient</th><th>N° dossier</th><th>Acte</th><th class="droite">Montant (FCFA)</th></tr></thead>
        <tbody>
            @foreach($bordereau->lignes as $l)
                <tr>
                    <td>{{ $l->date_acte->format('d/m/Y') }}</td>
                    <td>{{ $l->patient ? mb_strtoupper($l->patient->nom) . ' ' . $l->patient->prenom : '-' }}</td>
                    <td>{{ $l->patient->numero_dossier ?? '' }}</td>
                    <td>{{ $l->libelle }}</td>
                    <td class="droite">{{ number_format($l->montant, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot><tr><th colspan="4">Total</th><th class="droite">{{ number_format($bordereau->montant, 0, ',', ' ') }}</th></tr></tfoot>
    </table>
</body>
</html>
