<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="block block-rounded bg-info text-white">
            <div class="block-content block-content-full">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="h4 mb-2">Bonjour, {{ Auth::user()->prenom ?? Auth::user()->name }} !</h3>
                        <p class="fs-sm mb-3 opacity-75">
                            Gestion des rendez-vous et accueil des patients.
                        </p>
                        <div class="h2 mb-0">{{ $todayAppointments->count() }} RDV aujourd'hui</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-calendar-alt fa-4x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rendez-vous du jour -->
<div class="row">
    <div class="col-lg-8">
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
                                    {{ \Carbon\Carbon::parse($appointment->date_heure)->format('H:i') }} •
                                    {{ $appointment->motif }}
                                </div>
                                <div class="fs-sm">
                                    <i class="fas fa-user-md me-1"></i>
                                    Dr. {{ $appointment->medecin->prenom ?? 'N/A' }}
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
    </div>

    <div class="col-lg-4">
        <!-- Statistiques rapides -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fas fa-chart-bar text-success me-2"></i>
                    Aujourd'hui
                </h3>
            </div>
            <div class="block-content">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Nouveaux patients</span>
                        <strong class="text-primary">{{ $stats['new_patients_today'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>RDV réalisés</span>
                        <strong class="text-success">{{ $stats['rdv_realises'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>RDV en attente</span>
                        <strong class="text-warning">{{ $stats['rdv_attente'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Factures à émettre</span>
                        <strong class="text-danger">{{ $stats['factures_pending'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fas fa-bolt text-warning me-2"></i>
                    Actions Rapides
                </h3>
            </div>
            <div class="block-content">
                <a href="{{ route('rendezvous.create') }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-calendar-plus me-2"></i>Nouveau RDV
                </a>
                <a href="{{ route('patients.create') }}" class="btn btn-success w-100 mb-2">
                    <i class="fas fa-user-plus me-2"></i>Nouveau Patient
                </a>
                <a href="{{ route('factures.create') }}" class="btn btn-warning w-100">
                    <i class="fas fa-file-invoice me-2"></i>Nouvelle Facture
                </a>
            </div>
        </div>
    </div>
</div>
