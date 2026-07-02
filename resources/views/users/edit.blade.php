@extends('layouts.app')

@section('titre', 'Modifier un Utilisateur')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">✏️ Modifier l'Utilisateur : {{ $user->prenom }} {{ $user->nom }}</h3>
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

                        <form method="POST" action="{{ route('users.update', $user->uuid) }}"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row g-4">
                                <!-- Informations Personnelles -->
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">Informations de base</h5>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" name="prenom" class="form-control"
                                        value="{{ old('prenom', $user->prenom) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control"
                                        value="{{ old('nom', $user->nom) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $user->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" name="telephone" class="form-control phone-input"
                                        value="{{ old('telephone', $user->telephone) }}">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" name="adresse" class="form-control"
                                        value="{{ old('adresse', $user->adresse) }}">
                                </div>

                                <!-- Statut -->
                                <div class="col-12 mt-4">
                                    <h5 class="border-bottom pb-2 mb-3">Statut et Photo</h5>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select name="statut" class="form-select" required>
                                        @foreach ($statuts as $key => $label)
                                            <option value="{{ $key }}"
                                                {{ old('statut', $user->statut) === $key ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Photo de profil</label>
                                    <input type="file" name="photo" class="form-control">
                                    @if ($user->photo)
                                        <img src="{{ asset('storage/' . $user->photo) }}" class="mt-2 rounded" width="60"
                                            height="60" style="object-fit: cover;">
                                    @endif
                                </div>

                                <!-- Rôle et Permissions -->
                                <div class="col-12 mt-4">
                                    <h5 class="border-bottom pb-2 mb-3">Rôle & Permissions</h5>
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="form-label">Rôle principal <span class="text-danger">*</span></label>
                                    <select name="role" class="form-select form-select-lg" required>
                                        <option value="">-- Sélectionnez un rôle --</option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ old('role', $userRole) == $role->name ? 'selected' : '' }}>
                                                {{ $role->libelle ?? $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <p class="mb-2 fw-semibold">Permissions Spécifiques (Optionnel)</p>
                                    <small class="text-muted d-block mb-3">
                                        <i class="fa fa-info-circle"></i> En général, il suffit d'assigner un rôle.
                                        Ne cochez des permissions ici que pour donner des droits
                                        <strong>additionnels</strong> à cet utilisateur.
                                        <br>
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle me-1">✓
                                            Direct</span> = permission accordée directement à l'utilisateur &nbsp;|&nbsp;
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">Via rôle</span>
                                        = incluse dans le rôle assigné
                                    </small>

                                    @php
                                        // Permissions directes (cochables/décochables)
                                        $directPerms = old('permissions', $userPermissions);
                                        // Toutes les permissions (directes + via rôle)
                                        $allUserPerms = $user->getAllPermissions()->pluck('name')->toArray();
                                        // Permissions uniquement via le rôle (pas directes)
                                        $roleOnlyPerms = array_diff($allUserPerms, $userPermissions);
                                    @endphp

                                    <div class="row">
                                        @if (isset($permissions))
                                            @foreach ($permissions as $module => $modulePermissions)
                                                @php
                                                    // Compter les permissions directes cochées dans ce module
                                                    $activeCount = 0;
                                                    $directCountInModule = 0;
                                                    $checkedDirectInModule = 0;
                                                    foreach ($modulePermissions as $p) {
                                                        if (
                                                            in_array($p->name, $directPerms) ||
                                                            in_array($p->name, $roleOnlyPerms)
                                                        ) {
                                                            $activeCount++;
                                                        }
                                                        if (!in_array($p->name, $roleOnlyPerms)) {
                                                            $directCountInModule++;
                                                        }
                                                        if (in_array($p->name, $directPerms)) {
                                                            $checkedDirectInModule++;
                                                        }
                                                    }
                                                    $moduleSlug = Str::slug($module, '_');
                                                    $allDirectChecked =
                                                        $directCountInModule > 0 &&
                                                        $checkedDirectInModule === $directCountInModule;
                                                @endphp
                                                <div class="col-md-6 col-xl-4 mb-3">
                                                    <div
                                                        class="card h-100 shadow-sm border-0 {{ $activeCount > 0 ? 'border-start border-4 border-success' : 'bg-light' }}">
                                                        <div
                                                            class="card-header {{ $activeCount > 0 ? 'bg-success bg-opacity-10' : 'bg-white' }} fw-bold text-capitalize border-bottom-0 py-2 d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center gap-2">
                                                                @if ($directCountInModule > 0)
                                                                    <input type="checkbox"
                                                                        class="module-select-all form-check-input mt-0"
                                                                        id="selectAll_{{ $moduleSlug }}"
                                                                        data-module="{{ $moduleSlug }}"
                                                                        title="Tout cocher / décocher"
                                                                        {{ $allDirectChecked ? 'checked' : '' }}
                                                                        style="cursor:pointer; width:1.1em; height:1.1em;">
                                                                @endif
                                                                <label for="selectAll_{{ $moduleSlug }}"
                                                                    class="mb-0 fw-bold text-capitalize"
                                                                    style="cursor:pointer;">{{ $module }}</label>
                                                            </div>
                                                            @if ($activeCount > 0)
                                                                <span class="badge bg-success rounded-pill"
                                                                    style="font-size:0.7rem;">{{ $activeCount }}
                                                                    actif(s)</span>
                                                            @endif
                                                        </div>
                                                        <div class="card-body p-2">
                                                            @foreach ($modulePermissions as $permission)
                                                                @php
                                                                    $isDirectChecked = in_array(
                                                                        $permission->name,
                                                                        $directPerms,
                                                                    );
                                                                    $isViaRole = in_array(
                                                                        $permission->name,
                                                                        $roleOnlyPerms,
                                                                    );
                                                                    $actionLabel =
                                                                        explode('.', $permission->name)[1] ??
                                                                        $permission->name;
                                                                @endphp

                                                                @if ($isViaRole && !$isDirectChecked)
                                                                    {{-- Permission héritée du rôle : non modifiable, juste informative --}}
                                                                    <div class="d-flex align-items-center mb-1 px-1 py-1 rounded bg-secondary bg-opacity-10"
                                                                        style="font-size:0.82rem;">
                                                                        <i class="fa fa-check-circle text-secondary me-2"
                                                                            style="font-size:0.9rem;"></i>
                                                                        <span
                                                                            class="text-secondary">{{ $actionLabel }}</span>
                                                                        <span
                                                                            class="ms-auto badge bg-secondary bg-opacity-25 text-secondary"
                                                                            style="font-size:0.65rem;">Via rôle</span>
                                                                    </div>
                                                                @else
                                                                    {{-- Permission directe : cochable --}}
                                                                    <div class="form-check form-switch mb-1">
                                                                        <input class="form-check-input module-perm"
                                                                            type="checkbox" name="permissions[]"
                                                                            value="{{ $permission->name }}"
                                                                            id="edit_perm_{{ $permission->id }}"
                                                                            data-module="{{ $moduleSlug }}"
                                                                            {{ $isDirectChecked ? 'checked' : '' }}>
                                                                        <label
                                                                            class="form-check-label d-flex align-items-center gap-1"
                                                                            style="font-size: 0.85rem;"
                                                                            for="edit_perm_{{ $permission->id }}">
                                                                            {{ $actionLabel }}
                                                                            @if ($isDirectChecked)
                                                                                <span
                                                                                    class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"
                                                                                    style="font-size:0.65rem;">Direct</span>
                                                                            @endif
                                                                        </label>
                                                                    </div>
                                                                @endif
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
                                        <i class="fa fa-save me-1"></i> Enregistrer les modifications
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
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Initialiser l'état indéterminé au chargement
            document.querySelectorAll('.module-select-all').forEach(function(masterCb) {
                syncMasterState(masterCb);
            });

            // Clic sur la checkbox maître → cocher/décocher toutes les enfants
            document.querySelectorAll('.module-select-all').forEach(function(masterCb) {
                masterCb.addEventListener('change', function() {
                    var module = this.dataset.module;
                    document.querySelectorAll('.module-perm[data-module="' + module + '"]').forEach(
                        function(cb) {
                            cb.checked = masterCb.checked;
                        });
                });
            });

            // Clic sur une permission enfant → mettre à jour l'état de la checkbox maître
            document.querySelectorAll('.module-perm').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    var module = this.dataset.module;
                    var master = document.querySelector('.module-select-all[data-module="' +
                        module + '"]');
                    if (master) syncMasterState(master);
                });
            });

            // Synchronise l'état visuel de la checkbox maître (checked / indeterminate / unchecked)
            function syncMasterState(masterCb) {
                var module = masterCb.dataset.module;
                var children = document.querySelectorAll('.module-perm[data-module="' + module + '"]');
                var total = children.length;
                var checked = 0;
                children.forEach(function(c) {
                    if (c.checked) checked++;
                });

                if (checked === 0) {
                    masterCb.checked = false;
                    masterCb.indeterminate = false;
                } else if (checked === total) {
                    masterCb.checked = true;
                    masterCb.indeterminate = false;
                } else {
                    masterCb.checked = false;
                    masterCb.indeterminate = true;
                }
            }
        });
    </script>
@endsection
