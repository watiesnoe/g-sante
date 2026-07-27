@extends('layouts.app')

@section('titre', 'Familles de Médicaments')

@section('content')
<div class="container mt-4">
    <div class="row">
    @include('layouts.partials.configside')
    <div class="col-xl-9 col-lg-8">
        <div class="block block-rounded">
            <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                <h3 class="block-title">
                    <i class="fa fa-users-cog me-2 text-primary"></i> Familles de Médicaments
                </h3>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('export.model', 'familles') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                        <i class="fa fa-file-excel me-1"></i> Exporter
                    </a>
                    @can('stock.familles')
                    <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="familles" data-label="Familles médicaments">
                        <i class="fa fa-file-import me-1"></i> Importer
                    </button>
                    <button class="btn btn-sm btn-primary" id="btnAdd">
                        <i class="fa fa-plus me-1"></i> Ajouter
                    </button>
                    @endcan
                </div>
            </div>
            <div class="block-content block-content-full">
                <table id="familleTable" class="table table-bordered table-striped table-hover w-100">
                        <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Date création</th>
                            <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>  
</div>
</div>

@include('layouts.partials.import_modal')

{{-- Modal Ajout / Modification --}}
<div class="modal fade" id="crudModal" tabindex="-1" aria-labelledby="modalTitleLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Ajouter une famille</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form id="crudForm">
                @csrf
                <input type="hidden" id="id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Ex: Antibiotiques" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnSave">
                        <i class="fa fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Configuration Ajax globale pour le token CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Initialisation de DataTable
            let table = $('#familleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('familles.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nom', name: 'nom' },
                    { data: 'created_at', name: 'created_at', className: 'text-center' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                   // url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12'i><'col-sm-12'p>>",
                pagingType: 'simple_numbers'
            });

            // 2. Bouton Ajouter
            $('#btnAdd').click(function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('Ajouter une famille');
                $('#btnSave').show();
                $('#nom').prop('disabled', false);
                $('#crudModal').modal('show');
            });

            // 3. Sauvegarde (Ajout ou Modification)
            $('#crudForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? "{{ url('familles') }}/" + id : "{{ route('familles.store') }}";
                let type = id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    type: type,
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#crudModal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message || 'Opération réussie',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMsg = '';
                        if (errors) {
                            for (let key in errors) {
                                errorMsg += errors[key][0] + '<br>';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            html: errorMsg || 'Une erreur est survenue.'
                        });
                    }
                });
            });

            // 4. Modification
            $(document).on('click', '.edit', function() {
                let id = $(this).data('id');
                $.get("{{ url('familles') }}/" + id + "/edit", function(data) {
                    $('#modalTitle').text('Modifier la famille');
                    $('#id').val(data.id);
                    $('#nom').val(data.nom).prop('disabled', false);
                    $('#btnSave').show();
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les données.', 'error');
                });
            });

            // 5. Détails
            $(document).on('click', '.view', function() {
                let id = $(this).data('id');
                $.get("{{ url('familles') }}/" + id, function(data) {
                    $('#modalTitle').text('Détails de la famille');
                    $('#id').val('');
                    $('#nom').val(data.nom).prop('disabled', true);
                    $('#btnSave').hide();
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les détails.', 'error');
                });
            });

            // Réinitialiser au fermeture du modal
            $('#crudModal').on('hidden.bs.modal', function () {
                $('#nom').prop('disabled', false);
                $('#btnSave').show();
                $('#crudForm')[0].reset();
            });

            // 6. Suppression
            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette famille sera supprimée définitivement !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('familles') }}/" + id,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire('Supprimé !', response.message || 'Famille supprimée.', 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Impossible de supprimer cette famille.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection