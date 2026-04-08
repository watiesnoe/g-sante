@extends('layouts.app')

@section('titre', '🩺 Gestion des Examens')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('examens.create') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </a>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des examens</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-vcenter" id="examens-table">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Service médical</th>
                                        <th>Description</th>
                                        <th>Prix</th>
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
        $(document).ready(function() {
            let table = $('#examens-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('examens.index') }}",
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'service', name: 'service_medical.nom' },
                    { data: 'description', name: 'description' },
                    { data: 'prix', name: 'prix' },
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
                pagingType: 'simple_numbers'
            });

            // Suppression AJAX avec SweetAlert
            $(document).on('click', '.btn-delete', function() {
                let url = $(this).data('url');

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cet examen sera supprimé définitivement !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if(result.isConfirmed) {
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
