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

            <div class="row">
                <div class="">
                    <button  type="button" class="btnNouveauPatient btn btn-primary float-end mb-2" id="btnNouveauPatient" >Ajouter</button>
                </div>

            </div>
            <!-- 🏥 Ajouter prestation -->
            <div class="card  mb-3">

                <div class="card-header bg-primary text-white">

                </div>
                <div class="card-body">


                    <div class="row g-3  mb-3">
                        <div class="col-12 ">
                            <select class="form-select js-select2" id="patient" name="patient_id">
                                <option value="">-- Sélectionner un patient existant --</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ isset($ticket) && $ticket->patient_id == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->nom }} {{ $patient->prenom }} - {{ $patient->telephone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Prestation</label>
                            <select id="prestation" class="form-select js-select2 hyphens-auto">
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
                <div class="card-header bg-primary">
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
                <div class="card-header bg-primary text-white">
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
        // Formulaire Nouveau Patient - Version Simplifiée
        // Version finale: Modal style carte avec boutons clairs
            $("#btnNouveauPatient").on("click", function() {
                Swal.fire({
                    title: false,
                    html: `
                        <div class="text-start">
                            <div class="text-center mb-4">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3 d-inline-block">
                                    <i class="fa fa-user-plus fa-2x text-primary"></i>
                                </div>
                                <h4 class="mt-2 mb-0">Nouveau Patient</h4>
                                <small class="text-muted">Remplissez les informations ci-dessous</small>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fa fa-font me-1"></i>Nom <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="nom" placeholder="DUPONT">
                                    <small class="text-muted"><span id="nomCount">0</span>/50</small>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">
                                        <i class="fa fa-user me-1"></i>Prénom <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="prenom" placeholder="Jean">
                                    <small class="text-muted"><span id="prenomCount">0</span>/50</small>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Genre <span class="text-danger">*</span></label>
                                    <select class="form-select" id="genre">
                                        <option value="">Sélectionner</option>
                                        <option value="M">👨 Masculin</option>
                                        <option value="F">👩 Féminin</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold">Âge</label>
                                    <input type="number" class="form-control" id="age" placeholder="Âge" min="0" max="150">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="fa fa-phone me-1"></i>Téléphone
                                </label>
                                <input type="tel" class="form-control" id="telephone" placeholder="07 12 34 56">
                                <small class="text-muted">8 chiffres</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ethnie</label>
                                <select class="form-select" id="ethnie">
                                    <option value="">Choisir</option>
                                    <option value="Bambara">Bambara</option>
                                    <option value="Peulh">Peulh</option>
                                    <option value="Malinké">Malinké</option>
                                    <option value="Sénoufo">Sénoufo</option>
                                    <option value="Dogon">Dogon</option>
                                    <option value="Autre">Autre</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" id="ethnieAutre" placeholder="Précisez l'ethnie">
                            </div>
                            
                            <div class="alert alert-info mt-3 py-2 mb-0">
                                <i class="fa fa-info-circle me-1"></i>
                                <small>Les champs avec <span class="text-danger">*</span> sont obligatoires</small>
                            </div>
                        </div>
                    `,
                    width: '600px',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: '<i class="fa fa-save me-2"></i>Enregistrer',
                    cancelButtonText: '<i class="fa fa-times me-2"></i>Annuler',
                    confirmButtonColor: '#1bc5bd',
                    cancelButtonColor: '#dc3545',
                    reverseButtons: false,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-4 shadow-lg',
                        confirmButton: 'btn btn-success px-4 py-2 fw-semibold',
                        cancelButton: 'btn btn-danger px-4 py-2 fw-semibold',
                        actions: 'd-flex justify-content-end gap-2'
                    },
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        // Récupération des données
                        const nom = $('#nom').val().trim();
                        const prenom = $('#prenom').val().trim();
                        const genre = $('#genre').val();
                        const telephone = $('#telephone').val().replace(/\s/g, '');
                        let ethnie = $('#ethnie').val();
                        
                        // Validation
                        if (!nom) {
                            Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Le nom est requis');
                            return false;
                        }
                        if (nom.length > 50) {
                            Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Nom trop long (max 50)');
                            return false;
                        }
                        
                        if (!prenom) {
                            Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Le prénom est requis');
                            return false;
                        }
                        if (prenom.length > 50) {
                            Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Prénom trop long (max 50)');
                            return false;
                        }
                        
                        if (!genre) {
                            Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Le genre est requis');
                            return false;
                        }
                        
                        if (telephone && !/^\d{8}$/.test(telephone)) {
                            Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Le téléphone doit contenir 8 chiffres');
                            return false;
                        }
                        
                        if (ethnie === 'Autre') {
                            ethnie = $('#ethnieAutre').val().trim();
                            if (!ethnie) {
                                Swal.showValidationMessage('<i class="fa fa-exclamation-circle me-2"></i>Veuillez préciser l\'ethnie');
                                return false;
                            }
                        }
                        
                        // Envoi
                        return $.post("{{ route('patients.store') }}", {
                            nom: nom.toUpperCase(),
                            prenom: prenom,
                            genre: genre,
                            telephone: telephone,
                            ethnie: ethnie || null,
                            age: $('#age').val() || null,
                            _token: $('meta[name="csrf-token"]').attr('content') || "{{ csrf_token() }}"
                        });
                    },
                    didOpen: () => {
                        // Focus sur le nom
                        $('#nom').focus();
                        
                        // Compteurs de caractères
                        $('#nom').on('input', function() {
                            const count = $(this).val().length;
                            $('#nomCount').text(count);
                            if (count > 50) {
                                $('#nomCount').addClass('text-danger fw-bold');
                            } else {
                                $('#nomCount').removeClass('text-danger fw-bold');
                            }
                        });
                        
                        $('#prenom').on('input', function() {
                            const count = $(this).val().length;
                            $('#prenomCount').text(count);
                            if (count > 50) {
                                $('#prenomCount').addClass('text-danger fw-bold');
                            } else {
                                $('#prenomCount').removeClass('text-danger fw-bold');
                            }
                        });
                        
                        // Ethnie "Autre"
                        $('#ethnie').on('change', function() {
                            const autreField = $('#ethnieAutre');
                            if ($(this).val() === 'Autre') {
                                autreField.removeClass('d-none').focus();
                            } else {
                                autreField.addClass('d-none').val('');
                            }
                        });
                        
                        // Formatage téléphone
                        $('#telephone').on('input', function() {
                            let value = $(this).val().replace(/\D/g, '');
                            if (value.length >= 2) {
                                value = value.match(/.{1,2}/g).join(' ');
                            }
                            $(this).val(value.substring(0, 14));
                        });
                        
                        // Style des boutons
                        $('.swal2-confirm').css({
                            'background-color': '#1bc5bd',
                            'border': 'none',
                            'border-radius': '8px',
                            'font-weight': '500'
                        });
                        
                        $('.swal2-cancel').css({
                            'background-color': '#dc3545',
                            'border': 'none',
                            'border-radius': '8px',
                            'font-weight': '500'
                        });
                    }
                }).then(result => {
                    if (result.isConfirmed && result.value?.success) {
                        const p = result.value.patient;
                        
                        // Ajout au select
                        const displayText = `${p.nom} ${p.prenom}${p.telephone ? ' - ' + p.telephone : ''}`;
                        const option = new Option(displayText, p.id, true, true);
                        $('#patient').append(option).trigger('change');
                        
                        // Notification de succès
                        Swal.fire({
                            icon: 'success',
                            title: 'Patient créé avec succès !',
                            html: `
                                <div class="text-center py-3">
                                    <div class="mb-3">
                                        <i class="fa fa-check-circle fa-4x text-success"></i>
                                    </div>
                                    <h5 class="mb-2 fw-bold">${p.nom} ${p.prenom}</h5>
                                    <div class="d-flex justify-content-center gap-3 text-muted small">
                                        ${p.telephone ? `<span><i class="fa fa-phone"></i> ${p.telephone}</span>` : ''}
                                        <span><i class="fa fa-id-card"></i> ID: ${p.id}</span>
                                    </div>
                                </div>
                            `,
                            timer: 3000,
                            showConfirmButton: true,
                            confirmButtonText: '<i class="fa fa-check me-1"></i> OK',
                            confirmButtonColor: '#1bc5bd',
                            customClass: {
                                popup: 'rounded-4',
                                confirmButton: 'btn px-4'
                            }
                        });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        // Optionnel: message d'annulation
                        console.log('Création annulée');
                    }
                });
            });
            // Fonction utilitaire pour afficher les erreurs de champ
            function showFieldError(fieldId, message) {
                $(`#${fieldId}`).addClass('is-invalid');
                Swal.showValidationMessage(`<i class="fa fa-exclamation-circle me-2"></i>${message}`);
                setTimeout(() => $(`#${fieldId}`).focus(), 100);
            }

                // Fonction utilitaire pour afficher les erreurs de champ
            function showFieldError(fieldId, message) {
                $(`#${fieldId}`).addClass('is-invalid');
                Swal.showValidationMessage(`<i class="fa fa-exclamation-circle me-2"></i>${message}`);
                setTimeout(() => $(`#${fieldId}`).focus(), 100);
            }

        });
    </script>
@endsection
