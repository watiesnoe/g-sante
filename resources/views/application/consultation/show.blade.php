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

                {{-- Protocole de traitement appliqué --}}
                @if($consultation->protocole)
                    <div class="border-top pt-4 mb-4">
                        <h5 class="text-success mb-3">
                            <i class="fas fa-file-medical-alt me-2"></i>Protocole de Traitement Appliqué
                        </h5>
                        <div class="alert alert-success border-0 rounded-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $consultation->protocole->titre }}</strong>
                                    <p class="mb-1 small text-muted mt-1">
                                        {{ $consultation->protocole->traitement_principal ?? 'Traitement via médicaments liés' }}
                                    </p>
                                </div>
                                <a href="{{ route('infectiologie.protocoles.show', $consultation->protocole) }}" 
                                   class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye me-1"></i>Voir
                                </a>
                            </div>
                            @if($consultation->protocole->medicaments->count())
                            <div class="mt-2 d-flex flex-wrap gap-1">
                                @foreach($consultation->protocole->medicaments as $pm)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">
                                        {{ $pm->nom }}
                                        <small class="text-muted">({{ $pm->pivot->type }})</small>
                                    </span>
                                @endforeach
                            </div>
                            @endif
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
                                <p>{{ $consultation->hospitalisation->salle->nom ?? 'N°'.$consultation->hospitalisation->salles_id }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Lit :</strong></p>
                                <p>{{ $consultation->hospitalisation->lit->numero ?? 'Lit '.$consultation->hospitalisation->lit_id }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-1"><strong>Observations :</strong></p>
                                <p>{{ $consultation->hospitalisation->observations ?: 'Aucune observation' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                {{-- Évolution & Suivi Clinique --}}
                <div class="border-top pt-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-primary mb-0">
                            <i class="fas fa-chart-line me-2"></i>Suivi de l'évolution clinique
                        </h5>
                        <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="collapse" data-bs-target="#collapseSuivi">
                            <i class="fas fa-plus me-1"></i> Ajouter un suivi
                        </button>
                    </div>

                    {{-- Formulaire d'ajout de suivi --}}
                    <div class="collapse mb-4" id="collapseSuivi">
                        <div class="card card-body bg-light border-0 shadow-sm">
                            <form id="formSuivi">
                                @csrf
                                <input type="hidden" name="consultation_id" value="{{ $consultation->id }}">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Date de suivi</label>
                                        <input type="date" name="date_suivi" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Évolution globale</label>
                                        <select name="evolution" class="form-select" required>
                                            <option value="Stagnation">Stagnation</option>
                                            <option value="Amélioration">Amélioration</option>
                                            <option value="Guérison">Guérison</option>
                                            <option value="Aggravation">Aggravation</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Température (°C)</label>
                                        <input type="text" name="temperature" class="form-control" placeholder="37.5">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Tension (TA)</label>
                                        <input type="text" name="tension" class="form-control" placeholder="12/8">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">Observations (progrès, effets secondaires...)</label>
                                        <textarea name="observations" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">Nouvelles recommandations</label>
                                        <textarea name="recommandations" class="form-control" rows="2" placeholder="ex: Continuer le repos, augmenter hydratation..."></textarea>
                                    </div>
                                    <div class="col-md-12 text-end">
                                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSaveSuivi">
                                            Enregistrer le suivi
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Liste chronologique des suivis --}}
                    <div class="timeline-suivi mt-3" id="suiviList">
                        @forelse($consultation->suivis->sortByDesc('date_suivi') as $suivi)
                            <div class="d-flex mb-3 border-bottom pb-3">
                                <div class="me-3 text-center" style="width: 80px;">
                                    <h6 class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($suivi->date_suivi)->format('d M') }}</h6>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($suivi->date_suivi)->format('Y') }}</small>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-{{ $suivi->evolution == 'Amélioration' || $suivi->evolution == 'Guérison' ? 'success' : ($suivi->evolution == 'Aggravation' ? 'danger' : 'warning') }} mb-2">
                                            {{ $suivi->evolution }}
                                        </span>
                                        <small class="text-muted">
                                            @if($suivi->temperature) <i class="fas fa-thermometer-half me-1"></i>{{ $suivi->temperature }}°C @endif
                                            @if($suivi->tension) <i class="fas fa-heartbeat ms-2 me-1"></i>{{ $suivi->tension }} @endif
                                        </small>
                                    </div>
                                    <p class="mb-1"><strong>Observations :</strong> {{ $suivi->observations ?: 'Aucune remarque' }}</p>
                                    @if($suivi->recommandations)
                                        <p class="mb-0 text-primary small"><strong>Conseils :</strong> {{ $suivi->recommandations }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-history fa-2x mb-2 opacity-25"></i>
                                <p>Aucun suivi enregistré pour cette consultation.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <script>
                    document.getElementById('formSuivi')?.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const btn = document.getElementById('btnSaveSuivi');
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

                        const formData = new FormData(this);
                        
                        fetch("{{ route('suivi.store') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(res => {
                            if(res.success) {
                                Swal.fire('Succès', res.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erreur', 'Une erreur est survenue', 'error');
                            }
                        })
                        .catch(err => {
                            Swal.fire('Erreur', 'Connexion au serveur impossible', 'error');
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = 'Enregistrer le suivi';
                        });
                    });
                </script>
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
