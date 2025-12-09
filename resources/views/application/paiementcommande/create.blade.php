@extends('layouts.app')

@section('title', 'Nouveau Paiement')

@section('content')
    <div class="content">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">
                    <i class="fa fa-credit-card me-1"></i>
                    Nouveau Paiement
                </h3>
                <div class="block-options">
                    <a href="{{ route('paiementscommande.dashboard') }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Retour au Dashboard
                    </a>
                </div>
            </div>
            <div class="block-content">
                <form action="{{ route('paiementscommande.store') }}" method="POST" id="paymentForm">
                    @csrf

                    <div class="row">
                        <!-- Informations Commande -->
                        <div class="col-md-6">
                            <div class="block block-rounded block-bordered">
                                <div class="block-header block-header-default">
                                    <h4 class="block-title">Informations Commande</h4>
                                </div>
                                <div class="block-content">
                                    <!-- Sélection de la commande -->
                                    <div class="mb-4">
                                        <label class="form-label" for="commande_id">Commande <span class="text-danger">*</span></label>
                                        <select class="form-select @error('commande_id') is-invalid @enderror"
                                                id="commande_id" name="commande_id" required>
                                            <option value="">Sélectionner une commande</option>
                                            @foreach($commandes as $cmd)
                                                <option value="{{ $cmd->id }}"
                                                        data-total="{{ $cmd->total }}"
                                                        data-paid="{{ $cmd->montantPaye() }}"
                                                        data-remaining="{{ $cmd->reste_a_payer }}"
                                                        data-statut="{{ $cmd->StatutPaiement }}"
                                                    {{ isset($commande) && $commande->id == $cmd->id ? 'selected' : '' }}>
                                                    {{ $cmd->reference }} - {{ $cmd->fournisseur->nom ?? 'N/A' }} - {{ number_format($cmd->total, 2) }} €
                                                    ({{ $cmd->payment_status_text }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('commande_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Informations de la commande sélectionnée -->
                                    <div id="commandeInfo" class="bg-light rounded p-3" style="display: none;">
                                        <div class="row mb-2">
                                            <div class="col-12">
                                                <span class="badge bg-secondary" id="infoStatut">Statut</span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Total Commande:</small>
                                                <div class="fw-bold" id="infoTotal">0.00 €</div>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Déjà Payé:</small>
                                                <div class="fw-bold text-success" id="infoPaid">0.00 €</div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-12">
                                                <small class="text-muted">Reste à Payer:</small>
                                                <div class="fw-bold text-warning" id="infoRemaining">0.00 €</div>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations Paiement -->
                        <div class="col-md-6">
                            <div class="block block-rounded block-bordered">
                                <div class="block-header block-header-default">
                                    <h4 class="block-title">Informations Paiement</h4>
                                </div>
                                <div class="block-content">
                                    <!-- Montant -->
                                    <div class="mb-4">
                                        <label class="form-label" for="montant">Montant <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number"
                                                   class="form-control @error('montant') is-invalid @enderror"
                                                   id="montant"
                                                   name="montant"
                                                   step="0.01"
                                                   min="0.01"
                                                   placeholder="0.00"
                                                   required>
                                            <span class="input-group-text">€</span>
                                            @error('montant')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="form-text text-muted">
                                            Maximum: <span id="maxAmount">0.00 €</span>
                                        </small>
                                    </div>

                                    <!-- Mode de paiement -->
                                    <div class="mb-4">
                                        <label class="form-label" for="mode">Mode de Paiement <span class="text-danger">*</span></label>
                                        <select class="form-select @error('mode') is-invalid @enderror"
                                                id="mode" name="mode" required>
                                            <option value="">Sélectionner un mode</option>
                                            <option value="espèce" {{ old('mode') == 'espèce' ? 'selected' : '' }}>💵 Espèces</option>
                                            <option value="virement" {{ old('mode') == 'virement' ? 'selected' : '' }}>🏦 Virement Bancaire</option>
                                            <option value="chèque" {{ old('mode') == 'chèque' ? 'selected' : '' }}>📄 Chèque</option>
                                            <option value="carte" {{ old('mode') == 'carte' ? 'selected' : '' }}>💳 Carte Bancaire</option>
                                            <option value="autre" {{ old('mode') == 'autre' ? 'selected' : '' }}>📋 Autre</option>
                                        </select>
                                        @error('mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Date de paiement -->
                                    <div class="mb-4">
                                        <label class="form-label" for="date_paiement">Date de Paiement <span class="text-danger">*</span></label>
                                        <input type="date"
                                               class="form-control @error('date_paiement') is-invalid @enderror"
                                               id="date_paiement"
                                               name="date_paiement"
                                               value="{{ old('date_paiement', date('Y-m-d')) }}"
                                               required>
                                        @error('date_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Référence (auto-générée mais affichée) -->
                                    <div class="mb-4">
                                        <label class="form-label" for="reference">Référence Paiement</label>
                                        <input type="text"
                                               class="form-control bg-light"
                                               id="reference"
                                               value="Générée automatiquement"
                                               readonly>
                                        <small class="form-text text-muted">
                                            Cette référence sera générée automatiquement lors de l'enregistrement
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observations -->
                    <div class="row">
                        <div class="col-12">
                            <div class="block block-rounded block-bordered">
                                <div class="block-header block-header-default">
                                    <h4 class="block-title">Observations</h4>
                                </div>
                                <div class="block-content">
                                <textarea class="form-control @error('observations') is-invalid @enderror"
                                          id="observations"
                                          name="observations"
                                          rows="3"
                                          placeholder="Notes supplémentaires sur ce paiement...">{{ old('observations') }}</textarea>
                                    @error('observations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Résumé et Actions -->
                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="alert alert-info">
                                <h5 class="alert-heading">Résumé du Paiement</h5>
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Montant à payer:</strong><br>
                                        <span id="summaryAmount" class="fw-bold fs-5">0.00 €</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Nouveau statut:</strong><br>
                                        <span id="summaryNewStatut" class="fw-bold fs-5">-</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <strong>Nouveau solde:</strong><br>
                                        <span id="summaryNewBalance" class="fw-bold fs-5 text-warning">0.00 € restant</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="reset" class="btn btn-alt-secondary me-2">
                                <i class="fa fa-refresh me-1"></i> Réinitialiser
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fa fa-check me-1"></i> Enregistrer le Paiement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialiser la date du jour si vide
            if (!$('#date_paiement').val()) {
                $('#date_paiement').val(new Date().toISOString().split('T')[0]);
            }

            // Gérer la sélection de commande
            $('#commande_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const total = parseFloat(selectedOption.data('total')) || 0;
                const paid = parseFloat(selectedOption.data('paid')) || 0;
                const remaining = parseFloat(selectedOption.data('remaining')) || 0;
                const statut = selectedOption.data('statut') || 'en_cours';

                if (selectedOption.val()) {
                    // Afficher les informations
                    $('#commandeInfo').show();
                    $('#infoTotal').text(total.toFixed(2) + ' €');
                    $('#infoPaid').text(paid.toFixed(2) + ' €');
                    $('#infoRemaining').text(remaining.toFixed(2) + ' €');
                    $('#maxAmount').text(remaining.toFixed(2) + ' €');

                    // Mettre à jour le statut
                    updateStatutDisplay(statut, $('#infoStatut'));

                    // Mettre à jour la barre de progression
                    const progress = total > 0 ? (paid / total) * 100 : 0;
                    $('#progressBar').css('width', progress + '%');

                    // Changer la couleur de la barre selon le statut
                    updateProgressBarColor(statut);

                    // Mettre à jour le montant maximum
                    $('#montant').attr('max', remaining);

                    // Réinitialiser et mettre à jour le résumé
                    $('#montant').val('');
                    updateSummary();
                } else {
                    $('#commandeInfo').hide();
                }
            });

            // Mettre à jour l'affichage du statut
            function updateStatutDisplay(statut, element) {
                const statuts = {
                    'total': { text: 'Totalement Payée', class: 'bg-success' },
                    'partielle': { text: 'Partiellement Payée', class: 'bg-warning' },
                    'en_cours': { text: 'En Cours de Paiement', class: 'bg-danger' }
                };

                const statutInfo = statuts[statut] || { text: statut, class: 'bg-secondary' };
                element.text(statutInfo.text).removeClass('bg-success bg-warning bg-danger bg-secondary').addClass(statutInfo.class);
            }

            // Mettre à jour la couleur de la barre de progression
            function updateProgressBarColor(statut) {
                const progressBar = $('#progressBar');
                progressBar.removeClass('bg-success bg-warning bg-danger');

                switch(statut) {
                    case 'total':
                        progressBar.addClass('bg-success');
                        break;
                    case 'partielle':
                        progressBar.addClass('bg-warning');
                        break;
                    case 'en_cours':
                        progressBar.addClass('bg-danger');
                        break;
                }
            }

            // Mettre à jour le résumé quand le montant change
            $('#montant').on('input', function() {
                updateSummary();
                validateAmount();
            });

            function updateSummary() {
                const amount = parseFloat($('#montant').val()) || 0;
                const remaining = parseFloat($('#infoRemaining').text()) || 0;
                const newBalance = remaining - amount;

                $('#summaryAmount').text(amount.toFixed(2) + ' €');
                $('#summaryNewBalance').text(newBalance.toFixed(2) + ' € restant');

                // Déterminer le nouveau statut
                let newStatut = 'en_cours';
                let statutClass = 'text-danger';

                if (newBalance <= 0) {
                    newStatut = 'Totalement Payée';
                    statutClass = 'text-success';
                } else if (amount > 0) {
                    newStatut = 'Partiellement Payée';
                    statutClass = 'text-warning';
                } else {
                    newStatut = 'En Cours de Paiement';
                    statutClass = 'text-danger';
                }

                $('#summaryNewStatut').text(newStatut).removeClass('text-success text-warning text-danger').addClass(statutClass);

                // Changer la couleur du nouveau solde
                if (newBalance <= 0) {
                    $('#summaryNewBalance').removeClass('text-warning text-danger').addClass('text-success');
                } else if (newBalance > 0) {
                    $('#summaryNewBalance').removeClass('text-success text-danger').addClass('text-warning');
                }
            }

            function validateAmount() {
                const amount = parseFloat($('#montant').val()) || 0;
                const remaining = parseFloat($('#infoRemaining').text()) || 0;
                const submitBtn = $('#submitBtn');

                if (amount > remaining) {
                    submitBtn.prop('disabled', true);
                    $('#montant').addClass('is-invalid');
                    $('#montant').siblings('.invalid-feedback').remove();
                    $('#montant').after('<div class="invalid-feedback">Le montant ne peut pas dépasser le reste à payer</div>');
                } else {
                    submitBtn.prop('disabled', false);
                    $('#montant').removeClass('is-invalid');
                    $('#montant').siblings('.invalid-feedback').remove();
                }
            }

            // Validation du formulaire
            $('#paymentForm').on('submit', function(e) {
                const amount = parseFloat($('#montant').val()) || 0;
                const remaining = parseFloat($('#infoRemaining').text()) || 0;

                if (amount > remaining) {
                    e.preventDefault();
                    alert('Erreur: Le montant ne peut pas dépasser le reste à payer.');
                    return false;
                }

                if (amount <= 0) {
                    e.preventDefault();
                    alert('Erreur: Le montant doit être supérieur à 0.');
                    return false;
                }

                // Afficher un loader
                $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Enregistrement...');
            });

            // Si une commande est présélectionnée, déclencher le changement
            @if(isset($commande) && $commande)
            $('#commande_id').trigger('change');
            @endif
        });
    </script>

    <style>
        .block-bordered {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }
        .progress {
            background-color: #e9ecef;
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        .badge {
            font-size: 0.75rem;
        }
    </style>
@endsection
