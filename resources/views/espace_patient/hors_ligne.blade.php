@extends('espace_patient.layout')
@section('titre', 'Hors connexion')
@section('contenu')
    <h1>Pas de connexion</h1>
    <div class="carte">Vous n'êtes pas connecté à Internet. Vos rendez-vous s'afficheront dès que la connexion revient.</div>
    <a class="btn" href="{{ route('patient.accueil') }}">Réessayer</a>
@endsection
