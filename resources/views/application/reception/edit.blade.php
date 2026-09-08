@extends('layouts.app')

@section('title_page', 'Modification Réception')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm block block-rounded">
            <div class="card-header bg-primary text-white block-header block-header-default">
                <h5 class="mb-0 block-title">Modification Réception: {{ $reception->reference_reception }}</h5>
                <div class="block-options">
                    <a href="{{ route('receptions.index') }}" class="btn btn-alt-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            
            <div class="card-body block-content">
                <form id="formReception">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="commande_id" value="{{ $reception->commande_id }}">

                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label class="form-label">Référence Réception</label>
                            <input type="text" name="reference_reception" class="form-control"
                                   value="{{ $reception->reference_reception }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date de Réception</label>
                            <input
                                type="date"
                                name="date_reception"
                                class="form-control"
                                value="{{ \Carbon\Carbon::parse($reception->date_reception)->format('Y-m-d') }}"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Commande</label>
                            <input type="text" class="form-control" value="{{ $reception->commande->reference ?? 'N/A' }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fournisseur</label>
                            <input type="text" class="form-control" value="{{ $reception->fournisseur->nom ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <!-- Ligne vide retirée pour observation -->
                    </div>

                    <div id="produitsContainer" class="mt-4 table-responsive">
                        <table class="table table-bordered table-striped mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>Médicament</th>
                                    <th>Unité</th>
                                    <th>Stock</th>
                                    <th>Qte Cmd</th>
                                    <th>Déjà Reçu (Global)</th>
                                    <th>Reste à Recevoir</th>
                                    <th>Quantité de cette Réception</th>
                                    <th>Lot</th>
                                    <th>Date Péremption</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($produits as $index => $p)
                                @php
                                    $deja_recu_global = $p['quantite_commandee'] - $p['quantite_restante'] + $p['quantite_recue'];
                                    $reste_a_recevoir = $p['quantite_commandee'] - $deja_recu_global;
                                @endphp
                                <tr>
                                    <td>{{ $p['nom'] }}</td>
                                    <td>
                                        @if(!empty($p['unite_nom']))
                                            <span class="badge bg-secondary">{{ $p['unite_nom'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $p['stock_ancien'] }}</td>
                                    <td>{{ $p['quantite_commandee'] }}</td>
                                    <td>{{ $deja_recu_global }}</td>
                                    <td class="text-primary fw-bold">{{ $reste_a_recevoir }}</td>
                                    <td>
                                        <label class="form-label small text-muted mb-1">Max: {{ $p['quantite_restante'] }}</label>
                                        <input type="number"
                                               name="receptions[{{ $index }}][quantite_recue]"
                                               class="form-control quantite-recue"
                                               data-quantite-restante="{{ $p['quantite_restante'] }}"
                                               min="0" value="{{ $p['quantite_recue'] }}">

                                        <small class="text-danger message-erreur d-none">
                                            ⚠️ Quantité reçue supérieure à la quantité restante ({{ $p['quantite_restante'] }})
                                        </small>

                                        <input type="hidden" name="receptions[{{ $index }}][commande_medicament_id]" value="{{ $p['commande_medicament_id'] }}">
                                        <input type="hidden" name="receptions[{{ $index }}][medicament_id]" value="{{ $p['medicament_id'] }}">
                                        <input type="hidden" name="receptions[{{ $index }}][unite_id]" value="{{ $p['unite_id'] ?? '' }}">
                                        <input type="hidden" name="receptions[{{ $index }}][quantite_commandee]" value="{{ $p['quantite_commandee'] }}">
                                        <input type="hidden" name="receptions[{{ $index }}][prix_unitaire]" value="{{ $p['prix_unitaire'] }}">
                                    </td>
                                    <td><input type="text" name="receptions[{{ $index }}][lot]" class="form-control" value="{{ $p['lot'] ?? '' }}"></td>
                                    <td><input type="date" name="receptions[{{ $index }}][date_peremption]" class="form-control" value="{{ $p['date_peremption'] ? \Carbon\Carbon::parse($p['date_peremption'])->format('Y-m-d') : '' }}"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-4 text-end">
                        <a href="{{ route('receptions.index') }}" class="btn btn-secondary me-2">Annuler</a>
                        <button type="submit" class="btn btn-success" id="btnValider">
                            <i class="fa fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    function checkQuantitesValidite() {
        let inputs = $('.quantite-recue');
        if (inputs.length === 0) {
            $('#btnValider').prop('disabled', true);
            return false;
        }

        let auMoinsUneValide = false;
        let aUneErreur = false;

        inputs.each(function () {
            let val = parseFloat($(this).val()) || 0;
            let max = parseFloat($(this).data('quantite-restante')) || 0;
            let messageErreur = $(this).siblings('.message-erreur');

            if (val > max || val < 0) {
                $(this).css('border', '2px solid red');
                messageErreur.removeClass('d-none');
                aUneErreur = true;
            } else {
                $(this).css('border', '');
                messageErreur.addClass('d-none');
            }

            if (val > 0 && val <= max) {
                auMoinsUneValide = true;
            }
        });

        let estValide = auMoinsUneValide && !aUneErreur;
        $('#btnValider').prop('disabled', !estValide);
        return estValide;
    }

    // Vérification au chargement
    checkQuantitesValidite();

    // Vérification des quantités reçues à la saisie
    $(document).on('input change', '.quantite-recue', function () {
        checkQuantitesValidite();
    });

    // Soumission du formulaire AJAX
    $('#formReception').on('submit', function (e) {
        e.preventDefault();

        if (!checkQuantitesValidite()) {
            Swal.fire({
                icon: 'warning',
                title: 'Quantité requise',
                text: 'Veuillez saisir au moins une quantité reçue supérieure à 0.',
            });
            return false;
        }

        $.ajax({
            url: "{{ route('receptions.update', $reception) }}",
            type: "PUT",
            data: $(this).serialize(),
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Réception modifiée !',
                    text: response.message ?? 'Les modifications ont été enregistrées avec succès.',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                }).then(() => {
                    window.location.href = "{{ route('receptions.index') }}";
                });
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message ?? 'Une erreur est survenue.';

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let details = Object.values(errors).flat().join('<br>');

                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur de validation',
                        html: details,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: message,
                    });
                }
            }
        });
    });
});
</script>
@endsection
