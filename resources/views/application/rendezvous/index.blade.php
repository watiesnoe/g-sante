@extends('layouts.app')

@section('titre', 'Liste des Rendez-vous')

@section('content')
    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" id="addRdvBtn"><i class="fa fa-plus me-1"></i> Ajouter</button>
        </div>
        <div class="card shadow-lg">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h4 class="m-0"><i class="fa fa-calendar me-2"></i>Liste des Rendez-vous</h4>
            </div>

            <div class="card-body">

                <table class="table table-bordered table-striped" id="rendezvous-table">
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
        </div>

    </div>

    <!-- Modal -->
    <div class="modal fade" id="rdvModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="rdvForm">
                @csrf
                <input type="hidden" name="id" id="rdv_id">

                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">Nouveau Rendez-vous</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Patient</label>
                            <input type="text" id="patient_name" class="form-control bg-light" readonly placeholder="Sélection manuelle non disponible">
                            <input type="hidden" name="patient_id" id="patient_id">
                        </div>
                        <div class="mb-2">
                            <label>Médecin</label>
                            <input type="text" id="medecin_name" class="form-control bg-light" readonly placeholder="Sélection manuelle non disponible">
                            <input type="hidden" name="medecin_id" id="medecin_id">
                        </div>
                        <input type="hidden" name="consultation_id" id="consultation_id">
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
        $(function() {

            // DataTable
            let table = $('#rendezvous-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('rendezvous.index') }}",
                columns: [{
                        data: 'patient',
                        name: 'patient.nom'
                    },
                    {
                        data: 'medecin',
                        name: 'medecin.name'
                    },
                    {
                        data: 'consultation',
                        name: 'consultation.id'
                    },
                    {
                        data: 'date_heure',
                        name: 'date_heure'
                    },
                    {
                        data: 'motif',
                        name: 'motif'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    } // ← actions au lieu de action
                ]
            });

            // Ajouter
            $('#addRdvBtn').click(function() {
                $('#rdvForm')[0].reset();
                $('#rdv_id').val('');
                $('#rdvModal .modal-title').text("Nouveau Rendez-vous");
                $('#rdvModal').modal('show');
            });

            // Check prefill data from Controller
            @if(isset($prefillConsultation))
                $('#rdvForm')[0].reset();
                $('#patient_id').val('{{ $prefillConsultation->patient_id }}');
                $('#medecin_id').val('{{ $prefillConsultation->medecin_id }}');
                $('#consultation_id').val('{{ $prefillConsultation->id }}');
                
                $('#patient_name').val('{{ addslashes($prefillConsultation->patient ? $prefillConsultation->patient->prenom . " " . $prefillConsultation->patient->nom : "") }}');
                $('#medecin_name').val('{{ addslashes($prefillConsultation->medecin ? $prefillConsultation->medecin->name : "") }}');
                
                $('#rdvModal .modal-title').text("Nouveau Rendez-vous");
                $('#rdvModal').modal('show');
            @endif

            // Sauvegarder
            $('#rdvForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('rendezvous.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function() {
                        $('#rdvModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors;
                        let errorMsg = "Erreur lors de l'enregistrement.";
                        if (errors) {
                            errorMsg = Object.values(errors).map(e => e.join('\n')).join('\n');
                        }
                        alert(errorMsg);
                    }
                });
            });

            // Éditer
            $(document).on('click', '.editRdv', function() {
                let id = $(this).data('id');
                $.get("/rendezvous/" + id + "/edit", function(rdv) {
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
            $(document).on('click', '.deleteRdv', function() {
                let id = $(this).data('id');
                if (confirm("Voulez-vous vraiment supprimer ce rendez-vous ?")) {
                    $.ajax({
                        url: "/rendezvous/" + id,
                        method: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function() {
                            table.ajax.reload();
                        }
                    });
                }
            });

        });
    </script>
    <script>
        $(document).on('click', '.btn-realise', function(e) {
            e.preventDefault();

            let url = $(this).data('url');

            if (confirm("Voulez-vous vraiment marquer ce rendez-vous comme réalisé ?")) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
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
                    error: function() {
                        alert("❌ Une erreur est survenue.");
                    }
                });
            }
        });
    </script>
@endsection
