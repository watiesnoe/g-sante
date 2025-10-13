@extends('layouts.app')

@section('title_page', $fiche->titre)

@section('content')
    <div class="content">
        <a href="{{ route('guide-clinique.index', ['vue' => 'berrebi']) }}" class="small"><i class="fa fa-arrow-left me-1"></i>Fiches Berrebi</a>
        <div class="mt-2 mb-2">
            <div class="text-muted small text-uppercase">{{ $fiche->specialite }} · Berrebi 2005{{ $fiche->pages ? ', ' . $fiche->pages : '' }}</div>
            <h2 class="h3 fw-bold mb-0">{{ $fiche->titre }}</h2>
        </div>
        <div class="alert alert-warning border-0 small mb-3">
            <i class="fa fa-exclamation-triangle me-1"></i>
            <strong>Texte de 2005 lu par OCR : à vérifier.</strong> Les posologies peuvent comporter des erreurs de lecture et certains médicaments ont été retirés depuis.
            Préférez les maladies du guide MSF quand elles existent, et le protocole national.
            @if($fiche->pages_lisibles)<div class="mt-1">Pages lisibles dans le PDF source : {{ $fiche->pages_lisibles }}.</div>@endif
        </div>

        @if($fiche->maladies->isNotEmpty())
            <div class="mb-3">
                <span class="small fw-bold">Voir aussi dans le guide MSF :</span>
                @foreach($fiche->maladies as $m)<a href="{{ route('guide-clinique.show', $m) }}" class="badge bg-primary text-decoration-none me-1">{{ $m->nom }}</a>@endforeach
            </div>
        @endif

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="block block-rounded h-100"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-search me-1"></i>Démarche diagnostique</h3></div>
                    <div class="block-content block-content-full small">
                        @forelse(\App\Models\FicheBerrebi::liste($fiche->demarche) as $etape)<div class="mb-1">• {{ $etape }}</div>@empty<span class="text-muted">Non lisible dans le PDF.</span>@endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="block block-rounded h-100"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-pills me-1"></i>Médicaments cités</h3></div>
                    <div class="block-content block-content-full small">
                        @forelse(\App\Models\FicheBerrebi::liste($fiche->medicaments_cites) as $med)<span class="badge bg-light text-dark border me-1 mb-1">{{ $med }}</span>@empty<span class="text-muted">Aucun.</span>@endforelse
                    </div>
                </div>
            </div>
            @if($fiche->traitement)
                <div class="col-12">
                    <div class="block block-rounded"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-prescription me-1"></i>Traitement (extrait du livre, OCR)</h3></div>
                        <div class="block-content block-content-full small" style="white-space: pre-line">{{ $fiche->traitement }}</div>
                    </div>
                </div>
            @endif
        </div>

        @if($fiche->ordonnances->isNotEmpty())
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-file-prescription me-1"></i>Ordonnances du livre ({{ $fiche->ordonnances->count() }})</h3></div>
                <div class="block-content block-content-full table-responsive">
                    <table class="table table-vcenter table-sm small mb-0">
                        <thead><tr><th>Médicament</th><th>Posologie 2005 <span class="badge bg-warning text-dark">à vérifier</span></th><th>Statut actuel</th><th>Posologie de référence (MSF)</th></tr></thead>
                        <tbody>
                            @foreach($fiche->ordonnances as $o)
                                <tr class="align-top {{ $o->estObsolete() ? 'table-danger' : ($o->estIncertain() ? 'table-warning' : '') }}">
                                    <td><span class="fw-semibold">{{ $o->dci ?? '—' }}</span>@if($o->nom_commercial)<div class="text-muted">{{ $o->nom_commercial }} (2005)</div>@endif</td>
                                    <td class="text-muted fst-italic">{{ $o->posologie_ocr ?? '—' }}</td>
                                    <td>
                                        @if($o->statut_2025)<div class="text-danger fw-bold"><i class="fa fa-ban me-1"></i>{{ $o->statut_2025 }}</div>@endif
                                        @if($o->statut_2022)<div>{{ $o->statut_2022 }}</div>@endif
                                        <div class="text-muted">OMS : {{ $o->liste_oms ?? '—' }} · MSF : {{ $o->guide_msf ?? '—' }}</div>
                                    </td>
                                    <td>{{ $o->posologie_reference ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
