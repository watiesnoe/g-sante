@extends('espace_patient.layout')
@section('titre', 'Connexion')
@section('contenu')
    <h1>Mes rendez-vous de santé</h1>
    <p class="aide">Prenez rendez-vous et suivez vos demandes. Pas de mot de passe : un code vous est envoyé par SMS.</p>
    <form method="POST" action="{{ route('patient.code') }}" class="carte">
        @csrf
        <label for="telephone">Votre numéro de téléphone</label>
        <input id="telephone" name="telephone" type="tel" inputmode="tel" autocomplete="tel" required value="{{ old('telephone', $telephone) }}" placeholder="Ex. 76 12 34 56">
        <button class="btn" type="submit">Recevoir mon code</button>
    </form>
@endsection
