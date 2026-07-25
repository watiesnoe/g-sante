@extends('layouts.app')

@section('titre', 'Gestion des Symptômes')

@section('content')
<div class="container mt-4">
    <div class="row">

        <!-- Sidebar -->
        @include('layouts.partials.configside')

        <!-- Contenu -->
        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                    <h3 class="block-title">Liste des Symptômes</h3>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('export.model', 'symptomes') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                            <i class="fa fa-file-excel me-1"></i> Exporter
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="symptomes" data-label="Symptômes">
                            <i class="fa fa-file-import me-1"></i> Importer
                        </button>
                        <button class="btn btn-sm btn-primary" id="btnAdd">
                            <i class="fa fa-plus me-1"></i> Ajouter
                        </button>
                    </div>
                </div>

                <div class="block-content block-content-full">
                    <div class="table-responsive">
                        <table id="symptomeTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th class="col-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@include('layouts.partials.import_modal')

<!-- MODAL -->
<div class="modal fade" id="crudModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter un symptôme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="crudForm">
                    @csrf
                    <input type="hidden" name="id" id="id">

                    <div class="mb-3">
                        <label class="form-label">Nom du symptôme</label>
                        <input type="text" name="nom" id="nom" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control"></textarea>
                    </div>

                    <div class="text-end border-top pt-3">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                            Fermer
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary" id="btnSave">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection


@section('scripts')
<script>
$(document).ready(function() {

    let table = $('#symptomeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('symptomes.index') }}",
        columns: [
            { data: 'nom', name: 'nom' },
            { data: 'description', name: 'description' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Ouvrir modal ajout
    $('#btnAdd').click(function() {
        $('#crudForm')[0].reset();
        $('#id').val('');
        $('#modalTitle').text('Ajouter un symptôme');
        $('#crudModal').modal('show');
    });

    // SUBMIT (create + update)
    $('#crudForm').submit(function(e) {
        e.preventDefault();

        let id = $('#id').val();
        let url = id ? "{{ url('symptomes') }}/" + id : "{{ route('symptomes.store') }}";
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(res) {
                $('#crudModal').modal('hide');
                table.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: res.message || 'Symptôme enregistré avec succès !',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(err) {
                console.log(err);
                let errMsg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Erreur lors de l\'enregistrement.';
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: errMsg
                });
            }
        });
    });

    // EDIT
    $('#symptomeTable').on('click', '.edit', function() {
        let id = $(this).data('id');

        $.get("{{ url('symptomes') }}/" + id + '/edit', function(data) {
            $('#modalTitle').text('Modifier le symptôme');
            $('#id').val(data.id);
            $('#nom').val(data.nom);
            $('#description').val(data.description);
            $('#crudModal').modal('show');
        });
    });

    // DELETE
    $('#symptomeTable').on('click', '.delete', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('symptomes') }}/" + id,
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Supprimé!',
                            text: res.message || 'Le symptôme a été supprimé.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function() {
                        Swal.fire('Erreur', 'Impossible de supprimer ce symptôme.', 'error');
                    }
                });
            }
        });
    });

    // RESET MODAL
    $('#crudModal').on('hidden.bs.modal', function () {
        $('#crudForm')[0].reset();
        $('#id').val('');
    });

});
</script>
@endsection