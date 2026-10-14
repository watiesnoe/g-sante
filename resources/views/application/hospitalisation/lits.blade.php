@extends('layouts.app')

@section('title_page', 'État des lits')

@section('content')
    @php
        $couleurs = ['Libre' => 'success', 'Occupé' => 'danger', 'Réservé' => 'warning', 'Maintenance' => 'secondary'];
        $libelles = ['Libre' => 'Libre', 'Occupé' => 'Occupé', 'Réservé' => 'Réservé', 'Maintenance' => 'Indisponible'];
    @endphp
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-bed me-2"></i>État des lits</h2>
            <div class="d-flex gap-2">
                @foreach($libelles as $code => $libelle)
                    <span class="badge bg-{{ $couleurs[$code] }} fs-sm">{{ $libelle }} : {{ $compte[$code] ?? 0 }}</span>
                @endforeach
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        @if($multiNiveaux && (auth()->user()->can('parametres.lits') || auth()->user()->can('parametres.salles')))
            <form method="POST" action="{{ route('lits.unite') }}" class="d-flex gap-2 mb-3">@csrf
                <select name="service_medical_id" class="form-select form-select-sm no-select2" style="width:auto" required>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select>
                <input type="text" name="nom" class="form-control form-control-sm" placeholder="Nouvelle unité de soins" style="max-width:260px" required>
                <button class="btn btn-sm btn-outline-primary">Créer l'unité</button>
            </form>
        @endif

        @foreach($salles as $salle)
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">
                        @if($multiNiveaux)<span class="text-muted">{{ $salle->service->nom ?? 'Sans service' }} › {{ $salle->uniteSoins->nom ?? 'Sans unité' }} ›</span>@endif
                        {{ $salle->nom }} <small class="text-muted">{{ $salle->type }}</small>
                    </h3>
                    @if($multiNiveaux && (auth()->user()->can('parametres.lits') || auth()->user()->can('parametres.salles')))
                        <form method="POST" action="{{ route('lits.salle_unite', $salle) }}" class="d-flex gap-1">@csrf
                            <select name="unite_soins_id" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                                <option value="">Unité…</option>
                                @foreach($unites as $u)<option value="{{ $u->id }}" {{ $salle->unite_soins_id === $u->id ? 'selected' : '' }}>{{ $u->service->nom ?? '' }} › {{ $u->nom }}</option>@endforeach
                            </select>
                        </form>
                    @endif
                </div>
                <div class="block-content pb-3">
                    <div class="row g-2">
                        @forelse($salle->lits as $lit)
                            @php $occ = $occupations[$lit->id] ?? null; $patient = $occ?->hospitalisation?->patient ?? $occ?->hospitalisation?->consultation?->patient; @endphp
                            <div class="col-6 col-md-4 col-xl-3">
                                <div class="border border-2 border-{{ $couleurs[$lit->statut] ?? 'secondary' }} rounded p-2 h-100">
                                    <div class="d-flex justify-content-between">
                                        <strong>Lit {{ $lit->numero }}</strong>
                                        <span class="badge bg-{{ $couleurs[$lit->statut] ?? 'secondary' }}">{{ $libelles[$lit->statut] ?? $lit->statut }}</span>
                                    </div>
                                    @if($patient)
                                        <div class="small mt-1">{{ mb_strtoupper($patient->nom) }} {{ $patient->prenom }}</div>
                                        <div class="small text-muted">depuis le {{ $occ->debut->format('d/m H:i') }}</div>
                                        @can('hospitalisations.edit')
                                            <form method="POST" action="{{ route('lits.transferer', $occ->hospitalisation) }}" class="d-flex gap-1 mt-1">
                                                @csrf
                                                <select name="lit_id" class="form-select form-select-sm no-select2" required>
                                                    <option value="">Transférer vers…</option>
                                                    @foreach($litsLibres as $l)<option value="{{ $l->id }}">Lit {{ $l->numero }}</option>@endforeach
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary"><i class="fa fa-exchange-alt"></i></button>
                                            </form>
                                        @endcan
                                    @else
                                        @if($lit->statut === 'Occupé')
                                            <div class="small text-danger mt-1">Marqué occupé sans séjour enregistré.</div>
                                        @endif
                                        <form method="POST" action="{{ route('lits.statut', $lit) }}" class="d-flex gap-1 mt-1">
                                            @csrf
                                            <select name="statut" class="form-select form-select-sm no-select2">
                                                <option value="Libre">Libre</option><option value="Réservé" {{ $lit->statut === 'Réservé' ? 'selected' : '' }}>Réservé</option><option value="Maintenance" {{ $lit->statut === 'Maintenance' ? 'selected' : '' }}>Indisponible</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-secondary">OK</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted small">Aucun lit dans cette salle.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
