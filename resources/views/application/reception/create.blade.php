@extends('layouts.app')

@section('title_page', 'Réception de Commande')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5>Réception de Commande</h5>
            </div>
            <div class="card-body">

                {{-- Sélection de la commande --}}
                <div class="mb-3">
                    <label for="commandeSelect" class="form-label">Sélectionner une commande</label>
                    <select name="commande_id" class="form-select" id="commandeSelect" required>
                        <option value="">-- Sélectionner Commande --</option>
                        @foreach($commandes as $c)
                        @if(in_array($c->statut, ['en_attente', 'en_cours']))
                            <option value="{{ $c->id }}">
                                {{ $c->reference }} - {{ $c->fournisseur->nom ?? 'Inconnu' }}
                                ({{ \Carbon\Carbon::parse($c->date_commande)->format('d/m/Y') }})
                            </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <form id="formReception">
                    @csrf
                    <input type="hidden" name="commande_id" id="commande_id">

                    {{-- Fournisseur affiché --}}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <label>Référence Réception</label>
                            <input type="text" id="reference_reception" name="reference_reception"
                                   class="form-control bg-light" placeholder="Générée automatiquement" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>Date de Réception</label>
                            <input
                                type="date"
                                name="date_reception"
                                class="form-control"
                                value="{{ date('Y-m-d') }}"
                                required
                            >
                        </div>


                        <div class="col-md-4">
                            <label>Fournisseur</label>
                            <input type="text" name="nomFournisseurAffiche" id="nomFournisseurAffiche"
                                   class="form-control" readonly>
                        </div>
                        <input type="hidden" name="fournisseur_id" id="fournisseur_id">
                    </div>

                    {{-- Produits --}}
                    <div id="produitsContainer" class="mt-4"></div>

                    {{-- Bouton Valider --}}
                    <button type="submit" class="btn btn-success mt-4" id="btnValider">✅ Valider</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {

            // Lorsqu'on sélectionne une commande
            $('#commandeSelect').on('change', function () {
                let commandeId = $(this).val();
                $('#commande_id').val(commandeId);
                $('#produitsContainer').html('');

                if (!commandeId) return;

                // Charger les produits liés à la commande
                $.ajax({
                    url: "{{ url('/commandes') }}/" + commandeId + "/produits",
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        console.log("✅ Données reçues :", data);

                        if (!data.produits || data.produits.length === 0) {
                            $('#produitsContainer').html('<p class="text-muted">✅ Tous les produits de cette commande ont déjà été reçus.</p>');
                            return;
                        }

                        // Mettre à jour les infos fournisseur
                        $('#fournisseur_id').val(data.fournisseur_id);
                        $('#nomFournisseurAffiche').val(data.fournisseur_nom);

                        // Construire le tableau des produits
                        let html = `
                    <table class="table table-bordered mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>Médicament</th>
                                <th>Stock</th>
                                <th>Quantité commandée</th>
                                <th>Quantité restante</th>
                                <th>Nouvelle quantité reçue</th>
                                <th>Lot</th>
                                <th>Date Péremption</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                        data.produits.forEach((p, index) => {
                            html += `
                                    <tr>
                                        <td>${p.nom}</td>
                                        <td>${p.stock_ancien}</td>
                                        <td>${p.quantite_commandee}</td>
                                        <td class="text-primary fw-bold">${p.quantite_restante}</td>
                                        <td>
                                            <input type="number"
                                                   name="receptions[${index}][quantite_recue]"
                                                   class="form-control quantite-recue"
                                                   data-quantite-restante="${p.quantite_restante}"
                                                   min="0" value="0">

                                            <small class="text-danger message-erreur d-none">
                                                ⚠️ Quantité reçue supérieure à la quantité restante (${p.quantite_restante})
                                            </small>

                                            <!-- 🟢 Identifiants nécessaires -->
                                            <input type="hidden" name="receptions[${index}][commande_medicament_id]" value="${p.commande_medicament_id}">
                                            <input type="hidden" name="receptions[${index}][medicament_id]" value="${p.medicament_id}">
                                            <input type="hidden" name="receptions[${index}][quantite_commandee]" value="${p.quantite_commandee}">
                                            <input type="hidden" name="receptions[${index}][prix_unitaire]" value="${p.prix_unitaire}">
                                        </td>
                                        <td><input type="text" name="receptions[${index}][lot]" class="form-control"></td>
                                        <td><input type="date" name="receptions[${index}][date_peremption]" class="form-control"></td>
                                    </tr>
                                `;
                                                    });


                        html += `</tbody></table>`;
                        $('#produitsContainer').html(html);
                    },
                    error: function (xhr, status, error) {
                        console.error("❌ Erreur AJAX :", status, error);
                        $('#produitsContainer').html('<p class="text-danger">Erreur lors du chargement des produits.</p>');
                    }
                });
            });

            // Vérification des quantités reçues
            $(document).on('input', '.quantite-recue', function () {
                let quantiteRestante = parseInt($(this).data('quantite-restante'));
                let quantiteRecue = parseInt($(this).val());
                let messageErreur = $(this).siblings('.message-erreur');

                if (quantiteRecue > quantiteRestante) {
                    $(this).css('border', '2px solid red');
                    messageErreur.removeClass('d-none');
                    $('#btnValider').prop('disabled', true);
                } else {
                    $(this).css('border', '');
                    messageErreur.addClass('d-none');

                    // Vérifie si toutes les autres saisies sont valides avant d'activer
                    let erreurExistante = $('.quantite-recue').toArray().some(input => {
                        return parseInt($(input).val()) > parseInt($(input).data('quantite-restante'));
                    });
                    $('#btnValider').prop('disabled', erreurExistante);
                }
            });

            // Soumission du formulaire AJAX
            $('#formReception').on('submit', function (e) {
                e.preventDefault();

                const $form = $(this);
                const $btn  = $('#btnValider');

                // 🔒 Anti-double-soumission
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...');

                $.ajax({
                    url: "{{ route('receptions.store') }}",
                    type: "POST",
                    data: $form.serialize(),
                    success: function (response) {
                        // Afficher la référence générée côté serveur
                        if (response.reference_reception) {
                            $('#reference_reception').val(response.reference_reception);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Réception enregistrée !',
                            text: response.message ?? 'Les produits ont été reçus avec succès.',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        // 🔓 Réactiver le bouton en cas d'erreur
                        $btn.prop('disabled', false).html('✅ Valider');

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
