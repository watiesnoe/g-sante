@extends('layouts.app')

@section('titre', 'Gestion des Salles')

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
                        <h3 class="block-title">Liste des Salles</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <div class="table-responsive">
                            <table id="sallesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Service médical</th>
                                        <th>Capacité</th>
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
                        <h5 class="modal-title" id="modalTitle">Ajouter une salle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="crudForm">
                            @csrf
                            <input type="hidden" name="id" id="id">
                            <div class="mb-3">
                                <label class="form-label">Nom de la salle</label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type de salle</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="">Sélectionner</option>
                                    <option value="Seringuage">Seringuage</option>
                                    <option value="Chirurgie">Chirurgie</option>
                                    <option value="Consultation">Consultation</option>
                                    <option value="Hospitalisation">Hospitalisation</option>
                                    <option value="Observation">Observation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Service médical</label>
                                <select name="service_medical_id" id="service_medical_id" class="form-control" required>
                                    <option value="">Sélectionner</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Capacité (Lits)</label>
                                <input type="number" name="capacite" id="capacite" class="form-control" min="1" required>
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
                    baseUrl: '/salles',
                    ajaxUrl: "{{ route('salles.index') }}",
                    tableId: '#sallesTable',
                    columns: [
                    
                        { data: 'nom', name: 'nom' },
                        { data: 'type', name: 'type' },
                        { data: 'service', name: 'service' },
                        { data: 'capacite', name: 'capacite' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false }
                    ],
                    mapData: function(data) {
                        $('#nom').val(data.nom);
                        $('#type').val(data.type);
                        $('#service_medical_id').val(data.service_medical_id);
                        $('#capacite').val(data.capacite);
                    }
                });
            });
        </script>
    @endsection

