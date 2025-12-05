<table class="table table-hover align-middle">
    <thead class="table-light">
    <tr>
        <th width="60">Photo</th>
        <th>Utilisateur</th>
        <th>Contact</th>
        <th>Rôle</th>
        <th>Statut</th>
        <th>Date création</th>
        <th width="150" class="text-center">Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($users as $user)
        <tr>
            <td>
                <img src="{{ $user->photo ? asset('storage/'.$user->photo) : asset('images/default-user.png') }}"
                     class="user-avatar rounded-circle" alt="Avatar">
            </td>
            <td>
                <div class="fw-semibold">{{ $user->prenom }} {{ $user->nom }}</div>
                <small class="text-muted">{{ $user->email }}</small>
            </td>
            <td>
                <div>{{ $user->telephone ?? '-' }}</div>
                <small class="text-muted">{{ $user->adresse ? Str::limit($user->adresse, 30) : '-' }}</small>
            </td>
            <td>
                    <span class="badge bg-{{ $user->role == 'admin' ? 'primary' : ($user->role == 'medecin' ? 'success' : ($user->role == 'secretaire' ? 'info' : 'secondary')) }}">
                        {{ ucfirst($user->role) }}
                    </span>
            </td>
            <td>
                <div class="dropdown">
                        <span class="badge bg-{{ $user->statut == 'actif' ? 'success' : ($user->statut == 'inactif' ? 'danger' : 'warning') }} dropdown-toggle cursor-pointer"
                              data-bs-toggle="dropdown">
                            {{ ucfirst($user->statut) }}
                        </span>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item change-status-btn" href="#" data-id="{{ $user->id }}" data-status="actif">
                                <i class="bi bi-check-circle text-success me-2"></i>Actif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item change-status-btn" href="#" data-id="{{ $user->id }}" data-status="inactif">
                                <i class="bi bi-x-circle text-danger me-2"></i>Inactif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item change-status-btn" href="#" data-id="{{ $user->id }}" data-status="suspendu">
                                <i class="bi bi-pause-circle text-warning me-2"></i>Suspendu
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
            <td>
                <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                <br>
                <small class="text-muted">{{ $user->created_at->format('H:i') }}</small>
            </td>
            <td class="text-center action-buttons">
                <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info" title="Voir">
                    <i class="bi bi-eye"></i>
                </a>
                <button class="btn btn-sm btn-outline-primary edit-user-btn" data-id="{{ $user->id }}" title="Modifier">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-user-btn" data-id="{{ $user->id }}" title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="text-muted">
                    <i class="bi bi-people display-4 d-block mb-2"></i>
                    Aucun utilisateur trouvé
                </div>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>

@if($users->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted">
            Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }} résultats
        </div>
        <nav>
            {{ $users->links() }}
        </nav>
    </div>
@endif
