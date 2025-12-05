@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('content')
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Tableau de Bord Médical</h1>
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de Bord</a></li>
                    <li class="breadcrumb-item active">Accueil</li>
                </ol>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="block block-rounded bg-primary text-white">
                    <div class="block-content block-content-full">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="h4 mb-2">Bonjour, Dr. {{ Auth::user()->name ?? 'Martin' }} !</h3>
                                <p class="fs-sm mb-3 opacity-75">
                                    Voici un aperçu de vos activités médicales aujourd'hui.
                                    Vous avez {{ $todayAppointments->count() }} rendez-vous programmés.
                                </p>
                                <div class="h2 mb-0">{{ $stats['consultations_today'] }} consultations aujourd'hui</div>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="fas fa-stethoscope fa-4x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <a class="block block-rounded block-link-pop text-center" href="{{ route('patients.index') }}">
                    <div class="block-content block-content-full">
                        <div class="item item-circle bg-primary text-white mx-auto mb-3">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="fw-semibold">Patients</div>
                        <small class="text-muted">Voir tous les patients</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a class="block block-rounded block-link-pop text-center" href="{{ route('consultations.index') }}">
                    <div class="block-content block-content-full">
                        <div class="item item-circle bg-success text-white mx-auto mb-3">
                            <i class="fas fa-notes-medical"></i>
                        </div>
                        <div class="fw-semibold">Consultations</div>
                        <small class="text-muted">Voir toutes les consultations</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a class="block block-rounded block-link-pop text-center" href="{{ route('rendezvous.index') }}">
                    <div class="block-content block-content-full">
                        <div class="item item-circle bg-warning text-white mx-auto mb-3">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="fw-semibold">Rendez-vous</div>
                        <small class="text-muted">Voir tous les RDV</small>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <a class="block block-rounded block-link-pop text-center" href="{{ route('ordonnances.index') }}">
                    <div class="block-content block-content-full">
                        <div class="item item-circle bg-danger text-white mx-auto mb-3">
                            <i class="fas fa-file-prescription"></i>
                        </div>
                        <div class="fw-semibold">Ordonnances</div>
                        <small class="text-muted">Voir toutes les ordonnances</small>
                    </div>
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('patients.index') }}" class="block block-rounded">
                    <div class="block-content block-content-full bg-primary text-center">
                        <div class="item item-circle bg-primary-lighter text-primary mx-auto my-3">
                            <i class="fas fa-user-injured"></i>
                        </div>
                        <div class="fs-1 fw-bold text-white">{{ $stats['total_patients'] }}</div>
                        <div class="text-white-75 mb-3">Patients Totals</div>
                        <span class="badge bg-white text-primary">
                        <i class="fa fa-arrow-up opacity-50 me-1"></i>
                        {{ $stats['new_patients_today'] }} aujourd'hui
                    </span>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('consultations.index') }}" class="block block-rounded">
                    <div class="block-content block-content-full bg-success text-center">
                        <div class="item item-circle bg-success-lighter text-success mx-auto my-3">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="fs-1 fw-bold text-white">{{ $stats['total_consultations'] }}</div>
                        <div class="text-white-75 mb-3">Consultations</div>
                        <span class="badge bg-white text-success">
                        <i class="fa fa-arrow-up opacity-50 me-1"></i>
                        {{ $stats['consultations_today'] }} aujourd'hui
                    </span>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('hospitalisations.index') }}" class="block block-rounded">
                    <div class="block-content block-content-full bg-warning text-center">
                        <div class="item item-circle bg-warning-lighter text-warning mx-auto my-3">
                            <i class="fas fa-procedures"></i>
                        </div>
                        <div class="fs-1 fw-bold text-white">{{ $stats['total_hospitalisations'] }}</div>
                        <div class="text-white-75 mb-3">Hospitalisations</div>
                        <span class="badge bg-white text-warning">
                        <i class="fas fa-bed opacity-50 me-1"></i>
                        {{ $stats['active_hospitalisations'] }} en cours
                    </span>
                    </div>
                </a>
            </div>
            <div class="col-sm-6 col-xl-3">
                <a href="{{ route('medicaments.index') }}" class="block block-rounded">
                    <div class="block-content block-content-full bg-danger text-center">
                        <div class="item item-circle bg-danger-lighter text-danger mx-auto my-3">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div class="fs-1 fw-bold text-white">{{ $stats['total_medicaments'] }}</div>
                        <div class="text-white-75 mb-3">Médicaments</div>
                        <span class="badge bg-white text-danger">
                        <i class="fas fa-exclamation-triangle opacity-50 me-1"></i>
                        {{ $stats['low_stock_medicaments'] }} alertes
                    </span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Sections détaillées (Rendez-vous, Stock, Hospitalisations) -->
        <div class="row">
            <div class="col-lg-6">
                <a href="{{ route('rendezvous.index') }}">
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">
                                <i class="fas fa-calendar-day text-primary me-2"></i>
                                Rendez-vous d'aujourd'hui
                            </h3>
                            <div class="block-options">
                                <span class="badge bg-primary">{{ $todayAppointments->count() }} RDV</span>
                            </div>
                        </div>
                        <div class="block-content">
                            @forelse($todayAppointments as $appointment)
                                <div class="mb-3 p-3 border-start border-3 border-{{ $appointment->statut == 'prevu' ? 'primary' : ($appointment->statut == 'realise' ? 'success' : 'warning') }}">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img class="img-avatar img-avatar32" src="{{ asset('assets/media/avatars/avatar0.jpg') }}" alt="">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-semibold">{{ $appointment->patient->prenom }} {{ $appointment->patient->nom }}</div>
                                            <div class="fs-sm text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                {{ \Carbon\Carbon::parse($appointment->date_heure)->format('H:i') }} • {{ $appointment->motif }}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                        <span class="badge bg-{{ $appointment->statut == 'prevu' ? 'primary' : ($appointment->statut == 'realise' ? 'success' : 'warning') }}">
                                            {{ $appointment->statut }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                    <div class="text-muted">Aucun rendez-vous aujourd'hui</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-6">
                <a href="{{ route('medicaments.index') }}">
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">
                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                Alertes Stock
                            </h3>
                            <div class="block-options">
                                <span class="badge bg-danger">{{ $lowStockMedicaments->count() }} alertes</span>
                            </div>
                        </div>
                        <div class="block-content">
                            @forelse($lowStockMedicaments as $medicament)
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
                            @empty
                                <div class="text-center py-4">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <div class="text-muted">Aucune alerte de stock</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-6">
                <a href="{{ route('hospitalisations.index') }}">
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">
                                <i class="fas fa-bed text-warning me-2"></i>
                                Hospitalisations en cours
                            </h3>
                            <div class="block-options">
                                <span class="badge bg-warning">{{ $activeHospitalisations->count() }} patients</span>
                            </div>
                        </div>
                        <div class="block-content">
                            @forelse($activeHospitalisations as $hospitalisation)
                                <div class="mb-3 p-3 bg-warning-lighter border-start border-3 border-warning">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img class="img-avatar img-avatar32" src="{{ asset('assets/media/avatars/avatar0.jpg') }}" alt="">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-semibold">{{ $hospitalisation->consultation->patient->prenom }} {{ $hospitalisation->consultation->patient->nom }}</div>
                                            <div class="fs-sm text-muted">
                                                <i class="fas fa-door-open me-1"></i>
                                                Chambre {{ $hospitalisation->salle->numero ?? 'N/A' }} • {{ $hospitalisation->service->nom ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                        <span class="badge bg-{{ $hospitalisation->etat == 'en cours' ? 'warning' : 'success' }}">
                                            {{ $hospitalisation->etat }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="fas fa-bed fa-2x text-muted mb-2"></i>
                                    <div class="text-muted">Aucune hospitalisation en cours</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-6">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fas fa-chart-bar text-success me-2"></i>
                            Activité des Consultations
                        </h3>
                    </div>
                    <div class="block-content">
                        <div class="pt-3" style="height: 300px;">
                            <canvas id="consultations-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/lib/chart.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('consultations-chart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($consultationStats['months']),
                        datasets: [{
                            label: 'Consultations',
                            data: @json($consultationStats['counts']),
                            backgroundColor: 'rgba(70, 128, 255, 0.1)',
                            borderColor: '#4680ff',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#4680ff',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
                            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                        }
                    }
                });
            }
        });
    </script>
@endsection
