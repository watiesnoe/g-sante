@extends('layouts.app')

@section('title_page', 'Gestion des hospitalisations')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mt-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class="mb-0">Liste des hospitalisations</h5>
                <a href="{{ route('hospitalisations.create') }}" class="btn btn-light btn-sm">
                    <i class="bx bx-plus"></i> Nouvelle hospitalisation
                </a>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table id="hospitalisationsTable" class="table table-bordered table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>Nom & Prénom</th>
                            <th>Salle/Lit</th>
                            <th>Date entrée</th>
                            <th>Motif</th>
                            <th>État</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        {{-- Modal paiement --}}
        <div class="modal fade" id="modalPaiement" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form id="formPaiement" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Paiement hospitalisation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body row">
                            <input type="hidden" id="hospitalisation_id" name="hospitalisation_id">

                            <div class="col-md-6 mb-3">
                                <label>Date d'entrée</label>
                                <input type="date" id="dateArrive" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Date de sortie</label>
                                <input type="date" id="dateSortie" name="dateSortie" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Prix / jour</label>
                                <input type="number" id="montantJour" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Total à payer</label>
                                <input type="number" id="montantTotal" name="montantTotal" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Montant reçu</label>
                                <input type="number" id="montantRecu" name="montantRecu" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Montant restant</label>
                                <input type="number" id="montantRestant" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="submit" class="btn btn-primary">Valider</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // ---------------- DataTable ----------------
            let table = $('#hospitalisationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("hospitalisations.realise") }}',
                columns: [
                    { data: 'patient', name: 'patient' },
                    { data: 'salle_lit', name: 'salle_lit' },
                    { data: 'date_entree', name: 'date_entree' },
                    { data: 'motif', name: 'motif' },
                    { data: 'etat', name: 'etat', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
            });

            // ---------------- Ouvrir modal paiement ----------------
            $(document).on('click', '.btn-paiement', function() {
                let id = $(this).data('id');
                if (!id) return console.error('hospitalisation id manquant');

                let url = '{{ route("hospitalisations.paiement.data", ":id") }}'.replace(':id', id);

                $.getJSON(url, function(response) {
                    let dateEntree = response.date_entree ? response.date_entree.split(' ')[0] : '';
                    let prixJour = response.prix_jour || 0;

                    $('#hospitalisation_id').val(id);
                    $('#dateArrive').val(dateEntree);
                    $('#montantJour').val(prixJour);

                    $('#dateSortie').val('');
                    $('#montantTotal').val('');
                    $('#montantRecu').val('');
                    $('#montantRestant').val('');

                    $('#modalPaiement').modal('show');
                }).fail(function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: 'Impossible de charger les informations de l’hospitalisation.'
                    });
                    console.error('Erreur chargement paiement:', xhr.responseText);
                });
            });

            // ---------------- Calcul total ----------------
            function calculerTotal() {
                let arrive = new Date($('#dateArrive').val());
                let sortie = new Date($('#dateSortie').val());
                let montantJour = parseFloat($('#montantJour').val()) || 0;

                if (!arrive || !sortie || sortie <= arrive) {
                    $('#montantTotal').val(0);
                    return 0;
                }

                let jours = Math.ceil((sortie - arrive) / (1000 * 3600 * 24));
                let total = jours * montantJour;
                $('#montantTotal').val(total.toFixed(2));
                return total;
            }

            $('#dateSortie').on('change', function() {
                let total = calculerTotal();
                let recu = parseFloat($('#montantRecu').val()) || 0;
                $('#montantRestant').val((total - recu).toFixed(2));
            });

            $('#montantRecu').on('input', function() {
                let total = parseFloat($('#montantTotal').val()) || 0;
                let recu = parseFloat($(this).val()) || 0;
                $('#montantRestant').val((total - recu).toFixed(2));
            });

            // ---------------- Soumission AJAX ----------------
            $('#formPaiement').submit(function(e) {
                e.preventDefault();

                let url = '{{ route("paiements.hospitalisation") }}';
                let formData = $(this).serialize();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(data) {
                        console.log('DEBUG store AJAX:', data); // <-- ici tu vois toutes les données
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.error('Erreur AJAX:', xhr.responseText);
                    }
                });
            });

        });
    </script>
@endsection
