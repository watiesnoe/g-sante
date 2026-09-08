@extends('layouts.app')
@section('titre', isset($fournisseur) ? 'Modifier Fournisseur' : 'Ajouter Fournisseur')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                {{ isset($fournisseur) ? 'Modifier Fournisseur' : 'Ajouter Fournisseur' }}
            </h5>
        </div>
        <div class="card-body">
            <form id="formFournisseur"
                  action="{{ isset($fournisseur) ? route('fournisseurs.update', $fournisseur) : route('fournisseurs.store') }}"
                  method="POST">
                @csrf
                @if(isset($fournisseur))
                    @method('PUT')
                @endif
                
                <div class="row">
                    <div class="mb-3 col-md-4">
                        <label for="nom" class="form-label">
                            <i class="fa fa-building me-1"></i>Nom <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="nom" name="nom" class="form-control"
                               value="{{ $fournisseur->nom ?? old('nom') }}" 
                               placeholder="Ex: SOGEA SATOM" required>
                        <div class="invalid-feedback">Le nom est obligatoire</div>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label for="contact" class="form-label">
                            <i class="fa fa-phone me-1"></i>Contact <span class="text-danger">*</span>
                        </label>
                        <input type="tel" id="contact" name="contact" class="form-control phone-input"
                               value="{{ $fournisseur->contact ?? old('contact') }}" required>
                        <div class="invalid-feedback" id="contactFeedback"></div>
                    </div>

                    <div class="mb-3 col-md-4">
                        <label for="adresse" class="form-label">
                            <i class="fa fa-map-marker me-1"></i>Adresse
                        </label>
                        <textarea id="adresse" name="adresse" class="form-control" 
                                  rows="2" placeholder="Adresse complète du fournisseur">{{ $fournisseur->adresse ?? old('adresse') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('fournisseurs.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i>Retour
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fa {{ isset($fournisseur) ? 'fa-save' : 'fa-check' }} me-1"></i>
                        {{ isset($fournisseur) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Configuration CSRF pour AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ============================================
    // Téléphone international (drapeau + indicatif selon le pays)
    // Le formatage/la validation sont gérés par intl-tel-input,
    // initialisé globalement sur tout input.phone-input (voir js.blade.php).
    // ============================================

    function getContactIti() {
        var input = document.getElementById('contact');
        return (window.intlTelInputGlobals && input) ? window.intlTelInputGlobals.getInstance(input) : null;
    }

    // Retour visuel de validité pendant la saisie
    // (affichage explicite via un id car intl-tel-input enveloppe l'input,
    // ce qui casse à la fois le sélecteur CSS Bootstrap ".is-invalid ~ .invalid-feedback"
    // et le sélecteur jQuery .siblings())
    var $contactFeedback = $('#contactFeedback');
    $('#contact').on('input blur countrychange', function() {
        let iti = getContactIti();
        if (!iti || this.value.trim() === '') {
            $(this).removeClass('is-invalid is-valid');
            $contactFeedback.html('').hide();
            return;
        }

        if (iti.isValidNumber()) {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $contactFeedback.html('').hide();
        } else {
            $(this).removeClass('is-valid').addClass('is-invalid');
            $contactFeedback.html('<i class="fa fa-exclamation-circle me-1"></i> Numéro invalide pour le pays sélectionné').show();
        }
    });

    // ============================================
    // Soumission du formulaire
    // ============================================
    $('#formFournisseur').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let method = form.find('input[name="_method"]').val() || 'POST';
        let btn = form.find('button[type="submit"]');
        let iti = getContactIti();

        // Validation finale du téléphone (selon le pays sélectionné dans le drapeau)
        if (iti && !iti.isValidNumber()) {
            Swal.fire({
                icon: 'error',
                title: 'Numéro invalide',
                text: 'Veuillez entrer un numéro de téléphone valide pour le pays sélectionné.',
                confirmButtonText: '<i class="fa fa-check me-1"></i> OK',
                confirmButtonColor: '#1bc5bd'
            });
            $('#contact').focus();
            return false;
        }

        // Désactiver le bouton pendant l'envoi
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Enregistrement en cours...');

        // Le champ contact est automatiquement converti au format international (+E.164)
        // avant sérialisation par le script global (voir js.blade.php)
        let formData = form.serialize();

        // Envoi AJAX
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès !',
                    text: response.message || 'Opération réussie',
                    timer: 1500,
                    showConfirmButton: false,
                    didClose: () => {
                        if (method !== 'PUT') {
                            form[0].reset();
                            $('#contact').removeClass('is-valid');
                        }
                        window.location.href = "{{ route('fournisseurs.index') }}";
                    }
                });
            },
            error: function(xhr) {
                let errorMessage = '';
                
                if (xhr.responseJSON?.errors) {
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errorMessage += `<div><i class="fa fa-times-circle me-1 text-danger"></i> ${value[0]}</div>`;
                    });
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else {
                    errorMessage = 'Une erreur est survenue lors de l\'enregistrement';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    html: errorMessage,
                    confirmButtonText: '<i class="fa fa-check me-1"></i> OK',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                btn.prop('disabled', false).html(`
                    <i class="fa ${method !== 'PUT' ? 'fa-check' : 'fa-save'} me-1"></i>
                    {{ isset($fournisseur) ? 'Mettre à jour' : 'Enregistrer' }}
                `);
            }
        });
    });

});
</script>
@endsection

@section('styles')
<style>
    /* Styles personnalisés */
    #contact.is-valid {
        border-color: #198754;
        background-image: none;
    }
    
    #contact.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }
    
    /* Animation pour le bouton */
    .btn-success:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    /* Style pour le textarea */
    textarea.form-control {
        resize: vertical;
        min-height: 60px;
    }
</style>
@endsection