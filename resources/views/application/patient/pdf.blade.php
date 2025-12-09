<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dossier Patient</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        .section { margin-bottom: 20px; }
        .section h3 { background-color: #eee; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
    </style>
</head>
<body>
<h2>Dossier Patient : {{ $patient->nom }} {{ $patient->prenom }}</h2>

<div class="section">
    <h3>Informations personnelles</h3>
    <p><strong>Nom :</strong> {{ $patient->nom }}</p>
    <p><strong>Prénom :</strong> {{ $patient->prenom }}</p>
    <p><strong>Genre :</strong> {{ $patient->genre }}</p>
    <p><strong>Age :</strong> {{ $patient->age }} ans</p>
    <p><strong>Ethnie :</strong> {{ $patient->ethnie }}</p>
    <p><strong>Téléphone :</strong> {{ $patient->telephone }}</p>
    <p><strong>Adresse :</strong> {{ $patient->adresse ?? '-' }}</p>
    <p><strong>Groupe Sanguin :</strong> {{ $patient->groupe_sanguin ?? '-' }}</p>
</div>

<div class="section">
    <h3>Consultations</h3>
    @foreach($patient->consultations as $c)
        <p><strong>Date :</strong> {{ $c->date_consultation }}</p>
        <p><strong>Motif :</strong> {{ $c->motif }}</p>
        <p><strong>Diagnostic :</strong> {{ $c->diagnostic }}</p>
        @if($c->ordonnances->count())
            <p><strong>Ordonnances :</strong></p>
            <ul>
                @foreach($c->ordonnances as $o)
                    <li>{{ $o->description ?? '-' }} ({{ $o->statut ?? '-' }})</li>
                @endforeach
            </ul>
        @endif
        <hr>
    @endforeach
</div>

<div class="section">
    <h3>Hospitalisations</h3>
    @foreach($patient->hospitalisations as $h)
        <p><strong>Du :</strong> {{ $h->date_entree }} <strong>Au :</strong> {{ $h->date_sortie ?? 'En cours' }}</p>
        <p><strong>Etat :</strong> {{ $h->etat }}</p>
        <hr>
    @endforeach
</div>

<div class="section">
    <h3>Rendez-vous</h3>
    @foreach($patient->rendezVous as $rdv)
        <p>{{ $rdv->date }} - {{ $rdv->motif ?? '-' }} ({{ $rdv->statut ?? '-' }})</p>
    @endforeach
</div>

</body>
</html>
