@extends('layouts.app')

@section('title_page', 'Conventions')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-handshake me-2"></i>Conventions avec les organismes payeurs</h2>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="block block-rounded">
                    <div class="block-content block-content-full">
                        <table class="table table-sm table-bordered table-striped">
                            <thead><tr><th>Organisme</th><th class="text-end">Taux</th><th class="text-end">Plafond / facture</th><th>Médicaments</th><th>Actes</th><th>Période</th></tr></thead>
                            <tbody>
                                @foreach($conventions as $c)
                                    <tr class="{{ $c->date_fin && $c->date_fin->lt(today()) ? 'text-muted' : '' }}">
                                        <td class="fw-semibold">{{ $c->assurance->nom ?? '-' }}</td>
                                        <td class="text-end">{{ rtrim(rtrim(number_format($c->taux, 2, ',', ' '), '0'), ',') }} %</td>
                                        <td class="text-end">{{ $c->plafond_par_facture !== null ? number_format($c->plafond_par_facture, 0, ',', ' ') . ' F' : '—' }}</td>
                                        <td>{{ $c->couvre_medicaments ? 'Oui' : 'Non' }}</td>
                                        <td>{{ $c->couverture_actes === 'toutes' ? 'Tous' : $c->prestations->pluck('nom')->implode(', ') }}</td>
                                        <td class="small">du {{ $c->date_effet->format('d/m/Y') }}{{ $c->date_fin ? ' au ' . $c->date_fin->format('d/m/Y') : '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="small text-muted mb-0">Une nouvelle convention clôt la précédente la veille de sa date d'effet ; les factures déjà émises gardent leurs parts.</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <form method="POST" action="{{ route('conventions.enregistrer') }}" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Nouvelle convention</h3></div>
                    <div class="block-content">
                        <div class="mb-2"><label class="form-label">Organisme</label>
                            <select name="assurance_id" class="form-select no-select2" required>@foreach($assurances as $a)<option value="{{ $a->id }}">{{ $a->nom }}</option>@endforeach</select></div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><label class="form-label">Taux (%)</label><input type="number" name="taux" class="form-control" min="0" max="100" step="0.01" required></div>
                            <div class="col-6"><label class="form-label">Plafond / facture</label><input type="number" name="plafond_par_facture" class="form-control" min="0"></div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><label class="form-label">Date d'effet</label><input type="date" name="date_effet" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                            <div class="col-6"><label class="form-label">Date de fin</label><input type="date" name="date_fin" class="form-control"></div>
                        </div>
                        <div class="form-check mb-2"><input type="hidden" name="couvre_medicaments" value="0"><input class="form-check-input" type="checkbox" name="couvre_medicaments" value="1" id="cm" checked><label class="form-check-label" for="cm">Couvre les médicaments</label></div>
                        <div class="mb-2"><label class="form-label">Actes couverts</label>
                            <select name="couverture_actes" class="form-select no-select2"><option value="toutes">Tous les actes</option><option value="liste">Seulement la liste ci-dessous</option></select></div>
                        <div class="mb-3"><select name="prestations[]" class="form-select js-select2" multiple style="width:100%">@foreach($prestations as $p)<option value="{{ $p->id }}">{{ $p->nom }}</option>@endforeach</select></div>
                        <div class="text-end mb-3"><button class="btn btn-primary">Enregistrer</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
