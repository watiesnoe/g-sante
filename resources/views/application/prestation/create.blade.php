@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - {{ isset($prestation) ? 'Modifier' : 'Ajouter' }} une Prestation
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">📰 {{ isset($prestation) ? 'Modifier la prestation' : 'Formulaire d\'ajout de prestation' }}</h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <a href="{{ route('prestations.index') }}" class="btn btn-success btn-sm rounded-pill shadow-sm">
                                <i class="fa fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                    <div class="block-content">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ isset($prestation) ? route('prestations.update', $prestation->uuid) : route('prestations.store') }}" id="prestationForm" class="mb-2" method="POST">
                            @csrf
                            @if(isset($prestation))
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="service_medical_id" class="form-label">Service Médical <span class="text-danger">*</span></label>
                                    <select class="form-select @error('service_medical_id') is-invalid @enderror" id="service_medical_id" name="service_medical_id" required>
                                        <option value="">-- Sélectionner un service --</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" {{ (old('service_medical_id') ?? ($prestation->service_medical_id ?? '')) == $service->id ? 'selected' : '' }}>
                                                {{ $service->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_medical_id') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom de la prestation <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                           id="nom" name="nom" value="{{ old('nom') ?? ($prestation->nom ?? '') }}" required placeholder="Ex: Consultation Générale">
                                    @error('nom') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="prix" class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control price-input @error('prix') is-invalid @enderror"
                                           id="prix" name="prix" value="{{ old('prix') ?? ($prestation->prix ?? '') }}" required placeholder="Ex: 15 000">
                                    @error('prix') <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3 d-flex align-items-center">
                                    <div class="form-check form-switch pt-4">
                                        <input class="form-check-input" type="checkbox" id="quantifiable" name="quantifiable" value="1"
                                               {{ (old('quantifiable') ?? ($prestation->quantifiable ?? false)) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="quantifiable">
                                            Prestation quantifiable (ex: consommables, médicaments...)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description (optionnelle)</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="3">{{ old('description') ?? ($prestation->description ?? '') }}</textarea>
                                @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="text-end border-top pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> {{ isset($prestation) ? 'Mettre à jour' : 'Enregistrer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        // Le formulaire est géré de façon classique pour simplifier la validation
        // Vous pouvez utiliser AJAX ici si vous préférez.
    </script>
@endsection
