

<!-- Statistiques principales -->
<div class="row mb-4">
    @php
        $mainCards = [
            [
                'title' => 'Consultations Aujourd\'hui',
                'value' => $stats['consultations_today'] ?? 0,
                'icon' => 'fas fa-stethoscope',
                'color' => 'primary',
                'trend' => '+3',
                'trend_up' => true,
                'subtitle' => 'Patients vus aujourd\'hui',
                'route' => route('consultations.index')
            ],
            [
                'title' => 'Total Consultations',
                'value' => $stats['total_consultations'] ?? 0,
                'icon' => 'fas fa-clipboard-list',
                'color' => 'success',
                'trend' => '+12%',
                'trend_up' => true,
                'subtitle' => 'Depuis le début',
                'route' => route('consultations.index')
            ],
            [
                'title' => 'Patients Total',
                'value' => $stats['total_patients'] ?? 0,
                'icon' => 'fas fa-procedures',
                'color' => 'info',
                'trend' => '+5 aujourd\'hui',
                'trend_up' => true,
                'subtitle' => $stats['new_patients_today'] ?? 0 . ' nouveaux',
                'route' => route('patients.index')
            ],
            [
                'title' => 'Hospitalisations Actives',
                'value' => $stats['active_hospitalisations'] ?? 0,
                'icon' => 'fas fa-bed',
                'color' => 'warning',
                'trend' => 'En cours',
                'trend_up' => false,
                'subtitle' => 'Patients hospitalisés',
                'route' => route('hospitalisations.index')
            ]
        ];
    @endphp

    @foreach($mainCards as $card)
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $card['route'] }}" class="text-decoration-none">
                <div class="card card-statistic h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-muted mb-1">{{ $card['title'] }}</h6>
                                <h2 class="mb-0 fw-bold text-{{ $card['color'] }}">{{ $card['value'] }}</h2>
                                <small class="text-muted">{{ $card['subtitle'] }}</small>
                            </div>
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-{{ $card['color'] }} bg-opacity-10 rounded-circle">
                                    <i class="{{ $card['icon'] }} text-{{ $card['color'] }} fs-4"></i>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                                    <span class="badge bg-{{ $card['trend_up'] ? 'success' : 'warning' }}-subtle text-{{ $card['trend_up'] ? 'success' : 'warning' }}">
                                        <i class="fas fa-{{ $card['trend_up'] ? 'arrow-up' : 'clock' }} me-1"></i>
                                        {{ $card['trend'] }}
                                    </span>
                            <span class="text-decoration-none small text-primary">
                                        Voir <i class="fas fa-chevron-right ms-1"></i>
                                    </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Deuxième ligne de statistiques -->
<div class="row mb-4">
    @php
        $secondaryCards = [
            ['title'=>'Hospitalisations Total', 'value'=>$stats['total_hospitalisations'] ?? 0, 'icon'=>'fas fa-hospital', 'color'=>'primary', 'route'=>route('hospitalisations.index')],
            ['title'=>'Médicaments', 'value'=>$stats['total_medicaments'] ?? 0, 'icon'=>'fas fa-pills', 'color'=>'success', 'route'=>route('medicaments.index')],
            ['title'=>'Stock Faible', 'value'=>$stats['low_stock_medicaments'] ?? 0, 'icon'=>'fas fa-exclamation-triangle', 'color'=>'danger', 'route'=>route('medicaments.index')],
            ['title'=>'Nouveaux Patients', 'value'=>$stats['new_patients_today'] ?? 0, 'icon'=>'fas fa-user-plus', 'color'=>'info', 'route'=>route('patients.index')],
        ];
    @endphp

    @foreach($secondaryCards as $card)
        <div class="col-xl-3 col-md-6 mb-3">
            <a href="{{ $card['route'] }}" class="text-decoration-none">
                <div class="card card-statistic-sm text-white bg-{{ $card['color'] }} hover-scale">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <h6 class="card-title mb-1 small">{{ $card['title'] }}</h6>
                            <h4 class="mb-0 fw-bold">{{ $card['value'] }}</h4>
                        </div>
                        <div>
                            <i class="{{ $card['icon'] }} fa-lg opacity-75"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Contenu principal -->
<div class="row">
    <!-- Rendez-vous du jour -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-day me-2 text-primary"></i>
                    Rendez-vous Aujourd'hui
                </h5>
                <a href="{{ route('rendezvous.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                @if($todayAppointments && $todayAppointments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($todayAppointments as $appointment)
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 rounded-circle me-3">
                                        <i class="fas fa-user-injured text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $appointment->patient->nom_complet ?? 'Patient' }}</h6>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($appointment->date_heure)->format('H:i') }}-

                                            {{ $appointment->type ?? 'Consultation' }}
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-{{ $appointment->statut == 'confirmé' ? 'success' : 'warning' }}-subtle text-{{ $appointment->statut == 'confirmé' ? 'success' : 'warning' }}">
                                        {{ $appointment->statut }}
                                    </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Aucun rendez-vous aujourd'hui</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Graphique des consultations -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Statistiques des Consultations
                </h5>
            </div>
            <div class="card-body">
                <canvas id="consultations-chart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Alertes et Données importantes -->
<div class="row">
    <!-- Médicaments stock faible -->
    <div class="col-lg-6 mb-4">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Alertes Stock Médicaments
                </h5>
            </div>
            <div class="card-body">
                @if($lowStockMedicaments && $lowStockMedicaments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                            <tr>
                                <th>Médicament</th>
                                <th>Stock Actuel</th>
                                <th>Stock Minimum</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($lowStockMedicaments as $medicament)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-pills text-warning me-2"></i>
                                            <span>{{ $medicament->nom }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger">{{ $medicament->stock }}</span>
                                    </td>
                                    <td>{{ $medicament->stock_min }}</td>
                                    <td>
                                        <span class="badge bg-danger">Stock Faible</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="{{ route('commandes.index') }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-shopping-cart me-1"></i>Commander
                        </a>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted mb-0">Tous les stocks sont suffisants</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Hospitalisations en cours -->
    <div class="col-lg-6 mb-4">
        <div class="card border-info">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bed me-2"></i>
                    Hospitalisations en Cours
                </h5>
            </div>
            <div class="card-body">
                @if($activeHospitalisations && $activeHospitalisations->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($activeHospitalisations as $hospitalisation)
                            <div class="list-group-item border-0 px-0">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">{{ $hospitalisation->consultation->patient->nom_complet ?? 'Patient' }}</h6>
                                        <small class="text-muted">
                                            Salle: {{ $hospitalisation->salle->nom ?? 'N/A' }} |
                                            Lit: {{ $hospitalisation->lit_id ?? 'N/A' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-info">Depuis {{ $hospitalisation->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Service: {{ $hospitalisation->service->nom ?? 'Général' }}
                                    </small>
                                    <a href="{{ route('hospitalisations.index') }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye me-1"></i>Voir
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-procedures fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">Aucune hospitalisation en cours</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Actions Rapides -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2 text-primary"></i>
                    Actions Rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('consultations.create') }}" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-stethoscope fa-2x text-primary mb-2"></i>
                                <h6 class="mb-1">Nouvelle Consultation</h6>
                                <small class="text-muted">Examiner un patient</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('rendezvous.create') }}" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-plus fa-2x text-success mb-2"></i>
                                <h6 class="mb-1">Nouveau Rendez-vous</h6>
                                <small class="text-muted">Planifier consultation</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('ordonnances.create') }}" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-file-medical fa-2x text-info mb-2"></i>
                                <h6 class="mb-1">Nouvelle Ordonnance</h6>
                                <small class="text-muted">Prescrire traitement</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('examens.index') }}" class="card quick-action-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-vials fa-2x text-warning mb-2"></i>
                                <h6 class="mb-1">Demander Examen</h6>
                                <small class="text-muted">Prescrire analyses</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dernières Consultations -->
<div class="row mb-4 mt-4 ">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Dernières Consultations
                </h5>
                <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-4">Patient</th>
                            <th>Date</th>
                            <th>Diagnostic</th>
                            <th>Statut</th>
                            <th class="pe-4">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $recentConsultations = \App\Models\Consultation::with('patient')
                                ->where('medecin_id', auth()->id())
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp

                        @foreach($recentConsultations as $consultation)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary bg-opacity-10 rounded-circle me-3">
                                            <i class="fas fa-user-injured text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $consultation->patient->nom_complet ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $consultation->patient->code_patient ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-nowrap">{{ $consultation->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $consultation->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;">
                                                {{ $consultation->diagnostic ?? 'Aucun diagnostic' }}
                                            </span>
                                </td>
                                <td>
                                            <span class="badge bg-success-subtle text-success rounded-pill">
                                                Terminée
                                            </span>
                                </td>
                                <td class="pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('consultations.show', $consultation) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('consultations.print', $consultation) }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

