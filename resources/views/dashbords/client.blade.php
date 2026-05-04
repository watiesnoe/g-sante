<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner d-flex justify-content-between align-items-center" style="background: var(--success-gradient);">
            <div>
                <h1 class="display-5 fw-bold mb-2">Bonjour, {{ Auth::user()->prenom }}</h1>
                <p class="lead mb-0">Bienvenue dans votre espace santé personnel.</p>
            </div>
            <div class="d-none d-md-block">
                <i class="fas fa-heartbeat fa-5x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Mes Rendez-vous -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i>Mes Prochains Rendez-vous</h5>
            </div>
            <div class="card-body">
                @forelse($mesRendezVous as $rdv)
                    <div class="d-flex align-items-center p-3 mb-3 border rounded-3 hover-lift">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle me-3">
                            <i class="fas fa-calendar-check fs-4"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">{{ $rdv->motif }}</h6>
                            <p class="mb-0 text-muted">
                                <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($rdv->date_heure)->format('d/m/Y à H:i') }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-user-md me-1"></i> Dr. {{ $rdv->medecin->prenom }} {{ $rdv->medecin->nom }}
                            </p>
                        </div>
                        <div>
                            <span class="badge bg-{{ $rdv->statut == 'confirmé' ? 'success' : 'warning' }}-subtle text-{{ $rdv->statut == 'confirmé' ? 'success' : 'warning' }} rounded-pill">
                                {{ ucfirst($rdv->statut) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Vous n'avez aucun rendez-vous prévu.</p>
                        <a href="{{ route('rendezvous.index') }}" class="btn btn-primary rounded-pill">Prendre rendez-vous</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Dernière Consultation -->
        @if($derniereConsultation)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-medical text-success me-2"></i>Dernière Consultation</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Date</p>
                            <p class="fw-bold">{{ \Carbon\Carbon::parse($derniereConsultation->date_consultation)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Diagnostic</p>
                            <p class="fw-bold">{{ $derniereConsultation->diagnostic ?? 'Non précisé' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Ordonnances Actives -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-pills text-info me-2"></i>Ordonnances Actives</h5>
            </div>
            <div class="card-body">
                @forelse($ordonnancesActives as $ordo)
                    <div class="p-3 bg-light rounded-3 mb-2">
                        <h6 class="mb-1 fw-bold">N° {{ $ordo->numero_ordonnance }}</h6>
                        <p class="small text-muted mb-0">Délivrée le {{ \Carbon\Carbon::parse($ordo->created_at)->format('d/m/Y') }}</p>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">Pas d'ordonnance active.</p>
                @endforelse
            </div>
        </div>

        <!-- Contact d'urgence -->
        <div class="card border-0 shadow-sm bg-danger-subtle text-danger">
            <div class="card-body text-center py-4">
                <i class="fas fa-ambulance fa-3x mb-3"></i>
                <h5 class="fw-bold">Besoin d'aide ?</h5>
                <p class="mb-3">En cas d'urgence médicale, contactez-nous immédiatement.</p>
                <h2 class="fw-bold mb-0">15 / 112</h2>
            </div>
        </div>
    </div>
</div>
