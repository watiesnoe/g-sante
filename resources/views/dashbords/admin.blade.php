<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="block block-rounded bg-primary text-white">
            <div class="block-content block-content-full">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="h4 mb-2">Bonjour, Admin {{ Auth::user()->prenom ?? Auth::user()->name }} !</h3>
                        <p class="fs-sm mb-3 opacity-75">
                            Gestion administrative de l'établissement médical.
                        </p>
                        <div class="h2 mb-0">{{ number_format($stats['revenus_mois'] ?? 0, 0, ',', ' ') }} F CFA</div>
                        <div class="text-white-75">Revenus ce mois</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-user-shield fa-4x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('personnel.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-primary text-center">
                <div class="item item-circle bg-primary-lighter text-primary mx-auto my-3">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['total_personnel'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Personnel</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('consultations.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-success text-center">
                <div class="item item-circle bg-success-lighter text-success mx-auto my-3">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['consultations_mois'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Consultations/mois</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('finances.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-warning text-center">
                <div class="item item-circle bg-warning-lighter text-warning mx-auto my-3">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ number_format($stats['revenus_mois'] ?? 0, 0, ',', ' ') }} F</div>
                <div class="text-white-75 mb-3">Revenus/mois</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('medicaments.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-info text-center">
                <div class="item item-circle bg-info-lighter text-info mx-auto my-3">
                    <i class="fas fa-pills"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['alertes_stock'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Alertes Stock</div>
            </div>
        </a>
    </div>
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
