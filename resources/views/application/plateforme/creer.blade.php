@extends('layouts.admin')

@section('title_page', 'Nouvel espace')

@section('content')
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-plus-circle me-2"></i>Nouvel espace d'établissement</h2>
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('plateforme.store') }}">
            @csrf
            <div class="block block-rounded">
                <div class="block-header block-header-default"><h3 class="block-title">1. L'établissement</h3></div>
                <div class="block-content">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><label class="form-label">Nom <span class="text-danger">*</span></label><input name="nom" class="form-control" value="{{ old('nom') }}" required></div>
                        <div class="col-md-3"><label class="form-label">Code (numérotation)</label><input name="code" class="form-control" value="{{ old('code') }}" placeholder="Ex. CSREF-SEG"></div>
                        <div class="col-md-3"><label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" id="typeEspace" class="form-select no-select2" required>@foreach($types as $cle => $libelle)<option value="{{ $cle }}" @selected(old('type') === $cle)>{{ $libelle }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Région</label><input name="region" class="form-control" value="{{ old('region') }}"></div>
                        <div class="col-md-3"><label class="form-label">District</label><input name="district" class="form-control" value="{{ old('district') }}"></div>
                        <div class="col-md-6"><label class="form-label">Adresse</label><input name="adresse" class="form-control" value="{{ old('adresse') }}"></div>
                        <div class="col-md-4"><label class="form-label">Téléphone</label><input name="telephone" class="form-control" value="{{ old('telephone') }}"></div>
                        <div class="col-md-4"><label class="form-label">E-mail</label><input name="email" type="email" class="form-control" value="{{ old('email') }}"></div>
                        <div class="col-md-4"><label class="form-label">Reprendre le catalogue de</label>
                            <select name="modele_id" class="form-select no-select2"><option value="">— Aucun (catalogue vide) —</option>@foreach($modeles as $m)<option value="{{ $m->id }}" @selected(old('modele_id') == $m->id)>{{ $m->nom }}</option>@endforeach</select>
                            <div class="form-text">Services, prestations, tarifs et médicaments (sans stock).</div></div>
                    </div>
                </div>
            </div>

            <div class="block block-rounded">
                <div class="block-header block-header-default"><h3 class="block-title">2. Abonnement et modules</h3></div>
                <div class="block-content">
                    <div class="row g-3 mb-3">
                        <div class="col-md-5"><label class="form-label">Formule</label>
                            <select name="formule_id" id="formule" class="form-select no-select2">
                                <option value="">— Sans abonnement (modules choisis ci-dessous) —</option>
                                @foreach($formules as $fo)<option value="{{ $fo->id }}" data-modules='@json($fo->modules)' @selected(old('formule_id') == $fo->id)>{{ $fo->nom }}{{ $fo->prix !== null ? ' — ' . number_format((float) $fo->prix, 0, ',', ' ') . ' XOF / ' . $fo->duree_mois . ' mois' : '' }}</option>@endforeach
                            </select>
                            @if($formules->isEmpty())<div class="form-text"><a href="{{ route('plateforme.formules') }}">Créez d'abord vos formules</a> pour abonner l'espace.</div>@endif</div>
                        <div class="col-md-3"><label class="form-label">Début</label><input type="date" name="debut" class="form-control" value="{{ old('debut', today()->toDateString()) }}"></div>
                        <div class="col-md-3"><label class="form-label">Période d'essai (jours)</label><input type="number" name="essai_jours" min="1" max="90" class="form-control" value="{{ old('essai_jours') }}" placeholder="Aucune"></div>
                    </div>
                    <p class="small text-muted" id="aideModules">Pré-cochés selon le type. Le propriétaire ne pourra pas les modifier ; vous pourrez les changer à tout moment depuis la plateforme.</p>
                    <div class="row g-1 mb-3">
                        @foreach($modules as $cle => $module)
                            <div class="col-md-4 col-lg-3"><div class="form-check form-switch">
                                <input class="form-check-input module" type="checkbox" name="modules[]" value="{{ $cle }}" id="mod-{{ $cle }}">
                                <label class="form-check-label small" for="mod-{{ $cle }}">{{ $module['label'] }}</label>
                            </div></div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="block block-rounded">
                <div class="block-header block-header-default"><h3 class="block-title">3. Le propriétaire</h3></div>
                <div class="block-content">
                    <p class="small text-muted">Responsable de l'établissement. Il recevra un mot de passe provisoire, à changer à sa première connexion, puis créera les comptes de son personnel.</p>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><label class="form-label">Prénom <span class="text-danger">*</span></label><input name="proprietaire[prenom]" class="form-control" value="{{ old('proprietaire.prenom') }}" required></div>
                        <div class="col-md-3"><label class="form-label">Nom <span class="text-danger">*</span></label><input name="proprietaire[nom]" class="form-control" value="{{ old('proprietaire.nom') }}" required></div>
                        <div class="col-md-3"><label class="form-label">E-mail (identifiant) <span class="text-danger">*</span></label><input name="proprietaire[email]" type="email" class="form-control" value="{{ old('proprietaire.email') }}" required></div>
                        <div class="col-md-3"><label class="form-label">Téléphone</label><input name="proprietaire[telephone]" class="form-control" value="{{ old('proprietaire.telephone') }}"></div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a href="{{ route('plateforme.index') }}" class="btn btn-secondary">Annuler</a>
                <button class="btn btn-primary"><i class="fa fa-check me-1"></i> Créer l'espace et le compte du propriétaire</button>
            </div>
        </form>
        </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            const profils = @json($profils);
            const anciens = @json(old('modules'));
            function cocher() {
                const actifs = anciens ?? profils[$('#typeEspace').val()] ?? [];
                $('.module').each(function () { this.checked = actifs.includes(this.value); });
            }
            $('#typeEspace').on('change', function () { if (!$('#formule').val()) { $('.module').each(function () { this.checked = (profils[$('#typeEspace').val()] ?? []).includes(this.value); }); } });
            // Avec une formule, les modules sont ceux de la formule
            $('#formule').on('change', function () {
                const choix = $(this).find(':selected').data('modules');
                $('.module').prop('disabled', !!choix);
                if (choix) { $('.module').each(function () { this.checked = choix.includes(this.value); }); }
                $('#aideModules').text(choix ? 'Modules de la formule choisie.' : 'Pré-cochés selon le type. Le propriétaire ne pourra pas les modifier ; vous pourrez les changer à tout moment depuis la plateforme.');
            });
            cocher();
            $('#formule').trigger('change');
        });
    </script>
@endsection
