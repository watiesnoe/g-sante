@extends('espace_patient.layout')
@section('titre', 'Code de connexion')
@section('contenu')
    <h1>Saisissez votre code</h1>
    @if(session('demo_code'))
        <div class="alerte demo">Mode démonstration (aucun opérateur SMS branché) : votre code est <strong>{{ session('demo_code') }}</strong>.</div>
    @endif
    <form method="POST" action="{{ route('patient.verifier') }}" class="carte">
        @csrf
        <p class="aide">Code envoyé au {{ $telephone }}. Il expire dans 10 minutes.</p>
        <label for="code">Code à 6 chiffres</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required autofocus>
        <button class="btn" type="submit">Me connecter</button>
    </form>
    <form method="POST" action="{{ route('patient.code') }}">
        @csrf
        <input type="hidden" name="telephone" value="{{ $telephone }}">
        <button class="btn sec" type="submit">Renvoyer un code</button>
    </form>
    <a class="btn sec" href="{{ route('patient.connexion') }}">Changer de numéro</a>
@endsection
