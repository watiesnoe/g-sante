<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="block block-rounded bg-dark text-white">
            <div class="block-content block-content-full">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="h4 mb-2">Bonjour, Super Admin {{ Auth::user()->prenom ?? Auth::user()->name }} !</h3>
                        <p class="fs-sm mb-3 opacity-75">
                            Vous avez un accès complet à toutes les fonctionnalités du système.
                        </p>
                        <div class="h2 mb-0">{{ $stats['total_users'] ?? 0 }} utilisateurs</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-crown fa-4x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('users.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-primary text-center">
                <div class="item item-circle bg-primary-lighter text-primary mx-auto my-3">
                    <i class="fas fa-users"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['total_users'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Utilisateurs</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('medecins.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-success text-center">
                <div class="item item-circle bg-success-lighter text-success mx-auto my-3">
                    <i class="fas fa-user-md"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['total_medecins'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Médecins</div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('patients.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-warning text-center">
                <div class="item item-circle bg-warning-lighter text-warning mx-auto my-3">
                    <i class="fas fa-user-injured"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['total_patients'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Patients</div>
                <span class="badge bg-white text-warning">
                    <i class="fa fa-arrow-up opacity-50 me-1"></i>
                    {{ $stats['new_patients_today'] ?? 0 }} aujourd'hui
                </span>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('consultations.index') }}" class="block block-rounded">
            <div class="block-content block-content-full bg-info text-center">
                <div class="item item-circle bg-info-lighter text-info mx-auto my-3">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <div class="fs-1 fw-bold text-white">{{ $stats['total_consultations'] ?? 0 }}</div>
                <div class="text-white-75 mb-3">Consultations</div>
            </div>
        </a>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-4 mb-3">
        <a class="block block-rounded block-link-pop text-center" href="{{ route('users.index') }}">
            <div class="block-content block-content-full">
                <div class="item item-circle bg-primary text-white mx-auto mb-3">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="fw-semibold">Gestion Utilisateurs</div>
                <small class="text-muted">Gérer tous les utilisateurs</small>
            </div>
        </a>
    </div>
    <div class="col-md-4 mb-3">
        <a class="block block-rounded block-link-pop text-center" href="{{ route('configuration') }}">
            <div class="block-content block-content-full">
                <div class="item item-circle bg-success text-white mx-auto mb-3">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="fw-semibold">Paramètres Système</div>
                <small class="text-muted">Configuration générale</small>
            </div>
        </a>
    </div>
    <div class="col-md-4 mb-3">
        <a class="block block-rounded block-link-pop text-center" href="#">
            <div class="block-content block-content-full">
                <div class="item item-circle bg-warning text-white mx-auto mb-3">
                    <i class="fas fa-history"></i>
                </div>
                <div class="fw-semibold">Journaux</div>
                <small class="text-muted">Activités du système</small>
            </div>
        </a>
    </div>
</div>
