@extends('layouts.app')

@section('titre', '🛏️ Gestion des Structures')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
             <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('lits.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i> Ajouter
                    </a>
                </div>
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">
               
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Liste des structure</h3>
                    </div>
                    <div class="block-content block-content-full">
                         <table id="servicesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th >Description</th>
                                    <th>Créé le</th>
                                    <th width="20%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
  <div class="modal fade" id="crudModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nouvelle structure</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom de la structure</label>
                                <input type="text" name="nom" id="nom" class="form-control" placeholder="Ex: Clinique du Soleil" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" placeholder="Informations complémentaires"></textarea>
                            </div>
                            <div class="text-end border-top pt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-primary" id="btnSave">Enregistrer</button>
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
                    baseUrl: '/services',
                    ajaxUrl: "{{ route('services.index') }}",
                    tableId: '#servicesTable',
                    columns: [
                        { data: 'nom', name: 'nom' },
                        { data: 'description', name: 'description' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    mapData: function(data) {
                        $('#nom').val(data.nom);
                        $('#description').val(data.description);
                    }
                });
            });
        </script>
    @endsection

