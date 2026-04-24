@extends('layouts.app')

@section('content')
<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Créer un Utilisateur</h3>
            <div class="block-options">
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-alt-secondary">
                    <i class="fa fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
        <div class="block-content block-content-full">
            <p>La fonctionnalité de création classique d'utilisateur a été remplacée par une Modale AJAX sur la page index ou via l'enregistrement.</p>
        </div>
    </div>
</div>
@endsection
