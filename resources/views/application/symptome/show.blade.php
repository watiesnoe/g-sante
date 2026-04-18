@extends('layouts.app')

@section('titre', 'Détail Symptôme - ' . $symptome->nom)

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 fw-bold mb-0">
                <i class="fa fa-stethoscope me-2 text-warning"></i>{{ $symptome->nom }}
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('symptomes.index') }}">Symptômes</a></li>
                    <li class="breadcrumb-item active">{{ $symptome->nom }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('symptomes.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="row">
        {{-- Infos du symptôme --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fa fa-info-circle text-primary me-2"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="160" class="text-muted small text-uppercase">Nom</th>
                            <td><strong>{{ $symptome->nom }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Description</th>
                            <td>{{ $symptome->description ?: 'Aucune description disponible' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Enregistré le</th>
                            <td>{{ $symptome->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Maladies liées --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="fa fa-virus text-danger me-2"></i>Maladies associées
                        <span class="badge bg-danger ms-2">{{ $symptome->maladies->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($symptome->maladies as $maladie)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-soft-danger text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                <i class="fa fa-disease me-1"></i>{{ $maladie->nom }}
                            </span>
                            @if($maladie->protocole)
                                <a href="{{ route('infectiologie.protocoles.show', $maladie->protocole->id) }}"
                                   class="btn btn-xs btn-outline-primary btn-sm" title="Voir protocole">
                                    <i class="fas fa-book-medical"></i>
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Aucune maladie associée à ce symptôme.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
