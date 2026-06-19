@extends('layouts.app')

@section('titre', 'Créer un Utilisateur')

@section('content')
<div class="container mt-4">
    <div class="row">
        <!-- Sidebar gauche -->
        @include('layouts.partials.configside')
        
        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                    <h3 class="block-title">➕ Nouvel Utilisateur</h3>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
                <div class="block-content block-content-full">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <!-- Informations Personnelles -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Informations de base</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" name="telephone" class="form-control phone-input" value="{{ old('telephone') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
                            </div>

                            <!-- Sécurité -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">Sécurité</h5>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmation mot de passe <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <!-- Photo -->
                            <div class="col-md-12 mt-4">
                                <label class="form-label">Photo de profil</label>
                                <input type="file" name="photo" class="form-control">
                            </div>

                            <!-- Rôle et Permissions -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">Rôle & Permissions</h5>
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Rôle principal <span class="text-danger">*</span></label>
                                <select name="role" class="form-select form-select-lg" required>
                                    <option value="">-- Sélectionnez un rôle --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ $role->libelle ?? $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <p class="mb-2 fw-semibold">Permissions Spécifiques (Optionnel)</p>
                                <small class="text-muted d-block mb-3"><i class="fa fa-info-circle"></i> En général, il suffit d'assigner un rôle. Ne cochez des permissions ici que pour donner des droits exceptionnels/additionnels à cet utilisateur spécifique.</small>
                                
                                <div class="row">
                                    @if(isset($permissions))
                                        @foreach ($permissions as $module => $modulePermissions)
                                            <div class="col-md-6 col-xl-4 mb-3">
                                                <div class="card h-100 shadow-sm border-0 bg-light">
                                                    <div class="card-header bg-white fw-bold text-capitalize border-bottom-0 py-2">
                                                        {{ $module }}
                                                    </div>
                                                    <div class="card-body p-2">
                                                        @foreach($modulePermissions as $permission)
                                                            <div class="form-check form-switch mb-1">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                                                    id="create_perm_{{ $permission->id }}"
                                                                    {{ is_array(old('permissions')) && in_array($permission->name, old('permissions')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" style="font-size: 0.85rem;" for="create_perm_{{ $permission->id }}">
                                                                    {{ explode('.', $permission->name)[1] ?? $permission->name }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-save me-1"></i> Créer l'utilisateur
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
