@extends('layouts.app')

@section('title_page', 'Gratuités')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-hand-holding-heart me-2"></i>Règles de gratuité</h2>
        <div class="alert alert-info small">Saisissez chaque règle à partir du texte officiel en vigueur (programme national, arrêté…). Une gratuité s'applique automatiquement à la facturation dès que le patient et l'acte remplissent les conditions.</div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="block block-rounded"><div class="block-content block-content-full">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Règle</th><th>Conditions</th><th>Couvre</th><th>Période</th><th></th></tr></thead>
                        <tbody>
                            @forelse($regles as $r)
                                <tr class="{{ $r->actif ? '' : 'text-muted' }}">
                                    <td class="fw-semibold">{{ $r->nom }}</td>
                                    <td class="small">
                                        {{ $r->age_max_mois !== null ? 'Âge ≤ ' . ($r->age_max_mois % 12 === 0 ? ($r->age_max_mois / 12) . ' an(s)' : $r->age_max_mois . ' mois') : '' }}
                                        {{ $r->genre ? ($r->genre === 'F' ? 'Femmes' : 'Hommes') : '' }}
                                        {{ $r->grossesse_en_cours ? 'Grossesse en cours' : '' }}
                                    </td>
                                    <td class="small">
                                        {{ ['aucun' => 'Aucun acte', 'tous' => 'Tous les actes', 'liste' => $r->prestations->pluck('nom')->implode(', ')][$r->portee_actes] }}
                                        @if($r->couvre_medicaments)<br>Médicaments : {{ $r->medicaments->isEmpty() ? 'tous' : $r->medicaments->pluck('nom')->implode(', ') }}@endif
                                    </td>
                                    <td class="small">{{ $r->date_effet->format('d/m/Y') }}{{ $r->date_fin ? ' → ' . $r->date_fin->format('d/m/Y') : '' }}</td>
                                    <td><form method="POST" action="{{ route('gratuites.basculer', $r) }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ $r->actif ? 'Désactiver' : 'Activer' }}</button></form></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Aucune règle saisie.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div></div>
            </div>
            <div class="col-xl-5">
                <form method="POST" action="{{ route('gratuites.store') }}" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Nouvelle règle</h3></div>
                    <div class="block-content">
                        <div class="mb-2"><input type="text" name="nom" class="form-control" placeholder="Intitulé (référence du texte)" required></div>
                        <div class="row g-2 mb-2">
                            <div class="col-4"><label class="form-label small">Âge max (mois)</label><input type="number" name="age_max_mois" class="form-control" min="0"></div>
                            <div class="col-4"><label class="form-label small">Sexe</label><select name="genre" class="form-select no-select2"><option value="">Tous</option><option value="F">Femmes</option><option value="M">Hommes</option></select></div>
                            <div class="col-4 form-check pt-4 ps-4"><input type="hidden" name="grossesse_en_cours" value="0"><input class="form-check-input" type="checkbox" name="grossesse_en_cours" value="1" id="gec"><label class="form-check-label small" for="gec">Enceinte</label></div>
                        </div>
                        <div class="mb-2"><label class="form-label small">Actes gratuits</label>
                            <select name="portee_actes" class="form-select no-select2"><option value="liste">Seulement ceux choisis</option><option value="tous">Tous les actes</option><option value="aucun">Aucun acte</option></select></div>
                        <div class="mb-2"><select name="prestations[]" class="form-select js-select2" multiple style="width:100%">@foreach($prestations as $p)<option value="{{ $p->id }}">{{ $p->nom }}</option>@endforeach</select></div>
                        <div class="form-check mb-2"><input type="hidden" name="couvre_medicaments" value="0"><input class="form-check-input" type="checkbox" name="couvre_medicaments" value="1" id="cmed"><label class="form-check-label small" for="cmed">Médicaments gratuits (tous si aucun choisi)</label></div>
                        <div class="mb-2"><select name="medicaments[]" class="form-select js-select2" multiple style="width:100%">@foreach($medicaments as $m)<option value="{{ $m->id }}">{{ $m->nom }}</option>@endforeach</select></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="form-label small">À partir du</label><input type="date" name="date_effet" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                            <div class="col-6"><label class="form-label small">Jusqu'au</label><input type="date" name="date_fin" class="form-control"></div>
                        </div>
                        <div class="text-end mb-3"><button class="btn btn-primary">Enregistrer</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
