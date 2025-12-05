@extends('layouts.app')

@section('titre', "Dossier Patient : $patient->nom $patient->prenom")

@section('content')
    <div class="container mt-4">
        <h2>Dossier Patient</h2>
        <p><strong>Nom :</strong> {{ $patient->nom }}</p>
        <p><strong>Prénom :</strong> {{ $patient->prenom }}</p>
        <p><strong>Sexe :</strong> {{ $patient->sexe }}</p>
        <p><strong>Date de naissance :</strong> {{ $patient->date_naissance->format('d/m/Y') }}</p>
        <p><strong>Téléphone :</strong> {{ $patient->telephone }}</p>
        <p><strong>Adresse :</strong> {{ $patient->adresse }}</p>

        <h4 class="mt-4">Consultations</h4>
        @forelse($consultations as $consultation)
            <div class="card mb-3">
                <div class="card-header">
                    Consultation du {{ $consultation->date_consultation->format('d/m/Y') }} - Médecin : {{ $consultation->medecin->name }}
                </div>
                <div class="card-body">
                    <p><strong>Motif :</strong> {{ $consultation->motif }}</p>
                    <p><strong>Diagnostic :</strong> {{ $consultation->diagnostic }}</p>
                    <p><strong>Notes :</strong> {{ $consultation->notes }}</p>

                    <h5>Ordonnances</h5>
                    <ul>
                        @foreach($consultation->ordonnances as $ordonnance)
                            <li>{{ $ordonnance->description ?? 'Aucune ordonnance' }}</li>
                        @endforeach
                    </ul>

                    <h5>Examens</h5>
                    <ul>
                        @foreach($consultation->examens as $examen)
                            <li>{{ $examen->type }} - {{ $examen->resultat ?? 'Résultat non disponible' }}</li>
                        @endforeach
                    </ul>

                    @if($consultation->hospitalisation)
                        <h5>Hospitalisation</h5>
                        <p>Du {{ $consultation->hospitalisation->date_debut->format('d/m/Y') }} au {{ $consultation->hospitalisation->date_fin->format('d/m/Y') }}</p>
                        <p>Service : {{ $consultation->hospitalisation->service }}</p>
                    @endif

                    @if($consultation->certificat)
                        <h5>Certificat</h5>
                        <p>{{ $consultation->certificat->description }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p>Aucune consultation pour ce patient.</p>
        @endforelse

        <a href="{{ route('patients.print', $patient) }}" class="btn btn-secondary" target="_blank">Imprimer ce dossier</a>
    </div>
@endsection
