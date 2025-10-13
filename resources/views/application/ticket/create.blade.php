@extends('layouts.app')

@section('titre', isset($ticket) ? '✏️ Édition Ticket Prestation' : '➕ Création Ticket Prestation')

@section('content')
    <div class="container mt-4">

        <form id="ticketForm"
              action="{{ isset($ticket) ? route('tickets.update', $ticket->id) : route('tickets.store') }}"
              method="POST">
            @csrf
            @if(isset($ticket))
                @method('PUT')
            @endif

            <!-- 🔍 Patient -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🔍 Recherche Patient</h5>
                    <button class="btn btn-light btn-sm" type="button" id="btnNouveauPatient">➕ Nouveau Patient</button>
                </div>
                <div class="card-body">
                    <input type="text" class="form-control mb-2" id="search_patient" placeholder="Nom ou téléphone du patient...">
                    <select class="form-select" id="patient" name="patient_id">
                        <option value="">-- Sélectionner un patient existant --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                {{ isset($ticket) && $ticket->patient_id == $patient->id ? 'selected' : '' }}>
                                {{ $patient->nom }} {{ $patient->prenom }} - {{ $patient->telephone }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- 🏥 Ajouter prestation -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🏥 Ajouter une Prestation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Prestation</label>
                            <select id="prestation" class="form-select">
                                <option value="">-- Choisir une prestation --</option>
                                @foreach($prestations as $prestation)
                                    <option value="{{ $prestation->id }}"
                                            data-service="{{ $prestation->serviceMedical->nom ?? '' }}"
                                            data-prix="{{ $prestation->prix }}">
                                        {{ $prestation->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Quantité</label>
                            <input type="number" id="quantite" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Remise (%)</label>
                            <input type="number" id="remise" class="form-control" value="0" min="0" max="100">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="btnAjouter" class="btn btn-success w-100">➕ Ajouter</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🛒 Panier -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">🛒 Prestations Ajoutées</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-0">
                        <table class="table table-bordered align-middle" id="panierTable">
                            <thead class="table-light">
                            <tr>
                                <th>Prestation</th>
                                <th>Service</th>
                                <th>Prix Unitaire (XOF)</th>
                                <th>Quantité</th>
                                <th>Remise</th>
                                <th>Sous-total</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($ticket) && $ticket->items?->count() > 0)
                                @foreach($ticket->items as $ligne)
                                    <tr data-prestation-id="{{ $ligne->prestation_id }}">
                                        <td>{{ $ligne->prestation->nom }}</td>
                                        <td>{{ $ligne->prestation->serviceMedical->nom ?? '' }}</td>
                                        <td>{{ number_format($ligne->prix_unitaire,0,',',' ') }}</td>
                                        <td>{{ $ligne->quantite }}</td>
                                        <td>{{ $ligne->remise }}%</td>
                                        <td class="sousTotal">{{ number_format($ligne->sous_total,0,',',' ') }}</td>
                                        <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">🗑️</button></td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">💰 Total</th>
                                <th id="grandTotal">{{ isset($ticket) ? number_format($ticket->total,0,',',' ') : 0 }}</th>
                                <th></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 📝 Observations -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">📝 Observations & Validation</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control mb-3" id="description" name="description" rows="3" placeholder="Notes ou détails supplémentaires...">{{ $ticket->description ?? '' }}</textarea>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-danger me-2">❌ Annuler</a>
                        <button type="submit" class="btn btn-success">
                            {{ isset($ticket) ? '✏️ Mettre à jour Ticket' : '✅ Enregistrer Ticket' }}
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            // --- Recherche patient
            $("#search_patient").on("keyup", function(){
                var value = $(this).val().toLowerCase();
                $("#patient option").filter(function(){
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1 || $(this).val() === "");
                });
            });

            // --- Ajouter prestation au panier
            // --- Ajouter prestation au panier
            $("#btnAjouter").on("click", function(){
                let prestation = $("#prestation option:selected");
                let id = prestation.val();
                let nom = prestation.text();
                let service = prestation.data("service");
                let prix = parseInt(prestation.data("prix"));
                let quantite = parseInt($("#quantite").val());
                let remise = parseFloat($("#remise").val()) || 0;

                if(!id || quantite <= 0 || remise < 0 || remise > 100){
                    Swal.fire('Erreur','Veuillez choisir une prestation, une quantité valide et une remise entre 0 et 100.','error');
                    return;
                }

                // 🚨 Vérifier si la prestation est déjà dans le tableau
                if($("#panierTable tbody tr[data-prestation-id='"+id+"']").length > 0){
                    Swal.fire('Attention','Cette prestation est déjà ajoutée dans le panier.','warning');
                    return;
                }

                let sousTotal = prix * quantite * (1 - remise/100);
                $("#emptyRow").remove();

                let row = `
                    <tr data-prestation-id="${id}">
                        <td>${nom}</td>
                        <td>${service}</td>
                        <td>${prix.toLocaleString()}</td>
                        <td>${quantite}</td>
                        <td>${remise.toFixed(0)}%</td>
                        <td class="sousTotal">${sousTotal.toLocaleString()}</td>
                        <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">🗑️</button></td>
                    </tr>`;
                $("#panierTable tbody").append(row);
                calculerTotal();
            });


            // --- Supprimer ligne
            $(document).on("click", ".btnSupprimer", function(){
                $(this).closest("tr").remove();
                if($("#panierTable tbody tr").length === 0){
                    $("#panierTable tbody").html(`<tr id="emptyRow">
                <td colspan="7" class="text-center text-muted">Aucune prestation ajoutée</td>
                </tr>`);
                }
                calculerTotal();
            });

            // --- Calcul total & hidden inputs
            function calculerTotal(){
                let total = 0;
                let items = [];

                $("#panierTable tbody tr").each(function(){
                    let cols = $(this).find("td");
                    let prestation_id = $(this).data('prestation-id');
                    if(prestation_id){
                        let item = {
                            prestation_id: prestation_id,
                            service: cols.eq(1).text(),  // ajouter service
                            prix_unitaire: parseInt(cols.eq(2).text().replace(/\s/g,'')),
                            quantite: parseInt(cols.eq(3).text()),
                            remise: parseInt(cols.eq(4).text()) || 0,
                            sous_total: parseInt(cols.eq(5).text().replace(/\s/g,''))
                        };

                        items.push(item);
                        total += item.sous_total;
                    }
                });

                $("#grandTotal").text(total.toLocaleString());

                $("#ticketForm input[name^='items']").remove();
                items.forEach((item, index) => {
                    for (const [key, value] of Object.entries(item)) {
                        $("#ticketForm").append(
                            `<input type="hidden" name="items[${index}][${key}]" value="${value}">`
                        );
                    }
                });
            }

            // --- Soumission formulaire
            $("#ticketForm").on("submit", function(e){
                e.preventDefault();

                // ⚡ Assurer que les hidden inputs items sont générés avant validation
                calculerTotal();

                if($("#panierTable tbody tr").length === 0 || $("#emptyRow").length > 0){
                    Swal.fire('Erreur','Ajoutez au moins une prestation au panier.','error');
                    return;
                }

                let $form = $(this);
                let formData = $form.serialize();
                let isUpdate = $form.find("input[name='_method']").val() === "PUT";

                Swal.fire({
                    title: isUpdate ? 'Confirmer la modification ?' : 'Confirmer la création ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if(result.isConfirmed){
                        $.ajax({
                            url: $form.attr('action'),
                            type: "POST",
                            data: formData,
                            success: function(response){
                                if(response.success){
                                    Swal.fire('✅ Succès', response.message, 'success').then(() => {
                                        window.location.href = "{{ route('tickets.index') }}";
                                    });
                                } else {
                                    Swal.fire('Erreur','Opération échouée.','error');
                                }
                            },
                            error: function(xhr){
                                let msg = 'Erreur serveur';
                                if(xhr.responseJSON && xhr.responseJSON.errors){
                                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                } else if(xhr.responseJSON && xhr.responseJSON.message){
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Erreur', msg, 'error');
                            }
                        });
                    }
                });
            });
            $("#btnNouveauPatient").on("click", function(){
                Swal.fire({
                    title:'Nouveau Patient',
                    html:`
                <input id="np_nom" class="swal2-input" placeholder="Nom">
                <input id="np_prenom" class="swal2-input" placeholder="Prénom">
                <select id="np_genre" class="swal2-input">
                    <option value="">-- Genre --</option>
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                </select>
                <input id="np_tel" class="swal2-input" placeholder="Téléphone">
                <input id="np_ethnie" class="swal2-input" placeholder="Ethnie">
                <input id="np_age" type="number" class="swal2-input" placeholder="Âge" min="0">
            `,
                    focusConfirm:false,
                    preConfirm:()=>{
                        let data={
                            nom: $("#np_nom").val(),
                            prenom: $("#np_prenom").val(),
                            genre: $("#np_genre").val(),
                            telephone: $("#np_tel").val(),
                            ethnie: $("#np_ethnie").val(),
                            age: $("#np_age").val(),
                            _token: "{{ csrf_token() }}"
                        };
                        return $.post("{{ route('patients.store') }}", data)
                            .done(function(response){
                                if(response.success){
                                    let p = response.patient;
                                    let text = `${p.nom} ${p.prenom} - ${p.telephone}`;
                                    $("#patient").append(new Option(text, p.id, true, true));
                                    Swal.fire('Ajouté !','Nouveau patient enregistré avec succès.','success');
                                }
                            })
                            .fail(function(xhr){
                                Swal.showValidationMessage('Erreur : ' + (xhr.responseJSON.message || 'Erreur serveur'));
                            });
                    }
                });
            });

        });
    </script>
@endsection
