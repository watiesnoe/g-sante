@extends('layouts.app')

@section('titre', '🛏️ Gestion des lits')

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Liste des lits</h3>
                        <a href="{{ route('lits.create') }}" class="btn btn-primary">➕ Ajouter un lit</a>
                    </div>
                    <div class="block-content block-content-full">
                        <table class="table table-bordered table-striped table-vcenter" id="lits-table">
                            <thead>
                            <tr>
                                <th>#</th>
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
@endsection

@section('scripts')
    <script>
        $(function() {
            let table = $('#lits-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('lits.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'numero', name: 'numero' },
                    { data: 'salle', name: 'salle.nom' },
                    { data: 'statut', name: 'statut' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
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
