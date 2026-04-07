<x-config-layout titre="Gestion des Maladies" icon="fa fa-virus">
    <x-slot name="actions">
        <button class="btn btn-primary" id="btnAdd">
            <i class="fa fa-plus me-1"></i> Ajouter
        </button>
    </x-slot>

    <div class="table-responsive">
        <table id="maladiesTable" class="table table-bordered table-striped">
            <thead>
                <tr>

                    <th>Nom</th>
                    <th>Description</th>
                    <th>Symptômes</th>
                    <th width="100">Actions</th>
                </tr>
            </thead>
        </table>
    </div>

    <x-slot name="modals">
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
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" placeholder="Description"></textarea>
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
                // Initialize CRUD Helper
                CrudHelper.init({
                    baseUrl: '/maladies',
                    ajaxUrl: "{{ route('maladies.index') }}",
                    tableId: '#maladiesTable',
                    columns: [

                        {
                            data: 'nom',
                            name: 'nom'
                        },
                        {
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'symptomes',
                            name: 'symptomes',
                            orderable: false
                        },
                        {
                            data: 'actions',
                            name: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    mapData: function(data) {
                        $('[name="nom"]').val(data.nom);
                        $('[name="description"]').val(data.description);
                        let symptomeIds = data.symptomes.map(s => s.id);
                        $('#symptomes').val(symptomeIds).trigger('change');
                    }
                });

                // Initialize Select2 in modal
                $('.select2').select2({
                    dropdownParent: $('#crudModal'),
                    width: '100%',
                    placeholder: 'Sélectionner les symptômes'
                });
            });
        </script>
    @endsection
</x-config-layout>
