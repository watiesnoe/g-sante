@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Modèles de documents
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')
            <div class="col-xl-9 col-lg-8">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                <div class="alert alert-info small">
                    Champs utilisables : @foreach(\App\Models\ModeleDocument::CHAMPS as $champ)<code>{{ $champ }}</code> @endforeach
                </div>
                @foreach($modeles->push(new \App\Models\ModeleDocument(['type' => 'certificat', 'actif' => true])) as $modele)
                    <form method="POST" action="{{ route('modeles.enregistrer') }}" class="block block-rounded">
                        @csrf
                        <input type="hidden" name="id" value="{{ $modele->id }}">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">{{ $modele->exists ? $modele->titre : 'Nouveau modèle' }}</h3>
                        </div>
                        <div class="block-content">
                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <select name="type" class="form-select no-select2">
                                        @foreach(\App\Models\ModeleDocument::TYPES as $code => $libelle)
                                            <option value="{{ $code }}" {{ $modele->type === $code ? 'selected' : '' }}>{{ $libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6"><input type="text" name="titre" class="form-control" value="{{ $modele->titre }}" placeholder="Titre" required></div>
                                <div class="col-md-2 form-check form-switch pt-2 ps-5">
                                    <input type="hidden" name="actif" value="0">
                                    <input class="form-check-input" type="checkbox" name="actif" value="1" {{ $modele->actif ? 'checked' : '' }}>
                                    <label class="form-check-label">Actif</label>
                                </div>
                            </div>
                            <textarea name="contenu" class="form-control mb-2" rows="8" required>{{ $modele->contenu }}</textarea>
                            <div class="text-end mb-3"><button class="btn btn-sm btn-primary">Enregistrer</button></div>
                        </div>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
@endsection
