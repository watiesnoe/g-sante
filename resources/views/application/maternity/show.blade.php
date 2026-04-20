@extends('layouts.app')

@section('content')
<div class="content">
    <div class="row">
        <!-- Sidebar Info -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <div class="avatar-lg bg-pink-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: #fce4ec; color: #e91e63;">
                        <i class="fa fa-female fa-3x"></i>
                    </div>
                    <h4 class="fw-bold mb-1">{{ $grossesse->patient->nom }} {{ $grossesse->patient->prenom }}</h4>
                    <span class="badge bg-soft-success text-success px-3 py-2">Grossesse Active</span>
                </div>
                <div class="list-group list-group-flush border-top">
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Âge de la patiente</span>
                        <span class="fw-bold">{{ \Carbon\Carbon::parse($grossesse->patient->date_naissance)->age ?? '?' }} ans</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">DDR</span>
                        <span class="fw-bold text-primary">{{ \Carbon\Carbon::parse($grossesse->ddr)->format('d-m-Y') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">DPA Estimée</span>
                        <span class="fw-bold text-danger">{{ \Carbon\Carbon::parse($grossesse->dpa)->format('d-m-Y') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Terme actuel</span>
                        <span class="badge bg-info text-white">{{ round(\Carbon\Carbon::parse($grossesse->ddr)->diffInWeeks(now())) }} SA</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Renseignements Obstétricaux</div>
                <div class="card-body">
                    <div class="d-flex justify-content-around mb-3 text-center">
                        <div>
                            <h3 class="mb-0 fw-bold">G{{ $grossesse->gestite }}</h3>
                            <small class="text-muted">Gestité</small>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">P{{ $grossesse->parite }}</h3>
                            <small class="text-muted">Parité</small>
                        </div>
                    </div>
                    @if($grossesse->antecedents_particuliers)
                    <div class="alert alert-warning border-0 mb-0">
                        <i class="fa fa-exclamation-triangle me-1"></i> 
                        <small>{{ $grossesse->antecedents_particuliers }}</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions Rapides -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white fw-bold">Actions de Soin</div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('consultations.create', ['patient_id' => $grossesse->patient_id, 'grossesse_id' => $grossesse->id]) }}" class="btn btn-outline-primary text-start">
                            <i class="fa fa-stethoscope me-2"></i> Consultation complète
                        </a>
                        <a href="{{ route('rendezvous.index', ['patient_id' => $grossesse->patient_id]) }}" class="btn btn-outline-info text-start">
                            <i class="fa fa-calendar-plus me-2"></i> Fixer un Rendez-vous
                        </a>
                        <a href="{{ route('ordonnances.index', ['patient_id' => $grossesse->patient_id]) }}" class="btn btn-outline-success text-start">
                            <i class="fa fa-pills me-2"></i> Prescrire (Ordonnance)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Timeline / CPN -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Consultations Prénatales (CPN)</h5>
                    <div class="d-flex gap-2">
                        @if($grossesse->statut == 'En cours')
                        <button class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCloture">
                            <i class="fa fa-times-circle me-1"></i> Clôturer le suivi
                        </button>
                        @endif
                        <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCpn">
                            <i class="fa fa-plus me-1"></i> Ajouter une CPN
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>N°</th>
                                    <th>Date</th>
                                    <th>Poids/TA</th>
                                    <th>HU</th>
                                    <th>BCF</th>
                                    <th>Observations</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grossesse->cpns->sortBy('numero_cpn') as $cpn)
                                <tr>
                                    <td><span class="badge bg-secondary rounded-pill">CPN {{ $cpn->numero_cpn }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($cpn->date_cpn)->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $cpn->poids ?? '-' }} kg</div>
                                        <div class="text-muted small">{{ $cpn->tension ?? '-' }}</div>
                                    </td>
                                    <td>{{ $cpn->hauteur_uterine ?? '-' }} cm</td>
                                    <td>{{ $cpn->bcf ?? '-' }}</td>
                                    <td class="small">{{ Str::limit($cpn->observations, 50) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <p class="text-muted mb-0">Aucune CPN enregistrée pour le moment.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter CPN -->
<div class="modal fade" id="modalCpn" tabindex="-1">
    <div class="modal-dialog modal-lg border-0">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">Nouvelle Consultation Prénatale (CPN)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('maternity.cpn.store') }}" method="POST">
                @csrf
                <input type="hidden" name="grossesse_id" value="{{ $grossesse->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Numéro CPN</label>
                            <input type="number" name="numero_cpn" class="form-control" value="{{ $grossesse->cpns->count() + 1 }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">Date de visite</label>
                            <input type="date" name="date_cpn" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Prochain RDV</label>
                            <input type="date" name="prochain_rdv" class="form-control">
                        </div>
                        
                        <div class="col-md-12"><hr class="my-1"></div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Poids (kg)</label>
                            <input type="number" step="0.1" name="poids" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Tension (TA)</label>
                            <input type="text" name="tension" class="form-control" placeholder="12/8">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">H. Utérine (cm)</label>
                            <input type="number" name="hauteur_uterine" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">BCF</label>
                            <input type="text" name="bcf" class="form-control" placeholder="Bruit Coeur Foetal">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Traitement reçu / Supplémentation</label>
                            <input type="text" name="traitement_recu" class="form-control" placeholder="ex: Fer, Acide Folique, Moustiquaire...">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Observations cliniques</label>
                            <textarea name="observations" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer la CPN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Clôturer Suivi -->
<div class="modal fade" id="modalCloture" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">Clôturer le Suivi de Grossesse</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('maternity.close', $grossesse->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Statut de clôture</label>
                        <select name="statut" class="form-select" required>
                            <option value="Terminée">Terminée (Accouchement)</option>
                            <option value="Interrompue">Interrompue (Perte/Fausse couche)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Issue / Résultat</label>
                        <select name="issue" class="form-select" required>
                            <option value="Accouchement normal">Accouchement normal</option>
                            <option value="Césarienne">Césarienne</option>
                            <option value="Fausse couche">Fausse couche</option>
                            <option value="Mort-né">Mort-né</option>
                            <option value="Interruption Médicale de Grossesse (IMG)">Interruption Médicale de Grossesse (IMG)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date de fin</label>
                        <input type="date" name="date_fin" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger px-4">Confirmer la clôture</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
