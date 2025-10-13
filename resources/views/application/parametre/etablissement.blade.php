@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Établissement
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('etablissement.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- 1. Identité --}}
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">1. Votre établissement</h3>
                        </div>
                        <div class="block-content">
                            <p class="text-muted small">Ces informations sont imprimées sur les tickets, ordonnances et reçus.</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $etablissement->nom) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Slogan / sous-titre</label>
                                    <input type="text" name="slogan" class="form-control" value="{{ old('slogan', $etablissement->slogan) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $etablissement->adresse) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Région</label>
                                    <input type="text" name="region" class="form-control" value="{{ old('region', $etablissement->region) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">District sanitaire</label>
                                    <input type="text" name="district" class="form-control" value="{{ old('district', $etablissement->district) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $etablissement->telephone) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $etablissement->email) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site web</label>
                                    <input type="text" name="site_web" class="form-control" value="{{ old('site_web', $etablissement->site_web) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Logo (PNG ou JPG, 2 Mo max)</label>
                                    <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg">
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    @if(file_exists($etablissement->logoPath()))
                                        <img src="{{ asset($etablissement->logo ?: 'image/logo/logo.png') }}?v={{ filemtime($etablissement->logoPath()) }}" alt="Logo" style="max-height: 70px;">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small">
                        <i class="fa fa-info-circle me-1"></i> Le type d'établissement, les modules, les règles de fonctionnement et le serveur en ligne sont gérés par l'administrateur de la plateforme.
                        @can('etablissements.gerer')
                            <a href="{{ route('plateforme.configuration', $etablissement) }}" class="alert-link ms-1">Les configurer dans l'Administration</a>
                        @endcan
                    </div>

                    <div class="text-end mb-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>


            </div>
        </div>
    </div>
@endsection
