@extends('layouts.app')

@section('titre', 'Gestion des Fournisseurs')

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


            <!-- Contenu principal -->
            <div class="col-xl-12 col-lg-12">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des Fournisseurs</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="fournisseurTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Contact</th>
                                        <th>Adresse</th>
                                        <th width="100">Actions</th>
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
                        <h5 class="modal-title" id="modalTitle">Ajouter un fournisseur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" id="nom" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" id="contact" name="contact" class="form-control" placeholder="Téléphone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" id="adresse" name="adresse" class="form-control">
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
        $(document).ready(function() {
            // 🎯 1. Élimination des alertes i18n intrusives en production
            $.fn.dataTable.ext.errMode = 'throw';

            // 🎯 2. Définition des routes de l'API Resource Fournisseurs
            const ROUTES = {
                index: "{{ route('fournisseurs.index') }}",
                store: "/fournisseurs",
                show: "/fournisseurs/:id",
                update: "/fournisseurs/:id",
                destroy: "/fournisseurs/:id",
            };

            // 🎯 3. Initialisation native de DataTables
            var table = $('#fournisseurTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: ROUTES.index,
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'contact', name: 'contact' },
                    { data: 'adresse', name: 'adresse' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                // Traduction française intégrée en local
                language: {
                    emptyTable: "Aucune donnée disponible dans le tableau",
                    info: "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
                    infoEmpty: "Affichage de l'élément 0 à 0 sur 0 élément",
                    infoFiltered: "(filtré de _MAX_ éléments au total)",
                    lengthMenu: "Afficher _MENU_ éléments",
                    loadingRecords: "Chargement...",
                    processing: "Traitement...",
                    search: "Rechercher :",
                    zeroRecords: "Aucun élément correspondant trouvé",
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

            // ── 🛠️ ACTION : CLIC SUR "AJOUTER" ─────────────────────────────────
            $('#btnAdd').on('click', function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('➕ Ajouter un fournisseur');
                $('#btnSave').show();
                $('#crudForm input').prop('disabled', false);
                $('#crudModal').modal('show');
            });

            // ── 🛠️ ACTION : CLIC SUR "VOIR" (S'il est présent dans vos actions) ──
            $(document).on('click', '.view', function() {
                let id = $(this).data('id');
                let url = ROUTES.show.replace(':id', id);

                $.get(url, function(data) {
                    $('#modalTitle').text('🔍 Détails du Fournisseur');
                    mapDataToForm(data);
                    $('#crudForm input').prop('disabled', true);
                    $('#btnSave').hide();
                    $('#crudModal').modal('show');
                });
            });

            // ── 🛠️ ACTION : CLIC SUR "MODIFIER" (EDIT) ─────────────────────────
            $(document).on('click', '.edit', function() {
                let id = $(this).data('id');
                let url = ROUTES.show.replace(':id', id); 

                $.get(url, function(data) {
                    $('#modalTitle').text('✏️ Modifier le fournisseur');
                    mapDataToForm(data);
                    $('#crudForm input').prop('disabled', false);
                    $('#btnSave').show();
                    $('#crudModal').modal('show');
                });
            });

            // ── 🛠️ ACTION : SOUMISSION DU FORMULAIRE (AJOUT / MODIFICATION) ────
            $('#crudForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = id ? ROUTES.update.replace(':id', id) : ROUTES.store;
                
                let formData = $(this).serialize();
                if (id) {
                    formData += '&_method=PUT'; // Support natif pour la route PUT de Laravel
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#crudModal').modal('hide');
                        table.ajax.reload(null, false); // Recharge le tableau sans perdre la page courante
                        if (typeof Dashmix !== 'undefined') {
                            Dashmix.helpers('jq-notify', {type: 'success', icon: 'fa fa-check me-1', message: response.message || 'Opération réussie ✅'});
                        } else {
                            alert(response.message || 'Opération réussie ✅');
                        }
                    },
                    error: function(xhr) {
                        alert("Une erreur est survenue lors de l'enregistrement.");
                    }
                });
            });

            // ── 🛠️ ACTION : CLIC SUR "SUPPRIMER" (DELETE) ──────────────────────
            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                let url = ROUTES.destroy.replace(':id', id);

                if (confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            alert(response.message || 'Fournisseur supprimé avec succès 🗑️');
                        },
                        error: function() {
                            alert("Impossible de supprimer ce fournisseur.");
                        }
                    });
                }
            });

            // Remplissage des données reçues dans les champs de la modale
            function mapDataToForm(data) {
                $('#id').val(data.uuid || data.id); // S'adapte si vous utilisez des ID numériques ou des UUID
                $('#nom').val(data.nom);
                $('#contact').val(data.contact);
                $('#adresse').val(data.adresse);
            }
        });
    </script>
@endsection