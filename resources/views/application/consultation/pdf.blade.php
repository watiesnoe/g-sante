<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail Consultation #{{ $consultation->id }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        
        .section-title { background: #0665d0; color: white; padding: 8px 12px; font-weight: bold; text-transform: uppercase; margin: 20px 0 10px; border-radius: 4px; font-size: 10px; }
        
        .grid-container { width: 100%; border-collapse: collapse; }
        .grid-container td { border: 1px solid #eee; padding: 10px; vertical-align: top; width: 50%; }
        .label { font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .value { font-size: 11px; color: #000; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        table.data-table th { background-color: #f8f9fa; color: #333; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; border: 1px solid #dee2e6; }
        table.data-table td { padding: 8px; border: 1px solid #dee2e6; }
        
        .imc-box { display: inline-block; padding: 5px 10px; border-radius: 4px; font-weight: bold; }
        .imc-normal { background: #d4edda; color: #155724; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'CONS-' . date('Ymd') . '-' . $consultation->id])

    <h2 style="text-align: center; color: #0665d0; text-transform: uppercase; margin: 10px 0;">Rapport de Consultation</h2>

    <div class="section-title">Informations Générales</div>
    <table class="grid-container">
        <tr>
            <td>
                <span class="label">Patient</span>
                <span class="value" style="font-size: 14px; font-weight: bold;">{{ $consultation->patient->nom }} {{ $consultation->patient->prenom }}</span>
                <span class="value">{{ $consultation->patient->age }} ans | Sexe: {{ $consultation->patient->genre }}</span>
                <span class="value">Tél: {{ $consultation->patient->telephone }}</span>
            </td>
            <td>
                <span class="label">Médecin Prescripteur</span>
                <span class="value" style="font-weight: bold;">Dr. {{ $consultation->medecin->name }}</span>
                <span class="label" style="margin-top: 10px;">Date de consultation</span>
                <span class="value">{{ $consultation->date_consultation ?? now()->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Paramètres Vitaux (Constantes)</div>
    <table class="grid-container">
        <tr>
            <td style="width: 25%;">
                <span class="label">Poids</span>
                <span class="value">{{ $consultation->poids }} kg</span>
            </td>
            <td style="width: 25%;">
                <span class="label">Taille</span>
                <span class="value">{{ $consultation->taille }} cm</span>
            </td>
            <td style="width: 25%;">
                <span class="label">IMC</span>
                <span class="value">{{ $consultation->taille > 0 ? number_format($consultation->poids/(($consultation->taille/100)**2),2) : '-' }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">Tension</span>
                <span class="value">{{ $consultation->tension }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">Groupe Sanguin</span>
                <span class="value" style="font-weight: bold; color: #d9534f;">{{ $consultation->groupe_sanguin }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Anamnèse & Diagnostic</div>
    <table class="grid-container">
        <tr>
            <td colspan="2">
                <span class="label">Motif de consultation</span>
                <span class="value">{{ $consultation->motif }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Symptômes identifiés</span>
                <ul style="margin: 5px 0; padding-left: 15px;">
                    @foreach($consultation->symptomes as $symptome)
                        <li class="value">{{ $symptome->nom }}</li>
                    @endforeach
                </ul>
            </td>
            <td>
                <span class="label">Pathologie suspectée</span>
                <ul style="margin: 5px 0; padding-left: 15px;">
                    @foreach($consultation->maladies as $maladie)
                        <li class="value" style="font-weight: bold;">{{ $maladie->nom }}</li>
                    @endforeach
                </ul>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">Diagnostic final</span>
                <span class="value" style="font-style: italic;">{{ $consultation->diagnostic }}</span>
            </td>
        </tr>
    </table>

    @if($consultation->ordonnances->count() > 0)
    <div class="section-title">Ordonnance & Traitements</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Médicament</th>
                <th>Posologie</th>
                <th width="60" style="text-align: center;">Durée</th>
                <th width="60" style="text-align: center;">Qté</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consultation->ordonnances as $ordonnance)
                @foreach($ordonnance->medicaments as $med)
                    <tr>
                        <td style="font-weight: bold;">{{ $med->nom }}</td>
                        <td>{{ $med->pivot->posologie }}</td>
                        <td style="text-align: center;">{{ $med->pivot->duree_jours }} j</td>
                        <td style="text-align: center;">{{ $med->pivot->quantite }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @endif

    @if($consultation->examens->count() > 0)
    <div class="section-title">Examens Complémentaires</div>
    <ul style="margin: 5px 0; padding-left: 20px;">
        @foreach($consultation->examens as $ex)
            <li class="value">{{ $ex->examen }}</li>
        @endforeach
    </ul>
    @endif

    @if($consultation->hospitalisation)
    <div class="section-title">Hospitalisation</div>
    <table class="grid-container">
        <tr>
            <td>
                <span class="label">Salle / Lit</span>
                <span class="value">{{ $consultation->hospitalisation->salles_id }} / Lit: {{ $consultation->hospitalisation->lit_id }}</span>
            </td>
            <td>
                <span class="label">Date d'entrée</span>
                <span class="value">{{ $consultation->hospitalisation->date_entree }}</span>
            </td>
        </tr>
    </table>
    @endif

    <div class="footer">
        G-SANTÉ - Rapport Médical Confidentiel | Page 1/1 | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
