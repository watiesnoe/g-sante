@extends('layouts.app')

@section('titre')
    {{ isset($patient) ? 'Modifier' : 'Ajouter' }} un Patient
@endsection

@section('content')
    <div class="content">
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default bg-primary-dark">
                <h3 class="block-title text-white">
                    <i class="fa fa-user-plus me-1"></i> {{ isset($patient) ? 'Modification' : 'Nouveau' }} Patient
                </h3>
            </div>
            <div class="block-content p-4">
                <form id="patientForm"
                    action="{{ isset($patient) ? route('patients.update', $patient) : route('patients.store') }}"
                    method="POST">
                    @csrf
                    @if (isset($patient))
                        @method('PUT')
                    @endif

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="block block-rounded block-bordered mb-0">
                                <div class="block-header block-header-default border-bottom">
                                    <h3 class="block-title small fw-bold">IDENTITÉ DU PATIENT</h3>
                                </div>
                                <div class="block-content pb-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="nom">Nom <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nom" name="nom"
                                            value="{{ old('nom', $patient->nom ?? '') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="prenom">Prénom <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="prenom" name="prenom"
                                            value="{{ old('prenom', $patient->prenom ?? '') }}" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="genre">Genre <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-select" id="genre" name="genre" required>
                                                    <option value="">Sélectionner...</option>
                                                    <option value="M"
                                                        {{ old('genre', $patient->genre ?? '') == 'M' ? 'selected' : '' }}>
                                                        Masculin</option>
                                                    <option value="F"
                                                        {{ old('genre', $patient->genre ?? '') == 'F' ? 'selected' : '' }}>
                                                        Féminin</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label" for="age">Âge <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="age" name="age"
                                                    value="{{ old('age', $patient->age ?? '') }}" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="telephone">Téléphone <span
                                                class="text-danger">*</span></label>
                                        <input type="tel" class="form-control phone-input" id="telephone"
                                            name="telephone" value="{{ old('telephone', $patient->telephone ?? '') }}"
                                            required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label" for="ethnie">Ethnie / Origine <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="ethnie" name="ethnie"
                                            value="{{ old('ethnie', $patient->ethnie ?? '') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="block block-rounded block-bordered h-100">
                                <div class="block-header block-header-default border-bottom">
                                    <h3 class="block-title small fw-bold">ASSURANCE & SANTÉ</h3>
                                </div>
                                <div class="block-content pb-3">
                                    <div class="mb-3">
                                        <label class="form-label" for="assurance_id">Assurance</label>
                                        <select class="form-select" id="assurance_id" name="assurance_id">
                                            <option value="">Aucune (Cash)</option>
                                            @foreach (\App\Models\Assurance::all() as $assurance)
                                                <option value="{{ $assurance->id }}"
                                                    {{ old('assurance_id', $patient->assurance_id ?? '') == $assurance->id ? 'selected' : '' }}>
                                                    {{ $assurance->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="numero_assurance">N° de police d'assurance</label>
                                        <input type="text" class="form-control" id="numero_assurance"
                                            name="numero_assurance"
                                            value="{{ old('numero_assurance', $patient->numero_assurance ?? '') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="groupe_sanguin">Groupe Sanguin</label>
                                        <select class="form-select" id="groupe_sanguin" name="groupe_sanguin">
                                            <option value="">Inconnu</option>
                                            @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs)
                                                <option value="{{ $gs }}"
                                                    {{ old('groupe_sanguin', $patient->groupe_sanguin ?? '') == $gs ? 'selected' : '' }}>
                                                    {{ $gs }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="adresse">Adresse de résidence</label>
                                        <textarea class="form-control" id="adresse" name="adresse" rows="4">{{ old('adresse', $patient->adresse ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <a href="{{ route('patients.index') }}" class="btn btn-alt-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="fa fa-save me-1"></i>
                            {{ isset($patient) ? 'Mettre à jour' : 'Enregistrer le patient' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Liaisons indispensables chargées de manière sécurisée si non présentes dans votre layout global --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/css/intlTelInput.css">
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/js/intlTelInput.min.js"></script> --}}

    <script>
        $(document).ready(function() {
            // 1. Initialisation d'intl-tel-input configurée pour la sous-région (Guinée, Mali, Sénégal, etc.)
            const phoneInput = document.getElementById('telephone');
            let iti = null;

            if (phoneInput) {
                iti = window.intlTelInput(phoneInput, {
                    initialCountry: "gn", // Pays par défaut (Guinée)
                    preferredCountries: ["gn", "ml", "sn", "ci"], // Choix rapides prioritaires
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/js/utils.js",
                    autoPlaceholder: "polite"
                });
            }

            // 2. Gestion de la soumission du formulaire via AJAX
            $('#patientForm').on('submit', function(e) {
                e.preventDefault();

                let $form = $(this);
                let actionUrl = $form.attr('action');

                // EXTRACTION SOUPLE DU NUMÉRO SANS BLOCAGE INTRINSÈQUE
                if (iti) {
                    if (iti.isValidNumber()) {
                        // S'il est 100% conforme à l'indicateur sélectionné, on injecte le format international propre (+224...)
                        phoneInput.value = iti.getNumber();
                    } else {
                        // Si l'outil le juge "non standard" (ex: numéro malien à 8 chiffres alors que l'indicateur affiche la Guinée)
                        // On nettoie simplement les espaces vides et on laisse passer la saisie brute utilisateur.
                        phoneInput.value = phoneInput.value.replace(/\s+/g, '');
                    }
                }

                let formData = new FormData(this);
                let $submitBtn = $form.find('button[type="submit"]');
                let originalBtnHtml = $submitBtn.html();

                // Nettoyage des alertes et feedback d'erreurs Bootstrap
                $('.invalid-feedback').remove();
                $('.form-control, .form-select').removeClass('is-invalid');

                // Verrouillage du bouton durant le traitement
                $submitBtn.html('<i class="fa fa-spinner fa-spin me-1"></i> Traitement...').attr('disabled', true);

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès !',
                                text: response.message || 'Opération complétée avec succès.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "{{ route('patients.index') }}";
                            });
                        }
                    },
                    error: function(xhr) {
                        $submitBtn.html(originalBtnHtml).attr('disabled', false);

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation échouée',
                                text: 'Veuillez vérifier les champs du formulaire.',
                                confirmButtonColor: '#3085d6'
                            });

                            $.each(errors, function(field, messages) {
                                let $input = $('#' + field);
                                if ($input.length) {
                                    $input.addClass('is-invalid');
                                    $input.parent().append('<div class="invalid-feedback">' + messages[0] + '</div>');
                                }
                            });
                        } else {
                            let errorText = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message : "Une erreur interne est survenue.";

                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur ' + xhr.status,
                                text: errorText,
                                confirmButtonColor: '#d33'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection