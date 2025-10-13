@extends('layouts.app')

@section('title_page', 'Guide clinique')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <h2 class="h3 fw-bold mb-0"><i class="fa fa-stethoscope me-2"></i>Guide clinique</h2>
                <div class="text-muted small">{{ $maladies->count() }} maladie(s) affichée(s) · symptômes, signes de gravité, examens, traitements</div>
            </div>
        </div>
        <div class="alert alert-warning border-0 small mb-3">
            <i class="fa fa-exclamation-triangle me-1"></i>
            <strong>Aide à la décision, pas un substitut au jugement clinique.</strong>
            Les doses sont des résumés : vérifiez la fiche d'origine, le protocole national (PNLP, PNLT, PTME…) et l'état du patient (poids, âge, grossesse, allergies, fonction rénale) avant toute prescription.
            @if($source)<div class="text-muted mt-1">Source : {{ $source }}.</div>@endif
        </div>

        @include('application.guide_clinique._onglets')

        <form method="GET" class="block block-rounded mb-3">
            <div class="block-content block-content-full">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Maladie ou symptôme</label>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="ex. toux, paludisme, convulsions…">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Chapitre</label>
                        <select name="chapitre" class="form-select no-select2" onchange="this.form.submit()">
                            <option value="">Tous les chapitres</option>
                            @foreach($chapitres as $c)<option value="{{ $c }}" @selected(request('chapitre') === $c)>{{ $c }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Symptômes observés</label>
                        <select name="symptomes[]" class="form-select js-select2" multiple data-placeholder="Choisir un ou plusieurs symptômes">
                            @foreach($symptomes as $s)<option value="{{ $s->id }}" @selected(in_array($s->id, $coches))>{{ $s->nom }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-1 d-grid align-items-end"><button class="btn btn-primary mt-4"><i class="fa fa-search"></i></button></div>
                </div>
                @if(request()->hasAny(['q', 'chapitre', 'symptomes']))
                    <a href="{{ route('guide-clinique.index') }}" class="small">Effacer la recherche</a>
                @endif
            </div>
        </form>

        @if($coches)
            {{-- Recherche par symptômes : maladies classées par compatibilité --}}
            <div class="block block-rounded"><div class="block-content block-content-full table-responsive">
                <table class="table table-hover table-vcenter mb-0">
                    <thead><tr><th>Maladie possible</th><th>Chapitre</th><th class="text-center">Symptômes retrouvés</th></tr></thead>
                    <tbody>
                        @forelse($maladies as $m)
                            <tr>
                                <td><a href="{{ route('guide-clinique.show', $m) }}" class="fw-semibold">{{ $m->nom }}</a>
                                    @if($m->alerte_oms)<i class="fa fa-exclamation-triangle text-danger ms-1" title="Alerte OMS 2025"></i>@endif</td>
                                <td class="small text-muted">{{ $m->chapitre }}</td>
                                <td class="text-center"><span class="badge bg-{{ $m->symptomes_retrouves >= 2 ? 'success' : 'secondary' }}">{{ $m->symptomes_retrouves }} / {{ $m->symptomes_count }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">Aucune maladie ne correspond à ces symptômes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        @else
            @forelse($parChapitre as $chapitre => $liste)
                <h3 class="h5 fw-bold mt-4 mb-2">{{ $chapitre ?: 'Sans chapitre' }} <span class="text-muted fw-normal small">({{ $liste->count() }})</span></h3>
                <div class="row g-2">
                    @foreach($liste as $m)
                        <div class="col-md-6 col-xl-4">
                            <a href="{{ route('guide-clinique.show', $m) }}" class="block block-rounded block-link-shadow h-100 mb-0">
                                <div class="block-content block-content-full py-2">
                                    <div class="fw-semibold text-dark">{{ $m->nom }}
                                        @if($m->alerte_oms)<i class="fa fa-exclamation-triangle text-danger ms-1" title="Alerte OMS 2025"></i>@endif</div>
                                    <div class="small text-muted">{{ $m->symptomes_count }} symptôme(s) · {{ \Illuminate\Support\Str::limit($m->description, 90) }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="text-muted">Aucune maladie ne correspond à la recherche.</div>
            @endforelse
        @endif
    </div>
@endsection
