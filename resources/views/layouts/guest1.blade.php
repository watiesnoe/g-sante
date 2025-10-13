@extends('layouts.app')

@section('titre', 'Créer un Ticket')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">🎟️ Nouveau Ticket</h2>

        {{-- Recherche patient --}}
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                👤 Patient
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label for="patient_id" class="form-label">Rechercher un patient</label>
                        <select id="patient_id" class="form-select">
                            <option value="">-- Sélectionner un patient --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">{{ $patient->nom }} {{ $patient->prenom }} - {{ $patient->telephone }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-success w-100">
                            ➕ Nouveau Patient
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formulaire Ticket --}}
        <form action="{{ route('tickets.store') }}" method="POST" id="ticketForm">
            @csrf

            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    🏥 Prestations
                </div>
                <div class="card-body">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                        <tr>
                            <th>Prestation</th>
                            <th>Prix (FCFA)</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- lignes ajoutées dynamiquement --}}
                        </tbody>
                    </table>
                    <button type="button" id="addItem" class="btn btn-outline-primary">
                        ➕ Ajouter une prestation
                    </button>
                </div>
            </div>

            {{-- Description --}}
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    📝 Informations complémentaires
                </div>
                <div class="card-body">
                    <textarea name="description" class="form-control" rows="3" placeholder="Notes sur le ticket..."></textarea>
                </div>
            </div>

            {{-- Total --}}
            <div class="card mb-3">
                <div class="card-header bg-dark text-white">
                    💰 Paiement
                </div>
                <div class="card-body">
                    <h4>Total : <span id="total">0</span> FCFA</h4>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">💾 Enregistrer le Ticket</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function(){

            // Prestations disponibles en JS
            let prestations = @json($prestations);

            // Ajouter une ligne
            $("#addItem").click(function(){
                let options = '<option value="">-- Sélectionner --</option>';
                prestations.forEach(function(prestation){
                    options += `<option value="${prestation.id}" data-prix="${prestation.prix}">${prestation.nom} (${prestation.prix} FCFA)</option>`;
                });

                let row = `
            <tr>
                <td>
                    <select name="prestations[]" class="form-select prestationSelect">
                        ${options}
                    </select>
                </td>
                <td class="prix">0</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm removeItem">❌</button>
                </td>
            </tr>
        `;
                $("#itemsTable tbody").append(row);
            });

            // Changer prestation => MAJ prix
            $(document).on('change', '.prestationSelect', function(){
                let prix = $(this).find(':selected').data('prix') || 0;
                $(this).closest('tr').find('.prix').text(prix);
                calculerTotal();
            });

            // Supprimer une ligne
            $(document).on('click', '.removeItem', function(){
                $(this).closest('tr').remove();
                calculerTotal();
            });

            // Calcul du total
            function calculerTotal(){
                let total = 0;
                $(".prix").each(function(){
                    total += parseFloat($(this).text()) || 0;
                });
                $("#total").text(total);
            }
        });
    </script>
@endsection
@extends('layouts.app')

@section('titre', isset($ticket) ? 'Édition Ticket Prestation' : 'Création Ticket Prestation')

@section('content')
    <div class="container mt-4">

        <!-- 🔍 Recherche patient -->
        <div class="card shadow-lg mb-3">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">🔍 Recherche Patient</h5>
                <button class="btn btn-light btn-sm" id="btnNouveauPatient">➕ Nouveau Patient</button>
            </div>
            <div class="card-body">
                <input type="text" class="form-control mb-2" id="search_patient" placeholder="Nom ou téléphone du patient...">
                <select class="form-select" id="patient">
                    <option value="">-- Sélectionner un patient existant --</option>
                    @foreach($patients as $patientOption)
                        <option value="{{ $patientOption->id }}"
                                @if(isset($ticket) && $ticket->patient_id == $patientOption->id) selected @endif>
                            {{ $patientOption->nom }} {{ $patientOption->prenom }} - {{ $patientOption->telephone }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 🏥 Ajout prestation -->
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
                        @if(isset($ticket) && $ticket->items->count())
                            @foreach($ticket->items as $item)
                                <tr data-prestation-id="{{ $item->prestation_id }}">
                                    <td>{{ $item->nom }}</td>
                                    <td>{{ $item->service }}</td>
                                    <td>{{ number_format($item->prix, 0, ',', ' ') }}</td>
                                    <td>{{ $item->quantite }}</td>
                                    <td>{{ $item->remise }}%</td>
                                    <td class="sousTotal">{{ number_format($item->sous_total, 0, ',', ' ') }}</td>
                                    <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">🗑️</button></td>
                                </tr>
                            @endforeach
                        @else
                            <tr id="emptyRow">
                                <td colspan="7" class="text-center text-muted">Aucune prestation ajoutée</td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">💰 Total</th>
                            <th id="grandTotal">
                                {{ isset($ticket) ? number_format($ticket->items->sum('sous_total'),0,',',' ') : 0 }}
                            </th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 📝 Observations + Actions -->
        <div class="card shadow-lg">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">📝 Observations & Validation</h5>
            </div>
            <div class="card-body">
                <form id="ticketForm" action="{{ isset($ticket) ? route('tickets.update', $ticket->id) : route('tickets.store') }}" method="POST">
                    @csrf
                    @if(isset($ticket))
                        @method('PUT')
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description / Observations</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Notes ou détails supplémentaires...">{{ $ticket->description ?? '' }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="reset" class="btn btn-outline-danger me-2">❌ Annuler</button>
                        <button type="submit" class="btn btn-success">{{ isset($ticket) ? '✅ Mettre à jour' : '✅ Enregistrer Ticket' }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            // --- Calcul total & création hidden inputs
            function calculerTotal(){
                let total = 0;
                let items = [];

                $("#panierTable tbody tr").each(function(){
                    let prestation_id = $(this).data('prestation-id');
                    if(prestation_id){
                        let cols = $(this).find("td");
                        let prix = parseInt(cols.eq(2).text().replace(/\s/g,'')) || 0;
                        let quantite = parseInt(cols.eq(3).text()) || 0;
                        let remise = parseFloat(cols.eq(4).text()) || 0;
                        let sous_total = prix * quantite * (1 - remise/100);
                        cols.eq(5).text(sous_total.toLocaleString());

                        items.push({
                            prestation_id: prestation_id,
                            nom: cols.eq(0).text(),
                            service: cols.eq(1).text(),
                            prix: prix,
                            quantite: quantite,
                            remise: remise,
                            sous_total: sous_total
                        });

                        total += sous_total;
                    }
                });

                $("#grandTotal").text(total.toLocaleString());

                // hidden inputs
                $("#ticketForm input[name^='items']").remove();
                items.forEach((item,index)=>{
                    for(const [key,value] of Object.entries(item)){
                        $("#ticketForm").append(`<input type="hidden" name="items[${index}][${key}]" value="${value}">`);
                    }
                });

                // patient
                $("#ticketForm input[name='patient_id']").remove();
                $("#ticketForm").append(`<input type="hidden" name="patient_id" value="${$("#patient").val()}">`);
            }

            // --- Initialisation total si édition
            calculerTotal();

            // --- Ajouter prestation
            $("#btnAjouter").on("click", function(){
                let prestation = $("#prestation option:selected");
                let id = prestation.val();
                let nom = prestation.text();
                let service = prestation.data("service");
                let prix = parseInt(prestation.data("prix")) || 0;
                let quantite = parseInt($("#quantite").val()) || 1;
                let remise = parseFloat($("#remise").val()) || 0;

                if(!id || quantite <= 0 || remise < 0 || remise > 100){
                    Swal.fire('Erreur','Veuillez choisir une prestation, une quantité valide et une remise entre 0 et 100.','error');
                    return;
                }

                $("#emptyRow").remove();
                let sousTotal = prix * quantite * (1 - remise/100);
                let row = `<tr data-prestation-id="${id}">
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
            $(document).on("click",".btnSupprimer", function(){
                $(this).closest("tr").remove();
                if($("#panierTable tbody tr").length===0){
                    $("#panierTable tbody").html(`<tr id="emptyRow"><td colspan="7" class="text-center text-muted">Aucune prestation ajoutée</td></tr>`);
                }
                calculerTotal();
            });

            // --- Recherche patient
            $("#search_patient").on("keyup", function(){
                let value = $(this).val().toLowerCase();
                $("#patient option").filter(function(){
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1 || $(this).val() === "");
                });
            });

            // --- Soumission Ajax
            $("#ticketForm").on("submit", function(e){
                e.preventDefault();
                if($("#panierTable tbody tr").length===0 || $("#emptyRow").length>0){
                    Swal.fire('Erreur','Ajoutez au moins une prestation au panier.','error');
                    return;
                }

                let $form=$(this);
                let url = $form.attr('action');
                let method = $form.find('input[name="_method"]').val() || 'POST';

                Swal.fire({
                    title:'Confirmer ?',
                    text:"Voulez-vous enregistrer ce ticket ?",
                    icon:'question',
                    showCancelButton:true,
                    confirmButtonText:'Oui',
                    cancelButtonText:'Annuler'
                }).then((result)=>{
                    if(result.isConfirmed){
                        let $submitBtn=$form.find('button[type="submit"]');
                        $submitBtn.prop('disabled', true);
                        $.ajax({
                            url: url,
                            type: method,
                            data: $form.serialize(),
                            success: function(response){
                                if(response.success){
                                    Swal.fire('✅ Succès', response.message, 'success').then(()=>{
                                        if(method==='POST'){
                                            $form[0].reset();
                                            $("#panierTable tbody").html(`<tr id="emptyRow"><td colspan="7" class="text-center text-muted">Aucune prestation ajoutée</td></tr>`);
                                            $("#grandTotal").text('0');
                                        } else {
                                            location.reload();
                                        }
                                    });
                                } else {
                                    Swal.fire('Erreur', response.message, 'error');
                                }
                            },
                            error:function(xhr){
                                let msg='Erreur serveur';
                                if(xhr.responseJSON && xhr.responseJSON.errors){
                                    msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                } else if(xhr.responseJSON && xhr.responseJSON.message){
                                    msg = xhr.responseJSON.message;
                                }
                                Swal.fire('Erreur', msg, 'error');
                            },
                            complete:function(){$submitBtn.prop('disabled', false);}
                        });
                    }
                });
            });

            // --- Nouveau patient
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

