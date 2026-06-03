@extends('layouts.app')

@section('titre', 'Atlas des Pathologies Infectieuses')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-atlas me-2"></i>Atlas des Pathologies</h2>
            <p class="text-muted small mb-0">Base de connaissances des maladies suivies dans l'établissement.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddPathologie">
                <i class="fas fa-plus me-1"></i> Nouvelle Pathologie
            </button>
        </div>
    </div>

    <div class="row g-4">
        @foreach($pathologies as $p)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 pathology-card rounded-3 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="icon-shape bg-soft-primary text-primary">
                            <i class="fas fa-microbe"></i>
                        </div>
                        <span class="badge rounded-pill bg-light text-dark border">{{ $p->consultations_count }} cas enregistrés</span>
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-2">{{ $p->nom }}</h5>
                    <p class="small text-muted mb-3 line-clamp-2">{{ $p->description ?: 'Aucune description disponible pour cette pathologie.' }}</p>
                    
                    <div class="mb-3">
                        <h6 class="text-uppercase small fw-bold text-secondary mb-2" style="font-size: 0.65rem;">Symptômes Clés</h6>
                        <div class="d-flex flex-wrap gap-1">
                            @forelse($p->symptomes->take(3) as $s)
                                <span class="badge bg-soft-secondary text-secondary small">{{ $s->nom }}</span>
                            @empty
                                <span class="text-muted small italic">Aucun signe lié</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-auto">
                        @if($p->protocole)
                            <a href="{{ route('infectiologie.protocoles.show', $p->protocole->uuid) }}" class="btn btn-sm btn-outline-primary w-100 rounded-pill">
                                <i class="fas fa-book-medical me-1"></i> Voir le Protocole Expert
                            </a>
                        @else
                            <a href="{{ route('infectiologie.protocoles') }}" class="btn btn-sm btn-outline-warning w-100 rounded-pill">
                                <i class="fas fa-plus-circle me-1"></i> Créer un Protocole
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal Ajout Pathologie (Simplified) -->
<div class="modal fade" id="modalAddPathologie" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Pathologie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Formulaire simplifié -->
                <div class="mb-3">
                    <label class="form-label">Nom de la maladie</label>
                    <input type="text" class="form-control" placeholder="Ex: Zika, Ebola...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary disabled">Enregistrer (Module admin)</button>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(6, 101, 208, 0.1); }
    .bg-soft-secondary { background-color: #f1f4f8; }
    .icon-shape {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .pathology-card { transition: all 0.3s; border: 1px solid transparent !important; }
    .pathology-card:hover { transform: translateY(-5px); border-color: rgba(6, 101, 208, 0.2) !important; box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
