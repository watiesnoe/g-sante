@extends('layouts.app')

@section('titre', "Dossier de {$patient->nom} {$patient->prenom}")

@section('content')
    <div class="container mt-4">

        <!-- En-tête et impression -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Dossier Patient : {{ $patient->nom }} {{ $patient->prenom }}</h2>
            <button class="btn btn-primary" onclick="window.print()">Imprimer Dossier</button>
        </div>

        <!-- ============================= -->
        <!-- 1. Informations personnelles -->
        <!-- ============================= -->
        <div class="card mb-3">
            <div class="card-header bg-info text-white">Informations Personnelles</div>
            <div class="card-body">
                <p><strong>Nom :</strong> {{ $patient->nom }}</p>
                <p><strong>Prénom :</strong> {{ $patient->prenom }}</p>
                <p><strong>Genre :</strong> {{ $patient->genre }}</p>
                <p><strong>Age :</strong> {{ $patient->age }} ans</p>
                <p><strong>Ethnie :</strong> {{ $patient->ethnie }}</p>
                <p><strong>Téléphone :</strong> {{ $patient->telephone }}</p>
                <p><strong>Adresse :</strong> {{ $patient->adresse ?? '-' }}</p>
                <p><strong>Groupe Sanguin :</strong> {{ $patient->groupe_sanguin ?? '-' }}</p>
            </div>
        </div>

        <!-- ============================= -->
        <!-- 2. Consultations -->
        <!-- ============================= -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white">Consultations</div>
            <div class="card-body">
                @if($patient->consultations->count())
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Motif</th>
                            <th>Diagnostic</th>
                            <th>Notes</th>
                            <th>Poids (kg)</th>
                            <th>Tension</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($patient->consultations->sortByDesc('date_consultation') as $c)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($c->date_consultation)->format('d/m/Y') }}</td>
                                <td>{{ $c->motif }}</td>
                                <td>{{ $c->diagnostic }}</td>
                                <td>{{ $c->notes }}</td>
                                <td>{{ $c->poids }}</td>
                                <td>{{ $c->tension }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Aucune consultation trouvée.</p>
                @endif
            </div>
        </div>

        <!-- ============================= -->
        <!-- 3. Ordonnances -->
        <!-- ============================= -->
        <!-- 3. Ordonnances (Tableau par ordonnance) -->
        <!-- ============================= -->
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">Ordonnances</div>
            <div class="card-body">
                @php
                    $ordonnances = $patient->consultations
                                    ->flatMap->ordonnances
                                    ->sortByDesc('date')
                                    ->unique('id');
                @endphp

                @if($ordonnances->count())
                    @foreach($ordonnances as $o)
                        <div class="mb-4 p-2 border rounded">
                            <p><strong>Date Consultation :</strong> {{ \Carbon\Carbon::parse($o->consultation->date_consultation ?? $o->date)->format('d/m/Y') }}</p>
                            <p><strong>Statut :</strong> {{ $o->statutordo ?? '-' }}</p>

                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                <tr>
                                    <th>Médicament</th>
                                    <th>Posologie</th>
                                    <th>Durée (jours)</th>
                                    <th>Quantité</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($o->medicaments as $m)
                                    <tr>
                                        <td>{{ $m->nom }}</td>
                                        <td>{{ $m->pivot->posologie }}</td>
                                        <td>{{ $m->pivot->duree_jours }}</td>
                                        <td>{{ $m->pivot->quantite }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @else
                    <p>Aucune ordonnance enregistrée.</p>
                @endif
            </div>
        </div>


        <!-- ============================= -->
        <!-- 4. Examens prescrits -->
        <!-- ============================= -->
        <div class="card mb-3">
            <div class="card-header bg-dark text-white">Examens prescrits</div>
            <div class="card-body">
                @php
                    $examens = $patient->consultations->flatMap->examens;
                @endphp

                @if($examens->count())
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Date Consultation</th>
                            <th>Type</th>
                            <th>Résultat</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($examens as $ex)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($ex->consultation->date_consultation)->format('d/m/Y') }}</td>
                                <td>{{ $ex->type }}</td>
                                <td>{{ $ex->resultat ?? '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Aucun examen prescrit.</p>
                @endif
            </div>
        </div>

        <!-- ============================= -->
        <!-- 5. Hospitalisations -->
        <!-- ============================= -->
        @if($patient->hospitalisations->count())
            <div class="card mb-3">
                <div class="card-header bg-warning text-white">Hospitalisations</div>
                <div class="card-body">
                    @foreach($patient->hospitalisations as $h)
                        <p><strong>Du :</strong> {{ \Carbon\Carbon::parse($h->date_entree)->format('d/m/Y') }}
                            <strong>Au :</strong> {{ $h->date_sortie ? \Carbon\Carbon::parse($h->date_sortie)->format('d/m/Y') : 'En cours' }}</p>
                        <p><strong>État :</strong> {{ $h->etat }}</p>

                        @if($h->paiements->count())
                            <p><strong>Paiements :</strong></p>
                            <ul>
                                @foreach($h->paiements as $p)
                                    <li>{{ $p->montant_recu }} / {{ $p->montant_total }} ({{ $p->statut }})</li>
                                @endforeach
                            </ul>
                        @endif

                        <hr>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- ============================= -->
        <!-- 6. Rendez-vous -->
        <!-- ============================= -->
        @if($patient->rendezVous->count())
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">Rendez-vous</div>
                <div class="card-body">
                    <ul>
                        @foreach($patient->rendezVous as $rdv)
                            <li>{{ \Carbon\Carbon::parse($rdv->date)->format('d/m/Y H:i') }} - {{ $rdv->motif }}
                                (Statut : {{ $rdv->statut }})</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

    </div>
@endsection
