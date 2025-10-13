@extends('layouts.app')

@section('titre', 'Gestion des Unités')

@section('content')
    <div class="content">
     <div class="row">
        <!-- Sidebar -->
        @include('layouts.partials.configside')

        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h5 class="mb-0 text-primary fw-bold">
                        {{ isset($examen) ? '✏️ Modifier un examen' : '➕ Ajouter un nouvel examen' }}
                    </h5>
                </div>
                <div class="block-content">
                    <table class="table table-bordered table-striped" id="uniteTable">
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
        <div class="modal fade" id="uniteModal" tabindex="-1">
            <div class="modal-dialog">
                <form id="uniteForm">
                    @csrf
                    <input type="hidden" id="unite_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Ajouter une unité</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nom de l'unité</label>
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
    </div>
    </div>
    <!-- Modal Ajouter/Modifier -->

@endsection

@section('scripts')
    <script>
        $(function () {
            // Initialisation DataTable
            let table = $('#uniteTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('unites.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable:false, searchable:false },
                    { data: 'nom', name: 'nom' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable:false, searchable:false },
                ]
            });

            // Bouton Ajouter
            $('#btnAddUnite').click(function () {
                $('#uniteForm')[0].reset();
                $('#unite_id').val('');
                $('#modalTitle').text('Ajouter une unité');
                $('#uniteModal').modal('show');
            });

            // Soumission du formulaire (Ajouter/Modifier)
            $('#uniteForm').submit(function (e) {
                e.preventDefault();
                let id = $('#unite_id').val();
                let url = id ? "/unites/" + id : "{{ route('unites.store') }}";
                let type = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    type: type,
                    data: { nom: $('#nom').val(), _token: "{{ csrf_token() }}" },
                    success: function (res) {
                        $('#uniteModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Succès', res.success, 'success');
                    },
                    error: function (xhr) {
                        Swal.fire('Erreur', xhr.responseJSON.message, 'error');
                    }
                });
            });

            // Bouton Modifier
            $(document).on('click', '.editUnite', function () {
                let id = $(this).data('id');
                let nom = $(this).data('nom');

                $('#unite_id').val(id);
                $('#nom').val(nom);
                $('#modalTitle').text('Modifier une unité');
                $('#uniteModal').modal('show');
            });

            // Bouton Supprimer
            $(document).on('click', '.deleteUnite', function () {
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
                            url: "/unites/" + id,
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
