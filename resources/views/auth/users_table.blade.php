@extends('layouts.app')

@section('titre', 'Liste des Utilisateurs')

@section('content')
    <div class="container-fluid py-4">
        <!-- En-tête avec statistiques -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="fw-bold text-dark mb-1">👥 Gestion des Utilisateurs</h3>
                                <p class="text-muted mb-0">Gérez tous les utilisateurs du système</p>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                                <i class="bi bi-plus-circle me-2"></i>Nouvel Utilisateur
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0" id="total-users">{{ $stats['total'] ?? 0 }}</h4>
                                <small>Total Utilisateurs</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-people fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0" id="total-medecins">{{ $stats['medecins'] ?? 0 }}</h4>
                                <small>Médecins</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-heart-pulse fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0" id="total-secretaires">{{ $stats['secretaires'] ?? 0 }}</h4>
                                <small>Secrétaires</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-badge fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0" id="total-admins">{{ $stats['admins'] ?? 0 }}</h4>
                                <small>Administrateurs</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-shield-check fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card bg-secondary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0" id="total-actifs">{{ $stats['actifs'] ?? 0 }}</h4>
                                <small>Utilisateurs Actifs</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="fw-bold mb-0" id="total-inactifs">{{ $stats['inactifs'] ?? 0 }}</h4>
                                <small>Utilisateurs Inactifs</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-x-circle fs-2 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Recherche</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="search-input" class="form-control border-start-0" placeholder="Nom, email, téléphone...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Rôle</label>
                        <select id="role-filter" class="form-select">
                            <option value="">Tous les rôles</option>
                            <option value="admin">Administrateur</option>
                            <option value="medecin">Médecin</option>
                            <option value="secretaire">Secrétaire</option>
                            <option value="client">Client</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Statut</label>
                        <select id="status-filter" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tri par</label>
                        <select id="sort-by" class="form-select">
                            <option value="created_at">Date création</option>
                            <option value="name">Nom</option>
                            <option value="email">Email</option>
                            <option value="role">Rôle</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Ordre</label>
                        <select id="sort-order" class="form-select">
                            <option value="desc">Décroissant</option>
                            <option value="asc">Croissant</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button id="reset-filters" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des utilisateurs -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0">Liste des Utilisateurs</h6>
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-2" id="users-count">{{ $users->total() }} utilisateur(s)</span>
                        <div class="spinner-border spinner-border-sm text-primary d-none" id="loading-spinner" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive" id="users-table-container">
                    @include('users.partials.users_table')
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de création d'utilisateur -->
    @include('users.partials.create_modal')

    <!-- Modal d'édition d'utilisateur -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-user-form">
                    <!-- Le formulaire d'édition sera chargé ici via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Toutes les données associées à cet utilisateur seront perdues.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .stat-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
        .table-hover tbody tr {
            transition: background-color 0.15s ease;
        }
        .action-buttons .btn {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        .action-buttons .btn:hover {
            opacity: 1;
        }
        .badge {
            font-size: 0.75em;
        }
    </style>
@endpush

@section('scripts')
    <script>
        $(document).ready(function() {
            let currentPage = 1;
            let deleteUserId = null;

            // Chargement initial des données
            loadUsers();

            // Recherche en temps réel avec debounce
            let searchTimeout;
            $('#search-input').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadUsers();
                }, 500);
            });

            // Filtres
            $('#role-filter, #status-filter, #sort-by, #sort-order').on('change', function() {
                currentPage = 1;
                loadUsers();
            });

            // Réinitialisation des filtres
            $('#reset-filters').on('click', function() {
                $('#search-input').val('');
                $('#role-filter').val('');
                $('#status-filter').val('');
                $('#sort-by').val('created_at');
                $('#sort-order').val('desc');
                currentPage = 1;
                loadUsers();
            });

            // Pagination
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                currentPage = $(this).attr('href').split('page=')[1];
                loadUsers();
            });

            // Chargement des utilisateurs via AJAX
            function loadUsers() {
                showLoading();

                const filters = {
                    search: $('#search-input').val(),
                    role: $('#role-filter').val(),
                    statut: $('#status-filter').val(),
                    sort_by: $('#sort-by').val(),
                    sort_order: $('#sort-order').val(),
                    page: currentPage
                };

                $.ajax({
                    url: '{{ route("users.index") }}',
                    type: 'GET',
                    data: filters,
                    success: function(response) {
                        $('#users-table-container').html(response.html);
                        updateStats(response.stats);
                        updateUsersCount(response.total);
                        hideLoading();
                    },
                    error: function(xhr) {
                        console.error('Erreur lors du chargement des utilisateurs:', xhr);
                        hideLoading();
                        showError('Erreur lors du chargement des données');
                    }
                });
            }

            // Mise à jour des statistiques
            function updateStats(stats) {
                if (stats) {
                    $('#total-users').text(stats.total || 0);
                    $('#total-medecins').text(stats.medecins || 0);
                    $('#total-secretaires').text(stats.secretaires || 0);
                    $('#total-admins').text(stats.admins || 0);
                    $('#total-actifs').text(stats.actifs || 0);
                    $('#total-inactifs').text(stats.inactifs || 0);
                }
            }

            // Mise à jour du compteur d'utilisateurs
            function updateUsersCount(total) {
                const countText = total === 1 ? '1 utilisateur' : `${total} utilisateurs`;
                $('#users-count').text(countText);
            }

            // Affichage du chargement
            function showLoading() {
                $('#loading-spinner').removeClass('d-none');
            }

            function hideLoading() {
                $('#loading-spinner').addClass('d-none');
            }

            // Affichage des erreurs
            function showError(message) {
                // Vous pouvez utiliser Toast ou SweetAlert ici
                console.error(message);
            }

            // Édition d'utilisateur
            $(document).on('click', '.edit-user-btn', function() {
                const userId = $(this).data('id');

                $.ajax({
                    url: `/users/${userId}/edit`,
                    type: 'GET',
                    success: function(response) {
                        $('#edit-user-form').html(response);
                        $('#editUserModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('Erreur lors du chargement du formulaire:', xhr);
                        showError('Erreur lors du chargement du formulaire');
                    }
                });
            });

            // Soumission du formulaire d'édition
            $(document).on('submit', '#edit-user-form form', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#editUserModal').modal('hide');
                        loadUsers();
                        showSuccess('Utilisateur mis à jour avec succès');
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            displayFormErrors('#edit-user-form', errors);
                        } else {
                            showError('Erreur lors de la mise à jour');
                        }
                    }
                });
            });

            // Suppression d'utilisateur
            $(document).on('click', '.delete-user-btn', function() {
                deleteUserId = $(this).data('id');
                $('#deleteUserModal').modal('show');
            });

            $('#confirm-delete').on('click', function() {
                if (!deleteUserId) return;

                $.ajax({
                    url: `/users/${deleteUserId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#deleteUserModal').modal('hide');
                        loadUsers();
                        showSuccess('Utilisateur supprimé avec succès');
                    },
                    error: function(xhr) {
                        $('#deleteUserModal').modal('hide');
                        showError('Erreur lors de la suppression');
                    }
                });
            });

            // Changement de statut
            $(document).on('click', '.change-status-btn', function() {
                const userId = $(this).data('id');
                const newStatus = $(this).data('status');

                $.ajax({
                    url: `/users/${userId}/status`,
                    type: 'PATCH',
                    data: {
                        statut: newStatus
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        loadUsers();
                        showSuccess('Statut mis à jour avec succès');
                    },
                    error: function(xhr) {
                        showError('Erreur lors du changement de statut');
                    }
                });
            });

            // Affichage des messages de succès
            function showSuccess(message) {
                // Utiliser Toast ou SweetAlert
                if (typeof Toast !== 'undefined') {
                    Toast.fire({
                        icon: 'success',
                        title: message
                    });
                } else {
                    alert(message);
                }
            }

            // Affichage des erreurs de formulaire
            function displayFormErrors(formSelector, errors) {
                $(`${formSelector} .is-invalid`).removeClass('is-invalid');
                $(`${formSelector} .invalid-feedback`).remove();

                for (const field in errors) {
                    const input = $(`${formSelector} [name="${field}"]`);
                    input.addClass('is-invalid');
                    input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
                }
            }
        });
    </script>
@endsection
