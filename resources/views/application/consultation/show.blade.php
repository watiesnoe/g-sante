@extends('layouts.app')

@section('titre', 'Détail Consultation')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            {{-- En-tête de la carte --}}
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold text-primary mb-0">
                            <i class="fas fa-file-medical me-2"></i>Détail Consultation
                        </h3>
                        <p class="text-muted mb-0 mt-1">Référence #{{ $consultation->id }}</p>
                    </div>
                    <div>
                        <button onclick="window.print()" class="btn btn-outline-primary">
                            <i class="fas fa-print me-2"></i>Imprimer
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary ms-2">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
            </div>

            {{-- Corps de la carte --}}
            <div class="card-body">
                {{-- Informations patient et consultation en deux colonnes --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="border-start border-primary border-4 ps-3">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-user me-2"></i>Informations patient
                            </h5>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-2"><strong>Nom complet :</strong></p>
                                    <p class="mb-2"><strong>Téléphone :</strong></p>
                                    <p class="mb-2"><strong>Adresse :</strong></p>
                                    <p class="mb-2"><strong>Ticket :</strong></p>
                                    <p class="mb-2"><strong>Groupe sanguin :</strong></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-2">{{ $consultation->patient->nom }} {{ $consultation->patient->prenom }}</p>
                                    <p class="mb-2">{{ $consultation->patient->telephone }}</p>
                                    <p class="mb-2">{{ $consultation->adresse_patient }}</p>
                                    <p class="mb-2">#{{ $consultation->ticket_id }}</p>
                                    <p class="mb-2">{{ $consultation->groupe_sanguin ?? 'Non renseigné' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mt-3 mt-md-0">
                        <div class="border-start border-info border-4 ps-3">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-stethoscope me-2"></i>Consultation
                            </h5>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-2"><strong>Médecin :</strong></p>
                                    <p class="mb-2"><strong>Date :</strong></p>
                                    <p class="mb-2"><strong>Motif :</strong></p>
                                    <p class="mb-2"><strong>Diagnostic :</strong></p>
                                </div>
                                <div class="col-6">
                                    <p class="mb-2">{{ $consultation->medecin->name }}</p>
                                    <p class="mb-2">{{ $consultation->date_consultation ?? now()->format('d/m/Y') }}</p>
                                    <p class="mb-2">{{ $consultation->motif }}</p>
                                    <p class="mb-2">{{ $consultation->diagnostic }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Constantes vitales --}}
                <div class="border-top pt-4 mb-4">
                    <h5 class="text-success mb-3">
                        <i class="fas fa-heartbeat me-2"></i>Constantes vitales
                    </h5>
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Poids</small>
                                <strong class="fs-4">{{ $consultation->poids }} kg</strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Taille</small>
                                <strong class="fs-4">{{ $consultation->taille }} cm</strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">IMC</small>
                                <strong class="fs-4">{{ $consultation->taille > 0 ? number_format($consultation->poids/(($consultation->taille/100)**2),2) : '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">Tension</small>
                                <strong class="fs-4">{{ $consultation->tension ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                @if($consultation->notes)
                    <div class="border-top pt-4 mb-4">
                        <h5 class="text-secondary mb-3">
                            <i class="fas fa-notes-medical me-2"></i>Notes / Antécédents
                        </h5>
                        <div class="bg-light p-3 rounded">
                            {{ $consultation->notes }}
                        </div>
                    </div>
                @endif

                {{-- Symptômes et maladies --}}
                <div class="border-top pt-4 mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h5 class="text-warning mb-3">
                                <i class="fas fa-head-side-medical me-2"></i>Symptômes
                            </h5>
                            @if($consultation->symptomes->count())
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($consultation->symptomes as $symptome)
                                        <span class="badge bg-warning text-dark px-3 py-2">{{ $symptome->nom }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Aucun symptôme enregistré</p>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-danger mb-3">
                                <i class="fas fa-disease me-2"></i>Maladies
                            </h5>
                            @if($consultation->maladies->count())
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($consultation->maladies as $maladie)
                                        <span class="badge bg-danger px-3 py-2">{{ $maladie->nom }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">Aucune maladie diagnostiquée</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Ordonnances --}}
                <div class="border-top pt-4 mb-4">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-prescription-bottle me-2"></i>Ordonnances / Médicaments
                    </h5>
                    @if($consultation->ordonnances->count())
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
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
                                            <td><strong>{{ $med->nom }}</strong></td>
                                            <td>{{ $med->pivot->posologie }}</td>
                                            <td>{{ $med->pivot->duree_jours }}</td>
                                            <td>{{ $med->pivot->quantite }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Aucun médicament prescrit</p>
                    @endif
                </div>

                {{-- Examens et rendez-vous --}}
                <div class="border-top pt-4 mb-4">
                    <div class="row">
                        @if($consultation->examens->count())
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h5 class="text-info mb-3">
                                    <i class="fas fa-flask me-2"></i>Examens prescrits
                                </h5>
                                <ul class="list-unstyled">
                                    @foreach($consultation->examens as $ex)
                                        <li class="mb-2">
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            {{ $ex->examen }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if($consultation->rendezVous->count())
                            <div class="col-md-6">
                                <h5 class="text-success mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>Rendez-vous
                                </h5>
                                <ul class="list-unstyled">
                                    @foreach($consultation->rendezVous as $r)
                                        <li class="mb-2">
                                            <i class="fas fa-clock text-primary me-2"></i>
                                            <strong>{{ \Carbon\Carbon::parse($r->date_heure)->format('d/m/Y H:i') }}</strong>
                                            <span class="text-muted ms-2">{{ $r->motif }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Certificat --}}
                @if($consultation->certificat && $consultation->certificat->contenu)
                    <div class="border-top pt-4 mb-4">
                        <h5 class="text-secondary mb-3">
                            <i class="fas fa-file-alt me-2"></i>Certificat médical
                        </h5>
                        <div class="border p-3 rounded bg-light">
                            {{ $consultation->certificat->contenu }}
                        </div>
                    </div>
                @endif

                {{-- Hospitalisation --}}
                @if($consultation->hospitalisation)
                    <div class="border-top pt-4">
                        <h5 class="text-danger mb-3">
                            <i class="fas fa-hospital-user me-2"></i>Hospitalisation
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Date entrée :</strong></p>
                                <p>{{ \Carbon\Carbon::parse($consultation->hospitalisation->date_entree)->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Salle :</strong></p>
                                <p>{{ $consultation->hospitalisation->salles_id }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Lit :</strong></p>
                                <p>{{ $consultation->hospitalisation->lit_id }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Observations :</strong></p>
                                <p>{{ $consultation->hospitalisation->observations ?: 'Aucune observation' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Pied de la carte --}}
            <div class="card-footer bg-white text-center text-muted py-3">
                <small>
                    <i class="fas fa-clock me-1"></i>
                    Document généré le {{ now()->format('d/m/Y à H:i') }}
                </small>
            </div>
        </div>
    </div>

    {{-- Styles pour l'impression --}}
    <style>
        @media print {
            .btn, .card-footer {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            .card-body {
                padding: 0 !important;
            }
            .bg-light {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .badge {
                border: 1px solid #ccc;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
        }
    </style>
@endsection
