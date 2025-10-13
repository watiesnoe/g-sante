@extends('layouts.app')

@section('titre', 'Liste des Rendez-vous')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">📅 Liste des Rendez-vous</h2>

        <!-- Bouton Ajouter -->
        <button class="btn btn-primary mb-3" id="addRdvBtn">➕ Nouveau</button>

        <!-- Tableau -->
        <table class="table table-bordered" id="rendezvous-table">
            <thead class="table-dark">
            <tr>
                <th>Patient</th>
                <th>Médecin</th>
                <th>Consultation</th>
                <th>Date & Heure</th>
                <th>Motif</th>
                <th>Action</th>
            </tr>
            </thead>
        </table>
    </div>

    <!-- Modal Ajouter / Éditer -->
    <div class="modal fade" id="rdvModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="rdvForm">
                @csrf
                <input type="hidden" name="id" id="rdv_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouveau Rendez-vous</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Patient</label>
                            <input type="text" name="patient_id" id="patient_id" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Médecin</label>
                            <input type="text" name="medecin_id" id="medecin_id" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Consultation</label>
                            <input type="text" name="consultation_id" id="consultation_id" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Date & Heure</label>
                            <input type="datetime-local" name="date_heure" id="date_heure" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label>Motif</label>
                            <input type="text" name="motif" id="motif" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {

            // DataTable
            let table = $('#rendezvous-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('rendezvous.index') }}",
                columns: [
                    { data: 'patient', name: 'patient.nom' },
                    { data: 'medecin', name: 'medecin.name' },
                    { data: 'consultation', name: 'consultation.id' },
                    { data: 'date_heure', name: 'date_heure' },
                    { data: 'motif', name: 'motif' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false } // ← actions au lieu de action
                ]
            });

            // Ajouter
            $('#addRdvBtn').click(function(){
                $('#rdvForm')[0].reset();
                $('#rdv_id').val('');
                $('#rdvModal .modal-title').text("Nouveau Rendez-vous");
                $('#rdvModal').modal('show');
            });

            // Sauvegarder
            $('#rdvForm').submit(function(e){
                e.preventDefault();
                $.ajax({
                    url: "{{ route('rendezvous.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(){
                        $('#rdvModal').modal('hide');
                        table.ajax.reload();
                    }
                });
            });

            // Éditer
            $(document).on('click', '.editRdv', function(){
                let id = $(this).data('id');
                $.get("/rendezvous/"+id+"/edit", function(rdv){
                    $('#rdv_id').val(rdv.id);
                    $('#patient_id').val(rdv.patient_id);
                    $('#medecin_id').val(rdv.medecin_id);
                    $('#consultation_id').val(rdv.consultation_id);
                    $('#date_heure').val(rdv.date_heure.replace(" ", "T"));
                    $('#motif').val(rdv.motif);
                    $('#rdvModal .modal-title').text("Modifier Rendez-vous");
                    $('#rdvModal').modal('show');
                });
            });

            // Supprimer
            $(document).on('click', '.deleteRdv', function(){
                let id = $(this).data('id');
                if(confirm("Voulez-vous vraiment supprimer ce rendez-vous ?")){
                    $.ajax({
                        url: "/rendezvous/"+id,
                        method: "DELETE",
                        data: {_token: "{{ csrf_token() }}"},
                        success: function(){
                            table.ajax.reload();
                        }
                    });
                }
            });

        });
    </script>
    <script>
        $(document).on('click', '.btn-realise', function (e) {
            e.preventDefault();

            let url = $(this).data('url');

            if (confirm("Voulez-vous vraiment marquer ce rendez-vous comme réalisé ?")) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès',
                                text: 'Rendez-vous marqué comme réalisé !',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            $('#rdvTable').DataTable().ajax.reload(); // refresh tableau
                        }
                    },
                    error: function () {
                        alert("❌ Une erreur est survenue.");
                    }
                });
            }
        });
    </script>
@endsection
