@extends('layouts.app')

@section('titre', 'Détails du Protocole - ' . $protocole->maladie->nom)

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-file-medical-alt me-2"></i>Guide Thérapeutique</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('infectiologie.protocoles') }}">Infectiologie</a></li>
                    <li class="breadcrumb-item active">Protocole</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-print me-1"></i> Imprimer
            </button>
            <a href="{{ route('infectiologie.protocoles') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Header Card -->
            <div class="card border-0 shadow-sm mb-4 overflow-hidden rounded-3">
                <div class="card-header bg-primary py-3">
                    <h5 class="card-title text-white mb-0 text-uppercase letter-spacing-1">{{ $protocole->titre }}</h5>
                </div>
                <div class="card-body bg-white p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center text-primary border-end d-none d-md-block">
                            <i class="fas fa-virus fa-3x"></i>
                        </div>
                        <div class="col-md-10 ps-md-4">
                            <div class="badge bg-soft-primary px-3 py-2 text-primary mb-2">Pathologie infectieuse</div>
                            <h3 class="mb-1 text-dark">{{ $protocole->maladie->nom }}</h3>
                            <p class="text-muted mb-0 small">{{ $protocole->maladie->description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical & Diagnostic Section -->
            <div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                    <span class="icon-shape bg-soft-info text-info me-3 h5 mb-0">
                        <i class="fas fa-stethoscope"></i>
                    </span>
                    <h5 class="mb-0">Identification & Diagnostic</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-uppercase small fw-bold text-info mb-3"><i class="fas fa-exclamation-circle me-1"></i> Signes Cliniques</h6>
                            <p class="text-dark line-height-1.6">{{ $protocole->signes ?: 'Non renseigné' }}</p>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-uppercase small fw-bold text-info mb-3"><i class="fas fa-flask me-1"></i> Examens (Dx)</h6>
                            <p class="text-dark line-height-1.6">{{ $protocole->diagnostics ?: 'Non renseigné' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Section -->
            <div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden border-start border-4 border-success">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center text-success">
                    <span class="icon-shape bg-soft-success text-success me-3 h5 mb-0">
                        <i class="fas fa-pills"></i>
                    </span>
                    <h5 class="mb-0">Stratégie Thérapeutique (Traitement Principal)</h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <div class="mb-4">
                        <h4 class="h5 text-success fw-bold">{{ $protocole->traitement_principal }}</h4>
                    </div>
                    <div class="bg-light p-3 rounded-3 mb-4">
                        <h6 class="text-uppercase small fw-bold text-muted mb-2"><i class="fas fa-clock me-1"></i> Posologie & Administration</h6>
                        <p class="text-dark mb-0 fs-5" style="white-space: pre-wrap;">{{ $protocole->posologie_principale }}</p>
                    </div>

                    @if($protocole->traitement_alternatif)
                    <div class="border-top pt-4 mt-2">
                        <h6 class="text-uppercase small fw-bold text-warning mb-3"><i class="fas fa-exchange-alt me-1"></i> Alternative / Secours</h6>
                        <div class="d-flex align-items-start">
                            <div class="me-3 text-warning"><i class="fas fa-arrow-right mt-1"></i></div>
                            <div>
                                <strong class="text-dark d-block mb-1">{{ $protocole->traitement_alternatif }}</strong>
                                <p class="text-muted small mb-0">{{ $protocole->posologie_alternative }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Etiologies Card -->
            <div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden bg-primary text-white">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-microscope fa-3x mb-3 opacity-50"></i>
                    <h5 class="mb-3">Étiologies & Germes</h5>
                    <div class="mb-4">
                        <div class="badge bg-white text-primary mb-2 shadow-sm">Nourrisson (0-3 mois)</div>
                        <p class="small opacity-90">{{ $protocole->germes_nourrisson ?: 'Consulter les guides standards' }}</p>
                    </div>
                    <div class="mb-0">
                        <div class="badge bg-white text-primary mb-2 shadow-sm">Adulte / Enfant (>3 mois)</div>
                        <p class="small opacity-90">{{ $protocole->germes_adulte ?: 'Consulter les guides standards' }}</p>
                    </div>
                </div>
            </div>

            <!-- Remarks Card -->
            <div class="card border-0 shadow-sm mb-4 rounded-3 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                    <span class="icon-shape bg-soft-warning text-warning me-3 h5 mb-0">
                        <i class="fas fa-info-circle"></i>
                    </span>
                    <h5 class="mb-0">Observations</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small italic mb-0">{{ $protocole->remarques ?: 'Aucune remarque particulière pour ce protocole.' }}</p>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm rounded-3">
                <h6 class="alert-heading small fw-bold mb-2">Avertissement Clinique</h6>
                <p class="mb-0 small">Ce protocole est indicatif. La décision finale appartient au médecin traitant en fonction du terrain clinique du patient.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(6, 101, 208, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .icon-shape {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    .line-height-1.6 { line-height: 1.6; }
    .letter-spacing-1 { letter-spacing: 1px; }
    @media print {
        .btn, .breadcrumb, nav { display: none !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    }
</style>
@endsection
