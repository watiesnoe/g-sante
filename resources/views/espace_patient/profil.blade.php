@extends('espace_patient.layout')
@section('titre', 'Mon identité')
@section('contenu')
    <h1>Votre identité</h1>
    <p class="aide">Indiquez le nom et le prénom sous lesquels l'établissement vous connaît : l'accueil s'en sert pour retrouver votre dossier.</p>
    <form method="POST" action="{{ route('patient.profil.enregistrer') }}" class="carte">
        @csrf
        <label for="nom">Nom</label>
        <input id="nom" name="nom" required autocomplete="family-name" value="{{ old('nom', $compte->nom) }}">
        <label for="prenom">Prénom</label>
        <input id="prenom" name="prenom" required autocomplete="given-name" value="{{ old('prenom', $compte->prenom) }}">
        <button class="btn" type="submit">Enregistrer</button>
    </form>
@endsection
