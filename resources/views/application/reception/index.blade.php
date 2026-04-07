@extends('layouts.app')

@section('titre', 'Réceptions')

@section('content')
    <div class="container mt-4">

            <!-- 🔷 HEADER -->
            <div class="block block-rounded">
                <div class="block-content block-content-full d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1">
                            <i class="fa fa-truck-loading text-primary me-2"></i> Réceptions
                        </h3>
                        <p class="text-muted mb-0">Gestion des réceptions de médicaments</p>
                    </div>

                    <a href="{{ route('receptions.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus-circle me-1"></i> Ajouter
                    </a>
                </div>
            </div>

            <!-- 🔔 ALERTES -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- 📊 TABLE -->
            <div class="block block-rounded">
                <div class="block-header">
                    <h3 class="block-title">
                        <i class="fa fa-list me-1"></i> Liste des réceptions
                    </h3>

                    <div class="block-options">
                        <button type="button" class="btn-block-option" data-action="refresh_toggle">
                            <i class="si si-refresh"></i>
                        </button>
                        <button type="button" class="btn-block-option" data-action="fullscreen_toggle">
                            <i class="si si-size-fullscreen"></i>
                        </button>
                    </div>
                </div>

                <div class="block-content">

                    <div class="table-responsive">
                        <table id="table-receptions" class="table table-hover table-striped table-bordered table-vcenter">
                            <thead class="table-dark">
                                <tr>
                                    <th>Référence</th>
                                    <th>Commande</th>
                                    <th>Fournisseur</th>
                                    <th>Date</th>
                                    <th class="text-center">Avancement Cmd.</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <!-- 🔥 VIDE car AJAX -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            // 🔥 DATATABLE AJAX
            $('#table-receptions').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('receptions.index') }}",

                columns: [{
                        data: 'reference',
                        name: 'reference_reception'
                    },
                    {
                        data: 'commande',
                        name: 'commande.reference'
                    },
                    {
                        data: 'fournisseur',
                        name: 'fournisseur.nom'
                    },
                    {
                        data: 'date',
                        name: 'date_reception'
                    },
                    {
                        data: 'pourcentage',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],

                pageLength: 10,
                responsive: true,
                language: {
                    // url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                }
            });

            // 🔥 TOOLTIP
            $(document).on('mouseenter', '[data-bs-toggle="tooltip"]', function() {
                new bootstrap.Tooltip(this);
            });

        });


        // 🔥 CONFIRM DELETE
        function confirmDelete(id, ref) {
            Swal.fire({
                title: "Supprimer ?",
                html: "Réception <b>" + (ref || '#' + id) + "</b>",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Oui",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete-form-' + id).submit();
                }
            });
        }
    </script>
@endsection
