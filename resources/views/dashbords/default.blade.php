<!-- Statistiques principales -->
<div class="row mb-4">
    @php
        $defaultStats = [
            [
                'title' => 'Total Ordonnances',
                'value' => $stats['total_ordonnances'] ?? 0,
                'icon' => 'fas fa-prescription-bottle',
                'color' => 'primary',
                'gradient' => 'var(--primary-gradient)',
                'subtitle' => 'Aujourd\'hui: ' . ($stats['ordonnances_today'] ?? 0)
            ],
            [
                'title' => 'Stock Médicaments',
                'value' => $stats['total_medicaments'] ?? 0,
                'icon' => 'fas fa-pills',
                'color' => 'success',
                'gradient' => 'var(--success-gradient)',
                'subtitle' => 'Faible: ' . ($stats['medicaments_low_stock'] ?? 0)
            ],
            [
                'title' => 'Total Patients',
                'value' => $stats['total_patients'] ?? 0,
                'icon' => 'fas fa-user-injured',
                'color' => 'info',
                'gradient' => 'var(--info-gradient)',
                'subtitle' => 'Nouveaux: ' . ($stats['patients_today'] ?? 0)
            ],
            [
                'title' => 'Salle d\'Attente',
                'value' => $stats['total_tickets'] ?? 0,
                'icon' => 'fas fa-ticket-alt',
                'color' => 'warning',
                'gradient' => 'var(--warning-gradient)',
                'subtitle' => 'Tickets en cours'
            ],
        ];
    @endphp

    @foreach($defaultStats as $stat)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-statistic h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-stat me-3" style="background: {{ $stat['gradient'] }}; color: white;">
                            <i class="{{ $stat['icon'] }} fs-5"></i>
                        </div>
                        <h6 class="card-title text-muted mb-0 fw-bold">{{ $stat['title'] }}</h6>
                    </div>
                    <h2 class="mb-0 fw-bold">{{ $stat['value'] }}</h2>
                    <small class="text-muted">{{ $stat['subtitle'] }}</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Deuxième ligne de statistiques -->
<div class="row mb-4">
    <!-- Hospitalisation -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-stat bg-secondary-subtle text-secondary me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                        <i class="fas fa-bed fs-5"></i>
                    </div>
                    <h6 class="card-title text-muted mb-0 fw-bold">Occupation Lits</h6>
                </div>
                <h2 class="mb-0 fw-bold">{{ $stats['lits_occupes'] }}/{{ $stats['total_lits'] }}</h2>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar bg-secondary" role="progressbar" 
                         style="width: {{ round(($stats['lits_occupes'] / max($stats['total_lits'], 1)) * 100) }}%" 
                         aria-valuenow="{{ $stats['lits_occupes'] }}" aria-valuemin="0" aria-valuemax="{{ $stats['total_lits'] }}"></div>
                </div>
                <small class="text-muted d-block mt-2">Taux d'occupation: {{ round(($stats['lits_occupes'] / max($stats['total_lits'], 1)) * 100) }}%</small>
            </div>
        </div>
    </div>

    <!-- Fournisseurs -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-stat bg-info-subtle text-info me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                        <i class="fas fa-truck fs-5"></i>
                    </div>
                    <h6 class="card-title text-muted mb-0 fw-bold">Fournisseurs</h6>
                </div>
                <h2 class="mb-0 fw-bold">{{ $stats['total_fournisseurs'] }}</h2>
                <small class="text-muted">Partenaires actifs</small>
            </div>
        </div>
    </div>

    <!-- Alertes Stock -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-stat bg-danger-subtle text-danger me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                        <i class="fas fa-exclamation-triangle fs-5"></i>
                    </div>
                    <h6 class="card-title text-muted mb-0 fw-bold">Alertes Stock</h6>
                </div>
                <h2 class="mb-0 fw-bold text-danger">{{ $stats['medicaments_low_stock'] + $stats['medicaments_out_of_stock'] }}</h2>
                <div class="mt-2">
                    @if($stats['medicaments_out_of_stock'] > 0)
                        <span class="badge bg-danger rounded-pill">Ruptures: {{ $stats['medicaments_out_of_stock'] }}</span>
                    @endif
                    @if($stats['medicaments_low_stock'] > 0)
                        <span class="badge bg-warning text-dark rounded-pill">Faible: {{ $stats['medicaments_low_stock'] }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menu d'actions rapides -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('ordonnances.index') }}" class="btn btn-lg btn-primary w-100 py-3">
                            <i class="fas fa-prescription-bottle me-2"></i> Gérer Ordonnances
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('medicaments.index') }}" class="btn btn-lg btn-success w-100 py-3">
                            <i class="fas fa-pills me-2"></i> Stock Médicaments
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('fournisseurs.index') }}" class="btn btn-lg btn-info text-white w-100 py-3">
                            <i class="fas fa-truck me-2"></i> Gérer Fournisseurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
