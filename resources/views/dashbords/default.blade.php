<div class="row">
    <!-- Ordonnances -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Ordonnances Total</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pharmacienStats['total_ordonnances'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-prescription-bottle fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-muted">
                    Aujourd'hui: {{ $pharmacienStats['ordonnances_today'] }}
                </div>
            </div>
        </div>
    </div>

    <!-- Médicaments -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Médicaments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pharmacienStats['total_medicaments'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-pills fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <span class="badge badge-warning">Stock faible: {{ $pharmacienStats['medicaments_low_stock'] }}</span>
                    <span class="badge badge-danger">Rupture: {{ $pharmacienStats['medicaments_out_of_stock'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fournisseurs -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Fournisseurs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pharmacienStats['total_fournisseurs'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Patients -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Patients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pharmacienStats['total_patients'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-muted">
                    Nouveaux aujourd'hui: {{ $pharmacienStats['patients_today'] }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deuxième ligne de statistiques -->
<div class="row">
    <!-- Hospitalisation -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                            Lits Hospitalisation</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $pharmacienStats['lits_occupes'] }}/{{ $pharmacienStats['total_lits'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bed fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2 text-xs text-muted">
                    Taux d'occupation: {{ round(($pharmacienStats['lits_occupes'] / max($pharmacienStats['total_lits'], 1)) * 100) }}%
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-dark shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                            Tickets Salle d'Attente</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pharmacienStats['total_tickets'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes Stock -->
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Alertes Stock</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $pharmacienStats['medicaments_low_stock'] + $pharmacienStats['medicaments_out_of_stock'] }}
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
                <div class="mt-2">
                    @if($pharmacienStats['medicaments_out_of_stock'] > 0)
                        <span class="badge badge-danger">Ruptures: {{ $pharmacienStats['medicaments_out_of_stock'] }}</span>
                    @endif
                    @if($pharmacienStats['medicaments_low_stock'] > 0)
                        <span class="badge badge-warning">Faible: {{ $pharmacienStats['medicaments_low_stock'] }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menu d'actions rapides -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actions Rapides</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('ordonnances.index') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-prescription-bottle mr-2"></i>Gérer Ordonnances
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('medicaments.index') }}" class="btn btn-success btn-block">
                            <i class="fas fa-pills mr-2"></i>Stock Médicaments
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('fournisseurs.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-truck mr-2"></i>Fournisseurs
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('paiements.index') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-money-bill-wave mr-2"></i>Paiements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
