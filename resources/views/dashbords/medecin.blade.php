
<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-5 fw-bold mb-2">Bonjour, Dr. {{ Auth::user()->prenom }}</h1>
                <p class="lead mb-0">Vous avez <span class="fw-bold">{{ $stats['consultations_today'] ?? 0 }} consultations</span> prévues aujourd'hui.</p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-user-md fa-5x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques principales -->
<div class="row mb-4">
    @php
        $mainCards = [
            [
                'title' => 'Consultations Aujourd\'hui',
                'value' => $stats['consultations_today'] ?? 0,
                'icon' => 'fas fa-stethoscope',
                'color' => 'primary',
                'gradient' => 'var(--primary-gradient)',
                'subtitle' => 'Patients en attente',
                'route' => route('consultations.index')
            ],
            [
                'title' => 'Patients Suivis',
                'value' => $stats['total_patients'] ?? 0,
                'icon' => 'fas fa-procedures',
                'color' => 'success',
                'gradient' => 'var(--success-gradient)',
                'subtitle' => 'Dossiers actifs',
                'route' => route('patients.index')
            ],
            [
                'title' => 'Hospitalisations',
                'value' => $stats['active_hospitalisations'] ?? 0,
                'icon' => 'fas fa-bed',
                'color' => 'warning',
                'gradient' => 'var(--warning-gradient)',
                'subtitle' => 'Patients hospitalisés',
                'route' => route('hospitalisations.index')
            ],
            [
                'title' => 'Alertes Stock',
                'value' => $stats['low_stock_medicaments'] ?? 0,
                'icon' => 'fas fa-exclamation-triangle',
                'color' => 'danger',
                'gradient' => 'var(--danger-gradient)',
                'subtitle' => 'Médicaments critiques',
                'route' => route('medicaments.index')
            ]
        ];
    @endphp

    @foreach($mainCards as $card)
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $card['route'] }}" class="text-decoration-none text-dark">
                <div class="card card-statistic h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-stat me-3" style="background: {{ $card['gradient'] }}; color: white;">
                                <i class="{{ $card['icon'] }} fs-5"></i>
                            </div>
                            <h6 class="card-title text-muted mb-0 fw-bold">{{ $card['title'] }}</h6>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ $card['value'] }}</h2>
                        <small class="text-muted">{{ $card['subtitle'] }}</small>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Contenu Principal -->
<div class="row">
    <!-- Graphique des Consultations -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-chart-line text-primary me-2"></i>Activité des Consultations</h5>
                <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Historique Complet</a>
            </div>
            <div class="card-body">
                <canvas id="consultations-chart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Rendez-vous du Jour -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-day text-info me-2"></i>Aujourd'hui</h5>
            </div>
            <div class="card-body p-0">
                @forelse($todayAppointments as $appointment)
                    <div class="p-3 border-bottom d-flex align-items-center hover-bg">
                        <div class="avatar-stat bg-info-subtle text-info me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">{{ $appointment->patient->nom_complet ?? 'Patient' }}</h6>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($appointment->date_heure)->format('H:i') }} - {{ $appointment->motif }}</small>
                        </div>
                        <span class="badge bg-info-subtle text-info rounded-pill">RDV</span>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun rendez-vous pour le moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Deuxième Ligne : Alertes et Hospitalisations -->
<div class="row">
    <!-- Médicaments en alerte -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-pills me-2"></i>Stock Critique</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 border-0 small text-uppercase">Médicament</th>
                                <th class="border-0 small text-uppercase">Stock</th>
                                <th class="border-0 small text-uppercase text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockMedicaments->take(5) as $med)
                                <tr>
                                    <td class="ps-3 fw-bold">{{ $med->nom }}</td>
                                    <td><span class="badge bg-danger-subtle text-danger">{{ $med->stock }} unités</span></td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('commandes.index') }}" class="btn btn-sm btn-primary">Commander</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4">Stock optimal <i class="fas fa-check text-success ms-1"></i></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Hospitalisations Actives -->
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-procedures me-2"></i>Hospitalisations Actives</h5>
            </div>
            <div class="card-body p-0">
                @forelse($activeHospitalisations->take(5) as $hosp)
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $hosp->consultation->patient->nom_complet ?? 'N/A' }}</h6>
                            <small class="text-muted">Salle : {{ $hosp->salle->nom }} | Service : {{ $hosp->service->nom }}</small>
                        </div>
                        <a href="{{ route('hospitalisations.show', $hosp->id) }}" class="btn btn-sm btn-outline-warning">Gérer</a>
                    </div>
                @empty
                    <div class="text-center py-4">Aucune hospitalisation en cours</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Actions Rapides -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('consultations.create') }}" class="btn btn-lg btn-primary w-100 py-3">
                            <i class="fas fa-stethoscope me-2"></i> Nouvelle Consultation
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('ordonnances.create') }}" class="btn btn-lg btn-success w-100 py-3">
                            <i class="fas fa-file-medical me-2"></i> Ordonnance
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="" class="btn btn-lg btn-info text-white w-100 py-3">
                            <i class="fas fa-calendar-plus me-2"></i> Rendez-vous
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('patients.create') }}" class="btn btn-lg btn-dark w-100 py-3">
                            <i class="fas fa-user-plus me-2"></i> Nouveau Patient
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
