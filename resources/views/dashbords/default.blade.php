

<!-- Accès Rapide -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-rocket me-2 text-primary"></i>
                    Accès Rapide
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('users.index') }}" class="card quick-access-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h6 class="mb-1">Utilisateurs</h6>
                                <small class="text-muted">Gestion des accès</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('patients.index') }}" class="card quick-access-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-procedures fa-2x text-success mb-2"></i>
                                <h6 class="mb-1">Patients</h6>
                                <small class="text-muted">Dossiers patients</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('consultations.index') }}" class="card quick-access-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-stethoscope fa-2x text-info mb-2"></i>
                                <h6 class="mb-1">Consultations</h6>
                                <small class="text-muted">Suivi médical</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <a href="{{ route('rendezvous.index') }}" class="card quick-access-card text-decoration-none">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-check fa-2x text-warning mb-2"></i>
                                <h6 class="mb-1">Rendez-vous</h6>
                                <small class="text-muted">Planning</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques principales avec indicateurs -->
<div class="row mb-4">
    @php
        $cards = [
            [
                'title' => 'Utilisateurs Total',
                'value' => $stats['total_users'] ?? 0,
                'icon' => 'fas fa-users',
                'color' => 'primary',
                'trend' => '+12%',
                'trend_up' => true,
                'subtitle' => 'Utilisateurs actifs',
                'route' => route('users.index')
            ],
            [
                'title' => 'Médecins',
                'value' => $stats['total_medecins'] ?? 0,
                'icon' => 'fas fa-user-md',
                'color' => 'success',
                'trend' => '+5%',
                'trend_up' => true,
                'subtitle' => 'Dont ' . ($stats['medecins_actifs'] ?? 0) . ' actifs',
                'route' => route('medecins.index')
            ],
            [
                'title' => 'Patients',
                'value' => $stats['total_patients'] ?? 0,
                'icon' => 'fas fa-procedures',
                'color' => 'info',
                'trend' => '+18%',
                'trend_up' => true,
                'subtitle' => ($stats['new_patients_today'] ?? 0) . ' nouveaux aujourd\'hui',
                'route' => route('patients.index')
            ],
            [
                'title' => 'Consultations',
                'value' => $stats['total_consultations'] ?? 0,
                'icon' => 'fas fa-stethoscope',
                'color' => 'warning',
                'trend' => '+8%',
                'trend_up' => true,
                'subtitle' => 'Ce mois',
                'route' => route('consultations.index')
            ]
        ];
    @endphp

    @foreach($cards as $card)
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
                                    <span class="badge bg-{{ $card['trend_up'] ? 'success' : 'danger' }}-subtle text-{{ $card['trend_up'] ? 'success' : 'danger' }}">
                                        <i class="fas fa-arrow-{{ $card['trend_up'] ? 'up' : 'down' }} me-1"></i>
                                        {{ $card['trend'] }}
                                    </span>
                            <span class="text-decoration-none small text-primary">
                                        Voir détails <i class="fas fa-chevron-right ms-1"></i>
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
            ['title'=>'Secrétaires', 'value'=>$stats['total_secretaires'] ?? 0, 'icon'=>'fas fa-user-tie', 'color'=>'secondary', 'route'=>route('users.index')],
            ['title'=>'Admins', 'value'=>$stats['total_admins'] ?? 0, 'icon'=>'fas fa-user-shield', 'color'=>'dark', 'route'=>route('users.index')],
            ['title'=>'Rendez-vous', 'value'=>$stats['total_rendezvou'] ?? 0, 'icon'=>'fas fa-calendar-check', 'color'=>'primary', 'route'=>route('rendezvous.index')],
            ['title'=>'Ordonnances', 'value'=>$stats['total_ordonnance'] ?? 0, 'icon'=>'fas fa-file-medical', 'color'=>'success', 'route'=>route('ordonnances.index')],
            ['title'=>'Médicaments', 'value'=>$stats['total_medicament'] ?? 0, 'icon'=>'fas fa-pills', 'color'=>'info', 'route'=>route('medicaments.index')],
            ['title'=>'Examens', 'value'=>$stats['total_examens'] ?? 0, 'icon'=>'fas fa-vials', 'color'=>'warning', 'route'=>route('examens.index')],
            ['title'=>'Lits Occupés', 'value'=>$stats['lits_occupes'] ?? 0, 'icon'=>'fas fa-bed', 'color'=>'danger', 'route'=>route('lits.index')],
            ['title'=>'Tickets Ouverts', 'value'=>$stats['total_ticket'] ?? 0, 'icon'=>'fas fa-ticket-alt', 'color'=>'secondary', 'route'=>route('tickets.index')],
        ];
    @endphp

    @foreach($secondaryCards as $card)
        <div class="col-xl-3 col-md-4 col-sm-6 mb-3">
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

<!-- Graphiques et Analytics -->
<div class="row">
    <!-- Graphique d'activité -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Activité de la Plateforme
                </h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-calendar me-1"></i>
                        <span id="chartPeriod">7 derniers jours</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item chart-period" href="#" data-period="7">7 derniers jours</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="30">30 derniers jours</a></li>
                        <li><a class="dropdown-item chart-period" href="#" data-period="90">3 derniers mois</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="activityChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Répartition des utilisateurs -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2 text-primary"></i>
                    Répartition des Utilisateurs
                </h5>
            </div>
            <div class="card-body">
                <canvas id="usersChart" height="250"></canvas>
                <div class="mt-3">
                    @php
                        $userDistribution = [
                            ['role' => 'Médecins', 'count' => $stats['total_medecins'] ?? 0, 'color' => '#28a745', 'route' => route('medecins.index')],
                            ['role' => 'Patients', 'count' => $stats['total_patients'] ?? 0, 'color' => '#17a2b8', 'route' => route('patients.index')],
                            ['role' => 'Secrétaires', 'count' => $stats['total_secretaires'] ?? 0, 'color' => '#6c757d', 'route' => route('users.index')],
                            ['role' => 'Admins', 'count' => $stats['total_admins'] ?? 0, 'color' => '#343a40', 'route' => route('users.index')],
                        ];
                    @endphp
                    @foreach($userDistribution as $dist)
                        <a href="{{ $dist['route'] }}" class="text-decoration-none d-block">
                            <div class="d-flex align-items-center justify-content-between mb-2 p-2 rounded hover-bg">
                                <div class="d-flex align-items-center">
                                    <span class="badge me-2" style="background-color: {{ $dist['color'] }}; width: 12px; height: 12px; border-radius: 50%;"></span>
                                    <span class="small text-dark">{{ $dist['role'] }}</span>
                                </div>
                                <span class="fw-semibold small text-dark">{{ $dist['count'] }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modules Principaux -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-th-large me-2 text-primary"></i>
                    Modules Principaux
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Gestion Médicale -->
                    <div class="col-lg-4">
                        <div class="card module-card border-0 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-hospital me-2"></i>
                                    Gestion Médicale
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('consultations.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-stethoscope me-2 text-primary"></i>Consultations</span>
                                        <span class="badge bg-primary rounded-pill">{{ $stats['total_consultations'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('rendezvous.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-calendar-check me-2 text-success"></i>Rendez-vous</span>
                                        <span class="badge bg-success rounded-pill">{{ $stats['total_rendezvou'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('ordonnances.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-file-medical me-2 text-info"></i>Ordonnances</span>
                                        <span class="badge bg-info rounded-pill">{{ $stats['total_ordonnance'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('examens.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-vials me-2 text-warning"></i>Examens</span>
                                        <span class="badge bg-warning rounded-pill">{{ $stats['total_examens'] ?? 0 }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestion Patients -->
                    <div class="col-lg-4">
                        <div class="card module-card border-0 shadow-sm">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-procedures me-2"></i>
                                    Gestion Patients
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('patients.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-users me-2 text-success"></i>Dossiers Patients</span>
                                        <span class="badge bg-success rounded-pill">{{ $stats['total_patients'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('hospitalisations.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-bed me-2 text-info"></i>Hospitalisations</span>
                                        <span class="badge bg-info rounded-pill">{{ $stats['total_hospitalisations'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('lits.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-procedures me-2 text-warning"></i>Gestion des Lits</span>
                                        <span class="badge bg-warning rounded-pill">{{ $stats['total_lits'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('salles.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-door-open me-2 text-secondary"></i>Salles</span>
                                        <span class="badge bg-secondary rounded-pill">{{ $stats['total_salles'] ?? 0 }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gestion Administrative -->
                    <div class="col-lg-4">
                        <div class="card module-card border-0 shadow-sm">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-cogs me-2"></i>
                                    Gestion Administrative
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-user-shield me-2 text-info"></i>Utilisateurs</span>
                                        <span class="badge bg-info rounded-pill">{{ $stats['total_users'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('medicaments.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-pills me-2 text-success"></i>Médicaments</span>
                                        <span class="badge bg-success rounded-pill">{{ $stats['total_medicament'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('fournisseurs.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-truck me-2 text-warning"></i>Fournisseurs</span>
                                        <span class="badge bg-warning rounded-pill">{{ $stats['total_fournisseur'] ?? 0 }}</span>
                                    </a>
                                    <a href="{{ route('commandes.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-shopping-cart me-2 text-primary"></i>Commandes</span>
                                        <span class="badge bg-primary rounded-pill">{{ $stats['total_commandes'] ?? 0 }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableaux de données -->
<div class="row mt-4">
    <!-- Activités récentes -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>
                    Activités Récentes
                </h5>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th class="ps-4">Utilisateur</th>
                            <th>Action</th>
                            <th>Date</th>
                            <th class="text-center">Statut</th>
                            <th class="pe-4"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($recentActivities ?? [] as $activity)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset($activity->user_avatar ?? 'assets/images/users/default.png') }}"
                                             class="rounded-circle me-3" width="36" height="36" alt="{{ $activity->user_name }}">
                                        <div>
                                            <div class="fw-semibold">{{ $activity->user_name }}</div>
                                            <small class="text-muted">{{ $activity->user_role ?? 'Utilisateur' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $activity->action }}</span>
                                    @if($activity->module)
                                        <br><small class="text-muted">Module: {{ $activity->module }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-nowrap">{{ $activity->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $activity->created_at->format('H:i') }}</small>
                                </td>
                                <td class="text-center">
                                            <span class="badge bg-{{ $activity->status_color ?? 'secondary' }}-subtle text-{{ $activity->status_color ?? 'secondary' }} rounded-pill">
                                                {{ $activity->status ?? 'Complété' }}
                                            </span>
                                </td>
                                <td class="pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i>Voir</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                        </ul>
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

    <!-- Notifications et Alertes -->
    <div class="col-lg-4">
        <!-- Notifications -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bell me-2 text-primary"></i>
                    Notifications
                </h5>
                <span class="badge bg-primary">{{ count($notifications ?? []) }}</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($notifications ?? [] as $note)
                        <a href="{{ $note->link ?? '#' }}" class="list-group-item list-group-item-action border-0">
                            <div class="d-flex w-100 justify-content-between align-items-start">
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="fas fa-{{ $note->icon ?? 'circle' }} text-{{ $note->type ?? 'primary' }} me-2 fs-6"></i>
                                        <h6 class="mb-0 fw-semibold">{{ $note->title }}</h6>
                                    </div>
                                    <p class="mb-1 text-muted small">{{ $note->message }}</p>
                                </div>
                                <small class="text-muted text-nowrap">{{ $note->time_ago }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Liens Rapides Administration -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-wrench me-2 text-warning"></i>
                    Administration
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('configuration') }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="fas fa-cog me-2"></i>Paramètres Système
                    </a>
                    <a href="" class="btn btn-outline-success btn-sm text-start">
                        <i class="fas fa-user-edit me-2"></i>Mon Profil
                    </a>
                    <a href="{{ route('services.index') }}" class="btn btn-outline-info btn-sm text-start">
                        <i class="fas fa-hand-holding-medical me-2"></i>Services Médicaux
                    </a>
                    <a href="{{ route('prestations.index') }}" class="btn btn-outline-warning btn-sm text-start">
                        <i class="fas fa-list-alt me-2"></i>Prestations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
