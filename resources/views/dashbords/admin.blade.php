<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner d-flex justify-content-between align-items-center" style="background: var(--primary-gradient);">
            <div>
                <h1 class="display-5 fw-bold mb-2">Bonjour, Admin {{ Auth::user()->prenom }}</h1>
                <p class="lead mb-0">Revenus ce mois : <span class="fw-bold">{{ number_format($stats['revenus_mois'] ?? 0, 0, ',', ' ') }} F CFA</span></p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-coins fa-5x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques principales -->
<div class="row mb-4">
    @php
        $adminStats = [
            ['title' => 'Personnel', 'value' => $stats['total_personnel'] ?? 0, 'icon' => 'fas fa-user-tie', 'color' => 'primary', 'gradient' => 'var(--primary-gradient)', 'route' => route('users.index')],
            ['title' => 'Consultations/mois', 'value' => $stats['consultations_mois'] ?? 0, 'icon' => 'fas fa-stethoscope', 'color' => 'success', 'gradient' => 'var(--success-gradient)', 'route' => route('consultations.index')],
            ['title' => 'Alertes Stock', 'value' => $stats['alertes_stock'] ?? 0, 'icon' => 'fas fa-exclamation-triangle', 'color' => 'danger', 'gradient' => 'var(--danger-gradient)', 'route' => route('medicaments.index')],
            ['title' => 'Total Patients', 'value' => $stats['total_patients'] ?? 0, 'icon' => 'fas fa-users', 'color' => 'info', 'gradient' => 'var(--info-gradient)', 'route' => route('patients.index')],
        ];
    @endphp

    @foreach($adminStats as $stat)
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ $stat['route'] }}" class="text-decoration-none text-dark">
                <div class="card card-statistic h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-stat me-3" style="background: {{ $stat['gradient'] }}; color: white;">
                                <i class="{{ $stat['icon'] }} fs-5"></i>
                            </div>
                            <h6 class="card-title text-muted mb-0 fw-bold">{{ $stat['title'] }}</h6>
                        </div>
                        <h2 class="mb-0 fw-bold">{{ $stat['value'] }}</h2>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

@if(isset($lowStockMedicaments) && $lowStockMedicaments->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Alertes Stock Médicaments
                    </h3>
                    <div class="block-options">
                        <span class="badge bg-danger">{{ $lowStockMedicaments->count() }} alertes</span>
                    </div>
                </div>
                <div class="block-content">
                    @foreach($lowStockMedicaments as $medicament)
                        <div class="mb-3 p-3 bg-danger-lighter border-start border-3 border-danger">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $medicament->nom }}</div>
                                    <div class="fs-sm text-muted">
                                        Stock: {{ $medicament->stock }} / Minimum: {{ $medicament->stock_min }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                            <span class="badge bg-{{ $medicament->stock <= 5 ? 'danger' : 'warning' }}">
                                {{ $medicament->stock <= 5 ? 'CRITIQUE' : 'FAIBLE' }}
                            </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
