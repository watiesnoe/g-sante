@extends('layouts.app')

@section('titre')
    {{ isset($assurance) ? 'Modifier l\'assurance' : 'Nouvelle Assurance' }}
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
                            {{ isset($assurance) ? 'Modifier : ' . $assurance->nom : 'Ajouter une assurance' }}
                        </h3>
                        <div class="block-options">
                            <a href="{{ route('assurances.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                    <div class="block-content p-4">
                        <form action="{{ isset($assurance) ? route('assurances.update', $assurance) : route('assurances.store') }}" method="POST">
                            @csrf
                            @if(isset($assurance))
                                @method('PUT')
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nom de l'assurance <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" 
                                           value="{{ old('nom', $assurance->nom ?? '') }}" placeholder="Ex: CNAM, Mutuelle..." required>
                                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Taux de prise en charge (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="taux" class="form-control @error('taux') is-invalid @enderror" 
                                           value="{{ old('taux', $assurance->taux ?? '80') }}" min="0" max="100" required>
                                    @error('taux') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Téléphone</label>
                                    <input type="tel" name="telephone" class="form-control phone-input @error('telephone') is-invalid @enderror" 
                                           value="{{ old('telephone', $assurance->telephone ?? '') }}">
                                    @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Adresse</label>
                                    <input type="text" name="adresse" class="form-control @error('adresse') is-invalid @enderror" 
                                           value="{{ old('adresse', $assurance->adresse ?? '') }}" placeholder="Adresse complète">
                                    @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button type="reset" class="btn btn-alt-secondary me-1">Réinitialiser</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i>
                                    {{ isset($assurance) ? 'Mettre à jour' : 'Enregistrer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
