@extends('layouts.app')

@section('titre', '🩺 Gestion des Médicaments')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('medicaments.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </a>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des Médicaments</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive small">
                            <table class="table table-bordered table-sm table-striped" id="medicamentsTable">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Unité</th>
                                        <th>Famille</th>
                                        <th>Stock</th>
                                        <th>Stock Min</th>
                                        <th>Prix Achat</th>
                                        <th>Prix Vente</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('#medicamentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('medicaments.index') }}',
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'unite', name: 'unite' },
                    { data: 'famille', name: 'famille' },
                    { data: 'stock', name: 'stock' },
                    { data: 'stock_min', name: 'stock_min' },
                    { data: 'prix_achat', name: 'prix_achat' },
                    { data: 'prix_vente', name: 'prix_vente' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
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
                pagingType: 'simple_numbers',
                rowCallback: function(row, data) {
                    if (data.stock <= data.stock_min) {
                        $(row).addClass('table-danger');
                    }
                }
            });

            // Confirmation avant suppression
            $('body').on('submit', 'form.d-inline', function(e) {
                if (!confirm('Voulez-vous vraiment supprimer ce médicament ?')) e.preventDefault();
            });
        });
    </script>
@endsection
