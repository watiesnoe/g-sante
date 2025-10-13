@extends('layouts.app')

@section('title_page', 'Fiches Berrebi')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-0"><i class="fa fa-stethoscope me-2"></i>Guide clinique</h2>
        <div class="text-muted small mb-2">{{ $total }} fiche(s) · symptôme → démarche diagnostique → traitement</div>
        <div class="alert alert-warning border-0 small mb-3">
            <i class="fa fa-exclamation-triangle me-1"></i>
            <strong>Livre de 2005, texte lu par OCR.</strong> Les traitements et posologies peuvent contenir des erreurs de lecture et ne tiennent pas compte des retraits de médicaments depuis 2005 :
            ils sont à vérifier avant tout usage et ne sont jamais ajoutés à une ordonnance. Source : {{ \App\Models\FicheBerrebi::SOURCE }}.
        </div>
        @include('application.guide_clinique._onglets')

        <form method="GET" class="row g-2 mb-3">
            <input type="hidden" name="vue" value="berrebi">
            <div class="col-md-6"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Symptôme, situation ou médicament (ex. hémorragie, grossesse, amoxicilline)…"></div>
            <div class="col-md-4">
                <select name="specialite" class="form-select no-select2" onchange="this.form.submit()">
                    <option value="">Toutes les spécialités</option>
                    @foreach($specialites as $sp)<option value="{{ $sp }}" @selected(request('specialite') === $sp)>{{ $sp }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="fa fa-search"></i></button></div>
        </form>

        @forelse($parSpecialite as $specialite => $fiches)
            <h3 class="h5 fw-bold mt-4 mb-2">{{ $specialite }} <span class="text-muted fw-normal small">({{ $fiches->count() }})</span></h3>
            <div class="row g-2">
                @foreach($fiches as $f)
                    <div class="col-md-6 col-xl-4">
                        <a href="{{ route('guide-clinique.berrebi', $f) }}" class="block block-rounded block-link-shadow h-100 mb-0">
                            <div class="block-content block-content-full py-2">
                                <div class="fw-semibold text-dark">{{ $f->titre }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit((string) $f->demarche, 90) ?: 'Démarche non lisible dans le PDF' }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="text-muted">Aucune fiche ne correspond à la recherche.</div>
        @endforelse
    </div>
@endsection
