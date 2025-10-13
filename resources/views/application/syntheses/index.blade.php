@extends('layouts.app')

@section('title_page', 'Fiches de synthèse')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div>
                <h2 class="h3 fw-bold mb-0"><i class="fa fa-route me-2"></i>Fiches de synthèse</h2>
                <div class="text-muted small">Du symptôme à la prescription : démarche de votre établissement, traitements reliés au guide des médicaments</div>
            </div>
            @can('syntheses.rediger')<a href="{{ route('syntheses.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Nouvelle fiche</a>@endcan
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-7"><input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Rechercher un symptôme, une pathologie (ex. douleur thoracique, fièvre)…" autofocus></div>
            <div class="col-md-4">
                <select name="statut" class="form-select no-select2" onchange="this.form.submit()">
                    <option value="">Toutes les fiches</option>
                    @foreach(\App\Models\FicheSynthese::STATUTS as $code => $libelle)<option value="{{ $code }}" @selected(request('statut') === $code)>{{ $libelle }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid"><button class="btn btn-primary"><i class="fa fa-search"></i></button></div>
        </form>

        <div class="row g-3">
            @forelse($fiches as $f)
                <div class="col-md-6 col-xl-4">
                    <a href="{{ route('syntheses.show', $f) }}" class="block block-rounded block-link-shadow h-100 mb-0 text-body">
                        <div class="block-content pb-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <strong>{{ $f->titre }}</strong>
                                <span class="badge bg-{{ \App\Models\FicheSynthese::COULEURS[$f->statut] }}">{{ \App\Models\FicheSynthese::STATUTS[$f->statut] }}</span>
                            </div>
                            @if($f->motif)<div class="small text-muted mb-1"><i class="fa fa-comment-medical me-1"></i>{{ $f->motif }}</div>@endif
                            @if($f->specialite)<span class="badge bg-body-dark text-dark">{{ $f->specialite }}</span>@endif
                            @if($f->medicaments->isNotEmpty())<div class="small mt-2"><i class="fa fa-pills me-1 text-primary"></i>{{ $f->medicaments->pluck('nom')->take(4)->implode(', ') }}{{ $f->medicaments->count() > 4 ? '…' : '' }}</div>@endif
                            <div class="small text-muted mt-2">{{ $f->auteur->name ?? '—' }} · {{ $f->updated_at->format('d/m/Y') }}</div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-12"><div class="block block-rounded mb-0"><div class="block-content py-4 text-center text-muted">
                    Aucune fiche pour le moment.@can('syntheses.rediger') <a href="{{ route('syntheses.create') }}">Rédigez la première</a> (par exemple « Douleur thoracique aiguë » ou « Fièvre de l'enfant »).@endcan
                </div></div></div>
            @endforelse
        </div>
        <div class="mt-3">{{ $fiches->links() }}</div>
    </div>
@endsection
