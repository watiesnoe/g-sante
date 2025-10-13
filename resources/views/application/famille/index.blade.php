@extends('layouts.app')

@section('titre', 'Gestion des Familles')

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary fw-bold">
                            Gestion des familles
                        </h5>
                        <button class="btn btn-success" id="btnAddFamille">
                            ➕ Ajouter une famille
                        </button>
                    </div>

                    <div class="block-content">
                        <table class="table table-bordered table-striped" id="familleTable">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Date création</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Ajouter/Modifier -->
            <div class="modal fade" id="familleModal" tabindex="-1">
                <div class="modal-dialog">
                    <form id="familleForm">
                        @csrf
                        <input type="hidden" id="famille_id">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Ajouter une famille</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Nom de la famille</label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-primary" id="saveBtn">Enregistrer</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Fin Modal -->
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // Initialisation DataTable
            let table = $('#familleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('familles.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
                    { data: 'nom', name: 'nom' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable:false, searchable:false },
                ]
            });

            // Bouton Ajouter
            $('#btnAddFamille').click(function () {
                $('#familleForm')[0].reset();
                $('#famille_id').val('');
                $('#modalTitle').text('Ajouter une famille');
                $('#familleModal').modal('show');
            });

            // Soumission du formulaire (Ajouter/Modifier)
            $('#familleForm').submit(function (e) {
                e.preventDefault();
                let id = $('#famille_id').val();
                let url = id
                    ? "{{ route('familles.update', ':id') }}".replace(':id', id)
                    : "{{ route('familles.store') }}";
                let type = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    type: type,
                    data: {
                        nom: $('#nom').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (res) {
                        $('#familleModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Succès', res.success, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Erreur', xhr.responseJSON.message, 'error');
                    }
                });
            });

            // Bouton Modifier
            $(document).on('click', '.editFamille', function () {
                let id = $(this).data('id');
                let nom = $(this).data('nom');

                $('#famille_id').val(id);
                $('#nom').val(nom);
                $('#modalTitle').text('Modifier une famille');
                $('#familleModal').modal('show');
            });

            // Bouton Supprimer
            $(document).on('click', '.deleteFamille', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Supprimer ?",
                    text: "Cette action est irréversible.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Oui, supprimer",
                    cancelButtonText: "Annuler"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/familles/" + id,
                            type: "DELETE",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (res) {
                                table.ajax.reload();
                                Swal.fire('Supprimé', res.success, 'success');
                            },
                            error: function () {
                                Swal.fire('Erreur', "Impossible de supprimer", 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
