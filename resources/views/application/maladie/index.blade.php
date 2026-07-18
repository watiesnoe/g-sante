@extends('layouts.app')

@section('titre', 'Gestion des Maladies')

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Liste des Maladies</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('export.model', 'maladies') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                                <i class="fa fa-file-excel me-1"></i> Exporter
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="maladies" data-label="Maladies">
                                <i class="fa fa-file-import me-1"></i> Importer
                            </button>
                            <button class="btn btn-sm btn-primary" id="btnAdd">
                                <i class="fa fa-plus me-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="maladiesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Symptômes</th>
                                        <th width="100" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.partials.import_modal')

        <div class="modal fade" id="crudModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajouter une maladie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" placeholder="Description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Symptômes :</label>
                                <select name="symptomes[]" id="symptomes" class="form-control select2" multiple>
                                    @foreach ($symptomes as $symptome)
                                        <option value="{{ $symptome->id }}">{{ $symptome->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-sm btn-primary" id="btnSave">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
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

            // Initialisation Select2
            $('#symptomes').select2({
                dropdownParent: $('#crudModal'),
                width: '100%',
                placeholder: 'Sélectionner les symptômes'
            });

            // 1. Initialisation de DataTable
            let table = $('#maladiesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('maladies.index') }}",
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'description', name: 'description' },
                    { data: 'symptomes', name: 'symptomes', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
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

            // 2. Gestion de l'ouverture du Modal pour Ajouter
            $('#btnAdd').click(function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#symptomes').val([]).trigger('change'); // Vider Select2
                $('#modalTitle').text('Ajouter une maladie');
                $('#crudModal').modal('show');
            });

            // 3. Gestion de la Sauvegarde (Ajout ou Modification)
            $('#crudForm').submit(function(e) {
                e.preventDefault();
                
                let id = $('#id').val();
                let url = id ? "{{ url('maladies') }}/" + id : "{{ route('maladies.store') }}";
                let type = id ? "PUT" : "POST";
                let data = $(this).serialize();

                $.ajax({
                    url: url,
                    type: type,
                    data: data,
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
                            html: errorMsg || 'Une erreur est survenue lors de l\'enregistrement.'
                        });
                    }
                });
            });

            // 4. Gestion de la Modification (.edit)
            $(document).on('click', '.edit', function() {
                let id = $(this).data('id'); 
                let url = "{{ url('maladies') }}/" + id + "/edit";

                $.get(url, function(data) {
                    $('#modalTitle').text('Modifier la maladie');
                    $('#id').val(data.id);
                    $('#nom').val(data.nom);
                    $('#description').val(data.description);
                    
                    // On récupère les IDs des symptômes liés
                    let symptomeIds = data.symptomes.map(s => s.id);
                    $('#symptomes').val(symptomeIds).trigger('change');

                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les données pour la modification.', 'error');
                });
            });

            // 5. Gestion de la Vue des détails (.view)
            $(document).on('click', '.view', function() {
                let id = $(this).data('id');
                let url = "{{ url('maladies') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Détails de la maladie');
                    $('#id').val('');
                    
                    $('#nom').val(data.nom).prop('disabled', true);
                    $('#description').val(data.description).prop('disabled', true);
                    
                    let symptomeIds = data.symptomes.map(s => s.id);
                    $('#symptomes').val(symptomeIds).trigger('change').prop('disabled', true);
                    
                    $('#btnSave').hide();
                    $('#crudModal').modal('show');
                }).fail(function() {
                    Swal.fire('Erreur', 'Impossible de charger les détails.', 'error');
                });
            });

            // Réinitialiser les inputs au masquage du modal
            $('#crudModal').on('hidden.bs.modal', function () {
                $('#nom').prop('disabled', false);
                $('#description').prop('disabled', false);
                $('#symptomes').prop('disabled', false).val([]).trigger('change');
                $('#btnSave').show();
            });

            // 6. Suppression (.delete)
            $(document).on('click', '.delete', function() {
                let id = $(this).data('id');
                let url = "{{ url('maladies') }}/" + id;

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette maladie sera supprimée définitivement !",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                Swal.fire('Supprimé !', response.message || 'La maladie a été supprimée.', 'success');
                                table.ajax.reload(null, false);
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Impossible de supprimer cette maladie.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection