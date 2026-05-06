@extends('layouts.app')

@section('titre', '👤 Mon Profil')

@section('content')
    <div class="content">
        <div class="row g-4">
            <div class="col-md-8 offset-md-2">
                
                <!-- Informations du profil -->
                <div class="block block-rounded shadow-sm animate__animated animate__fadeInUp">
                    <div class="block-header block-header-default bg-primary">
                        <h3 class="block-title"><i class="fa fa-user-circle me-2"></i>Informations Personnelles</h3>
                    </div>
                    <div class="block-content block-content-full">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Changement de mot de passe -->
                <div class="block block-rounded shadow-sm animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="block-header block-header-default bg-dark">
                        <h3 class="block-title"><i class="fa fa-lock me-2"></i>Sécurité du Compte</h3>
                    </div>
                    <div class="block-content block-content-full">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Suppression du compte (Optionnel/Masqué si non nécessaire) -->
                <div class="block block-rounded shadow-sm border-danger animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="block-header block-header-default bg-danger">
                        <h3 class="block-title"><i class="fa fa-exclamation-triangle me-2"></i>Zone de Danger</h3>
                    </div>
                    <div class="block-content block-content-full">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
