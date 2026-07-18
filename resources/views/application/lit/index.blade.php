@extends('layouts.app')

@section('titre', '🛏️ Gestion des lits')

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Liste des lits</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('export.model', 'lits') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                                <i class="fa fa-file-excel me-1"></i> Exporter
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="lits" data-label="Lits">
                                <i class="fa fa-file-import me-1"></i> Importer
                            </button>
                            <button class="btn btn-sm btn-primary" id="btnAdd">
                                <i class="fa fa-plus me-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter w-100" id="lits-table">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Salle</th>
                                        <th>Statut</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.partials.import_modal')

        <div class="modal fade" id="crudModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajouter un lit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            
                            <div class="mb-3">
                                <label class="form-label" entertainment-id="numero">Numéro du lit</label>
                                <input type="text" name="numero" id="numero" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label" entertainment-id="salle_id">Salle</label>
                                <select name="salle_id" id="salle_id" class="form-control" required>
                                    <option value="">Sélectionner une salle</option>
                                    @foreach($salles as $salle)
                                        <option value="{{ $salle->id }}">{{ $salle->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" entertainment-id="statut">Statut</label>
                                <select name="statut" id="statut" class="form-control" required>
                                    <option value="Libre">Libre</option>
                                    <option value="Occupé">Occupé</option>
                                    <option value="Maintenance">Maintenance</option>
                                </select>
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
            let table = $('#lits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lits.index') }}",
                columns: [
                    { data: 'numero', name: 'numero' },
                    { data: 'salle', name: 'salle' }, 
                    { data: 'statut', name: 'statut' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                language: {
                    // url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
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
                $('#modalTitle').text('Nouveau Lit');
                $('#crudModal').modal('show');
            });

            // 3. Gestion de la Sauvegarde (Ajout ou Modification)
            $('#crudForm').submit(function(e) {
                e.preventDefault();
                
                let id = $('#id').val();
                // Si ID existe, on utilise la méthode PUT standard de Laravel, sinon POST
                let url = id ? "{{ url('lits') }}/" + id : "{{ route('lits.store') }}";
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

            // 4. Gestion de la Modification (.edit) -> Correction de l'URL (/edit)
            $(document).on('click', '.edit', function() {
                let id = $(this).data('id'); 
                let url = "{{ url('lits') }}/" + id + "/edit"; // Ajout requis pour contourner l'absence de 'show'

                $.get(url, function(data) {
                    $('#modalTitle').text('Modifier le Lit');
                    $('#id').val(data.uuid || data.id);
                    $('#numero').val(data.numero);
                    $('#salle_id').val(data.salle_id);
                    $('#statut').val(data.statut);
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les données pour la modification.', 'error');
                });
            });

            // 5. Gestion de la Vue des détails (.view) -> Nécessite la méthode show() dans le contrôleur
            $(document).on('click', '.view', function() {
                let id = $(this).data('id');
                let url = "{{ url('lits') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Détails du Lit');
                    $('#id').val('');
                    
                    $('#numero').val(data.numero).prop('disabled', true);
                    $('#salle_id').val(data.salle_id).prop('disabled', true);
                    $('#statut').val(data.statut).prop('disabled', true);
                    
                    $('#btnSave').hide();
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les détails (Avez-vous bien créé la méthode show() ?).', 'error');
                });
            });

            // Réinitialiser les inputs au masquage du modal
            $('#crudModal').on('hidden.bs.modal', function () {
                $('#numero').prop('disabled', false);
                $('#salle_id').prop('disabled', false);
                $('#statut').prop('disabled', false);
                $('#btnSave').show();
            });

            // 6. Suppression (.delete)
            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                let url = "{{ url('lits') }}/" + id;

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Ce lit sera supprimé définitivement !",
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
                                Swal.fire('Supprimé !', response.message || 'Le lit a été supprimé.', 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Impossible de supprimer ce lit.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection