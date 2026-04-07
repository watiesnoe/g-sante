@extends('layouts.app')

@section('titre')
    🏥 Gestion des Salles
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">


                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('salles.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Ajouter</a>
                </div>
                <div class="card shadow-sm rounded-3">
                    <div class="d-flex justify-content-between align-items-center p-2 card-header">
                        <h4 class="mb-0">Liste des Salles</h4>
                    </div>
                    <div class="card-body">
                        <table id="salles-table" class="table table-bordered table-striped">
                            <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Service médical</th>
                                <th>Capacité</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // Initialisation DataTable
            let table = $('#salles-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('salles.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nom', name: 'nom' },
                    { data: 'type', name: 'type' },
                    { data: 'service', name: 'service' },
                    { data: 'capacite', name: 'capacite' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });


            // Suppression avec SweetAlert
            $(document).on('click', '.delete-btn', function () {
                let url = $(this).data('url');

                Swal.fire({
                    title: "Êtes-vous sûr ?",
                    text: "Cette salle sera supprimée définitivement.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                table.ajax.reload();
                                Swal.fire("Supprimé !", "La salle a été supprimée.", "success");
                            },
                            error: function () {
                                Swal.fire("Erreur !", "Impossible de supprimer.", "error");
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
