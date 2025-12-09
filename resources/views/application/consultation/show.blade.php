@extends('layouts.app')

@section('titre', 'Détail Consultation')

@section('content')
    <div class="container mt-4">
        <h2 class="text-center mb-4">Détail Consultation #{{ $consultation->id }}</h2>

        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">Patient</div>
            <div class="card-body">
                <p><strong>Nom :</strong> {{ $consultation->patient->nom }} {{ $consultation->patient->prenom }}</p>
                <p><strong>Téléphone :</strong> {{ $consultation->patient->telephone }}</p>
                <p><strong>Adresse :</strong> {{ $consultation->adresse_patient }}</p>
                <p><strong>Ticket :</strong> {{ $consultation->ticket_id }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">Consultation</div>
            <div class="card-body">
                <p><strong>Médecin :</strong> {{ $consultation->medecin->name }}</p>
                <p><strong>Date :</strong> {{ $consultation->date_consultation ?? now()->format('d/m/Y') }}</p>
                <p><strong>Motif :</strong> {{ $consultation->motif }}</p>
                <p><strong>Diagnostic :</strong> {{ $consultation->diagnostic }}</p>
                <p><strong>Notes / Antécédents :</strong> {{ $consultation->notes }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">Constantes</div>
            <div class="card-body">
                <p><strong>Poids :</strong> {{ $consultation->poids }} kg</p>
                <p><strong>Taille :</strong> {{ $consultation->taille }} cm</p>
                <p><strong>IMC :</strong> {{ $consultation->taille > 0 ? number_format($consultation->poids/(($consultation->taille/100)**2),2) : '-' }}</p>
                <p><strong>Tension :</strong> {{ $consultation->tension }}</p>
                <p><strong>Groupe sanguin :</strong> {{ $consultation->groupe_sanguin }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success text-white">Symptômes & Maladies</div>
            <div class="card-body">
                <p><strong>Symptômes :</strong></p>
                <ul>
                    @foreach($consultation->symptomes as $symptome)
                        <li>{{ $symptome->nom }}</li>
                    @endforeach
                </ul>

                <p><strong>Maladies :</strong></p>
                <ul>
                    @foreach($consultation->maladies as $maladie)
                        <li>{{ $maladie->nom }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">Ordonnances / Médicaments</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Médicament</th>
                        <th>Posologie</th>
                        <th>Durée (jours)</th>
                        <th>Quantité</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($consultation->ordonnances as $ordonnance)
                        @foreach($ordonnance->medicaments as $med)
                            <tr>
                                <td>{{ $med->nom }}</td>
                                <td>{{ $med->pivot->posologie }}</td>
                                <td>{{ $med->pivot->duree_jours }}</td>
                                <td>{{ $med->pivot->quantite }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">Examens</div>
            <div class="card-body">
                <ul>
                    @foreach($consultation->examens as $ex)
                        <li>{{ $ex->examen }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">Rendez-vous</div>
            <div class="card-body">
                <ul>
                    @foreach($consultation->rendezVous as $r)
                        <li>{{ \Carbon\Carbon::parse($r->date_heure)->format('d/m/Y H:i') }} - {{ $r->motif }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success text-white">Certificat</div>
            <div class="card-body">
                <p>{{ $consultation->certificat->contenu ?? '-' }}</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-danger text-white">Hospitalisation</div>
            <div class="card-body">
                @if($consultation->hospitalisation)
                    <p>Date entrée : {{ $consultation->hospitalisation->date_entree }}</p>
                    <p>Salle : {{ $consultation->hospitalisation->salles_id }}</p>
                    <p>Lit : {{ $consultation->hospitalisation->lit_id }}</p>
                    <p>Observations : {{ $consultation->hospitalisation->observations }}</p>
                @else
                    <p>Aucune hospitalisation</p>
                @endif
            </div>
        </div>

        <div class="text-center mb-5">
            <button onclick="window.print()" class="btn btn-primary">🖨 Imprimer</button>
        </div>
    </div>
@endsection
