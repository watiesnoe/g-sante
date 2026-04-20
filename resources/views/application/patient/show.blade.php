@extends('layouts.app')

@section('titre', "Dossier Médical - {$patient->nom}")

@section('content')
<div class="content py-3">
    <!-- En-tête Dynamique -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-7 col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item active">Dossier Médical</li>
                </ol>
            </nav>
            <h2 class="fw-bold text-dark mb-0">
                <i class="fas fa-folder-open text-primary me-2"></i>{{ strtoupper($patient->nom) }} {{ $patient->prenom }}
            </h2>
        </div>
        <div class="col-md-5 col-lg-4 mt-3 mt-md-0 text-md-end">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <button class="btn btn-sm btn-white border px-3" onclick="window.print()">
                    <i class="fas fa-print me-2 text-warning"></i>Imprimer
                </button>
                <a href="{{ route('patients.edit', $patient->id) }}" class="btn btn-sm btn-white border px-3">
                    <i class="fas fa-edit me-2 text-info"></i>Modifier
                </a>
                <a href="{{ route('consultations.create', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-primary px-3">
                    <i class="fas fa-plus me-2"></i>Consultation
                </a>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Colonne Gauche : Profil & Constantes -->
        <div class="col-xl-3 col-lg-4">
            <!-- Profil Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="bg-primary p-4 text-center position-relative">
                    <div class="position-absolute translate-middle-x" style="bottom: -40px; left: 50%;">
                        @php
                            $initials = strtoupper(substr($patient->nom, 0, 1) . substr($patient->prenom, 0, 1));
                        @endphp
                        <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px; border: 4px solid #fff; font-size: 1.8rem; font-weight: 800; color: var(--primary);">
                            {{ $initials }}
                        </div>
                    </div>
                </div>
                <div class="card-body pt-5 text-center px-4">
                    <h5 class="fw-bold mb-1 mt-2">{{ $patient->nom }} {{ $patient->prenom }}</h5>
                    <p class="text-muted small mb-3">ID: #PAT-{{ str_pad($patient->id, 5, '0', STR_PAD_LEFT) }}</p>
                    
                    <div class="d-flex justify-content-around mb-4">
                        <div class="text-center">
                            <div class="small text-muted">Âge</div>
                            <div class="fw-bold">{{ $patient->age }} ans</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted">Genre</div>
                            <div class="fw-bold">{{ $patient->genre == 'F' ? 'Femme' : 'Homme' }}</div>
                        </div>
                        <div class="text-center">
                            <div class="small text-muted">Groupe</div>
                            <div class="fw-bold text-danger">{{ $patient->groupe_sanguin ?? '?' }}</div>
                        </div>
                    </div>

                    <div class="border-top pt-3 text-start">
                        <div class="mb-2 small">
                            <i class="fas fa-phone-alt text-primary me-2 w-20px"></i>{{ $patient->telephone }}
                        </div>
                        <div class="mb-2 small">
                            <i class="fas fa-map-marker-alt text-primary me-2 w-20px"></i>{{ $patient->adresse ?? 'Non spécifiée' }}
                        </div>
                        <div class="mb-0 small">
                            <i class="fas fa-shield-alt text-primary me-2 w-20px"></i>Assurance: 
                            <span class="fw-bold {{ $patient->assurance ? 'text-success' : 'text-muted' }}">
                                {{ $patient->assurance->nom ?? 'Cahier Cash' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Constantes Vitales Rapides -->
            @php $lastConsult = $patient->consultations->sortByDesc('date_consultation')->first(); @endphp
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h6 class="fw-bold mb-3">Dernières Constantes</h6>
                <div class="d-grid gap-3">
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-soft-info">
                        <div class="small"><i class="fas fa-weight me-2"></i>Poids</div>
                        <div class="fw-bold">{{ $lastConsult->poids ?? '--' }} kg</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-soft-danger">
                        <div class="small"><i class="fas fa-heartbeat me-2"></i>Tension</div>
                        <div class="fw-bold">{{ $lastConsult->tension ?? '--' }}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-soft-warning">
                        <div class="small"><i class="fas fa-thermometer-half me-2"></i>Temp.</div>
                        <div class="fw-bold">{{ $lastConsult->temperature ?? '--' }}°C</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne Droite : Tabs & Contenu -->
        <div class="col-xl-9 col-lg-8">
            <!-- Navigation Tabs Style Dashboard -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 p-0 overflow-auto">
                    <ul class="nav nav-tabs nav-justified border-0 flex-nowrap" id="patientTabs" role="tablist" style="min-width: 600px;">
                        <li class="nav-item">
                            <a class="nav-link active border-0 py-3 fw-bold small text-uppercase active-tab" id="history-tab" data-bs-toggle="tab" href="#history">
                                <i class="fas fa-notes-medical me-2"></i>Consultations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-0 py-3 fw-bold small text-uppercase" id="ordonnances-tab" data-bs-toggle="tab" href="#ordonnances">
                                <i class="fas fa-file-prescription me-2"></i>Ordonnances
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-0 py-3 fw-bold small text-uppercase" id="hospitalization-tab" data-bs-toggle="tab" href="#hospitalization">
                                <i class="fas fa-bed me-2"></i>Hospit.
                            </a>
                        </li>
                        @if($patient->genre == 'F')
                        <li class="nav-item">
                            <a class="nav-link border-0 py-3 fw-bold small text-uppercase text-pink" id="maternity-tab" data-bs-toggle="tab" href="#maternity">
                                <i class="fas fa-baby me-2"></i>Maternité
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link border-0 py-3 fw-bold small text-uppercase" id="exams-tab" data-bs-toggle="tab" href="#exams">
                                <i class="fas fa-microscope me-2"></i>Examens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link border-0 py-3 fw-bold small text-uppercase" id="transfers-tab" data-bs-toggle="tab" href="#transfers">
                                <i class="fas fa-exchange-alt me-2"></i>Transferts
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-4">
                    <div class="tab-content" id="patientTabsContent">
                        
                        <!-- Tab: Consultations -->
                        <div class="tab-pane fade show active" id="history">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle border-0" id="table-consultations">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 small text-uppercase">Date</th>
                                            <th class="border-0 small text-uppercase">Motif</th>
                                            <th class="border-0 small text-uppercase">Diagnostic</th>
                                            <th class="border-0 small text-uppercase">Médecin</th>
                                            <th class="border-0 small text-uppercase text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($patient->consultations->sortByDesc('date_consultation') as $c)
                                        <tr>
                                            <td class="fw-bold small">{{ \Carbon\Carbon::parse($c->date_consultation)->format('d/m/Y') }}</td>
                                            <td><span class="text-primary fw-medium">{{ $c->motif }}</span></td>
                                            <td><p class="mb-0 small text-truncate" style="max-width: 250px;">{{ $c->diagnostic }}</p></td>
                                            <td><small>{{ $c->medecin->name ?? 'N/A' }}</small></td>
                                            <td class="text-center">
                                                <a href="{{ route('consultations.show', $c->id) }}" class="btn btn-sm btn-soft-primary rounded-pill">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted italic">Aucun historique de consultation.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab: Ordonnances -->
                        <div class="tab-pane fade" id="ordonnances">
                            @php
                                $ordonnances = $patient->ordonnances->sortByDesc('created_at');
                            @endphp
                            @forelse($ordonnances as $o)
                            <div class="card border rounded-3 mb-3 hover-shadow transition">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold">
                                            <i class="fas fa-file-alt me-2 text-primary"></i>
                                            Prescription du {{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y') }}
                                        </div>
                                        <div>
                                            <span class="badge bg-soft-success text-success px-3">{{ $o->statutordo ?? 'Prête' }}</span>
                                            <a href="{{ route('ordonnances.pdf', $o->id) }}" class="ms-2 btn btn-sm btn-soft-warning rounded-circle">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row bg-light rounded p-2 mx-0 small">
                                        @foreach($o->medicaments as $m)
                                        <div class="col-md-6 mb-1">
                                            • <strong>{{ $m->nom }}</strong> : {{ $m->pivot->posologie }} ({{ $m->pivot->duree_jours }}j)
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5 text-muted">Aucune ordonnance émise.</div>
                            @endforelse
                        </div>

                        <!-- Tab: Hospitalisations -->
                        <div class="tab-pane fade" id="hospitalization">
                            @forelse($patient->hospitalisations as $h)
                            <div class="card border-start border-4 border-warning shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="fw-bold mb-1">Salle : {{ $h->lit->salle->nom ?? 'Standard' }} (Lit #{{ $h->lit->numero }})</h6>
                                            <p class="mb-0 small text-muted">
                                                Entrée: <span class="text-dark fw-bold">{{ \Carbon\Carbon::parse($h->date_entree)->format('d/m/Y') }}</span> | 
                                                Sortie: <span class="{{ $h->date_sortie ? 'text-dark fw-bold' : 'text-danger fw-bold' }}">
                                                    {{ $h->date_sortie ? \Carbon\Carbon::parse($h->date_sortie)->format('d/m/Y') : 'Toujours hospitalisé' }}
                                                </span>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <span class="badge bg-{{ $h->etat == 'Sorti' ? 'success' : 'warning' }} px-3 mb-2">{{ $h->etat }}</span>
                                            <div class="small fw-bold">{{ number_format($h->paiements->sum('montant_recu'), 0, ',', ' ') }} FCFA payés</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-5 text-muted italic">Aucun historique d'hospitalisation.</div>
                            @endforelse
                        </div>

                        <!-- Tab: Maternité -->
                        @if($patient->genre == 'F')
                        <div class="tab-pane fade" id="maternity">
                            @php $activeGrossesse = $patient->grossesses->where('statut', 'En cours')->first(); @endphp
                            @if($activeGrossesse)
                                <div class="alert bg-pink-light border-0 d-flex justify-content-between align-items-center rounded-4 p-4">
                                    <div>
                                        <h5 class="fw-bold text-pink mb-1"><i class="fas fa-baby-carriage me-2"></i>Grossesse en cours</h5>
                                        <p class="mb-0 small text-dark">DDR: {{ \Carbon\Carbon::parse($activeGrossesse->ddr)->format('d/m/Y') }} | **DPA: {{ \Carbon\Carbon::parse($activeGrossesse->dpa)->format('d/m/Y') }}**</p>
                                    </div>
                                    <a href="{{ route('maternity.show', $activeGrossesse->id) }}" class="btn btn-pink text-white rounded-pill shadow-sm">
                                        Gérer le suivi <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>

                                <h6 class="fw-bold mt-4 mb-3">Historique des CPN</h6>
                                @foreach($activeGrossesse->cpns->sortByDesc('date_cpn') as $cpn)
                                    <div class="border-start border-3 border-pink ps-3 pb-3 position-relative">
                                        <div class="position-absolute bg-pink rounded-circle" style="width:12px; height:12px; left:-8px; top:0;"></div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark">CPN #{{ $cpn->numero_cpn }}</span>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($cpn->date_cpn)->format('d/m/Y') }}</small>
                                        </div>
                                        <p class="mb-0 small text-muted italic">{{ $cpn->observations ?? 'Acune observation particulière.' }}</p>
                                        <div class="mt-1">
                                            <span class="badge bg-soft-info text-dark small">HU: {{ $cpn->hauteur_uterine }}cm</span>
                                            <span class="badge bg-soft-danger text-dark small">BCF: {{ $cpn->bcf }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5 rounded-4 bg-light border-dashed">
                                    <i class="fas fa-baby-carriage fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun dossier de grossesse actif.</p>
                                    <a href="{{ route('maternity.create', ['patient_id' => $patient->id]) }}" class="btn btn-soft-pink rounded-pill">
                                        Initialiser un suivi de grossesse
                                    </a>
                                </div>
                            @endif
                        </div>
                        @endif

                        <!-- Tab: Examens -->
                        <div class="tab-pane fade" id="exams">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Examen</th>
                                            <th>Statut</th>
                                            <th>Résultat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($patient->examens as $ex)
                                        <tr>
                                            <td class="small">{{ $ex->created_at->format('d/m/Y') }}</td>
                                            <td class="fw-bold">{{ $ex->type }}</td>
                                            <td>
                                                <span class="badge bg-soft-{{ $ex->resultat ? 'success' : 'warning' }} text-dark px-3">
                                                    {{ $ex->resultat ? 'Réalisé' : 'En attente' }}
                                                </span>
                                            </td>
                                            <td>
                                                <p class="mb-0 small text-truncate italic" style="max-width: 200px;">
                                                    {{ $ex->resultat ?? '--' }}
                                                </p>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted italic">Aucun examen réalisé.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab: Transferts -->
                        <div class="tab-pane fade" id="transfers">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Destination</th>
                                            <th>Motif</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($patient->transferts->sortByDesc('date_transfert') as $t)
                                        <tr>
                                            <td class="small">{{ \Carbon\Carbon::parse($t->date_transfert)->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($t->type === 'medecin')
                                                    <span class="badge bg-soft-primary text-primary">Médecin</span>
                                                @elseif($t->type === 'service')
                                                    <span class="badge bg-soft-info text-info">Service</span>
                                                @else
                                                    <span class="badge bg-soft-warning text-warning">Externe</span>
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($t->type === 'medecin')
                                                    {{ $t->sourceMedecin->name ?? '-' }}
                                                @elseif($t->type === 'service')
                                                    {{ $t->sourceService->nom ?? '-' }}
                                                @else
                                                    Interne
                                                @endif
                                            </td>
                                            <td class="small">
                                                @if($t->type === 'medecin')
                                                    {{ $t->destMedecin->name ?? '-' }}
                                                @elseif($t->type === 'service')
                                                    {{ $t->destService->nom ?? '-' }}
                                                @else
                                                    {{ $t->hopital_destination }}
                                                @endif
                                            </td>
                                            <td class="small italic">{{ $t->motif }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted italic">Aucun historique de transfert.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Aide Mémoire / Notes du patient -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-clipboard-list me-2 text-primary"></i>Observations cliniques globales</h6>
                    <div class="p-3 bg-light rounded-3 text-muted italic small">
                        {{ $patient->antecedents ?? 'Aucun antécédent médical majeur renseigné pour ce patient.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-pink-light { background-color: rgba(233, 30, 99, 0.05); }
    .btn-pink { background-color: #e91e63; color: white; }
    .btn-pink:hover { background-color: #d81b60; color: white; }
    .text-pink { color: #e91e63; }
    .btn-soft-pink { background-color: rgba(233, 30, 99, 0.1); color: #e91e63; border: none; }
    .btn-soft-primary { background-color: rgba(0, 97, 242, 0.1); color: #0061f2; border: none; }
    .btn-soft-success { background-color: rgba(26, 188, 156, 0.1); color: #1abc9c; border: none; }
    .btn-soft-danger { background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; border: none; }
    .btn-soft-warning { background-color: rgba(243, 156, 18, 0.1); color: #f39c12; border: none; }
    .btn-soft-info { background-color: rgba(52, 152, 219, 0.1); color: #3498db; border: none; }
    .hover-shadow:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
    .transition { transition: all 0.3s ease; }
    .w-20px { width: 20px; display: inline-block; }
    .active-tab { border-bottom: 3px solid var(--primary) !important; color: var(--primary) !important; }
    
    @media print {
        .btn-group, .nav-tabs, .btn-pink, .breadcrumb { display: none !important; }
        .card { border: 1px solid #ddd !important; shadow: none !important; }
        .container-fluid { background: white !important; }
    }
</style>
@endsection
