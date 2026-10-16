@extends('espace_patient.layout')
@section('titre', 'Mes rendez-vous')
@section('contenu')
    <h1>Bonjour {{ $compte->prenom }}</h1>
    <a class="btn" href="{{ route('patient.nouveau') }}">Demander un rendez-vous</a>

    <h2>Mes rendez-vous</h2>
    @forelse($demandes as $d)
        <div class="carte">
            <div class="ligne" style="justify-content:space-between; align-items:center;">
                <strong>{{ $d->creneau->translatedFormat('l j F Y') }} à {{ $d->creneau->format('H\hi') }}</strong>
                <span class="badge {{ $d->statut }}">{{ \App\Models\EnLigne\Demande::STATUTS[$d->statut] ?? $d->statut }}</span>
            </div>
            <div>{{ $d->nomMedecin() }} — {{ $d->site->nom }}</div>
            @if($d->motif)<div class="aide">{{ $d->motif }}</div>@endif
            @if($d->message)<div class="aide">Message de l'établissement : {{ $d->message }}</div>@endif

            @if($d->statut === 'proposee' && $d->creneau_propose)
                <div class="alerte info" style="margin:10px 0 0;">L'établissement vous propose le <strong>{{ $d->creneau_propose->translatedFormat('l j F') }} à {{ $d->creneau_propose->format('H\hi') }}</strong>.</div>
                <form method="POST" action="{{ route('patient.accepter', $d->uuid) }}">@csrf<button class="btn" type="submit">Accepter ce créneau</button></form>
            @endif

            @if($d->modifiable())
                <div class="ligne">
                    @if($d->statut !== 'proposee')
                        <a class="btn sec petit" href="{{ route('patient.nouveau', ['remplace' => $d->uuid]) }}">Changer d'horaire</a>
                    @endif
                    <form method="POST" action="{{ route('patient.annuler', $d->uuid) }}" onsubmit="return confirm('Annuler ce rendez-vous ?');">@csrf<button class="btn danger petit" type="submit">Annuler</button></form>
                </div>
            @elseif($d->statut === 'confirmee')
                <div class="aide" style="margin-top:6px;">Pour annuler moins de {{ $d->site->delai_annulation_heures }} h avant, appelez l'établissement{{ $d->site->telephone ? ' au ' . $d->site->telephone : '' }}.</div>
            @endif
        </div>
    @empty
        <div class="carte vide">Aucun rendez-vous pour le moment.</div>
    @endforelse
@endsection
