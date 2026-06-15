@extends('layouts.app')

@section('titre', isset($medicament) ? 'Modifier Médicament' : 'Ajouter Médicament')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    {{ isset($medicament) ? '✏️ Modifier Médicament' : '➕ Ajouter Médicament' }}
                </h5>
                <a href="{{ route('medicaments.index') }}" class="btn btn-light btn-sm">↩️ Retour à la liste</a>
            </div>
            <div class="card-body">
                <form action="{{ isset($medicament) ? route('medicaments.update', $medicament->id) : route('medicaments.store') }}" method="POST">
                    @csrf
                    @if(isset($medicament)) @method('PUT') @endif

                    <!-- Nom -->
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" value="{{ $medicament->nom ?? old('nom') }}" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ $medicament->description ?? old('description') }}</textarea>
                    </div>

                    <!-- Stock & Prix -->
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="{{ $medicament->stock ?? 0 }}" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock Min</label>
                            <input type="number" name="stock_min" class="form-control" value="{{ $medicament->stock_min ?? 0 }}" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix Achat</label>
                            <input type="number" name="prix_achat" class="form-control" value="{{ $medicament->prix_achat ?? 0 }}" step="0.01">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix Vente</label>
                            <input type="number" name="prix_vente" class="form-control" value="{{ $medicament->prix_vente ?? 0 }}" step="0.01">
                        </div>
                    </div>

                    <!-- Unité de Vente -->
                    <div class="mb-3">
                        <label class="form-label">Unité de Vente <span class="text-danger">*</span></label>
                        <select name="unite_id" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($unites as $unite)
                                <option value="{{ $unite->id }}" {{ (isset($medicament) && $medicament->unite_id == $unite->id) ? 'selected' : '' }}>
                                    {{ $unite->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Famille Médicament -->
                    <div class="mb-3">
                        <label class="form-label">Famille Médicament <span class="text-danger">*</span></label>
                        <select name="famille_id" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($familles as $famille)
                                <option value="{{ $famille->id }}" {{ (isset($medicament) && $medicament->famille_id == $famille->id) ? 'selected' : '' }}>
                                    {{ $famille->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between p-3">
                        <a href="{{ route('medicaments.index') }}" class="btn btn-secondary">↩️ Annuler</a>
                        <button type="submit" class="btn btn-success">
                            {{ isset($medicament) ? '💾 Mettre à jour' : '✅ Enregistrer' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
