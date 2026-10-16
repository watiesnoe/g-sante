@extends('espace_patient.layout')
@section('titre', 'Demander un rendez-vous')
@section('contenu')
    <h1>{{ $remplace ? 'Changer d\'horaire' : 'Demander un rendez-vous' }}</h1>
    <div class="carte"><strong>{{ $site->nom }}</strong>
        @unless($remplace)<div><a href="{{ route('patient.nouveau') }}">Changer d'établissement</a></div>@endunless
    </div>

    <form method="GET" action="{{ route('patient.nouveau', $site) }}" class="carte">
        @if($remplace)<input type="hidden" name="remplace" value="{{ $remplace->uuid }}">@endif
        <label for="medecin">Médecin</label>
        <select id="medecin" name="medecin" onchange="this.form.submit()">
            <option value="">Choisir…</option>
            @foreach($medecins as $m)
                <option value="{{ $m->uuid }}" @selected($medecin?->uuid === $m->uuid)>{{ $m->nom }}{{ $m->service ? ' — ' . $m->service : '' }}</option>
            @endforeach
        </select>
        <noscript><button class="btn sec" type="submit">Voir les horaires</button></noscript>
    </form>

    @if($medecin)
        <form method="POST" action="{{ route('patient.demander', $site) }}">
            @csrf
            <input type="hidden" name="medecin_uuid" value="{{ $medecin->uuid }}">
            @if($remplace)<input type="hidden" name="remplace" value="{{ $remplace->uuid }}">@endif
            @forelse($creneaux as $jour => $liste)
                <h2>{{ \Illuminate\Support\Carbon::parse($jour)->translatedFormat('l j F') }}</h2>
                <div class="creneaux">
                    @foreach($liste as $c)
                        <label><input type="radio" name="creneau" value="{{ $c->debut->format('Y-m-d H:i:s') }}" required @checked(old('creneau') === $c->debut->format('Y-m-d H:i:s'))><span>{{ $c->debut->format('H\hi') }}</span></label>
                    @endforeach
                </div>
            @empty
                <div class="carte vide">Aucun horaire libre pour ce médecin. Essayez un autre médecin ou revenez plus tard.</div>
            @endforelse
            @if($creneaux->isNotEmpty())
                <label for="motif">Motif (facultatif)</label>
                <input id="motif" name="motif" maxlength="200" value="{{ old('motif', $remplace?->motif) }}" placeholder="Ex. contrôle, fièvre…">
                <button class="btn" type="submit">{{ $remplace ? 'Demander ce nouvel horaire' : 'Envoyer ma demande' }}</button>
                <p class="aide">Votre demande sera confirmée par l'établissement. Vous recevrez un SMS.</p>
            @endif
        </form>
    @endif
    <a class="btn sec" href="{{ route('patient.accueil') }}">Retour</a>
@endsection
