@extends('layouts.app')

@section('titre', 'Gestion des Fournisseurs')

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


            <!-- Contenu principal -->
            <div class="col-xl-12 col-lg-12">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des Fournisseurs</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="fournisseurTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Contact</th>
                                        <th>Adresse</th>
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
                        <h5 class="modal-title" id="modalTitle">Ajouter un fournisseur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" id="nom" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" id="contact" name="contact" class="form-control" placeholder="Téléphone">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" id="adresse" name="adresse" class="form-control">
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
                    baseUrl: '/fournisseurs',
                    storeUrl: '/fournisseurs',
                    ajaxUrl: "{{ route('fournisseurs.index') }}",
                    tableId: '#fournisseurTable',
                    columns: [
                        { data: 'nom', name: 'nom' },
                        { data: 'contact', name: 'contact' },
                        { data: 'adresse', name: 'adresse' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    mapData: function(data) {
                        $('#nom').val(data.nom);
                        $('#contact').val(data.contact);
                        $('#adresse').val(data.adresse);
                    }
                });
            });
        </script>
    @endsection
