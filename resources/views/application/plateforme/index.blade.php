@extends('layouts.admin')

@section('title_page', 'Plateforme')

@section('content')
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h3 fw-bold mb-0"><i class="fa fa-hospital me-2"></i>Établissements</h2>
                <div class="text-muted small">Espaces des établissements, abonnements et propriétaires</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('plateforme.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Nouvel espace</a>
            </div>
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}
            @if(session('recu'))<a href="{{ route('plateforme.recu', session('recu')) }}" target="_blank" class="alert-link ms-2"><i class="fa fa-print me-1"></i>Imprimer le reçu</a>@endif</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @include('application.plateforme._identifiants')

        @if($formules->isEmpty())
            <div class="alert alert-info">Aucune formule d'abonnement : <a href="{{ route('plateforme.formules') }}" class="alert-link">créez vos formules</a> (Silver, Gold…) pour abonner les espaces.</div>
        @endif

        <div class="block block-rounded"><div class="block-content block-content-full table-responsive">
            <table class="table table-bordered table-vcenter">
                <thead><tr><th>Établissement</th><th>Statut</th><th>Abonnement</th><th>Propriétaire</th><th class="text-end">Comptes</th><th class="text-end">Patients</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @foreach($etablissements as $e)
                        <tr>
                            <td>
                                <strong>{{ $e->nom }}</strong>
                                <div class="small text-muted">{{ $types[$e->type] ?? $e->type }}{{ $e->code ? ' · ' . $e->code : '' }}{{ $e->district ? ' · ' . $e->district : '' }}</div>
                                <div class="small text-muted">{{ count($e->modules ?? []) }} module(s) accordé(s)</div>
                            </td>
                            <td>
                                @if($e->actif)<span class="badge bg-success">Actif</span>
                                @else<span class="badge bg-danger">Suspendu</span>@if($e->motif_suspension)<div class="small text-muted">{{ $e->motif_suspension }}</div>@endif
                                @endif
                            </td>
                            <td>
                                @if($a = $e->abonnement)
                                    <strong>{{ $a->formule->nom }}</strong>@if($a->essai) <span class="badge bg-info">essai</span>@endif
                                    <div><span class="badge bg-{{ \App\Models\Abonnement::COULEURS[$a->statut] ?? 'secondary' }}">{{ \App\Models\Abonnement::STATUTS[$a->statut] ?? $a->statut }}</span></div>
                                    <div class="small text-muted">jusqu'au {{ $a->fin->format('d/m/Y') }}
                                        @if($a->statut === 'actif' && $a->joursRestants() <= 30)<span class="text-warning fw-semibold">({{ $a->joursRestants() }} j)</span>@endif</div>
                                @else
                                    <span class="small text-muted">Aucun</span>
                                @endif
                            </td>
                            <td>
                                @forelse($proprietaires[$e->id] as $p)
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span>{{ $p->prenom }} {{ $p->nom }}<div class="small text-muted">{{ $p->email }}{{ $p->doit_changer_mot_de_passe ? ' · jamais connecté' : '' }}{{ $p->statut !== 'actif' ? ' · ' . $p->statut : '' }}</div></span>
                                        <form method="POST" action="{{ route('plateforme.reinitialiser', [$e, $p]) }}" onsubmit="return confirm('Générer un nouveau mot de passe provisoire pour {{ addslashes($p->email) }} ?');">@csrf
                                            <button class="btn btn-sm btn-alt-secondary" title="Nouveau mot de passe provisoire"><i class="fa fa-key"></i></button></form>
                                    </div>
                                @empty
                                    <span class="text-warning small"><i class="fa fa-exclamation-triangle me-1"></i>Aucun propriétaire</span>
                                @endforelse
                            </td>
                            <td class="text-end">{{ $comptes[$e->id] ?? 0 }}</td>
                            <td class="text-end">{{ $patients[$e->id] ?? 0 }}</td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @if($e->actif)
                                        <form method="POST" action="{{ route('plateforme.entrer', $e) }}">@csrf<button class="btn btn-sm btn-alt-primary" title="Ouvrir cet espace"><i class="fa fa-sign-in-alt me-1"></i>Entrer</button></form>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-alt-success" data-bs-toggle="collapse" data-bs-target="#abonnement-{{ $e->id }}"><i class="fa fa-file-invoice-dollar me-1"></i>Abonnement</button>
                                    <a href="{{ route('plateforme.configuration', $e) }}" class="btn btn-sm btn-alt-secondary"><i class="fa fa-sliders-h me-1"></i>Configurer</a>
                                    <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-toggle="collapse" data-bs-target="#proprio-{{ $e->id }}"><i class="fa fa-user-plus me-1"></i>Propriétaire</button>
                                    @if($e->actif)
                                        <form method="POST" action="{{ route('plateforme.suspendre', $e) }}" onsubmit="this.motif.value = prompt('Motif de la suspension (visible par les utilisateurs de l\'espace) :') || ''; return this.motif.value !== '';">@csrf<input type="hidden" name="motif"><button class="btn btn-sm btn-alt-danger"><i class="fa fa-pause me-1"></i>Suspendre</button></form>
                                    @else
                                        <form method="POST" action="{{ route('plateforme.reactiver', $e) }}">@csrf<button class="btn btn-sm btn-alt-success"><i class="fa fa-play me-1"></i>Réactiver</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="abonnement-{{ $e->id }}">
                            <td colspan="7" class="bg-body-light">
                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <form method="POST" action="{{ route('plateforme.abonner', $e) }}">
                                            @csrf
                                            <div class="fw-semibold mb-2">{{ $e->abonnement ? 'Formule' : 'Abonner cet espace' }}</div>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-sm-5"><select name="formule_id" class="form-select form-select-sm no-select2" required>
                                                    @foreach($formules as $fo)<option value="{{ $fo->id }}" @selected($e->abonnement?->formule_id === $fo->id)>{{ $fo->nom }}{{ $fo->prix !== null ? ' — ' . number_format((float) $fo->prix, 0, ',', ' ') . ' XOF / ' . $fo->duree_mois . ' mois' : '' }}</option>@endforeach
                                                </select></div>
                                                @if($e->abonnement && $e->abonnement->statut !== 'termine')
                                                    <div class="col-sm-7">
                                                        <button name="action" value="changer" class="btn btn-sm btn-primary">Changer de formule</button>
                                                        <span class="small text-muted">mêmes dates, modules mis à jour</span>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="action" value="nouveau">
                                                    <div class="col-sm-3"><label class="form-label small mb-0">Début</label><input type="date" name="debut" class="form-control form-control-sm" value="{{ today()->toDateString() }}" required></div>
                                                    <div class="col-sm-2"><label class="form-label small mb-0">Essai (j)</label><input type="number" name="essai_jours" min="1" max="90" class="form-control form-control-sm" placeholder="—"></div>
                                                    <div class="col-sm-2"><button class="btn btn-sm btn-primary">Abonner</button></div>
                                                @endif
                                            </div>
                                        </form>
                                    </div>
                                    @if($e->abonnement && $e->abonnement->statut !== 'termine')
                                        <div class="col-lg-6">
                                            <form method="POST" action="{{ route('plateforme.payer', $e) }}">
                                                @csrf
                                                <div class="fw-semibold mb-2">Enregistrer un paiement <span class="small text-muted fw-normal">— prolonge de {{ $e->abonnement->formule->duree_mois }} mois et rétablit les modules</span></div>
                                                <div class="row g-2 align-items-end">
                                                    <div class="col-sm-3"><label class="form-label small mb-0">Montant (XOF)</label><input type="number" name="montant" min="1" step="1" class="form-control form-control-sm" required value="{{ $e->abonnement->formule->prix !== null ? (int) $e->abonnement->formule->prix : '' }}"></div>
                                                    <div class="col-sm-3"><label class="form-label small mb-0">Mode</label><select name="mode" class="form-select form-select-sm no-select2">@foreach(\App\Models\PaiementAbonnement::MODES as $code => $libelle)<option value="{{ $code }}">{{ $libelle }}</option>@endforeach</select></div>
                                                    <div class="col-sm-3"><label class="form-label small mb-0">Référence</label><input name="reference" class="form-control form-control-sm" placeholder="N° transaction"></div>
                                                    <div class="col-sm-3"><label class="form-label small mb-0">Payé le</label><input type="date" name="paye_le" class="form-control form-control-sm" value="{{ today()->toDateString() }}" required></div>
                                                </div>
                                                <button class="btn btn-sm btn-success mt-2"><i class="fa fa-check me-1"></i>Enregistrer le paiement</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <tr class="collapse" id="proprio-{{ $e->id }}">
                            <td colspan="7" class="bg-body-light">
                                <form method="POST" action="{{ route('plateforme.proprietaire', $e) }}" class="row g-2 align-items-end">
                                    @csrf
                                    <div class="col-md-2"><label class="form-label small">Prénom</label><input name="prenom" class="form-control form-control-sm" required></div>
                                    <div class="col-md-2"><label class="form-label small">Nom</label><input name="nom" class="form-control form-control-sm" required></div>
                                    <div class="col-md-3"><label class="form-label small">E-mail (identifiant)</label><input name="email" type="email" class="form-control form-control-sm" required></div>
                                    <div class="col-md-2"><label class="form-label small">Téléphone</label><input name="telephone" class="form-control form-control-sm"></div>
                                    <div class="col-md-3"><button class="btn btn-sm btn-primary">Créer le compte propriétaire</button></div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>

        </div>
        </div>
    </div>
@endsection
