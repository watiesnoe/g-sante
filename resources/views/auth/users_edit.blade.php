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
            <input type="text" name="telephone" class="form-control" value="{{ $user->telephone }}">
        </div>
        <div class="col-md-6">
            <label>Adresse</label>
            <input type="text" name="adresse" class="form-control" value="{{ $user->adresse }}">
        </div>
        <div class="col-md-6">
            <label>Rôle</label>
            <select name="role" class="form-select">
                @foreach ($roles as $key => $label)
                    <option value="{{ $key }}" {{ $user->role === $key ? 'selected' : '' }}>{{ $label }}</option>
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
    </div>

    <div class="mt-3 text-end">
        <button type="submit" class="btn btn-primary">💾 Mettre à jour</button>
    </div>
</form>
