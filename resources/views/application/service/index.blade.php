@extends('layouts.app')

@section('titre', '🛏️ Gestion des Structures')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">

                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Liste des structure</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('export.model', 'services') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                                <i class="fa fa-file-excel me-1"></i> Exporter
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="services" data-label="Structures" title="Importer depuis Excel">
                                <i class="fa fa-file-import me-1"></i> Importer
                            </button>
                            <button class="btn btn-sm btn-primary" id="btnAdd">
                                <i class="fa fa-plus me-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        <table id="servicesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th width="15%">Date</th>
                                    <th width="20%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="crudModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nouvelle structure</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom de la structure</label>
                                <input type="text" name="nom" id="nom" class="form-control"
                                    placeholder="Ex: Clinique du Soleil" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"
                                    placeholder="Informations complémentaires"></textarea>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="btnSave">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.partials.import_modal')
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Configuration Ajax globale pour le token CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Initialisation de DataTable avec Ajax
            var table = $('#servicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('services.index') }}",
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    // url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                }
            });

            // 2. Gestion de l'ouverture du Modal pour Ajouter
            $('#btnAdd').click(function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('Nouvelle structure');
                $('#crudModal').modal('show');
            });

            // 3. Gestion de la Sauvegarde (Ajout ou Modification)
            $('#crudForm').submit(function(e) {
                e.preventDefault();
                
                var id = $('#id').val();
                // Si l'ID (UUID) existe, c'est une mise à jour, sinon une création
                var url = id ? "{{ url('services') }}/" + id : "{{ route('services.store') }}";
                var type = id ? "PUT" : "POST";
                var data = $(this).serialize();

                $.ajax({
                    url: url,
                    type: type,
                    data: data,
                    success: function(response) {
                        $('#crudModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMsg = '';
                        for (var key in errors) {
                            errorMsg += errors[key][0] + '\n';
                        }
                        Swal.fire('Erreur', errorMsg || 'Une erreur est survenue.', 'error');
                    }
                });
            });

            // 4. Gestion de la Modification (Récupération des données)
            $('body').on('click', '.edit', function() {
                var id = $(this).data('id');
                var url = "{{ url('services') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Modifier la structure');
                    // Remplissage des données séparées
                    $('#id').val(data.uuid || data.id);
                    $('#nom').val(data.nom);
                    $('#description').val(data.description);
                    $('#crudModal').modal('show');
                });
            });

            // 5. Gestion de la Vue des détails (Lecture seule)
            $('body').on('click', '.view', function() {
                var id = $(this).data('id');
                var url = "{{ url('services') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Détails de la structure');
                    $('#id').val(''); // On vide pour éviter une modification accidentelle
                    $('#nom').val(data.nom).prop('disabled', true);
                    $('#description').val(data.description).prop('disabled', true);
                    $('#btnSave').hide(); // Cacher le bouton enregistrer
                    $('#crudModal').modal('show');
                });
            });

            // Réinitialiser les champs à la fermeture du modal pour enlever le disabled
            $('#crudModal').on('hidden.bs.modal', function () {
                $('#nom').prop('disabled', false);
                $('#description').prop('disabled', false);
                $('#btnSave').show();
            });

            // 6. Gestion de la Suppression
            $('body').on('click', '.delete', function() {
                var id = $(this).data('id');
                var url = "{{ url('services') }}/" + id;

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimé!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Impossible de supprimer cette structure.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
