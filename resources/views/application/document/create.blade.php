@extends('layouts.app')

@section('title_page', 'Document médical')

@section('content')
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Document pour {{ mb_strtoupper($patient->nom) }} {{ $patient->prenom }} <small class="text-muted">({{ $patient->numero_dossier }})</small></h3>
                    </div>
                    <div class="block-content">
                        <form method="GET" action="{{ route('documents.create') }}" class="row g-2 mb-3">
                            <input type="hidden" name="patient" value="{{ $patient->uuid }}">
                            @if($consultation)<input type="hidden" name="consultation" value="{{ $consultation->uuid }}">@endif
                            <div class="col-md-9">
                                <select name="modele" class="form-select no-select2" onchange="this.form.submit()">
                                    @foreach($modeles as $m)
                                        <option value="{{ $m->id }}" {{ $modele && $modele->id === $m->id ? 'selected' : '' }}>{{ \App\Models\ModeleDocument::TYPES[$m->type] }} — {{ $m->titre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 text-muted small align-self-center">Changer de modèle recharge le texte.</div>
                        </form>

                        <form method="POST" action="{{ route('documents.store') }}">
                            @csrf
                            <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                            <input type="hidden" name="consultation_id" value="{{ $consultation->id ?? '' }}">
                            <input type="hidden" name="modele_document_id" value="{{ $modele->id ?? '' }}">
                            <input type="hidden" name="type" value="{{ $modele->type ?? 'certificat' }}">
                            <div class="row g-3 mb-3">
                                <div class="col-md-7">
                                    <label class="form-label">Titre</label>
                                    <input type="text" name="titre" class="form-control" value="{{ $modele ? \App\Models\ModeleDocument::TYPES[$modele->type] : 'Certificat médical' }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Destinataire</label>
                                    <input type="text" name="destinataire" class="form-control" placeholder="Ex. Service de chirurgie, Hôpital de Ségou">
                                </div>
                            </div>
                            <label class="form-label">Texte (modifiable avant impression)</label>
                            <textarea name="contenu" class="form-control mb-3" rows="16" required>{{ $contenu }}</textarea>
                            <div class="text-end mb-4">
                                <button class="btn btn-primary"><i class="fa fa-print me-1"></i> Enregistrer et imprimer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
