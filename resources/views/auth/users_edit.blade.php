<form id="editUserForm" action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-md-6">
            <label>Prénom</label>
            <input type="text" name="prenom" class="form-control" value="{{ $user->prenom }}" required>
        </div>
        <div class="col-md-6">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" value="{{ $user->nom }}" required>
        </div>
        <div class="col-md-6">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
        </div>
        <div class="col-md-6">
            <label>Téléphone</label>
            <input type="tel" name="telephone" class="form-control phone-input" value="{{ $user->telephone }}">
        </div>
        <div class="col-md-6">
            <label>Adresse</label>
            <input type="text" name="adresse" class="form-control" value="{{ $user->adresse }}">
        </div>
        <div class="col-md-6">
            <label>Rôle</label>
            <select name="role" class="form-select" required>
                <option value="">Sélectionnez un rôle</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ $userRole === $role->name ? 'selected' : '' }}>{{ $role->libelle ?? $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label>Statut</label>
            <select name="statut" class="form-select">
                @foreach ($statuts as $key => $label)
                    <option value="{{ $key }}" {{ $user->statut === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-12">
            <label>Photo</label>
            <input type="file" name="photo" class="form-control">
            @if($user->photo)
                <img src="{{ asset('storage/'.$user->photo) }}" class="mt-2 rounded-circle" width="60" height="60">
            @endif
        </div>
        
        <div class="col-md-12 mt-4">
            <h5 class="border-bottom pb-2">Permissions Spécifiques (Optionnel)</h5>
            <div class="row">
                @if(isset($permissions))
                    @foreach ($permissions as $module => $modulePermissions)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm border-0 bg-light">
                                <div class="card-header bg-white fw-bold text-capitalize border-bottom-0 py-2">
                                    {{ $module }}
                                </div>
                                <div class="card-body p-2">
                                    @foreach($modulePermissions as $permission)
                                        <div class="form-check form-switch mb-1">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" 
                                                id="edit_perm_{{ $permission->id }}"
                                                {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}>
                                            <label class="form-check-label" style="font-size: 0.85rem;" for="edit_perm_{{ $permission->id }}">
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
            <small class="text-muted"><i class="fa fa-info-circle"></i> En général, il suffit d'assigner un rôle. Ne cochez des permissions ici que pour donner des droits exceptionnels à cet utilisateur spécifique.</small>
        </div>
    </div>

    <div class="mt-3 text-end">
        <button type="submit" class="btn btn-sm btn-primary">💾 Mettre à jour</button>
    </div>
</form>
