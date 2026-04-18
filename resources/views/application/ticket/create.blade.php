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

            <div class="row mb-2">
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-primary" id="btnNouveauPatient">
                        <i class="fa fa-user-plus me-1"></i> Ajouter un Patient
                    </button>
                </div>
            </div>

            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Ajouter des prestations</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Patient --}}
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Patient</label>
                            <select class="form-select js-select2" id="patient" name="patient_id" required>
                                @if(isset($ticket) && $ticket->patient)
                                    <option value="{{ $ticket->patient->id }}" selected>
                                        {{ $ticket->patient->nom }} {{ $ticket->patient->prenom }} - {{ $ticket->patient->telephone }}
                                    </option>
                                @else
                                    <option value="">-- Sélectionner un patient --</option>
                                @endif
                            </select>
                        </div>

                        {{-- Assurance (Optionnel) --}}
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-success"><i class="fas fa-shield-alt"></i> Assurance</label>
                            <select class="form-select js-select2" id="assurance" name="assurance_id">
                                <option value="">-- Aucune assurance --</option>
                                @foreach($assurances as $assurance)
                                    <option value="{{ $assurance->id }}"
                                        {{ isset($ticket) && $ticket->assurance_id == $assurance->id ? 'selected' : '' }}>
                                        {{ $assurance->nom }} (Couvre à {{ $assurance->taux }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Prestation --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Prestation</label>
                            <select id="prestation" class="form-select js-select2">
                                <option value="">-- Choisir une prestation --</option>
                                @foreach($prestations as $prestation)
                                    <option value="{{ $prestation->id }}"
                                            data-service="{{ $prestation->serviceMedical->nom ?? 'N/A' }}"
                                            data-prix="{{ $prestation->prix }}"
                                            data-quantifiable="{{ $prestation->quantifiable ? '1' : '0' }}">
                                        {{ $prestation->nom }} ({{ $prestation->serviceMedical->nom ?? 'N/A' }}) - {{ $prestation->prix }} FCFA
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Quantité (Dynamique) --}}
                        <div class="col-md-2" id="container-quantite" style="display: none;">
                            <label class="form-label fw-bold">Quantité</label>
                            <input type="number" id="quantite" class="form-control" value="1" min="1">
                        </div>

                        {{-- Remise --}}
                        <div class="col-md-2">
                            <label class="form-label fw-bold">Remise (%)</label>
                            <input type="number" id="remise" class="form-control" value="0" min="0" max="100">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" id="btnAjouter" class="btn btn-success w-100"><span class="fa fa-plus me-1"></span> Ajouter</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-lg mb-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="panierTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Prestation</th>
                                    <th>Service</th>
                                    <th>Prix Unitaire</th>
                                    <th class="text-center">Quantité</th>
                                    <th>Remise</th>
                                    <th>Sous-total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($ticket) && $ticket->items?->count() > 0)
                                    @foreach($ticket->items as $ligne)
                                        <tr data-prestation-id="{{ $ligne->prestation_id }}" 
                                            data-quantifiable="{{ $ligne->prestation->quantifiable ? '1' : '0' }}">
                                            <td>{{ $ligne->prestation->nom }}</td>
                                            <td>{{ $ligne->prestation->serviceMedical->nom ?? '' }}</td>
                                            <td>{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                                            <td class="text-center">{{ $ligne->prestation->quantifiable ? $ligne->quantite : '-' }}</td>
                                            <td>{{ $ligne->remise }}%</td>
                                            <td class="sousTotal">{{ number_format($ligne->sous_total, 0, ',', ' ') }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm btnSupprimer">🗑️</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center text-muted py-4">Aucune prestation ajoutée</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td colspan="5" class="text-end fs-5">Total à payer (XOF) :</td>
                                    <td colspan="2" class="fs-5 text-primary" id="grandTotal">
                                        {{ isset($ticket) ? number_format($ticket->total, 0, ',', ' ') : 0 }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <label class="form-label fw-bold">Observations</label>
                    <textarea class="form-control mb-3" id="description" name="description" rows="2" placeholder="Notes optionnelles...">{{ $ticket->description ?? '' }}</textarea>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary">❌ Annuler</a>
                        <button type="submit" class="btn btn-primary px-5">
                            {{ isset($ticket) ? '💾 Mettre à jour' : '✅ Valider le Ticket' }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            
            // Initialisation de Select2 avec AJAX pour le patient
            $('#patient').select2({
                placeholder: "-- Rechercher un patient (nom ou tel) --",
                allowClear: true,
                ajax: {
                    url: "{{ route('patients.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term // Le terme recherché
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.nom + ' ' + item.prenom + ' - ' + item.telephone,
                                    id: item.id,
                                    assurance_id: item.assurance_id,
                                    fin_validite_assurance: item.fin_validite_assurance
                                }
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            // Autoremplissage et vérification assurance
            $('#patient').on('select2:select', function (e) {
                var data = e.params.data;
                if (data.assurance_id) {
                    let dNow = new Date();
                    dNow.setHours(0,0,0,0);
                    let valid = true;

                    if (data.fin_validite_assurance) {
                        let exp = new Date(data.fin_validite_assurance);
                        if (exp < dNow) {
                            valid = false;
                            Swal.fire({
                                icon: 'warning',
                                title: 'Assurance Expirée',
                                text: 'Les droits à l\'assurance de ce patient ont expiré le ' + data.fin_validite_assurance
                            });
                            $('#assurance').val('').trigger('change');
                        }
                    }

                    if (valid) {
                        $('#assurance').val(data.assurance_id).trigger('change');
                        if (typeof Toast !== 'undefined') {
                            Toast.fire({
                                icon: 'success',
                                title: 'Assurance rattachée automatiquement'
                            });
                        }
                    }
                } else {
                    $('#assurance').val('').trigger('change');
                }
            });

            // --- 1. Gestion affichage dynamique Quantité ---
            $('#prestation').on('change', function() {
                let selected = $(this).find('option:selected');
                let isQuantifiable = selected.data('quantifiable') == "1";

                if (isQuantifiable) {
                    $('#container-quantite').fadeIn();
                    $('#quantite').val(1);
                } else {
                    $('#container-quantite').hide();
                    $('#quantite').val(1); // On garde 1 pour le calcul interne
                }
            });

            // --- 2. Ajouter prestation au panier ---
            $("#btnAjouter").on("click", function() {
                let opt = $("#prestation option:selected");
                let id = opt.val();
                let nom = opt.text();
                let service = opt.data("service");
                let prix = parseInt(opt.data("prix"));
                let isQuantifiable = opt.data("quantifiable") == "1";
                
                let quantite = isQuantifiable ? parseInt($("#quantite").val()) : 1;
                let remise = parseFloat($("#remise").val()) || 0;

                if (!id || quantite <= 0 || remise < 0 || remise > 100) {
                    Swal.fire('Erreur', 'Vérifiez les données de la prestation.', 'error');
                    return;
                }

                if ($("#panierTable tbody tr[data-prestation-id='" + id + "']").length > 0) {
                    Swal.fire('Attention', 'Déjà dans le panier.', 'warning');
                    return;
                }

                let sousTotal = prix * quantite * (1 - remise / 100);
                $("#emptyRow").remove();

                let qteDisplay = isQuantifiable ? quantite : '-';

                let row = `
                    <tr data-prestation-id="${id}" data-quantifiable="${isQuantifiable ? '1' : '0'}">
                        <td>${nom}</td>
                        <td>${service}</td>
                        <td>${prix.toLocaleString()}</td>
                        <td class="text-center">${qteDisplay}</td>
                        <td>${remise.toFixed(0)}%</td>
                        <td class="sousTotal text-primary fw-bold">${sousTotal.toLocaleString()}</td>
                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm btnSupprimer"><span class="fa fa-trash me-1"></span></button></td>
                    </tr>`;

                $("#panierTable tbody").append(row);
                calculerTotal();
                
                // Reset champs
                $("#prestation").val('').trigger('change');
                $("#remise").val(0);
            });

            // --- 3. Supprimer une ligne ---
            $(document).on("click", ".btnSupprimer", function() {
                $(this).closest("tr").remove();
                if ($("#panierTable tbody tr").length === 0) {
                    $("#panierTable tbody").html(`<tr id="emptyRow"><td colspan="7" class="text-center text-muted py-4">Aucune prestation ajoutée</td></tr>`);
                }
                calculerTotal();
            });

            // --- 4. Calcul Total & Génération Inputs cachés ---
            function calculerTotal() {
                let total = 0;
                let items = [];

                $("#panierTable tbody tr").each(function() {
                    let row = $(this);
                    let id = row.data('prestation-id');
                    if (id) {
                        let isQuantifiable = row.data('quantifiable') == "1";
                        let cols = row.find("td");
                        
                        // Si pas quantifiable, on prend 1 par défaut pour le backend
                        let qteVal = isQuantifiable ? parseInt(cols.eq(3).text()) : 1;

                        let item = {
                            prestation_id: id,
                            prix_unitaire: parseInt(cols.eq(2).text().replace(/\s/g, '')),
                            quantite: qteVal,
                            remise: parseInt(cols.eq(4).text()) || 0,
                            sous_total: parseInt(cols.eq(5).text().replace(/\s/g, ''))
                        };
                        items.push(item);
                        total += item.sous_total;
                    }
                });

                $("#grandTotal").text(total.toLocaleString());

                // Nettoyage et injection des inputs pour Laravel
                $("#ticketForm input[name^='items']").remove();
                items.forEach((item, index) => {
                    for (const [key, value] of Object.entries(item)) {
                        $("#ticketForm").append(`<input type="hidden" name="items[${index}][${key}]" value="${value}">`);
                    }
                });
            }

            // --- 5. Soumission Formulaire ---
            $("#ticketForm").on("submit", function(e) {
                e.preventDefault();
                calculerTotal();

                if ($("#panierTable tbody tr[data-prestation-id]").length === 0) {
                    Swal.fire('Erreur', 'Veuillez ajouter au moins une prestation.', 'error');
                    return;
                }

                if(!$('#patient').val()){
                    Swal.fire('Erreur', 'Veuillez sélectionner un patient.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Confirmer l\'enregistrement ?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, valider',
                    cancelButtonText: 'Annuler'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: $(this).attr('action'),
                            type: "POST",
                            data: $(this).serialize(),
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Succès', res.message, 'success').then(() => {
                                        window.location.href = "{{ route('tickets.index') }}";
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Erreur', 'Une erreur est survenue lors de la sauvegarde.', 'error');
                            }
                        });
                    }
                });
            });

            // (Note: Le code pour le modal Nouveau Patient reste le même que votre version précédente)
            // ... Insérez ici votre code modal btnNouveauPatient si nécessaire ...
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
                            
                            <div class="row mb-3 border-top pt-3 mt-3">
                                <h6 class="text-primary fw-bold mb-2"><i class="fas fa-shield-alt"></i> Couverture Maladie (Optionnel)</h6>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small">Assurance</label>
                                    <select class="form-select form-select-sm" id="swal_assurance_id">
                                        <option value="">Aucune</option>
                                        @foreach($assurances as $assurance)
                                            <option value="{{ $assurance->id }}">{{ $assurance->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small">Fin Validité</label>
                                    <input type="date" class="form-control form-control-sm" id="swal_assurance_fin">
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label fw-semibold text-muted small">Numéro Assuré</label>
                                    <input type="text" class="form-control form-control-sm" id="swal_assurance_num" placeholder="Code ou Numéro carte...">
                                </div>
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
                            assurance_id: $('#swal_assurance_id').val() || null,
                            numero_assurance: $('#swal_assurance_num').val() || null,
                            fin_validite_assurance: $('#swal_assurance_fin').val() || null,
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
        });
    </script>
@endsection
 