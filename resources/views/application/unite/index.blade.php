@extends('layouts.app')

@section('titre', 'Gestion des Unités')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </button>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des Unités</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="uniteTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nom</th>
                                        <th>Date création</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="crudModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajouter une unité</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="btnSave">Enregistrer</button>
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
        $(function() {
            // Configuration Ajax globale pour le token CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Initialisation de DataTable
            let table = $('#uniteTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('unites.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nom', name: 'nom' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12'i><'col-sm-12'p>>",
                pagingType: 'simple_numbers'
            });

            // 2. Gestion de l'ouverture du Modal pour Ajouter
            $('#btnAdd').click(function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('Ajouter une unité');
                $('#crudModal').modal('show');
            });

            // 3. Gestion de la Sauvegarde (Ajout ou Modification)
            $('#crudForm').submit(function(e) {
                e.preventDefault();
                
                let id = $('#id').val();
                // Si ID existe, on utilise la méthode PUT standard de Laravel, sinon POST
                let url = id ? "{{ url('unites') }}/" + id : "{{ route('unites.store') }}";
                let type = id ? "PUT" : "POST";
                let data = $(this).serialize();

                $.ajax({
                    url: url,
                    type: type,
                    data: data,
                    success: function(response) {
                        $('#crudModal').modal('hide');
                        table.ajax.reload(null, false); // Recharge sans réinitialiser la pagination
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message || 'Opération réussie',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMsg = '';
                        if (errors) {
                            for (let key in errors) {
                                errorMsg += errors[key][0] + '<br>';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            html: errorMsg || 'Une erreur est survenue lors de l\'enregistrement.'
                        });
                    }
                });
            });

            // 4. Gestion de la Modification (.edit)
            $(document).on('click', '.edit', function() {
                let id = $(this).data('id'); 
                let url = "{{ url('unites') }}/" + id + "/edit";

                $.get(url, function(data) {
                    $('#modalTitle').text('Modifier l\'unité');
                    $('#id').val(data.id);
                    $('#nom').val(data.nom);
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les données pour la modification.', 'error');
                });
            });

            // 5. Gestion de la Vue des détails (.view)
            $(document).on('click', '.view', function() {
                let id = $(this).data('id');
                let url = "{{ url('unites') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Détails de l\'unité');
                    $('#id').val('');
                    
                    $('#nom').val(data.nom).prop('disabled', true);
                    
                    $('#btnSave').hide();
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les détails.', 'error');
                });
            });

            // Réinitialiser les inputs au masquage du modal
            $('#crudModal').on('hidden.bs.modal', function () {
                $('#nom').prop('disabled', false);
                $('#btnSave').show();
            });

            // 6. Suppression (.delete)
            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                let url = "{{ url('unites') }}/" + id;

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette unité sera supprimée définitivement !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire('Supprimé !', response.message || 'L\'unité a été supprimée.', 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Impossible de supprimer cette unité.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection