@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Nouvel établissement
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('etablissements.store') }}" method="POST" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Nouvel établissement</h3>
                    </div>
                    <div class="block-content">
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code (préfixe des numéros)</label>
                                <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Ex. CSREF-SEG">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    @foreach($types as $cle => $libelle)
                                        <option value="{{ $cle }}" {{ old('type') === $cle ? 'selected' : '' }}>{{ $libelle }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Le type charge les modules et les règles de départ ; ils restent modifiables.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reprendre le catalogue de</label>
                                <select name="modele_id" class="form-select">
                                    <option value="">— Aucun (catalogue vide) —</option>
                                    @foreach($modeles as $m)
                                        <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Services, prestations et tarifs, médicaments (sans stock).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Région</label>
                                <input type="text" name="region" class="form-control" value="{{ old('region') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District sanitaire</label>
                                <input type="text" name="district" class="form-control" value="{{ old('district') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full block-content-sm bg-body-light text-end">
                        <a href="{{ route('etablissements.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Créer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
