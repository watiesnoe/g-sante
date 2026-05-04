<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossier Médical - {{ $patient->nom }} {{ $patient->prenom }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        
        .section-title { background: #0665d0; color: white; padding: 8px 12px; font-weight: bold; text-transform: uppercase; margin: 20px 0 10px; border-radius: 4px; font-size: 10px; }
        
        .grid-container { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .grid-container td { border: 1px solid #eee; padding: 10px; vertical-align: top; width: 33.33%; }
        .label { font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .value { font-size: 11px; color: #000; }
        
        .item-box { border-left: 3px solid #0665d0; padding-left: 15px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #f8f9fa; }
        .item-date { font-weight: bold; color: #0665d0; font-size: 10px; margin-bottom: 5px; display: block; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    @include('layouts.pdf_header', ['docNumber' => 'DOS-' . date('Ymd') . '-' . $patient->id])

    <h2 style="text-align: center; color: #0665d0; text-transform: uppercase; margin: 10px 0;">Dossier Médical Complet</h2>

    <div class="section-title">Informations Personnelles</div>
    <table class="grid-container">
        <tr>
            <td>
                <span class="label">Nom & Prénom</span>
                <span class="value" style="font-size: 13px; font-weight: bold;">{{ $patient->nom }} {{ $patient->prenom }}</span>
            </td>
            <td>
                <span class="label">Genre / Âge</span>
                <span class="value">{{ $patient->genre }} | {{ $patient->age }} ans</span>
            </td>
            <td>
                <span class="label">Groupe Sanguin</span>
                <span class="value" style="color: #d9534f; font-weight: bold;">{{ $patient->groupe_sanguin ?? 'Non renseigné' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Téléphone</span>
                <span class="value">{{ $patient->telephone }}</span>
            </td>
            <td>
                <span class="label">Adresse</span>
                <span class="value">{{ $patient->adresse ?? '-' }}</span>
            </td>
            <td>
                <span class="label">Ethnie / Origine</span>
                <span class="value">{{ $patient->ethnie ?? '-' }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Historique des Consultations</div>
    @forelse($patient->consultations as $c)
        <div class="item-box">
            <span class="item-date">Consultation du {{ \Carbon\Carbon::parse($c->date_consultation)->format('d/m/Y') }}</span>
            <div style="margin-bottom: 5px;"><strong>Motif:</strong> {{ $c->motif }}</div>
            <div style="margin-bottom: 5px;"><strong>Diagnostic:</strong> {{ $c->diagnostic }}</div>
            @if($c->ordonnances->count())
                <div style="font-size: 9px; color: #666;">
                    <strong>Traitements prescrits:</strong> 
                    @foreach($c->ordonnances as $o)
                        {{ $o->description ?? 'Ordonnance' }}{{ !$loop->last ? ',' : '' }}
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <p style="color: #999; text-align: center;">Aucune consultation enregistrée</p>
    @endforelse

    <div class="section-title">Historique des Hospitalisations</div>
    @forelse($patient->hospitalisations as $h)
        <div class="item-box" style="border-left-color: #f0ad4e;">
            <span class="item-date" style="color: #f0ad4e;">Séjour du {{ \Carbon\Carbon::parse($h->date_entree)->format('d/m/Y') }} au {{ $h->date_sortie ? \Carbon\Carbon::parse($h->date_sortie)->format('d/m/Y') : 'En cours' }}</span>
            <div><strong>État à l'entrée/sortie:</strong> {{ $h->etat }}</div>
        </div>
    @empty
        <p style="color: #999; text-align: center;">Aucune hospitalisation enregistrée</p>
    @endforelse

    <div class="section-title">Rendez-vous à venir</div>
    @forelse($patient->rendezVous as $rdv)
        <div style="padding: 5px 10px; border-bottom: 1px solid #eee;">
            <strong>{{ \Carbon\Carbon::parse($rdv->date)->format('d/m/Y') }}</strong> - {{ $rdv->motif ?? 'Consultation de suivi' }} 
            <span style="float: right; color: #666; font-size: 9px;">Statut: {{ $rdv->statut }}</span>
        </div>
    @empty
        <p style="color: #999; text-align: center;">Aucun rendez-vous prévu</p>
    @endforelse

    <div class="footer">
        G-SANTÉ - Dossier Médical Informatisé | Page 1/1 | Généré le {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
