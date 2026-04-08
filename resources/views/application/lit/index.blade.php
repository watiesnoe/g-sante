@extends('layouts.app')

@section('titre', '🛏️ Gestion des lits')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('lits.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </a>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des lits</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter" id="lits-table">
                                <thead>
                                    <tr>
                                        <th>Numéro</th>
                                        <th>Salle</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
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
            let table = $('#lits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lits.index') }}",
                columns: [
                    { data: 'numero', name: 'numero' },
                    { data: 'salle', name: 'salle.nom' },
                    { data: 'statut', name: 'statut' },
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
                     "<'row'<'col-sm-12'i><'col-sm-12'p>>",
                pagingType: 'simple_numbers'
            });

            // suppression AJAX avec SweetAlert
            $(document).on('click', '.btn-delete', function() {
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Ce lit sera supprimé définitivement !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer !'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {_token: '{{ csrf_token() }}'},
                            success: function(response) {
                                Swal.fire('Supprimé !', response.message, 'success');
                                table.ajax.reload();
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Une erreur est survenue.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
