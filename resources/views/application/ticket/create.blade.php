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
            $("#btnNouveauPatient").on("click", function(){
                Swal.fire({
                    title: '<i class="fa fa-user-plus me-2"></i>Nouveau Patient',
                    html: `
            <div class="row g-3">
                <!-- Section Informations Personnelles -->
                <div class="col-12">
                    <h6 class="text-primary mb-3 border-bottom pb-2">
                        <i class="fa fa-id-card me-2"></i>Informations Personnelles
                    </h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="np_nom">
                        <i class="fa fa-font me-1 text-muted"></i>Nom <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="np_nom" name="np_nom"
                           placeholder="Ex: DUPONT" required
                           data-bs-toggle="tooltip" title="Saisissez le nom en majuscules">
                    <div class="form-text text-end"><span id="nom-counter">0</span>/50 caractères</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="np_prenom">
                        <i class="fa fa-user me-1 text-muted"></i>Prénom <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="np_prenom" name="np_prenom"
                           placeholder="Ex: Jean" required>
                    <div class="form-text text-end"><span id="prenom-counter">0</span>/50 caractères</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="np_genre">
                        <i class="fa fa-venus-mars me-1 text-muted"></i>Genre <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="np_genre" name="np_genre" required>
                        <option value="">-- Choisir le genre --</option>
                        <option value="M">👨 Masculin</option>
                        <option value="F">👩 Féminin</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="np_age">
                        <i class="fa fa-calendar-alt me-1 text-muted"></i>Âge
                    </label>
                    <div class="input-group">
                        <input type="number" class="form-control" id="np_age" name="np_age"
                               placeholder="Ex: 35" min="0" max="150"
                               data-bs-toggle="tooltip" title="Âge entre 0 et 150 ans">
                        <span class="input-group-text bg-light">ans</span>
                    </div>
                </div>

                <!-- Section Contact et Démographie -->
                <div class="col-12 mt-4">
                    <h6 class="text-primary mb-3 border-bottom pb-2">
                        <i class="fa fa-address-book me-2"></i>Contact et Démographie
                    </h6>
                </div>

                <div class="col-12">
                    <label class="form-label" for="np_tel">
                        <i class="fa fa-phone me-1 text-muted"></i>Téléphone
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fa fa-phone text-muted"></i>
                        </span>
                        <input type="tel" class="form-control" id="np_tel" name="np_tel"
                               placeholder="Ex: 07 12 34 56 78"
                               pattern="[0-9]{10}"
                               data-bs-toggle="tooltip"
                               title="Format : 10 chiffres sans espace">
                    </div>
                    <div class="form-text">Format : 10 chiffres (ex: 0712345678)</div>
                </div>

                <div class="col-12">
                    <label class="form-label" for="np_ethnie">
                        <i class="fa fa-globe-africa me-1 text-muted"></i>Ethnie
                    </label>
                    <select class="form-select" id="np_ethnie" name="np_ethnie">
                        <option value="">-- Choisir une ethnie --</option>
                        <option value="Bambara">Bambara</option>
                        <option value="Peul">Peul</option>
                        <option value="Sénoufo">Sénoufo</option>
                        <option value="Malinké">Malinké</option>
                        <option value="Soninké">Soninké</option>
                        <option value="Dogon">Dogon</option>
                        <option value="Autre">Autre</option>
                    </select>
                    <div class="form-text mt-1">
                        <input type="text" class="form-control form-control-sm d-none mt-2"
                               id="np_ethnie_autre" placeholder="Préciser l'ethnie">
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <div class="alert alert-info py-2 mb-0">
                    <i class="fa fa-info-circle me-2"></i>
                    <small>
                        <span class="text-danger">*</span> Champs obligatoires.
                        Les informations seront modifiables ultérieurement.
                    </small>
                </div>
            </div>
        `,
                    width: '700px',
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa fa-save me-1"></i>Enregistrer le patient',
                    cancelButtonText: '<i class="fa fa-times me-1"></i>Annuler',
                    confirmButtonColor: '#1bc5bd',
                    cancelButtonColor: '#d33',
                    customClass: {
                        popup: 'swal2-dashmix swal2-enhanced',
                        confirmButton: 'btn btn-success px-4',
                        cancelButton: 'btn btn-danger px-4'
                    },
                    buttonsStyling: false,
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        // Validation améliorée
                        const nom = $("#np_nom").val().trim();
                        const prenom = $("#np_prenom").val().trim();
                        const genre = $("#np_genre").val();
                        const tel = $("#np_tel").val().trim();
                        let ethnie = $("#np_ethnie").val();

                        // Validation des champs obligatoires
                        if (!nom) {
                            showFieldError('np_nom', 'Le nom est obligatoire');
                            return false;
                        }
                        if (nom.length > 50) {
                            showFieldError('np_nom', 'Le nom ne doit pas dépasser 50 caractères');
                            return false;
                        }

                        if (!prenom) {
                            showFieldError('np_prenom', 'Le prénom est obligatoire');
                            return false;
                        }
                        if (prenom.length > 50) {
                            showFieldError('np_prenom', 'Le prénom ne doit pas dépasser 50 caractères');
                            return false;
                        }

                        if (!genre) {
                            showFieldError('np_genre', 'Le genre est obligatoire');
                            return false;
                        }

                        // Validation téléphone
                        if (tel && !/^[0-9]{10}$/.test(tel.replace(/\s/g, ''))) {
                            showFieldError('np_tel', 'Le numéro doit contenir 10 chiffres');
                            return false;
                        }

                        // Gestion ethnie "Autre"
                        if (ethnie === 'Autre') {
                            const autreEthnie = $("#np_ethnie_autre").val().trim();
                            if (!autreEthnie) {
                                showFieldError('np_ethnie_autre', 'Veuillez préciser l\'ethnie');
                                return false;
                            }
                            ethnie = autreEthnie;
                        }

                        // Préparation des données
                        const data = {
                            nom: nom.toUpperCase(),
                            prenom: prenom,
                            genre: genre,
                            telephone: tel.replace(/\s/g, ''),
                            ethnie: ethnie,
                            age: $("#np_age").val() || null,
                            _token: "{{ csrf_token() }}"
                        };

                        return $.post("{{ route('patients.store') }}", data)
                            .done(function(response){
                                if(response.success){
                                    let p = response.patient;
                                    let text = `${p.nom} ${p.prenom}${p.telephone ? ' - ' + p.telephone : ''}`;

                                    // Ajouter au select
                                    $("#patient").append(new Option(text, p.id, true, true));
                                    $("#patient").trigger('change');

                                    Swal.fire({
                                        icon: 'success',
                                        title: '<i class="fa fa-check-circle me-2"></i>Patient créé !',
                                        html: `
                                <div class="text-start">
                                    <p class="mb-2"><strong>${p.nom} ${p.prenom}</strong> a été ajouté avec succès.</p>
                                    <small class="text-muted">ID Patient: ${p.id}</small>
                                </div>
                            `,
                                        timer: 3000,
                                        showConfirmButton: false,
                                        customClass: { popup: 'swal2-dashmix' }
                                    });
                                }
                            })
                            .fail(function(xhr){
                                let errorMessage = 'Erreur lors de l\'enregistrement';
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors).flat();
                                    errorMessage = errors.join('<br>');
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.showValidationMessage(`<i class="fa fa-exclamation-triangle me-2"></i>${errorMessage}`);
                            });
                    },
                    didOpen: () => {
                        // Initialisation
                        setTimeout(() => $("#np_nom").focus(), 300);

                        // Styles améliorés
                        $('.swal2-enhanced .form-control, .swal2-enhanced .form-select').addClass('form-control-alt');

                        // Compteurs de caractères
                        $('#np_nom, #np_prenom').on('input', function() {
                            const count = $(this).val().length;
                            const counterId = $(this).attr('id') + '-counter';
                            $('#' + counterId).text(count);
                            if (count > 50) {
                                $('#' + counterId).addClass('text-danger');
                            } else {
                                $('#' + counterId).removeClass('text-danger');
                            }
                        });

                        // Gestion ethnie "Autre"
                        $('#np_ethnie').on('change', function() {
                            const autreField = $('#np_ethnie_autre');
                            if ($(this).val() === 'Autre') {
                                autreField.removeClass('d-none').prop('required', true);
                                setTimeout(() => autreField.focus(), 100);
                            } else {
                                autreField.addClass('d-none').prop('required', false);
                            }
                        });

                        // Formatage téléphone
                        $('#np_tel').on('input', function() {
                            let value = $(this).val().replace(/\D/g, '');
                            if (value.length > 0) {
                                value = value.match(/.{1,2}/g).join(' ');
                            }
                            $(this).val(value.substring(0, 14));
                        });

                        // Initialisation des tooltips Bootstrap
                        $('[data-bs-toggle="tooltip"]').tooltip({
                            trigger: 'hover focus',
                            placement: 'top'
                        });
                    },
                    willClose: () => {
                        // Nettoyage des tooltips
                        $('[data-bs-toggle="tooltip"]').tooltip('dispose');
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
