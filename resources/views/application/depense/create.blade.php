@extends('layouts.app')

@section('title_page', 'Nouvelle dépense')

@section('content')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route('depenses.store') }}" enctype="multipart/form-data" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Nouvelle dépense</h3></div>
                    <div class="block-content">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Catégorie</label>
                                <select name="categorie_depense_id" class="form-select">
                                    <option value="">— Nouvelle catégorie —</option>
                                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ old('categorie_depense_id') == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ou nouvelle catégorie</label>
                                <input type="text" name="nouvelle_categorie" class="form-control" value="{{ old('nouvelle_categorie') }}" placeholder="Ex. Électricité, Carburant…">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Objet <span class="text-danger">*</span></label>
                                <input type="text" name="objet" class="form-control" value="{{ old('objet') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                                <input type="number" name="montant" class="form-control" value="{{ old('montant') }}" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bénéficiaire</label>
                                <input type="text" name="beneficiaire" class="form-control" value="{{ old('beneficiaire') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Service concerné</label>
                                <select name="service_medical_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($services as $s)<option value="{{ $s->id }}" {{ old('service_medical_id') == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>@endforeach
                                </select>
                            </div>
                            @if($lignes->isNotEmpty())
                            <div class="col-md-12">
                                <label class="form-label">Ligne budgétaire</label>
                                <select name="ligne_budgetaire_id" class="form-select">
                                    <option value="">—</option>
                                    @foreach($lignes as $l)<option value="{{ $l->id }}" {{ old('ligne_budgetaire_id') == $l->id ? 'selected' : '' }}>{{ $l->code }} — {{ $l->libelle }} (disponible {{ number_format($l->montant_prevu - $l->engage(), 0, ',', ' ') }} F)</option>@endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date_depense" class="form-control" value="{{ old('date_depense', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pièce justificative (PDF ou photo, 5 Mo max)</label>
                                <input type="file" name="piece_justificative" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
                        </div>
                    </div>
                    <div class="block-content block-content-full bg-body-light text-end">
                        <a href="{{ route('depenses.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-outline-primary">Enregistrer en brouillon</button>
                        <button type="submit" name="soumettre" value="1" class="btn btn-primary">Enregistrer et soumettre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
