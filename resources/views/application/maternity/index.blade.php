@extends('layouts.app')

@section('content')
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">Suivi de Maternité</h2>
            <p class="text-muted">Gestion des grossesses et consultations prénatales (CPN)</p>
        </div>
        <a href="{{ route('maternity.create') }}" class="btn btn-primary shadow-sm">
            <i class="fa fa-plus me-1"></i> Nouveau Suivi
        </a>
    </div>

    <div class="row">
        @foreach($grossesses as $g)
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-soft-primary border-0 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary">CPN En cours</span>
                    <small class="text-muted">DPA: {{ \Carbon\Carbon::parse($g->dpa)->format('d/m/Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm bg-primary-light text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="fa fa-user-female"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $g->patient->nom }} {{ $g->patient->prenom }}</h5>
                            <small class="text-muted">DDR: {{ \Carbon\Carbon::parse($g->ddr)->format('d/m/Y') }}</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Progression</small>
                            <small class="fw-bold">{{ round(\Carbon\Carbon::parse($g->ddr)->diffInWeeks(now())) }} Semaines</small>
                        </div>
                        @php
                            $weeks = \Carbon\Carbon::parse($g->ddr)->diffInWeeks(now());
                            $progress = min(100, ($weeks / 40) * 100);
                        @endphp
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="row text-center border-top pt-3">
                        <div class="col-6 border-end">
                            <small class="text-muted d-block">Gestité</small>
                            <span class="fw-bold">G{{ $g->gestite ?? '?' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Parité</small>
                            <span class="fw-bold">P{{ $g->parite ?? '?' }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="{{ route('maternity.show', $g) }}" class="btn btn-outline-primary w-100">
                        Voir le dossier <i class="fa fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        
        @if($grossesses->isEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm py-5 text-center">
                <div class="card-body">
                    <i class="fa fa-baby fa-4x text-gray-light mb-3"></i>
                    <h4>Aucune grossesse en suivi actuellement</h4>
                    <p class="text-muted">Commencez par initialiser un suivi pour une patiente.</p>
                    <a href="{{ route('maternity.create') }}" class="btn btn-primary px-4">
                        <i class="fa fa-plus me-2"></i> Initialiser un suivi
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
