@extends('layouts.app')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Détails de l'Utilisateur: {{ $user->nom }} {{ $user->prenom }}</h3>
            <div class="block-options">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
        <div class="block-content block-content-full">
            <div class="row">
                <div class="col-md-4">
                    @if($user->photo)
                        <img src="{{ asset('storage/'.$user->photo) }}" class="img-fluid rounded-circle w-50" alt="Photo">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->prenom.' '.$user->nom) }}&background=random" class="img-fluid rounded-circle w-50" alt="Photo">
                    @endif
                </div>
                <div class="col-md-8">
                    <h4>Informations</strong></h4>
                    <table class="table table-borderless table-striped">
                        <tbody>
                            <tr>
                                <td style="width: 30%;"><strong>Nom</strong></td>
                                <td>{{ $user->nom }}</td>
                            </tr>
                            <tr>
                                <td><strong>Prénom</strong></td>
                                <td>{{ $user->prenom }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Téléphone</strong></td>
                                <td>{{ $user->telephone ?? 'Non spécifié' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Rôle</strong></td>
                                <td><span class="badge bg-primary">{{ ucfirst($user->role) }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Statut</strong></td>
                                <td>
                                    <span class="badge {{ $user->statut == 'actif' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($user->statut) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
