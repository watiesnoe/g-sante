<x-config-layout titre="Gestion des Fournisseurs" icon="fa fa-truck">
    <x-slot name="actions">
        <button class="btn btn-primary" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Ajouter
        </button>
    </x-slot>

    <div class="table-responsive">
        <table id="fournisseursTable" class="table table-bordered table-striped">
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

    <x-slot name="modals">
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
                                <label class="form-label">Nom du fournisseur</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact</label>
                                <input type="text" name="contact" id="contact" class="form-control" placeholder="Numéro ou Email">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" id="adresse" class="form-control" placeholder="Adresse complète">
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
    </x-slot>

    @section('scripts')
        <script>
            $(document).ready(function() {
                CrudHelper.init({
                    baseUrl: '/fournisseurs',
                    ajaxUrl: "{{ route('fournisseurs.index') }}",
                    tableId: '#fournisseursTable',
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
</x-config-layout>
