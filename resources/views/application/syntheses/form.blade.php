@extends('layouts.app')

@section('title_page', $fiche->exists ? 'Modifier la fiche' : 'Nouvelle fiche de synthèse')

@section('content')
    @php
        $meds = old('medicaments', $fiche->exists ? $fiche->medicaments->map(fn($m) => ['id' => $m->id, 'usage' => $m->pivot->usage])->all() : []);
        $meds = array_values($meds) ?: [['id' => '', 'usage' => '']];
        $refs = old('references', $fiche->exists ? $fiche->references->map->only(['ouvrage', 'localisation', 'note'])->all() : []);
        $refs = array_values($refs) ?: [['ouvrage' => '', 'localisation' => '', 'note' => '']];
    @endphp
    <div class="content">
        <a href="{{ $fiche->exists ? route('syntheses.show', $fiche) : route('syntheses.index') }}" class="small"><i class="fa fa-arrow-left me-1"></i>Retour</a>
        <h2 class="h3 fw-bold mt-2 mb-3">{{ $fiche->exists ? 'Modifier : ' . $fiche->titre : 'Nouvelle fiche de synthèse' }}</h2>
        @if($fiche->statut === 'validee')<div class="alert alert-info small">Cette fiche est validée. Toute modification de son contenu la renverra en validation.</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="alert alert-light border small"><i class="fa fa-pen-nib me-1"></i>Rédigez avec vos propres mots. Pour un ouvrage (Berrebi, Dorosz…), indiquez la page ou le chapitre dans « Références » plutôt que d'en recopier le texte. Les posologies proviennent des fiches médicaments reliées.</div>

        <form method="POST" action="{{ $fiche->exists ? route('syntheses.update', $fiche) : route('syntheses.store') }}">
            @csrf
            @if($fiche->exists) @method('PUT') @endif

            <div class="block block-rounded"><div class="block-content">
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><label class="form-label">Titre <span class="text-danger">*</span></label><input name="titre" class="form-control" required value="{{ old('titre', $fiche->titre) }}" placeholder="Ex. Douleur thoracique aiguë"></div>
                    <div class="col-md-3"><label class="form-label">Spécialité</label><input name="specialite" class="form-control" value="{{ old('specialite', $fiche->specialite) }}" placeholder="Ex. Cardiologie"></div>
                    <div class="col-md-3"><label class="form-label">Pathologie liée</label>
                        <select name="maladie_id" class="form-select js-select2" style="width:100%"><option value="">—</option>@foreach($maladies as $m)<option value="{{ $m->id }}" @selected(old('maladie_id', $fiche->maladie_id) == $m->id)>{{ $m->nom }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Motif de consultation / symptôme</label><input name="motif" class="form-control" value="{{ old('motif', $fiche->motif) }}" placeholder="Ex. Douleur précordiale ou point de côté d'apparition récente"></div>
                </div>
            </div></div>

            <div class="block block-rounded"><div class="block-content">
                @foreach(\App\Models\FicheSynthese::SECTIONS as $champ => [$libelle, $aide])
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ $libelle }}</label> <span class="small text-muted">— {{ $aide }}</span>
                        <textarea name="{{ $champ }}" rows="4" class="form-control">{{ old($champ, $fiche->$champ) }}</textarea>
                    </div>
                @endforeach
            </div></div>

            <div class="block block-rounded">
                <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-pills me-1"></i> Médicaments (guide des médicaments)</h3></div>
                <div class="block-content">
                    <div id="lignesMedicaments">
                        @foreach($meds as $i => $m)
                            <div class="row g-2 mb-2 ligne-med">
                                <div class="col-md-6"><select name="medicaments[{{ $i }}][id]" class="form-select js-select2" style="width:100%"><option value="">Choisir une fiche…</option>
                                    @foreach($catalogue as $c)<option value="{{ $c->id }}" @selected((string) ($m['id'] ?? '') === (string) $c->id)>{{ $c->nom }} — {{ \App\Models\FicheMedicament::VOIES[$c->voie] ?? $c->voie }}{{ $c->deconseille ? ' (déconseillé)' : '' }}</option>@endforeach</select></div>
                                <div class="col-md-6"><input name="medicaments[{{ $i }}][usage]" class="form-control" value="{{ $m['usage'] ?? '' }}" placeholder="Place dans la stratégie (ex. 1re intention, si allergie…)"></div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-alt-secondary mb-3" id="ajouterMed">+ Médicament</button>
                </div>
            </div>

            <div class="block block-rounded">
                <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-book me-1"></i> Références</h3></div>
                <div class="block-content">
                    <datalist id="ouvrages">@foreach(\App\Models\FicheSynthese::OUVRAGES as $o)<option value="{{ $o }}">@endforeach</datalist>
                    <div id="lignesReferences">
                        @foreach($refs as $i => $r)
                            <div class="row g-2 mb-2">
                                <div class="col-md-5"><input name="references[{{ $i }}][ouvrage]" list="ouvrages" class="form-control" value="{{ $r['ouvrage'] ?? '' }}" placeholder="Ouvrage"></div>
                                <div class="col-md-3"><input name="references[{{ $i }}][localisation]" class="form-control" value="{{ $r['localisation'] ?? '' }}" placeholder="Page ou chapitre"></div>
                                <div class="col-md-4"><input name="references[{{ $i }}][note]" class="form-control" value="{{ $r['note'] ?? '' }}" placeholder="À quoi sert cette référence"></div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-alt-secondary mb-3" id="ajouterRef">+ Référence</button>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <button name="action" value="brouillon" class="btn btn-alt-primary"><i class="fa fa-save me-1"></i> Enregistrer le brouillon</button>
                <button name="action" value="soumettre" class="btn btn-primary"><i class="fa fa-paper-plane me-1"></i> Soumettre à validation</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            function dupliquer(conteneur, prefixe) {
                const lignes = $(conteneur).children();
                const n = lignes.length;
                const ligne = lignes.first().clone();
                ligne.find('.select2').remove();
                ligne.find('select, input').each(function () {
                    this.name = this.name.replace(/\[\d+\]/, '[' + n + ']');
                    $(this).val('').removeClass('select2-hidden-accessible').removeAttr('data-select2-id');
                });
                $(conteneur).append(ligne);
                ligne.find('select.js-select2').select2({ width: '100%' });
            }
            $('#ajouterMed').on('click', () => dupliquer('#lignesMedicaments'));
            $('#ajouterRef').on('click', () => dupliquer('#lignesReferences'));
        });
    </script>
@endsection
