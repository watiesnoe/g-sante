@extends('layouts.app')

@section('titre', 'Liste des Utilisateurs')

@section('content')
    <div class="container-fluid py-4">

        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="fw-bold text-dark mb-1">👥 Gestion des Utilisateurs</h3>
                            <p class="text-muted mb-0">Gérez tous les utilisateurs du système avec DataTables</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="bi bi-plus-circle me-2"></i>Nouvel Utilisateur
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
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="export-csv"><i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV</button>
                            <button type="button" class="btn btn-outline-success btn-sm" id="export-excel"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
                            <button type="button" class="btn btn-outline-danger btn-sm" id="export-pdf"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="reload-table"><i class="bi bi-arrow-repeat"></i></button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="users-datatable" class="table table-hover align-middle w-100">
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
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Création -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Créer un nouvel utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Prénom</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Nom</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Téléphone</label>
                                <input type="text" name="telephone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Adresse</label>
                                <input type="text" name="adresse" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label>Rôle</label>
                                <select name="role" class="form-select" required>
                                    <option value="admin">Administrateur</option>
                                    <option value="medecin">Médecin</option>
                                    <option value="secretaire">Secrétaire</option>
                                    <option value="client">Client</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Mot de passe</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label>Confirmation mot de passe</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label>Photo</label>
                                <input type="file" name="photo" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Créer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edition -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier l'utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-user-form">

                </div>
            </div>
        </div>
    </div>

    <!-- Modal Suppression -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Confirmation de suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Attention :</strong> Toutes les données associées seront perdues.
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

@section('scripts')
    <script>
        $(function() {
            var table = $('#users-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('users.index') }}",
                columns: [
                    { data: 'photo', name: 'photo', orderable: false, searchable: false },
                    { data: 'utilisateur', name: 'utilisateur' },
                    { data: 'contact', name: 'contact', orderable: false },
                    { data: 'role', name: 'role' },
                    { data: 'statut', name: 'statut' },
                    { data: 'date_creation', name: 'date_creation' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            // ---------------------------
            // Création d'utilisateur
            // ---------------------------
            $('#createUserModal form').submit(function(e) {
                e.preventDefault();
                var form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#createUserModal').modal('hide');
                        form[0].reset();
                        table.ajax.reload();
                        alert(res.message || 'Utilisateur créé avec succès !');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message || 'Erreur lors de la création.');
                    }
                });
            });

            // ---------------------------
            // Edition d'utilisateur
            // ---------------------------
            // Clique sur le bouton Edit
// Ouverture du modal avec AJAX
            $(document).on('click', '.edit-user', function(e) {
                e.preventDefault();
                var id = $(this).data('id');

                console.log('Tentative de chargement utilisateur ID:', id); // Debug
                $.ajax({
                    url: "{{ route('users.edit', ':id') }}".replace(':id', id),
                    type: "GET",
                    success: function(res) {
                        console.log('✅ Contenu du modal reçu :', res);
                        $('#edit-user-form').html(res);
                        $('#editUserModal').modal('show');
                    },
                    error: function(xhr) {
                        console.error('❌ Erreur AJAX:', xhr.status, xhr.responseText);
                    }
                });

            });

// Soumission du formulaire d'édition via AJAX
            $('#editUserModal').on('submit', '#editUserForm', function(e) {
                e.preventDefault();
                var form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#editUserModal').modal('hide'); // ferme le modal
                        $('#users-datatable').DataTable().ajax.reload(); // recharge le tableau
                        Swal.fire('Succès', res.message, 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Erreur', xhr.responseJSON?.message || 'Une erreur est survenue.', 'error');
                    }
                });
            });


            // ---------------------------
            // Toggle statut
            // ---------------------------
            $(document).on('click', '.toggle-status', function(e) {
                e.preventDefault();

                var id = $(this).data('id');
                var row = table.row($(this).closest('tr')).data();
                var statutText = $(row.statut).text().trim().toLowerCase();
                var newStatus = statutText === 'actif' ? 'inactif' : 'actif';

                if (confirm("Voulez-vous vraiment changer le statut de cet utilisateur ?")) {
                    $.ajax({
                        url: "{{ url('users') }}/" + id + "/status",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            statut: newStatus
                        },
                        success: function(response) {
                            console.log('✅ Statut changé en :', newStatus);
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Succès',
                                    text: response.message,
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                table.ajax.reload();
                            } else {
                                Swal.fire('Erreur', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                        }
                    });
                }
            });



            // ---------------------------
            // Suppression d'utilisateur
            // ---------------------------
            $(document).on('click', '.delete-user', function(e) {
                e.preventDefault();
                if (!confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) return;

                var id = $(this).data('id');

                $.ajax({
                    url: "/users/" + id,
                    type: "POST",
                    data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        table.ajax.reload();
                        alert(res.message || 'Utilisateur supprimé avec succès !');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON.message || 'Erreur lors de la suppression.');
                    }
                });
            });

            // ---------------------------
            // Reload table
            // ---------------------------
            $('#reload-table').click(function() {
                table.ajax.reload();
            });
        });

    </script>
@endsection
