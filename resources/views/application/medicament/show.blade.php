@extends('layouts.app')

@section('titre', 'Détail Médicament - ' . $medicament->nom)

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 fw-bold mb-0">
                <i class="fa fa-pills me-2 text-primary"></i>{{ $medicament->nom }}
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('medicaments.index') }}">Médicaments</a></li>
                    <li class="breadcrumb-item active">{{ $medicament->nom }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('medicaments.edit', $medicament->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-pencil-alt me-1"></i> Modifier
            </a>
            <a href="{{ route('medicaments.index') }}" class="btn btn-sm btn-secondary">
                <i class="fa fa-arrow-left me-1"></i> Retour
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Infos principales --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fa fa-info-circle text-primary me-2"></i>Informations générales</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="160" class="text-muted small text-uppercase">Nom</th>
                            <td><strong>{{ $medicament->nom }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Description</th>
                            <td>{{ $medicament->description ?: 'Aucune description' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Famille</th>
                            <td><span class="badge bg-info">{{ $medicament->famille->nom ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Unité</th>
                            <td>{{ $medicament->unite->nom ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Protocoles liés --}}
            @if($medicament->protocoles->count())
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-book-medical text-success me-2"></i>Protocoles de Traitement
                        <span class="badge bg-success ms-2">{{ $medicament->protocoles->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Protocole</th>
                                <th>Maladie</th>
                                <th>Type</th>
                                <th>Posologie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicament->protocoles as $proto)
                            <tr>
                                <td>
                                    <a href="{{ route('infectiologie.protocoles.show', $proto->id) }}">
                                        {{ $proto->titre }}
                                    </a>
                                </td>
                                <td><span class="badge bg-soft-info text-info">{{ $proto->maladie->nom ?? '-' }}</span></td>
                                <td>
                                    @php
                                        $typeColors = ['principal'=>'success','alternatif'=>'warning','adjuvant'=>'info','relais'=>'secondary','assos'=>'primary'];
                                        $color = $typeColors[$proto->pivot->type] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($proto->pivot->type) }}</span>
                                </td>
                                <td class="small">{{ $proto->pivot->posologie ?: '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Stock & Prix --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="fa fa-boxes text-warning me-2"></i>Stock & Tarification</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 {{ $medicament->stock <= $medicament->stock_min ? 'bg-danger bg-opacity-10 border border-danger' : 'bg-success bg-opacity-10 border border-success' }}">
                                <small class="text-muted d-block mb-1">Stock actuel</small>
                                <strong class="fs-4 {{ $medicament->stock <= $medicament->stock_min ? 'text-danger' : 'text-success' }}">
                                    {{ $medicament->stock }}
                                </strong>
                                @if($medicament->stock <= $medicament->stock_min)
                                    <span class="badge bg-danger d-block mt-1 small">Stock critique</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1">Stock minimum</small>
                                <strong class="fs-4">{{ $medicament->stock_min }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted d-block mb-1">Prix d'achat</small>
                                <strong class="fs-5">{{ number_format($medicament->prix_achat, 0, ',', ' ') }} F</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary">
                                <small class="text-muted d-block mb-1">Prix de vente</small>
                                <strong class="fs-5 text-primary">{{ number_format($medicament->prix_vente, 0, ',', ' ') }} F</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
