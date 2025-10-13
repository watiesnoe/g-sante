@extends('layouts.app')

@section('titre', 'Gestion des Maladies')

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">

                    <!-- Header avec bouton modal -->
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3>Gestion des Maladies</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#maladieModal">
                            Ajouter une maladie
                        </button>
                    </div>

                    <div class="block-content">
                        <hr>
                        <!-- Table Datatable -->
                        <table id="maladiesTable" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Symptômes</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter maladie -->
    <div class="modal fade" id="maladieModal" tabindex="-1" aria-labelledby="maladieModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- modal large pour beaucoup de symptômes -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="maladieModalLabel">Ajouter une maladie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="maladieForm">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="nom" class="form-control" placeholder="Nom de la maladie" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="description" class="form-control" placeholder="Description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Symptômes :</label>
                            <select name="symptomes[]" class="form-control select2" multiple>
                                @foreach($symptomes as $symptome)
                                    <option value="{{ $symptome->id }}">{{ $symptome->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- JQuery et Bootstrap -->
{{--    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>--}}

{{--    <!-- Datatables -->--}}
{{--    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">--}}
{{--    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>--}}

{{--    <!-- Select2 -->--}}
{{--    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>--}}

    <script>
        $(document).ready(function() {

            // Initialiser Select2 dans le modal
            $('.select2').select2({
                dropdownParent: $('#maladieModal'),
                width: '100%',
                placeholder: 'Sélectionner les symptômes'
            });

            // Initialiser Datatable
            var table = $('#maladiesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('maladies.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nom', name: 'nom' },
                    { data: 'description', name: 'description' },
                    { data: 'symptomes', name: 'symptomes', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ]
            });

            // AJAX store
            // AJAX store
            $('#maladieForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('maladies.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        $('#maladieForm')[0].reset();
                        $('#maladieModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        for (let key in errors) {
                            errorMsg += errors[key] + "\n";
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: errorMsg
                        });
                    }
                });
            });

// AJAX delete
            $('#maladiesTable').on('click', '.delete', function() {
                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Voulez-vous vraiment supprimer cette maladie ?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer !'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var id = $(this).data('id');
                        $.ajax({
                            url: '/maladies/' + id,
                            type: 'DELETE',
                            data: {_token: "{{ csrf_token() }}"},
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimé',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                table.ajax.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initialiser Dashmix Select2 avec recherche et placeholder
            $('.js-select2').select2({
                width: '100%',
                placeholder: "Rechercher et sélectionner les symptômes",
                allowClear: true,
                dropdownParent: $('#maladieModal') // si le select est dans un modal
            });

            // Si le select est dans un modal, réinitialiser le dropdownParent à chaque ouverture
            $('#maladieModal').on('shown.bs.modal', function () {
                $('.js-select2').select2({
                    dropdownParent: $('#maladieModal'),
                    width: '100%',
                    placeholder: "Rechercher et sélectionner les symptômes"
                });
            });
        });
    </script>

@endsection
