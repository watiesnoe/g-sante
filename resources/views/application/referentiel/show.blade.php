@extends('layouts.app')

@section('title_page', $fiche->nom)

@section('styles')
    <style>
        .fiche-texte { white-space: pre-line; }
        .fiche-tableau { white-space: pre; font-family: ui-monospace, "DejaVu Sans Mono", monospace; font-size: .85rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; overflow-x: auto; }
        .fiche-rubrique h3 { font-size: 1rem; font-weight: 700; color: #1c61e7; margin-bottom: .5rem; }
    </style>
@endsection

@section('content')
    <div class="content">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('referentiel.index') }}" class="small"><i class="fa fa-arrow-left me-1"></i>Guide des médicaments</a>
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2 mb-3">
            <div>
                <h2 class="h3 fw-bold mb-1">{{ $fiche->nom }}</h2>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge bg-primary">{{ \App\Models\FicheMedicament::VOIES[$fiche->voie] ?? $fiche->voie }}</span>
                    @if($fiche->categorie)<span class="badge bg-body-dark text-dark">{{ $fiche->categorie }}</span>@endif
                    @if($fiche->mention)<span class="badge bg-info">{{ $fiche->mention }}</span>@endif
                    @if($fiche->deconseille)<span class="badge bg-danger">Déconseillé par la source</span>@endif
                </div>
            </div>
            <div class="text-end small text-muted">
                {{ $fiche->source }}<br>page {{ $fiche->page }}@if($fiche->mise_a_jour_source) · fiche mise à jour : {{ $fiche->mise_a_jour_source }}@endif
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        @if($validation)
            <div class="alert alert-success d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="fa fa-check-circle me-1"></i>Validée pour votre établissement par <strong>{{ $validation->user->name ?? '—' }}</strong> le {{ $validation->updated_at->format('d/m/Y') }}.
                    @if($validation->commentaire)<br><span class="small">{{ $validation->commentaire }}</span>@endif</span>
                @can('referentiel.valider')
                    <form method="POST" action="{{ route('referentiel.retirer', $fiche) }}" onsubmit="return confirm('Retirer la validation de cette fiche ?');">@csrf<button class="btn btn-sm btn-alt-secondary">Retirer la validation</button></form>
                @endcan
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-circle me-1"></i><strong>À valider</strong> : cette fiche, importée automatiquement de la source, n'a pas encore été relue par un médecin ou un pharmacien de votre établissement. Vérifiez-la avant de vous y fier.
                @can('referentiel.valider')
                    <form method="POST" action="{{ route('referentiel.valider', $fiche) }}" class="d-flex flex-wrap gap-2 mt-2">@csrf
                        <input name="commentaire" class="form-control form-control-sm" style="max-width:420px" placeholder="Commentaire (facultatif), ex. conforme à la page {{ $fiche->page }}">
                        <button class="btn btn-sm btn-success"><i class="fa fa-check me-1"></i>J'ai relu cette fiche : la valider</button>
                    </form>
                @endcan
            </div>
        @endif

        @if($fiche->avertissement)
            <div class="alert alert-danger"><i class="fa fa-exclamation-triangle me-1"></i>{{ $fiche->avertissement }}</div>
        @endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="block block-rounded"><div class="block-content">
                    @foreach(\App\Models\FicheMedicament::RUBRIQUES as $colonne => $libelle)
                        @if(filled($fiche->$colonne))
                            <div class="fiche-rubrique mb-4">
                                <h3>{{ $libelle }}</h3>
                                {{-- Posologie : tableaux par âge et poids gardés tels que dans la source --}}
                                <div class="{{ $colonne === 'posologie' ? 'fiche-tableau' : 'fiche-texte' }}">{{ $fiche->$colonne }}</div>
                            </div>
                        @endif
                    @endforeach
                </div></div>
            </div>
            <div class="col-xl-4">
                @if($syntheses->isNotEmpty())
                    <div class="block block-rounded">
                        <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-route me-1"></i> Fiches de synthèse</h3></div>
                        <div class="block-content pb-3">
                            @foreach($syntheses as $s)
                                <a href="{{ route('syntheses.show', $s) }}" class="d-flex justify-content-between border-bottom py-2"><span>{{ $s->titre }}</span><span class="badge bg-{{ \App\Models\FicheSynthese::COULEURS[$s->statut] }}">{{ \App\Models\FicheSynthese::STATUTS[$s->statut] }}</span></a>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="block block-rounded">
                    <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-pills me-1"></i> Dans votre stock</h3></div>
                    <div class="block-content pb-3">
                        @forelse($lies as $m)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <span>{{ $m->nom }} <span class="small text-muted">· stock {{ $m->stock }}</span></span>
                                @can('stock.medicaments')
                                    <form method="POST" action="{{ route('referentiel.lier', $fiche) }}">@csrf<input type="hidden" name="medicament_id" value="{{ $m->id }}"><input type="hidden" name="action" value="delier"><button class="btn btn-sm btn-alt-secondary" title="Délier"><i class="fa fa-unlink"></i></button></form>
                                @endcan
                            </div>
                        @empty
                            <p class="small text-muted">Aucun produit de votre stock n'est relié à cette fiche.</p>
                        @endforelse
                        @can('stock.medicaments')
                            @if($suggestions->isNotEmpty())
                                <div class="small fw-semibold mt-3 mb-1">Produits qui pourraient correspondre</div>
                                @foreach($suggestions as $m)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span class="small">{{ $m->nom }}</span>
                                        <form method="POST" action="{{ route('referentiel.lier', $fiche) }}">@csrf<input type="hidden" name="medicament_id" value="{{ $m->id }}"><input type="hidden" name="action" value="lier"><button class="btn btn-sm btn-alt-primary"><i class="fa fa-link me-1"></i>Relier</button></form>
                                    </div>
                                @endforeach
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
