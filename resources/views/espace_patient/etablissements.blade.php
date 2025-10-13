@extends('espace_patient.layout')
@section('titre', 'Choisir un établissement')
@section('contenu')
    <h1>Où souhaitez-vous consulter ?</h1>
    @forelse($sites as $s)
        <a class="carte" href="{{ route('patient.nouveau', $s) }}">
            <strong>{{ $s->nom }}</strong>
            <div class="aide">{{ collect([$s->district, $s->region])->filter()->implode(', ') }}{{ $s->telephone ? ' — ' . $s->telephone : '' }}</div>
        </a>
    @empty
        <div class="carte vide">Aucun établissement ne propose de rendez-vous en ligne pour le moment.</div>
    @endforelse
    <a class="btn sec" href="{{ route('patient.accueil') }}">Retour</a>
@endsection
