@extends('layouts.admin')

@section('title_page', 'Administrateurs')

@section('content')
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-user-shield me-2"></i>Administrateurs de la plateforme</h2>
            <div class="text-muted small mb-3">Ils créent les établissements, gèrent les abonnements, les rôles et les permissions. Gardez-en le moins possible.</div>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @include('application.plateforme._identifiants')

            <div class="block block-rounded"><div class="block-content block-content-full table-responsive">
                <table class="table table-bordered table-vcenter table-admin">
                    <thead><tr><th>Administrateur</th><th>E-mail</th><th>Statut</th><th class="text-center">Actions</th></tr></thead>
                    <tbody>
                        @foreach($administrateurs as $a)
                            <tr>
                                <td><strong>{{ $a->prenom }} {{ $a->nom }}</strong> @if($a->id === auth()->id())<span class="badge bg-info">vous</span>@endif</td>
                                <td class="small">{{ $a->email }}</td>
                                <td><span class="badge bg-{{ $a->statut === 'actif' ? 'success' : 'secondary' }}">{{ ucfirst($a->statut) }}</span>
                                    @if($a->doit_changer_mot_de_passe)<div class="small text-muted">jamais connecté</div>@endif</td>
                                <td class="text-center">
                                    @if($a->id !== auth()->id())
                                        <div class="d-flex gap-1 justify-content-center">
                                            <form method="POST" action="{{ route('plateforme.administrateurs.reinitialiser', $a) }}" onsubmit="return confirm('Générer un nouveau mot de passe provisoire ?');">@csrf<button class="btn btn-sm btn-alt-warning" title="Nouveau mot de passe provisoire"><i class="fa fa-key"></i></button></form>
                                            <form method="POST" action="{{ route('plateforme.comptes.basculer', $a) }}">@csrf<button class="btn btn-sm {{ $a->statut === 'actif' ? 'btn-alt-danger' : 'btn-alt-success' }}" title="{{ $a->statut === 'actif' ? 'Désactiver' : 'Réactiver' }}"><i class="fa {{ $a->statut === 'actif' ? 'fa-user-slash' : 'fa-user-check' }}"></i></button></form>
                                        </div>
                                    @else
                                        <a href="{{ route('profile.edit') }}" class="small">Mon profil</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div></div>

            <form method="POST" action="{{ route('plateforme.administrateurs.store') }}" class="block block-rounded">
                @csrf
                <div class="block-header block-header-default"><h3 class="block-title">Ajouter un administrateur</h3></div>
                <div class="block-content">
                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-md-3"><label class="form-label">Prénom</label><input name="prenom" class="form-control" required value="{{ old('prenom') }}"></div>
                        <div class="col-md-3"><label class="form-label">Nom</label><input name="nom" class="form-control" required value="{{ old('nom') }}"></div>
                        <div class="col-md-4"><label class="form-label">E-mail (identifiant)</label><input name="email" type="email" class="form-control" required value="{{ old('email') }}"></div>
                        <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
                    </div>
                    <p class="small text-muted">Un mot de passe provisoire s'affichera une seule fois ; il devra être changé à la première connexion.</p>
                </div>
            </form>
        </div>
        </div>
    </div>
@endsection
