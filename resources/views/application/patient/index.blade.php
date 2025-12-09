@extends('layouts.app')

@section('titre', 'Liste des Patients')

@section('content')
    <!-- Page Content -->
    <div class="container mt-4">
        <!-- Quick Actions -->
  
        <!-- END Quick Actions -->

        <!-- Patients Table -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-list me-1"></i> Liste des Patients
                </h3>
                <div class="block-options">
                    <div class="dropdown">
                        <button type="button" class="btn-block-option" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:void(0)" onclick="$('#patients-table').DataTable().ajax.reload();">
                                <i class="fa fa-refresh me-1"></i> Actualiser
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('patients.create') }}">
                                <i class="fa fa-plus me-1"></i> Ajouter un patient
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="block-content block-content-full">
                <table id="patients-table" class="table table-bordered table-striped table-vcenter js-dataTable-responsive w-100">
                    <thead>
                    <tr>
                        <th class="text-center" style="width: 80px;">#</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th class="d-none d-sm-table-cell" style="width: 100px;">Sexe</th>
                        <th class="d-none d-sm-table-cell" style="width: 150px;">Téléphone</th>
                        <th class="d-none d-md-table-cell" style="width: 120px;">Date Création</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Les données seront chargées via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- END Patients Table -->
    </div>
    <!-- END Page Content -->
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            initializeDataTable();
        });

        function initializeDataTable() {
            var table = $('#patients-table');

            // Détruire l'instance existante
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
                table.empty(); // Vider le contenu
            }

            // Reconstruire le thead si nécessaire
            if (table.find('thead').length === 0) {
                table.append(`
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 80px;">#</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th class="d-none d-sm-table-cell" style="width: 100px;">Sexe</th>
                            <th class="d-none d-sm-table-cell" style="width: 150px;">Téléphone</th>
                            <th class="d-none d-md-table-cell" style="width: 120px;">Date Création</th>
                            <th class="text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `);
            }

            // Initialiser DataTable
            table.DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('patients.index') }}",
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    { data: 'nom', name: 'nom' },
                    { data: 'prenom', name: 'prenom' },
                    {
                        data: 'genre',
                        name: 'genre',
                        className: 'd-none d-sm-table-cell text-center'
                    },
                    {
                        data: 'telephone',
                        name: 'telephone',
                        className: 'd-none d-sm-table-cell'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        className: 'd-none d-md-table-cell',
                        render: function(data) {
                            return data ? new Date(data).toLocaleDateString('fr-FR') : '';
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                language: {
                    "emptyTable": "Aucune donnée disponible",
                    "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                    "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
                    "infoFiltered": "(filtré depuis _MAX_ entrées totales)",
                    "lengthMenu": "Afficher _MENU_ entrées",
                    "loadingRecords": "Chargement...",
                    "processing": "Traitement...",
                    "search": "Rechercher:",
                    "zeroRecords": "Aucun enregistrement correspondant trouvé",
                    "paginate": {
                        "first": "Premier",
                        "last": "Dernier",
                        "next": "Suivant",
                        "previous": "Précédent"
                    }
                },
                pageLength: 15,
                order: [[5, 'desc']]
            });
        }

        function refreshTable() {
            $('#patients-table').DataTable().ajax.reload(null, false);
        }
    </script>
@endsection
