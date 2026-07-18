@extends('layouts.app')

@section('titre', 'Liste des Utilisateurs')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Gestion des Utilisateurs</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('export.model', 'users') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                                <i class="fa fa-file-excel me-1"></i> Exporter
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="users" data-label="Utilisateurs">
                                <i class="fa fa-file-import me-1"></i> Importer
                            </button>
                            <button type="button" class="btn btn-sm btn-alt-secondary" id="reload-table" title="Rafraîcher">
                                <i class="fa fa-sync"></i>
                            </button>
                            <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus me-1"></i> Nouvel Utilisateur
                            </a>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="users-datatable" class="table table-bordered table-striped table-vcenter w-100">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Date création</th>
                                        <th width="120" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @include('layouts.partials.import_modal')

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
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-sm btn-danger" id="confirm-delete">Supprimer</button>
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
                    { data: 'utilisateur', name: 'utilisateur' },
                    { data: 'role', name: 'role' },
                    { data: 'statut', name: 'statut' },
                    { data: 'date_creation', name: 'date_creation' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers'
            });

            // Les anciens scripts de soumission AJAX des modales de création et d'édition ont été supprimés.
            // L'édition et la création se font maintenant sur des pages dédiées.
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
