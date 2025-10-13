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
                    <i class="fas fa-folder-open text-primary me-2"></i>{{ strtoupper($patient->nom) }}
                    {{ $patient->prenom }}
                </h2>
            </div>
            <div class="col-md-5 col-lg-4 mt-3 mt-md-0 text-md-end">
                <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                    <button class="btn btn-sm btn-white border px-3" onclick="window.print()">
                        <i class="fas fa-print me-2 text-warning"></i>Imprimer
                    </button>
                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-white border px-3">
                        <i class="fas fa-edit me-2 text-info"></i>Modifier
                    </a>
                    <a href="{{ route('consultations.listeentente', $patient->uuid) }}" class="btn btn-sm btn-primary px-3">
                        <i class="fas fa-plus me-2"></i>Consultation
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($patient->fusionne_dans_id)
            <div class="alert alert-warning">Ce dossier a été fusionné dans le dossier <a href="{{ route('patients.show', \App\Models\Patient::find($patient->fusionne_dans_id)) }}">{{ \App\Models\Patient::find($patient->fusionne_dans_id)?->numero_dossier }}</a>.</div>
        @endif

        {{-- EF-02.05 : synthèse du dossier --}}
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <h6 class="fw-bold text-muted text-uppercase small">N° de dossier</h6>
                    <div class="fs-5 fw-bold">{{ $patient->numero_dossier ?? '-' }}</div>
                    @if($patient->provenance)<div class="small text-muted mt-1">{{ \App\Models\Patient::PROVENANCES[$patient->provenance] ?? $patient->provenance }} {{ $patient->cscom_origine ? '— ' . $patient->cscom_origine : '' }}</div>@endif
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 {{ $synthese['allergies'] ? 'border-start border-4 border-danger' : '' }}"><div class="card-body">
                    <h6 class="fw-bold text-muted text-uppercase small">Allergies</h6>
                    <div class="{{ $synthese['allergies'] ? 'text-danger fw-semibold' : 'text-muted' }}">{!! $synthese['allergies'] ? nl2br(e($synthese['allergies'])) : 'Aucune connue' !!}</div>
                    <h6 class="fw-bold text-muted text-uppercase small mt-2">Antécédents</h6>
                    <div class="small">{!! $synthese['antecedents'] ? nl2br(e(\Illuminate\Support\Str::limit($synthese['antecedents'], 200))) : '<span class="text-muted">Non renseignés</span>' !!}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <h6 class="fw-bold text-muted text-uppercase small">Traitements en cours</h6>
                    @forelse($synthese['traitements'] as $t)
                        <div class="small"><b>{{ $t['nom'] }}</b> — {{ $t['posologie'] }} <span class="text-muted">(jusqu'au {{ $t['jusqu_au']->format('d/m') }})</span></div>
                    @empty
                        <div class="small text-muted">Aucun</div>
                    @endforelse
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body">
                    <h6 class="fw-bold text-muted text-uppercase small">Derniers passages</h6>
                    @forelse($synthese['passages'] as $p)
                        <div class="small">{{ $p->arrive_at->format('d/m/Y') }} — {{ $p->service->nom ?? 'Accueil' }} <span class="badge bg-{{ \App\Models\Passage::COULEURS[$p->statut] ?? 'secondary' }}">{{ \App\Models\Passage::STATUTS[$p->statut] ?? $p->statut }}</span></div>
                    @empty
                        <div class="small text-muted">Aucun</div>
                    @endforelse
                </div></div>
            </div>
        </div>

        @can('patients.fusionner')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="POST" action="{{ route('patients.fusion', $patient) }}" class="row g-2 align-items-end" onsubmit="return confirm('Rattacher tout le contenu du dossier choisi à celui-ci ? L\'opération pourra être annulée.')">
                        @csrf
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">Fusionner un doublon dans ce dossier</label>
                            <select name="doublon_id" id="doublonFusion" class="form-select" style="width:100%" required></select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="motif" class="form-control" placeholder="Motif (obligatoire)" required>
                        </div>
                        <div class="col-md-2"><button class="btn btn-outline-danger w-100">Fusionner</button></div>
                    </form>
                    @foreach($fusions as $f)
                        <div class="small mt-2">
                            {{ $f->created_at->format('d/m/Y') }} : dossier {{ $f->fusionne->numero_dossier ?? '#' . $f->patient_fusionne_id }} fusionné par {{ $f->user->name ?? '-' }} — {{ $f->motif }}
                            @if($f->annulee_at)
                                <span class="badge bg-secondary">annulée le {{ $f->annulee_at->format('d/m/Y') }}</span>
                            @else
                                <form method="POST" action="{{ route('fusions.annuler', $f) }}" class="d-inline">@csrf<button class="btn btn-link btn-sm p-0 ms-2" onclick="return confirm('Annuler cette fusion ?')">Annuler la fusion</button></form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endcan

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
                            </div>
                            <div class="mb-0 small"> <i
                                    class="fas fa-phone-alt text-primary me-2 w-20px"></i>{{ $patient->telephone }}
                            </div>
                            <div class="mb-2 small">
                                <i
                                    class="fas fa-map-marker-alt text-primary me-2 w-20px"></i>{{ $patient->adresse ?? 'Non spécifiée' }}
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
                    <div class="card-header bg-primary border-0 p-0 overflow-auto">
                        <ul class="nav nav-tabs nav-justified border-0 flex-nowrap" id="patientTabs" role="tablist"
                            style="min-width: 600px;">
                            <li class="nav-item">
                                <a class="nav-link active py-3 text-uppercase" id="history-tab" data-bs-toggle="tab"
                                    href="#history">
                                    <i class="fas fa-notes-medical me-2"></i>Consultations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3 text-uppercase" id="ordonnances-tab" data-bs-toggle="tab"
                                    href="#ordonnances">
                                    <i class="fas fa-file-prescription me-2"></i>Ordonnances
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3 text-uppercase" id="hospitalization-tab" data-bs-toggle="tab"
                                    href="#hospitalization">
                                    <i class="fas fa-bed me-2"></i>Hospit.
                                </a>
                            </li>
                            @if ($patient->genre == 'F')
                                <li class="nav-item">
                                    <a class="nav-link py-3 text-uppercase text-pink" id="maternity-tab"
                                        data-bs-toggle="tab" href="#maternity">
                                        <i class="fas fa-baby me-2"></i>Maternité
                                    </a>
                                </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link py-3 text-uppercase" id="exams-tab" data-bs-toggle="tab"
                                    href="#exams">
                                    <i class="fas fa-microscope me-2"></i>Examens
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3 text-uppercase" id="transfers-tab" data-bs-toggle="tab"
                                    href="#transfers">
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
                                                    <td class="fw-bold small">
                                                        {{ \Carbon\Carbon::parse($c->date_consultation)->format('d/m/Y') }}
                                                    </td>
                                                    <td><span class="text-primary fw-medium">{{ $c->motif }}</span>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 small text-truncate" style="max-width: 250px;">
                                                            {{ $c->diagnostic }}</p>
                                                    </td>
                                                    <td><small>{{ $c->medecin->name ?? 'N/A' }}</small></td>
                                                    <td class="text-center">
                                                        <a href="{{ route('consultations.show', $c) }}"
                                                            class="btn btn-sm btn-soft-primary rounded-pill">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-5 text-muted italic">Aucun
                                                        historique de consultation.</td>
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
                                                    Prescription du
                                                    {{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y') }}
                                                </div>
                                                <div>
                                                    <span
                                                        class="badge bg-soft-success text-success px-3">{{ $o->statutordo ?? 'Prête' }}</span>
                                                    <a href="{{ route('ordonnances.pdf', $o) }}"
                                                        class="ms-2 btn btn-sm btn-soft-warning rounded-circle">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="row bg-light rounded p-2 mx-0 small">
                                                @foreach ($o->medicaments as $m)
                                                    <div class="col-md-6 mb-1">
                                                        • <strong>{{ $m->nom }}</strong> :
                                                        {{ $m->pivot->posologie }} ({{ $m->pivot->duree_jours }}j)
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
                                                    <h6 class="fw-bold mb-1">Salle :
                                                        {{ $h->lit->salle->nom ?? 'Standard' }} (Lit
                                                        #{{ $h->lit->numero }})</h6>
                                                    <p class="mb-0 small text-muted">
                                                        Entrée: <span
                                                            class="text-dark fw-bold">{{ \Carbon\Carbon::parse($h->date_entree)->format('d/m/Y') }}</span>
                                                        |
                                                        Sortie: <span
                                                            class="{{ $h->date_sortie ? 'text-dark fw-bold' : 'text-danger fw-bold' }}">
                                                            {{ $h->date_sortie ? \Carbon\Carbon::parse($h->date_sortie)->format('d/m/Y') : 'Toujours hospitalisé' }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <span
                                                        class="badge bg-{{ $h->etat == 'Sorti' ? 'success' : 'warning' }} px-3 mb-2">{{ $h->etat }}</span>
                                                    <div class="small fw-bold">
                                                        {{ number_format($h->paiements->sum('montant_recu'), 0, ',', ' ') }}
                                                        FCFA payés</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 text-muted italic">Aucun historique d'hospitalisation.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Tab: Maternité -->
                            @if ($patient->genre == 'F')
                                <div class="tab-pane fade" id="maternity">
                                    @php $activeGrossesse = $patient->grossesses->where('statut', 'En cours')->first(); @endphp
                                    @if ($activeGrossesse)
                                        <div
                                            class="alert bg-pink-light border-0 d-flex justify-content-between align-items-center rounded-4 p-4">
                                            <div>
                                                <h5 class="fw-bold text-pink mb-1"><i
                                                        class="fas fa-baby-carriage me-2"></i>Grossesse en cours</h5>
                                                <p class="mb-0 small text-dark">DDR:
                                                    {{ \Carbon\Carbon::parse($activeGrossesse->ddr)->format('d/m/Y') }} |
                                                    **DPA:
                                                    {{ \Carbon\Carbon::parse($activeGrossesse->dpa)->format('d/m/Y') }}**
                                                </p>
                                            </div>
                                            <a href="{{ route('maternity.show', $activeGrossesse) }}"
                                                class="btn btn-pink text-white rounded-pill shadow-sm">
                                                Gérer le suivi <i class="fas fa-arrow-right ms-2"></i>
                                            </a>
                                        </div>

                                        <h6 class="fw-bold mt-4 mb-3">Historique des CPN</h6>
                                        @foreach ($activeGrossesse->cpns->sortByDesc('date_cpn') as $cpn)
                                            <div class="border-start border-3 border-pink ps-3 pb-3 position-relative">
                                                <div class="position-absolute bg-pink rounded-circle"
                                                    style="width:12px; height:12px; left:-8px; top:0;"></div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold text-dark">CPN #{{ $cpn->numero_cpn }}</span>
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($cpn->date_cpn)->format('d/m/Y') }}</small>
                                                </div>
                                                <p class="mb-0 small text-muted italic">
                                                    {{ $cpn->observations ?? 'Acune observation particulière.' }}</p>
                                                <div class="mt-1">
                                                    <span class="badge bg-soft-info text-dark small">HU:
                                                        {{ $cpn->hauteur_uterine }}cm</span>
                                                    <span class="badge bg-soft-danger text-dark small">BCF:
                                                        {{ $cpn->bcf }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-5 rounded-4 bg-light border-dashed">
                                            <i class="fas fa-baby-carriage fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Aucun dossier de grossesse actif.</p>
                                            <a href="{{ route('maternity.create', ['patient_id' => $patient->uuid]) }}"
                                                class="btn btn-soft-pink rounded-pill">
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
                                                    <td class="fw-bold">{{ $ex->examen }}</td>
                                                    <td>
                                                        <span
                                                            class="badge bg-soft-{{ $ex->resultat ? 'success' : 'warning' }} text-dark px-3">
                                                            {{ $ex->resultat ? 'Réalisé' : 'En attente' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <p class="mb-0 small text-truncate italic"
                                                            style="max-width: 200px;">
                                                            {{ $ex->resultat->resultat ?? '--' }}
                                                        </p>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted italic">Aucun
                                                        examen réalisé.</td>
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
                                                    <td class="small">
                                                        {{ \Carbon\Carbon::parse($t->date_transfert)->format('d/m/Y H:i') }}
                                                    </td>
                                                    <td>
                                                        @if ($t->type === 'medecin')
                                                            <span class="badge bg-soft-primary text-primary">Médecin</span>
                                                        @elseif($t->type === 'service')
                                                            <span class="badge bg-soft-info text-info">Service</span>
                                                        @else
                                                            <span class="badge bg-soft-warning text-warning">Externe</span>
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        @if ($t->type === 'medecin')
                                                            {{ $t->sourceMedecin->name ?? '-' }}
                                                        @elseif($t->type === 'service')
                                                            {{ $t->sourceService->nom ?? '-' }}
                                                        @else
                                                            Interne
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        @if ($t->type === 'medecin')
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
                                                    <td colspan="5" class="text-center py-5 text-muted italic">Aucun
                                                        historique de transfert.</td>
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
                        <h6 class="fw-bold mb-3"><i class="fas fa-clipboard-list me-2 text-primary"></i>Observations
                            cliniques globales</h6>
                        <div class="p-3 bg-light rounded-3 text-muted italic small">
                            {{ $patient->antecedents ?? 'Aucun antécédent médical majeur renseigné pour ce patient.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-pink-light {
            background-color: rgba(233, 30, 99, 0.05);
        }

        .btn-pink {
            background-color: #e91e63;
            color: white;
        }

        .btn-pink:hover {
            background-color: #d81b60;
            color: white;
        }

        .text-pink {
            color: #e91e63;
        }

        .btn-soft-pink {
            background-color: rgba(233, 30, 99, 0.1);
            color: #e91e63;
            border: none;
        }

        .btn-soft-primary {
            background-color: rgba(0, 97, 242, 0.1);
            color: #0061f2;
            border: none;
        }

        .btn-soft-success {
            background-color: rgba(26, 188, 156, 0.1);
            color: #1abc9c;
            border: none;
        }

        .btn-soft-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: none;
        }

        .btn-soft-warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: #f39c12;
            border: none;
        }

        .btn-soft-info {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
            border: none;
        }

        .hover-shadow:hover {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
        }

        .transition {
            transition: all 0.3s ease;
        }

        .w-20px {
            width: 20px;
            display: inline-block;
        }

        /* Correction Visibilité Onglets (Design Sombre) */
        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.75) !important;
            /* Blanc translucide pour les inactifs */
            font-weight: 600 !important;
            font-size: 0.85rem !important;
            border: none !important;
            transition: all 0.2s ease;
        }

        .nav-tabs .nav-link:hover {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-tabs .nav-link.active {
            color: #fff !important;
            background-color: rgba(255, 255, 255, 0.2) !important;
            border-bottom: 3px solid #fff !important;
        }

        .text-pink {
            color: #ffb1c1 !important;
        }

        /* Rose plus clair pour fond sombre */
        .bg-pink-light {
            background-color: rgba(233, 30, 99, 0.05);
        }
    </style>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion manuelle des onglets (compatible Dashmix/Bootstrap 4 & 5)
            const tabLinks = document.querySelectorAll('#patientTabs .nav-link');
            const tabPanes = document.querySelectorAll('.tab-content .tab-pane');

            tabLinks.forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Désactiver tous les onglets
                    tabLinks.forEach(l => {
                        l.classList.remove('active');
                    });
                    tabPanes.forEach(p => {
                        p.classList.remove('show', 'active');
                    });

                    // Activer l'onglet cliqué
                    link.classList.add('active');

                    // Récupérer la cible
                    const targetId = link.getAttribute('href') || link.getAttribute(
                        'data-bs-target');
                    const targetPane = document.querySelector(targetId);
                    if (targetPane) {
                        targetPane.classList.add('show', 'active');
                    }
                });
            });
        });
    
    $(function () {
        $('#doublonFusion').select2({
            placeholder: 'Rechercher le dossier en double…', minimumInputLength: 2,
            ajax: { url: "{{ route('patients.search') }}", dataType: 'json', delay: 250, data: (p) => ({ q: p.term }),
                processResults: (data) => ({ results: data.filter(p => p.id !== {{ $patient->id }}).map(p => ({ id: p.id, text: `${p.nom} ${p.prenom} — ${p.numero_dossier ?? ''} — ${p.telephone ?? ''}` })) }) }
        });
    });
</script>
@endsection
