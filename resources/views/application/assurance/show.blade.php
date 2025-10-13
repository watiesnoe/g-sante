@extends('layouts.app')

@section('titre')
    Détails de l'assurance : {{ $assurance->nom }}
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-id-card me-1"></i>
                            Détails de l'assurance : {{ $assurance->nom }}
                        </h3>
                        <div class="block-options">
                            <a href="{{ route('assurances.edit', $assurance) }}" class="btn btn-sm btn-info">
                                <i class="fa fa-pencil-alt me-1"></i> Modifier
                            </a>
                            <a href="{{ route('assurances.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                    <div class="block-content p-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Nom de l'assurance</label>
                                    <span class="fs-lg fw-bold text-dark">{{ $assurance->nom }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Taux de prise en charge</label>
                                    <span class="badge bg-success fs-base">{{ $assurance->taux }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Téléphone</label>
                                    <span class="fs-base">{{ $assurance->telephone ?? 'Non renseigné' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Adresse</label>
                                    <span class="fs-base">{{ $assurance->adresse ?? 'Non renseignée' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row border-top pt-4 mt-2">
                            <div class="col-md-12">
                                <p class="text-muted small">
                                    <i class="fa fa-clock me-1"></i>
                                    Créé le : {{ $assurance->created_at->format('d/m/Y H:i') }}
                                    @if($assurance->updated_at != $assurance->created_at)
                                        <br>
                                        <i class="fa fa-edit me-1"></i>
                                        Mis à jour le : {{ $assurance->updated_at->format('d/m/Y H:i') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
