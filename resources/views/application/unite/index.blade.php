<x-config-layout titre="Gestion des Unités" icon="fa fa-balance-scale">
    <x-slot name="actions">
        <button class="btn btn-primary" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Ajouter
        </button>
    </x-slot>

    <div class="table-responsive">
        <table id="uniteTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Date création</th>
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
                        <h5 class="modal-title" id="modalTitle">Ajouter une unité</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom de l'unité</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
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
                    baseUrl: '/unites',
                    ajaxUrl: "{{ route('unites.index') }}",
                    tableId: '#uniteTable',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
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
</x-config-layout>
