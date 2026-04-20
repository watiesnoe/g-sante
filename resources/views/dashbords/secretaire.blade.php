<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner d-flex justify-content-between align-items-center" style="background: var(--info-gradient);">
            <div>
                <h1 class="display-5 fw-bold mb-2">Bonjour, {{ Auth::user()->prenom }}</h1>
                <p class="lead mb-0">Vous avez <span class="fw-bold">{{ $todayAppointments->count() }} rendez-vous</span> à gérer aujourd'hui.</p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-headset fa-5x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistiques rapides -->
<div class="row mb-4">
    @php
        $secretaireStats = [
            ['title' => 'Nouveaux Patients', 'value' => $stats['new_patients_today'] ?? 0, 'icon' => 'fas fa-user-plus', 'color' => 'primary', 'gradient' => 'var(--primary-gradient)'],
            ['title' => 'RDV Réalisés', 'value' => $stats['rdv_realises'] ?? 0, 'icon' => 'fas fa-calendar-check', 'color' => 'success', 'gradient' => 'var(--success-gradient)'],
            ['title' => 'RDV en Attente', 'value' => $stats['rdv_attente'] ?? 0, 'icon' => 'fas fa-clock', 'color' => 'warning', 'gradient' => 'var(--warning-gradient)'],
            ['title' => 'Total Patients', 'value' => $stats['total_patients'] ?? 0, 'icon' => 'fas fa-users', 'color' => 'info', 'gradient' => 'var(--info-gradient)'],
        ];
    @endphp

    @foreach($secretaireStats as $stat)
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
                </div>
            </div>
        </div>
    @endforeach
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

        <!-- Actions rapides -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i>Actions Rapides</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('rendezvous.create') }}" class="btn btn-primary w-100 mb-3 py-3">
                    <i class="fas fa-calendar-plus me-2"></i>Nouveau RDV
                </a>
                <a href="{{ route('patients.create') }}" class="btn btn-success w-100 mb-3 py-3">
                    <i class="fas fa-user-plus me-2"></i>Nouveau Patient
                </a>
            </div>
        </div>
    </div>
</div>
