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
            <a href="{{ route('medicaments.edit', $medicament) }}" class="btn btn-sm btn-outline-primary">
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
                            <th class="text-muted small text-uppercase">Code Barre</th>
                            <td>
                                @if($medicament->code_barre)
                                    <span class="font-monospace fw-bold"><i class="fa fa-barcode me-1 text-muted"></i>{{ $medicament->code_barre }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Famille</th>
                            <td><span class="badge bg-info">{{ $medicament->famille->nom ?? '-' }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted small text-uppercase">Unité par défaut</th>
                            <td>{{ $medicament->uniteDefault->nom ?? ($medicament->unites->first()?->nom ?? '-') }}</td>
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
                    <div class="row text-center g-3 mb-4">
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
                    </div>

                    <h6 class="fw-bold border-bottom pb-2 mb-3 text-uppercase small text-muted"><i class="fa fa-layer-group me-1 text-primary"></i> Conditionnements & Prix</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Unité</th>
                                    <th style="width: 70px;" class="text-center">Facteur</th>
                                    <th class="text-end">P. Achat</th>
                                    <th class="text-end">P. Vente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medicament->unites as $unite)
                                    <tr class="{{ $unite->is_default ? 'table-primary fw-bold' : '' }}">
                                        <td>
                                            {{ $unite->nom }} ({{ $unite->symbole }})
                                            @if($unite->is_default)
                                                <span class="badge bg-primary ms-1 small">Défaut</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $unite->facteur }}</td>
                                        <td class="text-end">{{ number_format($unite->prix_achat, 2, ',', ' ') }} F</td>
                                        <td class="text-end text-primary">{{ number_format($unite->prix_vente, 2, ',', ' ') }} F</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
