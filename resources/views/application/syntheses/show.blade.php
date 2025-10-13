@extends('layouts.app')

@section('title_page', $fiche->titre)

@section('content')
    <div class="content">
        <a href="{{ route('syntheses.index') }}" class="small"><i class="fa fa-arrow-left me-1"></i>Fiches de synthèse</a>
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2 mb-3">
            <div>
                <h2 class="h3 fw-bold mb-1">{{ $fiche->titre }}</h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-{{ \App\Models\FicheSynthese::COULEURS[$fiche->statut] }}">{{ \App\Models\FicheSynthese::STATUTS[$fiche->statut] }}</span>
                    @if($fiche->specialite)<span class="badge bg-body-dark text-dark">{{ $fiche->specialite }}</span>@endif
                    @if($fiche->maladie)<span class="badge bg-info">{{ $fiche->maladie->nom }}</span>@endif
                </div>
                @if($fiche->motif)<div class="mt-2"><i class="fa fa-comment-medical me-1 text-primary"></i>{{ $fiche->motif }}</div>@endif
            </div>
            <div class="d-flex gap-2">
                @can('syntheses.rediger')<a href="{{ route('syntheses.edit', $fiche) }}" class="btn btn-alt-secondary"><i class="fa fa-pen me-1"></i> Modifier</a>@endcan
                @if($fiche->statut === 'a_valider' && auth()->user()->can('syntheses.valider') && $fiche->auteur_id !== auth()->id())
                    <form method="POST" action="{{ route('syntheses.valider', $fiche) }}">@csrf<button class="btn btn-success"><i class="fa fa-check me-1"></i> Valider la fiche</button></form>
                @endif
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @if($fiche->statut === 'validee')
            <div class="alert alert-success small mb-3"><i class="fa fa-check-circle me-1"></i>Validée par {{ $fiche->validateur->name ?? '—' }} le {{ $fiche->valide_at?->format('d/m/Y') }} · rédigée par {{ $fiche->auteur->name ?? '—' }}</div>
        @else
            <div class="alert alert-warning small mb-3"><i class="fa fa-exclamation-circle me-1"></i>Fiche non validée : à utiliser avec prudence. Rédigée par {{ $fiche->auteur->name ?? '—' }}.</div>
        @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="block block-rounded"><div class="block-content">
                    @foreach(\App\Models\FicheSynthese::SECTIONS as $champ => [$libelle])
                        @if(filled($fiche->$champ))
                            <div class="mb-4">
                                <h3 class="fs-base fw-bold text-primary mb-2">{{ $libelle }}</h3>
                                <div style="white-space: pre-line">{{ $fiche->$champ }}</div>
                            </div>
                        @endif
                    @endforeach
                </div></div>
            </div>
            <div class="col-xl-4">
                <div class="block block-rounded">
                    <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-pills me-1"></i> Médicaments</h3></div>
                    <div class="block-content pb-3">
                        @forelse($fiche->medicaments as $m)
                            @php $validation = $m->validationCourante(); @endphp
                            <a href="{{ route('referentiel.show', $m) }}" class="d-block border-bottom py-2 text-body">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-semibold">{{ $m->nom }}</span>
                                    @if($m->deconseille)<span class="badge bg-danger">Déconseillé</span>
                                    @elseif($validation)<span class="badge bg-success">Fiche validée</span>
                                    @else<span class="badge bg-warning text-dark">Fiche à valider</span>@endif
                                </div>
                                <div class="small text-muted">{{ \App\Models\FicheMedicament::VOIES[$m->voie] ?? $m->voie }} · p. {{ $m->page }}@if($m->pivot->usage) · {{ $m->pivot->usage }}@endif</div>
                            </a>
                        @empty
                            <p class="small text-muted mb-0">Aucun médicament relié.</p>
                        @endforelse
                        <p class="small text-muted mt-2 mb-0">Posologies, contre-indications et précautions : ouvrez la fiche du médicament.</p>
                    </div>
                </div>
                @if($fiche->references->isNotEmpty())
                    <div class="block block-rounded">
                        <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-book me-1"></i> Pour aller plus loin</h3></div>
                        <div class="block-content pb-3">
                            @foreach($fiche->references as $r)
                                <div class="border-bottom py-2">
                                    <div class="fw-semibold small">{{ $r->ouvrage }}</div>
                                    <div class="small text-muted">{{ $r->localisation }}@if($r->note) — {{ $r->note }}@endif</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
