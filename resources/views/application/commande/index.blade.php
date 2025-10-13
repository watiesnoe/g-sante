@extends('layouts.app')
@section('titre', 'Commandes')

@section('content')
    <div class="container mt-4">
        {{-- Alertes Session --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fa fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('commandes.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-boxes me-1"></i> Liste des Commandes
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table id="tableCommandes" class="table table-bordered table-striped table-vcenter w-100">
                        <thead>
                            <tr>
                                <th>Reference CMD</th>
                                <th>Fournisseur</th>
                                <th>Date Commande</th>
                                <th>Statut livraison</th>
                                <th class="text-center">Avancement</th>
                                <th>Total</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let table = $('#tableCommandes').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("commandes.index") }}',
                columns: [
                    { data: 'reference', name: 'reference' },
                    { data: 'fournisseur', name: 'fournisseur.nom' },
                    { data: 'date_commande', name: 'date_commande' },
                    { data: 'statut', name: 'statut' },
                    { data: 'avancement', name: 'avancement', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'total', name: 'total' },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
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
                pageLength: 10,
                order: [[0, 'desc']]
            });

            // Suppression AJAX avec confirmation
            $(document).on('click', '.btnSupprimer', function () {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: 'Voulez-vous vraiment supprimer cette commande ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/commandes/${id}`,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: function (response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimée !',
                                    text: response.message || 'Commande supprimée avec succès.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload(null, false);
                            },
                            error: function (xhr) {
                                let message = xhr.responseJSON?.message || 'Erreur lors de la suppression de la commande.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Suppression impossible',
                                    text: message
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
