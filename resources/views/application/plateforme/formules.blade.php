@extends('layouts.admin')

@section('title_page', 'Formules d\'abonnement')

@section('content')
    @php $f = $edition; @endphp
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h3 fw-bold mb-0"><i class="fa fa-layer-group me-2"></i>Formules d'abonnement</h2>
                <div class="text-muted small">Modules, limites et prix proposés aux établissements</div>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="row g-3 mb-3">
            @forelse($formules as $formule)
                <div class="col-md-6 col-xl-4">
                    <div class="block block-rounded h-100 mb-0 {{ $formule->actif ? '' : 'opacity-50' }}">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">{{ $formule->nom }} @unless($formule->actif)<span class="badge bg-secondary">retirée</span>@endunless</h3>
                            <div class="block-options"><a href="{{ route('plateforme.formules', ['modifier' => $formule->uuid]) }}" class="btn btn-sm btn-alt-secondary"><i class="fa fa-pen"></i></a></div>
                        </div>
                        <div class="block-content pb-3">
                            <div class="fs-3 fw-bold">{{ $formule->prix !== null ? number_format((float) $formule->prix, 0, ',', ' ') . ' ' . $formule->devise : 'Sur devis' }}
                                <span class="fs-sm text-muted fw-normal">/ {{ $formule->duree_mois }} mois</span></div>
                            @if($formule->description)<p class="small text-muted mb-2">{{ $formule->description }}</p>@endif
                            <ul class="small mb-2 ps-3">
                                <li>{{ $formule->max_utilisateurs ? $formule->max_utilisateurs . ' utilisateurs' : 'Utilisateurs illimités' }}</li>
                                <li>{{ $formule->max_lits !== null ? $formule->max_lits . ' lits' : 'Lits illimités' }}</li>
                                <li>{{ $formule->max_sms_mois !== null ? $formule->max_sms_mois . ' SMS / mois' : 'SMS illimités' }}</li>
                                <li>Grâce : {{ $formule->jours_grace }} jour(s) après l'échéance</li>
                            </ul>
                            <div class="small"><strong>{{ count($formule->modules ?? []) }} modules :</strong> {{ collect($formule->modules)->map(fn($m) => $modules[$m]['label'] ?? $m)->implode(', ') }}</div>
                            @if($formule->modules_coupes)
                                <div class="small text-danger mt-1"><strong>Coupés en cas de retard :</strong> {{ collect($formule->modules_coupes)->map(fn($m) => $modules[$m]['label'] ?? $m)->implode(', ') }}</div>
                            @endif
                            <div class="small text-muted mt-2">{{ $formule->abonnes_count }} espace(s) abonné(s)</div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info mb-0">Aucune formule pour l'instant. Créez la première ci-dessous (par exemple Silver, Gold…).</div></div>
            @endforelse
        </div>

        <form method="POST" action="{{ $f ? route('plateforme.formules.update', $f) : route('plateforme.formules.store') }}" class="block block-rounded" id="formulaire">
            @csrf
            @if($f) @method('PUT') @endif
            <div class="block-header block-header-default">
                <h3 class="block-title">{{ $f ? 'Modifier « ' . $f->nom . ' »' : 'Nouvelle formule' }}</h3>
                @if($f)<div class="block-options"><a href="{{ route('plateforme.formules') }}" class="btn btn-sm btn-alt-secondary">Annuler</a></div>@endif
            </div>
            <div class="block-content">
                <div class="row g-3 mb-3">
                    <div class="col-md-3"><label class="form-label">Nom <span class="text-danger">*</span></label><input name="nom" class="form-control" required value="{{ old('nom', $f?->nom) }}" placeholder="Ex. Silver"></div>
                    <div class="col-md-5"><label class="form-label">Description</label><input name="description" class="form-control" value="{{ old('description', $f?->description) }}" placeholder="Pour qui, ce qui est inclus"></div>
                    <div class="col-md-2"><label class="form-label">Prix (XOF)</label><input name="prix" type="number" min="0" step="1" class="form-control" value="{{ old('prix', $f?->prix !== null ? (int) $f->prix : '') }}" placeholder="Vide = sur devis"></div>
                    <div class="col-md-2"><label class="form-label">Durée payée (mois)</label><input name="duree_mois" type="number" min="1" max="60" class="form-control" required value="{{ old('duree_mois', $f?->duree_mois ?? 12) }}"></div>
                    <div class="col-md-3"><label class="form-label">Utilisateurs max.</label><input name="max_utilisateurs" type="number" min="1" class="form-control" value="{{ old('max_utilisateurs', $f?->max_utilisateurs) }}" placeholder="Vide = illimité"></div>
                    <div class="col-md-3"><label class="form-label">Lits max.</label><input name="max_lits" type="number" min="0" class="form-control" value="{{ old('max_lits', $f?->max_lits) }}" placeholder="Vide = illimité"></div>
                    <div class="col-md-3"><label class="form-label">SMS par mois</label><input name="max_sms_mois" type="number" min="0" class="form-control" value="{{ old('max_sms_mois', $f?->max_sms_mois) }}" placeholder="Vide = illimité"></div>
                    <div class="col-md-2"><label class="form-label">Jours de grâce</label><input name="jours_grace" type="number" min="0" max="90" class="form-control" required value="{{ old('jours_grace', $f?->jours_grace ?? 7) }}"></div>
                    <div class="col-md-1"><label class="form-label">Ordre</label><input name="ordre" type="number" min="0" class="form-control" value="{{ old('ordre', $f?->ordre ?? 0) }}"></div>
                </div>

                @php $inclus = old('modules', $f?->modules ?? []); $coupes = old('modules_coupes', $f?->modules_coupes ?? []); @endphp
                <table class="table table-sm table-vcenter mb-3">
                    <thead><tr><th>Module</th><th class="text-center" style="width:130px">Inclus</th><th class="text-center" style="width:220px">Coupé si retard de paiement</th></tr></thead>
                    <tbody>
                        @foreach($modules as $cle => $module)
                            <tr>
                                <td>{{ $module['label'] }} <span class="small text-muted">— {{ $module['description'] }}</span></td>
                                <td class="text-center"><input type="checkbox" class="form-check-input" name="modules[]" value="{{ $cle }}" @checked(in_array($cle, $inclus))></td>
                                <td class="text-center">
                                    @if(in_array($cle, $proteges))
                                        <span class="small text-muted" title="Des patients peuvent être en soins">jamais coupé</span>
                                    @else
                                        <input type="checkbox" class="form-check-input" name="modules_coupes[]" value="{{ $cle }}" @checked(in_array($cle, $coupes))>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="small text-muted">Patients, tickets, consultations et caisse sont toujours disponibles. Les urgences, l'hospitalisation et la maternité ne sont jamais coupées pour retard : des patients peuvent être en soins. Une coupure ne supprime aucune donnée ; tout revient au paiement.</p>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="actif" value="0">
                        <input class="form-check-input" type="checkbox" name="actif" value="1" id="actif" @checked(old('actif', $f?->actif ?? true))>
                        <label class="form-check-label" for="actif">Proposée aux nouveaux abonnements</label>
                    </div>
                    <button class="btn btn-primary"><i class="fa fa-save me-1"></i> {{ $f ? 'Enregistrer' : 'Créer la formule' }}</button>
                </div>
            </div>
        </form>
        </div>
        </div>
    </div>
@endsection
