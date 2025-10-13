@extends('layouts.admin')

@section('title_page', 'Propriétaires')

@section('content')
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h3 fw-bold mb-0"><i class="fa fa-user-tie me-2"></i>Propriétaires</h2>
                    <div class="text-muted small">Responsables des établissements : chacun administre son seul espace</div>
                </div>
                <a href="{{ route('plateforme.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Nouvel établissement et propriétaire</a>
            </div>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @include('application.plateforme._identifiants')

            <div class="block block-rounded"><div class="block-content block-content-full table-responsive">
                <table class="table table-bordered table-striped table-vcenter table-admin">
                    <thead><tr><th>#</th><th>Propriétaire</th><th>Contact</th><th>Établissement</th><th>Statut</th><th class="text-center">Actions</th></tr></thead>
                    <tbody>
                        @forelse($proprietaires as $i => $p)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $p->prenom }} {{ $p->nom }}</strong></td>
                                <td class="small">{{ $p->email }}@if($p->telephone)<div class="text-muted">{{ $p->telephone }}</div>@endif</td>
                                <td>
                                    @forelse($p->etablissements as $e)
                                        <div>{{ $e->nom }} @unless($e->actif)<span class="badge bg-danger">suspendu</span>@endunless</div>
                                    @empty
                                        <span class="text-warning small">Aucun</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="badge bg-{{ $p->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($p->statut) }}</span>
                                    @if($p->doit_changer_mot_de_passe)<div class="small text-muted">jamais connecté</div>@endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($e = $p->etablissements->first())
                                            <form method="POST" action="{{ route('plateforme.reinitialiser', [$e, $p]) }}" onsubmit="return confirm('Générer un nouveau mot de passe provisoire pour {{ addslashes($p->email) }} ?');">@csrf
                                                <button class="btn btn-sm btn-alt-warning" title="Nouveau mot de passe provisoire"><i class="fa fa-key"></i></button></form>
                                        @endif
                                        <form method="POST" action="{{ route('plateforme.comptes.basculer', $p) }}">@csrf
                                            <button class="btn btn-sm {{ $p->statut === 'actif' ? 'btn-alt-danger' : 'btn-alt-success' }}" title="{{ $p->statut === 'actif' ? 'Désactiver' : 'Réactiver' }}"><i class="fa {{ $p->statut === 'actif' ? 'fa-user-slash' : 'fa-user-check' }}"></i></button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted">Aucun propriétaire. Créez un établissement avec son propriétaire.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div></div>
        </div>
        </div>
    </div>
@endsection
