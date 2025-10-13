@extends('layouts.app')

@section('title_page', 'Accouchement')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-baby me-2"></i>Enregistrer un accouchement</h2>
        @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('accouchements.store') }}" class="block block-rounded">
            @csrf
            <div class="block-content">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mère <span class="text-danger">*</span></label>
                        <select id="mere" name="patient_id" class="form-select" style="width:100%" required>
                            @if($mere)<option value="{{ $mere->id }}" selected>{{ mb_strtoupper($mere->nom) }} {{ $mere->prenom }} — {{ $mere->numero_dossier }}</option>@endif
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label">Date et heure <span class="text-danger">*</span></label><input type="datetime-local" name="date_heure" class="form-control" value="{{ old('date_heure', now()->format('Y-m-d\TH:i')) }}" required></div>
                    <div class="col-md-3"><label class="form-label">Terme (SA)</label><input type="number" name="terme_sa" class="form-control" min="20" max="45" value="{{ old('terme_sa') }}"></div>
                    <div class="col-md-3"><label class="form-label">Mode <span class="text-danger">*</span></label>
                        <select name="mode" class="form-select no-select2">@foreach(\App\Models\Accouchement::MODES as $c => $l)<option value="{{ $c }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Présentation</label><input type="text" name="presentation" class="form-control" placeholder="Céphalique, siège…"></div>
                    <div class="col-md-3"><label class="form-label">État de la mère</label>
                        <select name="etat_mere" class="form-select no-select2">@foreach(\App\Models\Accouchement::ETATS_MERE as $c => $l)<option value="{{ $c }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Pertes sanguines (ml)</label><input type="number" name="perte_sang_ml" class="form-control" min="0"></div>
                    <div class="col-md-6"><label class="form-label">Complications</label><textarea name="complications" class="form-control" rows="2"></textarea></div>
                    <div class="col-md-6"><label class="form-label">Observations</label><textarea name="observations" class="form-control" rows="2"></textarea></div>
                    <div class="col-12 form-check ms-2"><input type="hidden" name="delivrance_complete" value="0"><input class="form-check-input" type="checkbox" name="delivrance_complete" value="1" id="dc" checked><label class="form-check-label" for="dc">Délivrance complète</label></div>
                </div>

                <h5>Nouveau-né(s)</h5>
                <div id="enfants">
                    <div class="row g-2 mb-2 enfant border rounded p-2">
                        <div class="col-md-2"><select name="enfants[0][sexe]" class="form-select no-select2"><option value="F">Fille</option><option value="M">Garçon</option><option value="I">Indéterminé</option></select></div>
                        <div class="col-md-2"><select name="enfants[0][etat]" class="form-select no-select2">@foreach(\App\Models\NouveauNe::ETATS as $c => $l)<option value="{{ $c }}">{{ $l }}</option>@endforeach</select></div>
                        <div class="col-md-2"><input type="text" name="enfants[0][prenom]" class="form-control" placeholder="Prénom (si donné)"></div>
                        <div class="col-md-1"><input type="number" name="enfants[0][poids_g]" class="form-control" placeholder="Poids g"></div>
                        <div class="col-md-1"><input type="number" step="0.1" name="enfants[0][taille_cm]" class="form-control" placeholder="Taille"></div>
                        <div class="col-md-1"><input type="number" step="0.1" name="enfants[0][perimetre_cranien_cm]" class="form-control" placeholder="PC"></div>
                        <div class="col-md-1"><input type="number" name="enfants[0][apgar_1]" class="form-control" min="0" max="10" placeholder="Apgar 1"></div>
                        <div class="col-md-1"><input type="number" name="enfants[0][apgar_5]" class="form-control" min="0" max="10" placeholder="Apgar 5"></div>
                        <div class="col-md-1 form-check pt-2"><input type="hidden" name="enfants[0][reanimation]" value="0"><input class="form-check-input" type="checkbox" name="enfants[0][reanimation]" value="1"><label class="form-check-label small">Réa.</label></div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="ajouterEnfant">+ Jumeau</button>
            </div>
            <div class="block-content block-content-full bg-body-light text-end">
                <button class="btn btn-primary">Enregistrer l'accouchement</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('#mere').select2({
                placeholder: 'Rechercher la mère…', minimumInputLength: 2,
                ajax: { url: "{{ route('patients.search') }}", dataType: 'json', delay: 250, data: (p) => ({ q: p.term, gender: 'F' }),
                    processResults: (data) => ({ results: data.map(p => ({ id: p.id, text: `${p.nom} ${p.prenom} — ${p.numero_dossier ?? ''}` })) }) }
            });
            let n = 1;
            $('#ajouterEnfant').on('click', function () {
                const bloc = $('#enfants .enfant:first').clone();
                bloc.find('[name]').each(function () { this.name = this.name.replace(/enfants\[\d+\]/, `enfants[${n}]`); if (this.type !== 'hidden') $(this).val(this.tagName === 'SELECT' ? $(this).find('option:first').val() : ''); });
                $('#enfants').append(bloc); n++;
            });
        });
    </script>
@endsection
