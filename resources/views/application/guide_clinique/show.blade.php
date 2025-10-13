@extends('layouts.app')

@section('title_page', $maladie->nom)

@php($abr = fn($texte) => \App\Models\AbreviationClinique::annoter($texte))

@section('content')
    <div class="content">
        <a href="{{ route('guide-clinique.index') }}" class="small"><i class="fa fa-arrow-left me-1"></i>Guide clinique</a>
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2 mb-2">
            <div>
                <div class="text-muted small text-uppercase">{{ $maladie->chapitre }}</div>
                <h2 class="h3 fw-bold mb-0">{{ $maladie->nom }}</h2>
                @unless($maladie->actif)<span class="badge bg-secondary">Archivée — hors du guide en vigueur</span>@endunless
            </div>
            @if($maladie->source_url)
                <a href="{{ $maladie->source_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fa fa-external-link-alt me-1"></i>Fiche d'origine</a>
            @endif
        </div>
        <div class="alert alert-warning border-0 small mb-3">
            <i class="fa fa-exclamation-triangle me-1"></i>
            Aide à la décision : vérifier la fiche d'origine, le protocole national et l'état du patient (poids, âge, grossesse, allergies, fonction rénale) avant toute prescription.
            Survolez une abréviation soulignée pour sa signification.
        </div>

        @foreach($maladie->alertesOms() as $alerte)
            <div class="alert alert-danger small mb-3"><i class="fa fa-ban me-1"></i><strong>Alerte :</strong> {{ $alerte }}</div>
        @endforeach

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="block block-rounded h-100"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-notes-medical me-1"></i>Symptômes</h3></div>
                    <div class="block-content block-content-full">
                        <p class="small">{!! $abr($maladie->description) !!}</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($maladie->symptomes->where('actif', true) as $s)<span class="badge bg-light text-dark border">{{ $s->nom }}</span>@endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="block block-rounded h-100 border border-danger"><div class="block-header bg-danger"><h3 class="block-title text-white"><i class="fa fa-exclamation-circle me-1"></i>Signes de gravité / quand référer</h3></div>
                    <div class="block-content block-content-full">
                        <ul class="small mb-0">
                            @forelse($maladie->signesGraviteListe() as $signe)<li>{!! $abr($signe) !!}</li>@empty<li class="text-muted">Non précisés.</li>@endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="block block-rounded h-100"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-flask me-1"></i>Diagnostic et examens</h3></div>
                    <div class="block-content block-content-full small">{!! $abr($maladie->examens) ?: '<span class="text-muted">Non précisé.</span>' !!}</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="block block-rounded h-100"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-shield-alt me-1"></i>Prévention</h3></div>
                    <div class="block-content block-content-full small">{!! $abr($maladie->prevention) ?: '<span class="text-muted">Non abordée dans la fiche.</span>' !!}</div>
                </div>
            </div>
            <div class="col-12">
                <div class="block block-rounded"><div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-prescription me-1"></i>Traitement</h3></div>
                    <div class="block-content block-content-full small">{!! $abr($maladie->traitement) ?: '<span class="text-muted">Non précisé.</span>' !!}</div>
                </div>
            </div>
        </div>

        <div class="block block-rounded mt-3">
            <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-pills me-1"></i>Médicaments cités ({{ $medicaments->count() }})</h3></div>
            <div class="block-content block-content-full table-responsive">
                <table class="table table-vcenter table-sm small mb-0">
                    <thead><tr><th>Molécule</th><th>Dans cette maladie</th><th>Contre-indications / précautions</th><th>Références</th><th>Votre stock</th></tr></thead>
                    <tbody>
                        @forelse($medicaments as $i => ['ligne' => $ligne, 'reference' => $ref, 'produit' => $produit])
                            <tr class="align-top">
                                <td class="fw-semibold text-nowrap">
                                    {{ $ligne->nom_medicament }}
                                    @if($ligne->fiche)<div><a href="{{ route('referentiel.show', $ligne->fiche) }}" class="fw-normal"><i class="fa fa-book-medical me-1"></i>Fiche</a></div>@endif
                                </td>
                                <td>@if($ligne->alerte)<div class="text-danger fw-bold mb-1"><i class="fa fa-ban me-1"></i>{{ $ligne->alerte }}</div>@endif
                                    {!! $abr($ligne->posologie) !!}
                                    @if($ref?->posologie_reference)
                                        <details class="mt-1"><summary class="text-primary">Posologie de référence (enfant / adulte)</summary>{!! $abr($ref->posologie_reference) !!}</details>
                                    @endif
                                </td>
                                <td>{!! $abr($ref?->contre_indications ?? $ligne->fiche?->precautions) ?: '<span class="text-muted">—</span>' !!}</td>
                                <td class="text-nowrap">
                                    @if($ref)
                                        <div>Liste OMS 2025 : <strong>{{ $ref->liste_oms ?? '—' }}</strong></div>
                                        @if($ref->dorosz && trim($ref->dorosz) !== '')<div>Dorosz 2022 : {{ \Illuminate\Support\Str::limit($ref->dorosz, 40) }}</div>@endif
                                        @if($ref->noms_commerciaux || $ref->posologie_vidal)
                                            <details><summary class="text-primary">Vidal 2018</summary>
                                                <div class="text-wrap" style="max-width:420px">
                                                    @if($ref->noms_commerciaux)<div><strong>Noms commerciaux (présents en 2022) :</strong> {{ $ref->noms_commerciaux }}</div>@endif
                                                    @if($ref->noms_arretes)<div><strong>Arrêtés / retirés :</strong> {{ $ref->noms_arretes }}</div>@endif
                                                    @if($ref->posologie_vidal)<div class="mt-1"><strong>Posologie (France) :</strong> {!! $abr(\Illuminate\Support\Str::limit($ref->posologie_vidal, 1500)) !!}</div>@endif
                                                </div>
                                            </details>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if($produit)
                                        <span class="badge bg-{{ $produit->stock > 0 ? 'success' : 'danger' }}">{{ $produit->stock > 0 ? $produit->stock . ' en stock' : 'Rupture' }}</span>
                                        <div class="text-muted">{{ $produit->nom }}</div>
                                    @else
                                        <span class="text-muted">Non référencé</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted">Aucun médicament cité.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($maladie->fichesBerrebi->isNotEmpty())
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-book me-1"></i>Fiches Berrebi liées (livre 2005, à vérifier)</h3></div>
                <div class="block-content block-content-full">
                    @foreach($maladie->fichesBerrebi as $f)
                        <a href="{{ route('guide-clinique.berrebi', $f) }}" class="badge bg-light text-dark border text-decoration-none me-1 mb-1 p-2"><span class="text-muted">{{ $f->specialite }} ·</span> {{ $f->titre }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        <details class="mt-3 small">
            <summary class="fw-bold">Abréviations utilisées</summary>
            <div class="row mt-2">
                @foreach($abreviations as $a)<div class="col-md-3 col-6"><strong>{{ $a->abreviation }}</strong> : {{ $a->signification }}</div>@endforeach
            </div>
        </details>
    </div>
@endsection
