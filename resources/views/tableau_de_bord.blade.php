@extends('layouts.app')

@section('title_page', 'Tableau de bord')

@section('styles')
    <style>
        .tdb-entete { background: linear-gradient(135deg, #1c61e7 0%, #0d2e6e 100%); color: #fff; border-radius: 14px; padding: 22px 26px; }
        .tdb-entete h1 { color: #fff; font-size: 1.55rem; font-weight: 800; margin: 0; }
        .tdb-entete .badge-profil { background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .28); border-radius: 999px; padding: 4px 12px; font-size: .82rem; font-weight: 600; }
        .tdb-tuile { border-radius: 12px; border-left: 4px solid var(--tdb-couleur); transition: transform .12s, box-shadow .12s; height: 100%; }
        .tdb-tuile:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, .08); }
        .tdb-tuile .valeur { font-size: 1.7rem; font-weight: 800; line-height: 1.1; }
        .tdb-tuile .icone { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .tdb-liste .ligne { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid rgba(0, 0, 0, .06); }
        .tdb-liste .ligne:last-child { border-bottom: 0; }
        .tdb-liste .principal { font-weight: 600; }
        .tdb-liste .vide { color: #94a3b8; text-align: center; padding: 22px 0; }
        .tdb-barres { display: flex; align-items: flex-end; gap: 10px; height: 150px; padding-top: 10px; }
        .tdb-barres .col-jour { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 4px; }
        .tdb-barres .barre { width: 100%; max-width: 38px; border-radius: 6px 6px 0 0; background: #1c61e7; min-height: 3px; }
        .tdb-barres .etiquette { font-size: .75rem; color: #64748b; white-space: nowrap; }
        .tdb-raccourci { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(0, 0, 0, .08); color: inherit; text-decoration: none; transition: background .12s, border-color .12s; }
        .tdb-raccourci:hover { background: rgba(28, 97, 231, .06); border-color: rgba(28, 97, 231, .35); color: inherit; }
        .tdb-raccourci i { color: #1c61e7; width: 20px; text-align: center; }
    </style>
@endsection

@section('content')
    @php
        $couleurs = ['primary' => '#1c61e7', 'success' => '#16a34a', 'warning' => '#d97706', 'danger' => '#dc2626', 'info' => '#0891b2', 'dark' => '#334155', 'secondary' => '#64748b'];
    @endphp
    <div class="content">

        {{-- En-tête : qui, où, quand --}}
        <div class="tdb-entete d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <div class="mb-2"><span class="badge-profil"><i class="fa fa-id-badge me-1"></i>{{ $espace['profil'] }}</span></div>
                <h1>{{ $espace['salutation'] }}</h1>
                <div class="opacity-75 mt-1"><i class="fa fa-hospital me-1"></i>{{ \App\Models\Etablissement::courant()->nom }}</div>
            </div>
            <div class="text-end">
                <div class="fs-4 fw-bold">{{ now()->format('H\hi') }}</div>
                <div class="opacity-75">{{ now()->translatedFormat('l j F Y') }}</div>
            </div>
        </div>

        {{-- Chiffres du jour --}}
        @if(count($espace['tuiles']))
            <div class="row g-3 mb-4">
                @foreach($espace['tuiles'] as $t)
                    @php $c = $couleurs[$t['couleur']] ?? $couleurs['primary']; @endphp
                    <div class="col-6 col-md-4 col-xl-3">
                        <a href="{{ $t['lien'] ?? '#' }}" class="block block-rounded mb-0 tdb-tuile text-body" style="--tdb-couleur: {{ $c }}">
                            <div class="block-content block-content-full d-flex justify-content-between align-items-center gap-2">
                                <div class="min-w-0">
                                    <div class="valeur">{{ $t['valeur'] }}</div>
                                    <div class="fw-semibold">{{ $t['titre'] }}</div>
                                    <div class="small text-muted text-truncate">{{ $t['detail'] }}</div>
                                </div>
                                <div class="icone" style="background: {{ $c }}1a; color: {{ $c }}"><i class="fa {{ $t['icone'] }}"></i></div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="row g-3">
            {{-- Listes « à traiter » --}}
            <div class="col-xl-8">
                <div class="row g-3">
                    @if($espace['activite'])
                        @php $max = max(1, collect($espace['activite'])->max('arrivees')); $maxE = max(1, collect($espace['activite'])->max('encaisse')); @endphp
                        <div class="col-md-6">
                            <div class="block block-rounded h-100 mb-0">
                                <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-chart-bar me-1 text-primary"></i> Arrivées — 7 jours</h3></div>
                                <div class="block-content pb-3">
                                    <div class="tdb-barres">
                                        @foreach($espace['activite'] as $j)
                                            <div class="col-jour" title="{{ $j['arrivees'] }} arrivée(s)">
                                                <span class="small fw-semibold">{{ $j['arrivees'] }}</span>
                                                <div class="barre" style="height: {{ round($j['arrivees'] / $max * 100) }}%"></div>
                                                <span class="etiquette">{{ $j['jour'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="block block-rounded h-100 mb-0">
                                <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-coins me-1 text-success"></i> Encaissé — 7 jours</h3></div>
                                <div class="block-content pb-3">
                                    <div class="tdb-barres">
                                        @foreach($espace['activite'] as $j)
                                            <div class="col-jour" title="{{ number_format($j['encaisse'], 0, ',', ' ') }} F">
                                                <span class="small fw-semibold">{{ $j['encaisse'] >= 1000 ? round($j['encaisse'] / 1000) . 'k' : (int) $j['encaisse'] }}</span>
                                                <div class="barre" style="background:#16a34a; height: {{ round(max(0, $j['encaisse']) / $maxE * 100) }}%"></div>
                                                <span class="etiquette">{{ $j['jour'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @forelse($espace['listes'] as $liste)
                        <div class="col-md-6">
                            <div class="block block-rounded h-100 mb-0 tdb-liste">
                                <div class="block-header block-header-default">
                                    <h3 class="block-title"><i class="fa {{ $liste['icone'] }} me-1 text-primary"></i> {{ $liste['titre'] }}</h3>
                                    @if($liste['lien'])<div class="block-options"><a href="{{ $liste['lien'] }}" class="btn btn-sm btn-alt-secondary">Tout voir</a></div>@endif
                                </div>
                                <div class="block-content pb-2">
                                    @forelse($liste['lignes'] as $l)
                                        <div class="ligne">
                                            <div class="min-w-0">
                                                <div class="principal text-truncate">{{ $l['principal'] }}</div>
                                                @if(!empty($l['secondaire']))<div class="small text-muted text-truncate">{{ $l['secondaire'] }}</div>@endif
                                            </div>
                                            @if(!empty($l['badge']))<span class="badge bg-{{ $l['couleur'] ?? 'secondary' }} flex-shrink-0">{{ $l['badge'] }}</span>@endif
                                        </div>
                                    @empty
                                        <div class="vide"><i class="fa fa-check-circle me-1"></i>{{ $liste['vide'] }}</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        @unless($espace['activite'])
                            <div class="col-12"><div class="block block-rounded mb-0"><div class="block-content py-4 text-center text-muted">Rien à traiter pour le moment.</div></div></div>
                        @endunless
                    @endforelse
                </div>
            </div>

            {{-- Raccourcis --}}
            <div class="col-xl-4">
                <div class="block block-rounded mb-0">
                    <div class="block-header block-header-default"><h3 class="block-title"><i class="fa fa-bolt me-1 text-warning"></i> Raccourcis</h3></div>
                    <div class="block-content pb-3">
                        <div class="d-grid gap-2">
                            @forelse($espace['raccourcis'] as $r)
                                <a href="{{ $r['lien'] }}" class="tdb-raccourci"><i class="fa {{ $r['icone'] }}"></i><span class="fw-semibold">{{ $r['titre'] }}</span><i class="fa fa-chevron-right ms-auto text-muted small"></i></a>
                            @empty
                                <span class="text-muted small">Aucun raccourci.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
