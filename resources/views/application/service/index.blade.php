@extends('layouts.app')

@section('titre', 'Gestion des Salles')

@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-sm btn-primary" id="btnAdd">
                    <i class="fa fa-plus me-1"></i> Ajouter
                </button>
            </div>

            @include('layouts.partials.configside')

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
                                        <th>Disponibilité</th>
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
            // Configuration Ajax globale pour le token CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // 1. Initialisation de DataTable avec Ajax
            var table = $('#sallesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('salles.index') }}",
                columns: [
                    { data: 'nom', name: 'nom' },
                    { data: 'type', name: 'type' },
                    { data: 'service', name: 'service' }, // S'adapte au champ retourné par ton contrôleur (ex: service.nom)
                    { data: 'capacite', name: 'capacite' },
                    { data: 'disponibilite', name: 'disponibilite' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                language: {
                    // url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
                }
            });

            // 2. Gestion de l'ouverture du Modal pour Ajouter
            $('#btnAdd').click(function() {
                $('#crudForm')[0].reset();
                $('#id').val('');
                $('#modalTitle').text('Nouvelle salle');
                $('#crudModal').modal('show');
            });

            // 3. Gestion de la Sauvegarde (Ajout ou Modification)
            $('#crudForm').submit(function(e) {
                e.preventDefault();
                
                var id = $('#id').val();
                // Si l'ID existe, c'est une mise à jour (PUT), sinon une création (POST)
                var url = id ? "{{ url('salles') }}/" + id : "{{ route('salles.store') }}";
                var type = id ? "PUT" : "POST";
                var data = $(this).serialize();

                $.ajax({
                    url: url,
                    type: type,
                    data: data,
                    success: function(response) {
                        $('#crudModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMsg = '';
                        for (var key in errors) {
                            errorMsg += errors[key][0] + '\n';
                        }
                        Swal.fire('Erreur', errorMsg || 'Une erreur est survenue.', 'error');
                    }
                });
            });

            // 4. Gestion de la Modification (Récupération des données)
            $('body').on('click', '.edit', function() {
                var id = $(this).data('id');
                var url = "{{ url('salles') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Modifier la salle');
                    
                    // Remplissage des données dans les champs correspondants
                    $('#id').val(data.uuid || data.id);
                    $('#nom').val(data.nom);
                    $('#type').val(data.type);
                    $('#service_medical_id').val(data.service_medical_id);
                    $('#capacite').val(data.capacite);
                    
                    $('#crudModal').modal('show');
                });
            });

            // 5. Gestion de la Vue des détails (Lecture seule)
            $('body').on('click', '.view', function() {
                var id = $(this).data('id');
                var url = "{{ url('salles') }}/" + id;

                $.get(url, function(data) {
                    $('#modalTitle').text('Détails de la salle');
                    $('#id').val(''); // On vide pour éviter une modification accidentelle
                    
                    // Désactivation des champs pour le mode lecture
                    $('#nom').val(data.nom).prop('disabled', true);
                    $('#type').val(data.type).prop('disabled', true);
                    $('#service_medical_id').val(data.service_medical_id).prop('disabled', true);
                    $('#capacite').val(data.capacite).prop('disabled', true);
                    
                    $('#btnSave').hide(); // Masquer le bouton enregistrer
                    $('#crudModal').modal('show');
                });
            });

            // Réinitialiser les champs à la fermeture du modal pour retirer le "disabled" et réafficher le bouton
            $('#crudModal').on('hidden.bs.modal', function () {
                $('#nom').prop('disabled', false);
                $('#type').prop('disabled', false);
                $('#service_medical_id').prop('disabled', false);
                $('#capacite').prop('disabled', false);
                $('#btnSave').show();
            });

            // 6. Gestion de la Suppression
            $('body').on('click', '.delete', function() {
                var id = $(this).data('id');
                var url = "{{ url('salles') }}/" + id;

                Swal.fire({
                    title: 'Êtes-vous sûr ?',
                    text: "Cette action est irréversible.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Oui, supprimer',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            success: function(response) {
                                table.ajax.reload();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Supprimé!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: function() {
                                Swal.fire('Erreur', 'Impossible de supprimer cette salle.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection