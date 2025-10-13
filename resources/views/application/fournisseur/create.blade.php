@extends('layouts.app')
@section('titre', isset($fournisseur) ? 'Modifier Fournisseur' : 'Ajouter Fournisseur')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    {{ isset($fournisseur) ? '✏️ Modifier Fournisseur' : '➕ Ajouter Fournisseur' }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($fournisseur) ? route('fournisseurs.update', $fournisseur->id) : route('fournisseurs.store') }}" method="POST">
                    @csrf
                    @if(isset($fournisseur)) @method('PUT') @endif

                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" id="nom" name="nom" class="form-control" value="{{ $fournisseur->nom ?? old('nom') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact <span class="text-danger">*</span></label>
                        <input type="text" id="contact" name="contact" class="form-control" value="{{ $fournisseur->contact ?? old('contact') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <input type="text" id="adresse" name="adresse" class="form-control" value="{{ $fournisseur->adresse ?? old('adresse') }}">
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('fournisseurs.index') }}" class="btn btn-secondary">↩️ Retour</a>
                        <button type="submit" class="btn btn-success">
                            {{ isset($fournisseur) ? '💾 Enregistrer les modifications' : '✅ Enregistrer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
