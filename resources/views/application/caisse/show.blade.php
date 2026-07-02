@extends('layouts.app')

@section('content')
    <main id="main-container">
        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-bold mb-1">Détails de la Session de Caisse</h2>
                    <p class="text-muted mb-0">Gérée par <strong>{{ $session->user->name }}</strong></p>
                </div>
                <a href="{{ route('caisse.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Retour à la supervision
                </a>
            </div>

            <!-- Balances Cards -->
            <div class="row">
                <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                    <a class="block block-rounded block-link-pop border-start border-primary border-4"
                        href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Fonds Initial</div>
                            <div class="fs-2 fw-normal text-dark">{{ number_format($session->solde_initial, 0, ',', ' ') }}
                                <small class="fs-6">XOF</small></div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                    <a class="block block-rounded block-link-pop border-start border-success border-4"
                        href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Entrées</div>
                            <div class="fs-2 fw-normal text-dark">+{{ number_format($totalEntrees, 0, ',', ' ') }} <small
                                    class="fs-6">XOF</small></div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                    <a class="block block-rounded block-link-pop border-start border-danger border-4"
                        href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Sorties</div>
                            <div class="fs-2 fw-normal text-dark">-{{ number_format($totalSorties, 0, ',', ' ') }} <small
                                    class="fs-6">XOF</small></div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-3 col-xl-3">
                    <a class="block block-rounded block-link-pop border-start border-info border-4"
                        href="javascript:void(0)">
                        <div class="block-content block-content-full">
                            <div class="fs-sm fw-semibold text-uppercase text-muted">Solde Théorique</div>
                            <div class="fs-2 fw-normal text-dark">
                                {{ number_format($session->solde_theorique, 0, ',', ' ') }} <small
                                    class="fs-6">XOF</small></div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Session Info -->
            <div class="row">
                <div class="col-md-4">
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Informations Générales</h3>
                        </div>
                        <div class="block-content pb-3">
                            <table class="table table-striped table-borderless table-vcenter fs-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-semibold" style="width: 40%;">Statut</td>
                                        <td>
                                            @if($session->statut == 'ouverte')
                                                <span class="badge bg-success">Ouverte</span>
                                            @else
                                                <span class="badge bg-secondary">Fermée</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Date d'ouverture</td>
                                        <td>{{ $session->opened_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Date de clôture</td>
                                        <td>{{ $session->closed_at ? $session->closed_at->format('d/m/Y H:i') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Solde Réel</td>
                                        <td class="fw-bold">{{ $session->solde_reel ? number_format($session->solde_reel, 0, ',', ' ') . ' XOF' : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Écart</td>
                                        <td>
                                            @if($session->statut == 'fermee')
                                                @if($session->ecart == 0)
                                                    <span class="text-success fw-bold">0 XOF</span>
                                                @elseif($session->ecart > 0)
                                                    <span class="text-success fw-bold">+{{ number_format($session->ecart, 0, ',', ' ') }} XOF</span>
                                                @else
                                                    <span class="text-danger fw-bold">{{ number_format($session->ecart, 0, ',', ' ') }} XOF</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Historique des Opérations</h3>
                        </div>
                        <div class="block-content block-content-full">
                            <div class="table-responsive">
                                <table id="mouvementsTable" class="table table-bordered table-striped table-vcenter w-100">
                                    <thead>
                                        <tr>
                                            <th>Date / Heure</th>
                                            <th>Type</th>
                                            <th>Motif</th>
                                            <th class="text-end">Montant</th>
                                            <th class="text-center" style="width: 100px;">Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de détails du mouvement -->
        <div class="modal fade" id="modal-mouvement-detail" tabindex="-1" aria-labelledby="modal-mouvement-detail-title" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title fw-bold" id="modal-mouvement-detail-title">
                            <i class="fa fa-info-circle me-2"></i>Détails de la Transaction
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" id="mouvement-detail-content">
                        <!-- Chargement dynamique -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#mouvementsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                // Appelle la route caisse.show pour cette session avec AJAX
                ajax: "{{ route('caisse.show', $session->uuid) }}",
                columns: [
                    { 
                        data: 'created_at', 
                        name: 'created_at' 
                    },
                    { 
                        data: 'type', 
                        name: 'type' 
                    },
                    { 
                        data: 'motif', 
                        name: 'motif' 
                    },
                    { 
                        data: 'montant', 
                        name: 'montant', 
                        className: 'text-end' 
                    },
                    { 
                        data: 'action', 
                        name: 'action', 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center' 
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json'
                },
                // Trie par défaut les opérations de la plus récente à la plus ancienne
                order: [[0, 'desc']] 
            });

            // Au clic sur le bouton d'affichage d'un mouvement
            $(document).on('click', '.btn-show-mouvement', function() {
                var uuid = $(this).data('id');
                var modal = $('#modal-mouvement-detail');
                var content = $('#mouvement-detail-content');
                
                // Afficher le spinner de chargement
                content.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Chargement...</span></div></div>');
                modal.modal('show');
                
                $.ajax({
                    url: '/caisse/mouvement/' + uuid,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var html = '';
                        
                        var typeClass = response.type === 'entree' ? 'success' : 'danger';
                        var typeText = response.type === 'entree' ? 'Entrée de Caisse' : 'Sortie de Caisse';
                        var typeIcon = response.type === 'entree' ? 'fa-arrow-up' : 'fa-arrow-down';
                        var bgStyle = response.type === 'entree' ? 'background-color: rgba(25, 135, 84, 0.12); color: #198754;' : 'background-color: rgba(220, 53, 69, 0.12); color: #dc3545;';
                        
                        html += '<div class="row g-3 mb-4">';
                        html += '  <div class="col-md-6 d-flex align-items-center">';
                        html += '    <div class="rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem; ' + bgStyle + '">';
                        html += '      <i class="fa ' + typeIcon + '"></i>';
                        html += '    </div>';
                        html += '    <div>';
                        html += '      <h4 class="fw-bold mb-0 text-dark">' + parseFloat(response.montant).toLocaleString('fr-FR') + ' XOF</h4>';
                        html += '      <span class="badge bg-' + typeClass + '">' + typeText + '</span>';
                        html += '    </div>';
                        html += '  </div>';
                        html += '  <div class="col-md-6 border-start ps-3">';
                        html += '    <div class="small text-muted mb-1"><i class="fa fa-calendar me-2"></i>Date / Heure : <span class="text-dark fw-semibold">' + response.date + '</span></div>';
                        html += '    <div class="small text-muted mb-1"><i class="fa fa-user me-2"></i>Caissier : <span class="text-dark fw-semibold">' + response.user + '</span></div>';
                        html += '    <div class="small text-muted"><i class="fa fa-comment-alt me-2"></i>Motif : <span class="text-dark fw-semibold">' + (response.motif || '-') + '</span></div>';
                        html += '  </div>';
                        html += '</div>';
                        
                        html += '<hr class="my-4">';
                        
                        if (response.reference_type && response.reference_data) {
                            var ref = response.reference_data;
                            html += '<h5 class="fw-bold text-primary mb-3"><i class="fa fa-receipt me-2"></i>Document de Référence (' + response.reference_type + ')</h5>';
                            
                            if (response.reference_type === 'Ticket') {
                                html += '<div class="card border-0 bg-light p-3 mb-3 shadow-sm">';
                                html += '  <div class="row g-3">';
                                html += '    <div class="col-sm-6"><strong>Patient :</strong> ' + ref.patient + '</div>';
                                html += '    <div class="col-sm-6"><strong>Ticket ID :</strong> #' + ref.id + '</div>';
                                html += '    <div class="col-sm-6"><strong>Couverture Assurance :</strong> ' + ref.taux_couverture + '%</div>';
                                html += '    <div class="col-sm-6"><strong>Total Facturé :</strong> ' + parseFloat(ref.total).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '    <div class="col-sm-6"><strong>Part Patient payée :</strong> ' + parseFloat(ref.part_patient).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '    <div class="col-sm-6"><strong>Part Assurance :</strong> ' + parseFloat(ref.part_assurance).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '  </div>';
                                html += '</div>';
                                
                                if (ref.items && ref.items.length > 0) {
                                    html += '<h6 class="fw-bold text-secondary mb-2">Prestations du Ticket</h6>';
                                    html += '<div class="table-responsive">';
                                    html += '  <table class="table table-sm table-bordered bg-white">';
                                    html += '    <thead class="table-light"><tr><th>Prestation</th><th>Service</th><th class="text-end">Prix</th><th class="text-center">Qté</th><th class="text-end">Remise</th><th class="text-end">Total</th></tr></thead>';
                                    html += '    <tbody>';
                                    ref.items.forEach(function(item) {
                                        html += '      <tr>';
                                        html += '        <td>' + item.prestation + '</td>';
                                        html += '        <td>' + item.service + '</td>';
                                        html += '        <td class="text-end">' + parseFloat(item.prix).toLocaleString('fr-FR') + '</td>';
                                        html += '        <td class="text-center">' + item.quantite + '</td>';
                                        html += '        <td class="text-end">' + parseFloat(item.remise).toLocaleString('fr-FR') + '</td>';
                                        html += '        <td class="text-end fw-semibold">' + parseFloat(item.total).toLocaleString('fr-FR') + ' XOF</td>';
                                        html += '      </tr>';
                                    });
                                    html += '    </tbody>';
                                    html += '  </table>';
                                    html += '</div>';
                                }
                                
                                html += '<div class="mt-3 text-end">';
                                html += '  <a href="' + ref.view_url + '" class="btn btn-primary btn-sm"><i class="fa fa-eye me-1"></i>Ouvrir le Ticket complet</a>';
                                html += '</div>';
                                
                            } else if (response.reference_type === 'Hospitalisation') {
                                html += '<div class="card border-0 bg-light p-3 mb-3 shadow-sm">';
                                html += '  <div class="row g-3">';
                                html += '    <div class="col-sm-6"><strong>Patient :</strong> ' + ref.patient + '</div>';
                                html += '    <div class="col-sm-6"><strong>Hospitalisation ID :</strong> #' + ref.id + '</div>';
                                html += '    <div class="col-sm-6"><strong>Date d\'entrée :</strong> ' + ref.date_entree + '</div>';
                                html += '    <div class="col-sm-6"><strong>Date de sortie :</strong> ' + ref.date_sortie + '</div>';
                                html += '    <div class="col-sm-6"><strong>Salle / Chambre :</strong> ' + ref.salle + '</div>';
                                html += '    <div class="col-sm-6"><strong>Lit :</strong> ' + ref.lit + '</div>';
                                html += '    <div class="col-sm-6"><strong>État actuel :</strong> <span class="badge bg-secondary">' + ref.etat + '</span></div>';
                                html += '    <div class="col-sm-12"><strong>Motif :</strong> ' + (ref.motif || 'Non renseigné') + '</div>';
                                html += '  </div>';
                                html += '</div>';
                                
                                html += '<div class="mt-3 text-end">';
                                html += '  <a href="' + ref.view_url + '" class="btn btn-primary btn-sm"><i class="fa fa-bed me-1"></i>Ouvrir l\'hospitalisation</a>';
                                html += '</div>';
                                
                            } else if (response.reference_type === 'Paiement') {
                                html += '<div class="card border-0 bg-light p-3 mb-3 shadow-sm">';
                                html += '  <div class="row g-3">';
                                html += '    <div class="col-sm-6"><strong>Patient :</strong> ' + ref.patient + '</div>';
                                html += '    <div class="col-sm-6"><strong>Hospitalisation :</strong> #' + ref.hospitalisation_id + '</div>';
                                html += '    <div class="col-sm-6"><strong>Montant Total Hospitalisation :</strong> ' + parseFloat(ref.montant_total).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '    <div class="col-sm-6"><strong>Montant Reçu :</strong> ' + parseFloat(ref.montant_recu).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '    <div class="col-sm-6"><strong>Reste à payer :</strong> ' + parseFloat(ref.montant_restant).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '    <div class="col-sm-6"><strong>Statut :</strong> <span class="badge bg-info">' + ref.statut + '</span></div>';
                                html += '    <div class="col-sm-6"><strong>Date de sortie :</strong> ' + ref.date_sortie + '</div>';
                                html += '  </div>';
                                html += '</div>';
                                
                                html += '<div class="mt-3 text-end">';
                                html += '  <a href="' + ref.view_url + '" class="btn btn-primary btn-sm"><i class="fa fa-bed me-1"></i>Ouvrir l\'hospitalisation</a>';
                                html += '</div>';
                                
                            } else if (response.reference_type === 'Ordonnance') {
                                html += '<div class="card border-0 bg-light p-3 mb-3 shadow-sm">';
                                html += '  <div class="row g-3">';
                                html += '    <div class="col-sm-6"><strong>Patient :</strong> ' + ref.patient + '</div>';
                                html += '    <div class="col-sm-6"><strong>Ordonnance ID :</strong> #' + ref.id + '</div>';
                                html += '    <div class="col-sm-6"><strong>Statut Paiement :</strong> <span class="badge bg-success">' + ref.statut + '</span></div>';
                                html += '  </div>';
                                html += '</div>';
                                
                                if (ref.medicaments && ref.medicaments.length > 0) {
                                    html += '<h6 class="fw-bold text-secondary mb-2">Médicaments prescrits & vendus</h6>';
                                    html += '<div class="table-responsive">';
                                    html += '  <table class="table table-sm table-bordered bg-white">';
                                    html += '    <thead class="table-light"><tr><th>Médicament / Posologie</th><th class="text-end">Prix Unitaire</th><th class="text-center">Qté</th><th class="text-end">Total</th></tr></thead>';
                                    html += '    <tbody>';
                                    ref.medicaments.forEach(function(item) {
                                        html += '      <tr>';
                                        html += '        <td>' + item.nom + ' <br><small class="text-muted">' + (item.posologie || '') + ' (' + (item.duree || '-') + ' jrs)</small></td>';
                                        html += '        <td class="text-end">' + parseFloat(item.prix).toLocaleString('fr-FR') + '</td>';
                                        html += '        <td class="text-center">' + item.quantite + '</td>';
                                        html += '        <td class="text-end fw-semibold">' + (parseFloat(item.prix) * item.quantite).toLocaleString('fr-FR') + ' XOF</td>';
                                        html += '      </tr>';
                                    });
                                    html += '    </tbody>';
                                    html += '  </table>';
                                    html += '</div>';
                                }
                                
                                html += '<div class="mt-3 text-end">';
                                html += '  <a href="' + ref.view_url + '" class="btn btn-primary btn-sm"><i class="fa fa-file-prescription me-1"></i>Ouvrir l\'ordonnance</a>';
                                html += '</div>';
                                
                            } else if (response.reference_type === 'PaiementCommande') {
                                html += '<div class="card border-0 bg-light p-3 mb-3 shadow-sm">';
                                html += '  <div class="row g-3">';
                                html += '    <div class="col-sm-6"><strong>Fournisseur :</strong> ' + ref.fournisseur + '</div>';
                                html += '    <div class="col-sm-6"><strong>Commande :</strong> #' + ref.commande_id + ' (Ref: ' + ref.commande_ref + ')</div>';
                                html += '    <div class="col-sm-6"><strong>Montant Payé :</strong> ' + parseFloat(ref.montant).toLocaleString('fr-FR') + ' XOF</div>';
                                html += '    <div class="col-sm-6"><strong>Mode de règlement :</strong> ' + ref.mode + '</div>';
                                html += '    <div class="col-sm-6"><strong>Date Paiement :</strong> ' + ref.date_paiement + '</div>';
                                html += '    <div class="col-sm-12"><strong>Observations :</strong> ' + (ref.observations || 'Aucune') + '</div>';
                                html += '  </div>';
                                html += '</div>';
                                
                                html += '<div class="mt-3 text-end">';
                                html += '  <a href="' + ref.view_url + '" class="btn btn-primary btn-sm"><i class="fa fa-shopping-cart me-1"></i>Ouvrir la Commande</a>';
                                html += '</div>';
                            }
                        } else {
                            html += '<div class="alert alert-info"><i class="fa fa-info-circle me-2"></i>Aucune référence détaillée n\'est liée à cette opération.</div>';
                        }
                        
                        content.html(html);
                    },
                    error: function(xhr) {
                        content.html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle me-2"></i>Erreur de chargement : ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText) + '</div>');
                    }
                });
            });
        });
    </script>
@endsection
