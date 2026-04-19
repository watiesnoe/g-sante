@extends('layouts.app')

@section('title_page', 'Gestion des hospitalisations')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('hospitalisations.create') }}" class="btn btn-sm btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-bed me-1"></i> Liste des Hospitalisations
                </h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table id="hospitalisationsTable" class="table table-bordered table-striped table-vcenter w-100">
                        <thead>
                            <tr>
                                <th>Nom &amp; Prénom</th>
                                <th>Salle/Lit</th>
                                <th>Date entrée</th>
                                <th>Motif</th>
                                <th>État</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 🔹 Modal Paiement --}}
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
                                <input type="date" id="date_entree" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Date de sortie</label>
                                <input type="date" id="date_sortie" name="date_sortie" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Prix / jour</label>
                                <input type="number" id="montant_jour" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Total à payer</label>
                                <input type="number" id="montant_total" name="montant_total" class="form-control" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Montant reçu</label>
                                <input type="number" id="montant_recu" name="montant_recu" class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Montant restant</label>
                                <input type="number" id="montant_restant" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-sm btn-primary">Valider</button>
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('partials.modals.transfert')
@endsection

@section('scripts')
    <script>
        $(function () {

            // ---------------- DataTable ----------------
            let table = $('#hospitalisationsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("hospitalisations.index") }}',
                columns: [
                    { data: 'patient', name: 'patient' },
                    { data: 'salle_lit', name: 'salle_lit' },
                    { data: 'date_entree', name: 'date_entree' },
                    { data: 'motif', name: 'motif' },
                    { data: 'etat', name: 'etat', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers'
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
                    $('#date_entree').val(dateEntree);
                    $('#montant_jour').val(prixJour);

                    $('#date_sortie').val('');
                    $('#montant_total').val('');
                    $('#montant_recu').val('');
                    $('#montant_restant').val('');

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
                let arrive = new Date($('#date_entree').val());
                let sortie = new Date($('#date_sortie').val());
                let montantJour = parseFloat($('#montant_jour').val()) || 0;

                if (!arrive || !sortie || sortie <= arrive) {
                    $('#montant_total').val(0);
                    return 0;
                }

                let jours = Math.ceil((sortie - arrive) / (1000 * 3600 * 24));
                let total = jours * montantJour;
                $('#montant_total').val(total.toFixed(2));
                return total;
            }

            $('#date_sortie').on('change', function() {
                let total = calculerTotal();
                let recu = parseFloat($('#montant_recu').val()) || 0;
                $('#montant_restant').val((total - recu).toFixed(2));
            });

            $('#montant_recu').on('input', function() {
                let total = parseFloat($('#montant_total').val()) || 0;
                let recu = parseFloat($(this).val()) || 0;
                $('#montant_restant').val((total - recu).toFixed(2));
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
                        Swal.fire({
                            icon: 'success',
                            title: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#modalPaiement').modal('hide');
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        console.error('Erreur AJAX:', xhr.responseJSON);
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: Object.values(xhr.responseJSON.errors || {}).join("\n")
                        });
                    }
                });
            });

        });
    </script>
@endsection
