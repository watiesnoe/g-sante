@extends('layouts.app')

@section('titre', 'Gestion des Familles')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Action Button Area -->
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </button>
            </div>

            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des Familles</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="familleTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Date création</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="crudModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Ajouter une famille</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom de la famille</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
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
            $(document).ready(function() {
                CrudHelper.init({
                    baseUrl: '/familles',
                    ajaxUrl: "{{ route('familles.index') }}",
                    tableId: '#familleTable',
                    columns: [
                         { data: 'nom', name: 'nom' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    mapData: function(data) {
                        $('#nom').val(data.nom);
                    }
                });
            });
        </script>
@endsection

