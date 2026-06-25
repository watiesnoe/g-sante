@extends('layouts.app')

@section('titre', 'Liste des Consultations')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('consultations.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-stethoscope me-1"></i> Liste des Consultations
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter w-100" id="consultations-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Médecin</th>
                                <th>Ticket</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('partials.modals.transfert')
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let filterStockCritique = false;

            const ROUTES = {
                index: '{{ route('medicaments.index') }}',
                store: '{{ route('medicaments.store') }}',
                show: '{{ rtrim(url('medicaments'), '/') }}/:id',
                update: '{{ rtrim(url('medicaments'), '/') }}/:id',
                destroy: '{{ rtrim(url('medicaments'), '/') }}/:id',
            };

            // 1. Initialisation native DataTables
            var table = $('#medicamentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: ROUTES.index,
                    data: function(d) {
                        d.famille_id = $('#filterFamille').val();
                        d.stock_critique = filterStockCritique ? 1 : 0;
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nom',
                        name: 'nom'
                    },
                    {
                        data: 'unite',
                        name: 'unite'
                    },
                    {
                        data: 'famille',
                        name: 'famille'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'stock_min',
                        name: 'stock_min'
                    },
                    {
                        data: 'prix_achat',
                        name: 'prix_achat'
                    },
                    {
                        data: 'prix_vente',
                        name: 'prix_vente'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],
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
                pagingType: 'simple_numbers',
                createdRow: function(row, data, dataIndex) {
                    if (data.stock !== null && data.stock_min !== null && parseInt(data.stock) <=
                        parseInt(data.stock_min)) {
                        $(row).addClass('table-danger');
                    }
                }
            });

            // ── 🎯 ACTION : NOUVEAU MÉDICAMENT (OUVERTURE MODAL) ─────────────────
            $('#btnAdd').on('click', function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('➕ Ajouter un Médicament');
                $('#btnSave').prop('disabled', false).show();
                $('#crudForm input, #crudForm select, #crudForm textarea').prop('disabled', false);
                $('#crudModal').modal('show');
            });

            // ── 🎯 ACTION : VOIR (DETAILS) ──────────────────────────────────────
            $(document).on('click', '.view', function() {
                let id = $(this).data('id');
                let url = ROUTES.show.replace(':id', id);

                $.get(url, function(data) {
                    $('#modalTitle').text('🔍 Détails du Médicament');
                    mapDataToForm(data);
                    // Désactiver les champs pour la simple visualisation
                    $('#crudForm input, #crudForm select, #crudForm textarea').prop('disabled',
                        true);
                    $('#btnSave').hide();
                    $('#crudModal').modal('show');
                });
            });

            // ── 🎯 ACTION : MODIFIER (EDIT) ─────────────────────────────────────
            $(document).on('click', '.edit', function() {
                let id = $(this).data('id');
                let url = ROUTES.show.replace(':id', id); // Charge les données actuelles

                $.get(url, function(data) {
                    $('#modalTitle').text('✏️ Modifier le Médicament');
                    mapDataToForm(data);
                    $('#crudForm input, #crudForm select, #crudForm textarea').prop('disabled',
                        false);
                    $('#btnSave').show();
                    $('#crudModal').modal('show');
                });
            });

            // ── 🎯 ACTION : ENREGISTRER (STORE & UPDATE) ────────────────────────
            $('#crudForm').on('submit', function(e) {
                e.preventDefault();
                let id = $('#id').val();
                let url = id ? ROUTES.update.replace(':id', id) : ROUTES.store;
                let method = id ? 'PUT' : 'POST';

                let formData = $(this).serialize();
                if (id) {
                    formData +=
                    '&_method=PUT'; // Gère la méthode PUT de Laravel via un formulaire sérialisé
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#crudModal').modal('hide');
                        table.ajax.reload(null,
                        false); // Recharge sans réinitialiser la pagination
                        if (typeof Dashmix !== 'undefined') {
                            Dashmix.helpers('jq-notify', {
                                type: 'success',
                                icon: 'fa fa-check me-1',
                                message: response.message
                            });
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        alert("Une erreur est survenue lors de l'enregistrement.");
                    }
                });
            });

            // ── 🎯 ACTION : SUPPRIMER (DELETE) ──────────────────────────────────
            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                let url = ROUTES.destroy.replace(':id', id);

                if (confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?')) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            table.ajax.reload(null, false);
                            alert(response.message);
                        },
                        error: function() {
                            alert("Impossible de supprimer ce médicament.");
                        }
                    });
                }
            });

            // Fonction utilitaire pour remplir le formulaire modal
            function mapDataToForm(data) {
                $('#id').val(data.uuid || data.id); // Utilise l'UUID s'il existe
                $('#nom').val(data.nom);
                $('#description').val(data.description);
                $('#stock').val(data.stock);
                $('#stock_min').val(data.stock_min);
                $('#prix_achat').val(data.prix_achat);
                $('#prix_vente').val(data.prix_vente);
                $('#unite_id').val(data.unite_id);
                $('#famille_id').val(data.famille_id);
            }

            // ── FILTRES ET CHECKBOXES ───────────────────────────────────────────
            $('#filterFamille').on('change', function() {
                table.draw();
            });

            $('#btnStockCritique').on('click', function() {
                filterStockCritique = !filterStockCritique;
                if (filterStockCritique) {
                    $(this).css({
                        'transform': 'scale(0.98)',
                        'box-shadow': '0 0 10px rgba(220, 53, 69, 0.5)'
                    }).addClass('border border-light');
                    $('#badgeCritiqueActive').removeClass('d-none');
                } else {
                    $(this).css({
                        'transform': '',
                        'box-shadow': ''
                    }).removeClass('border border-light');
                    $('#badgeCritiqueActive').addClass('d-none');
                }
                table.draw();
            });

            $('#checkAll').on('click', function() {
                $('.medicament-checkbox').prop('checked', this.checked);
                updateBulkButton();
            });

            $(document).on('change', '.medicament-checkbox', function() {
                updateBulkButton();
            });

            function updateBulkButton() {
                var count = $('.medicament-checkbox:checked').length;
                $('#selectedCount').text(count);
                $('#btnBulkCommand').toggleClass('d-none', count === 0);
            }

            $('#btnBulkCommand').on('click', function() {
                var ids = $('.medicament-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                if (ids.length) {
                    var container = $('#selectedMedicamentsInputs').empty();
                    ids.forEach(function(id) {
                        container.append('<input type="hidden" name="medicament_ids[]" value="' +
                            id + '">');
                    });
                    $('#bulkCommandForm').submit();
                }
            });
        });
    </script>
@endsection
