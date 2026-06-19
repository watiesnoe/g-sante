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
                  action="{{ isset($fournisseur) ? route('fournisseurs.update', $fournisseur->id) : route('fournisseurs.store') }}"
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
                        <div class="invalid-feedback"></div>
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
    // Formatage simple des numéros de téléphone
    // ============================================
    
    // Fonction pour formater le numéro en groupes de 2 chiffres
    function formatPhoneNumber(input) {
        let value = input.value.replace(/\D/g, ''); // Garder uniquement les chiffres
        
        if (value.length === 0) {
            input.value = '';
            return;
        }
        
        // Ajouter le + si le numéro commence par 223 ou autre code
        let formatted = '';
        
        // Si le numéro commence par 223 (Mali) ou un code pays
        if (value.startsWith('223') && value.length >= 3) {
            formatted = '+' + value.substring(0, 3);
            let rest = value.substring(3);
            // Grouper par 2 chiffres
            let groups = rest.match(/.{1,2}/g);
            if (groups) {
                formatted += ' ' + groups.join(' ');
            }
        } 
        // Si le numéro commence déjà par + (cas où l'utilisateur a tapé +)
        else if (input.value.startsWith('+')) {
            // Extraire le code pays
            let codeMatch = value.match(/^(\d{1,4})(.*)$/);
            if (codeMatch) {
                formatted = '+' + codeMatch[1];
                let rest = codeMatch[2];
                let groups = rest.match(/.{1,2}/g);
                if (groups) {
                    formatted += ' ' + groups.join(' ');
                }
            }
        }
        // Numéro local sans code pays
        else {
            let groups = value.match(/.{1,2}/g);
            if (groups) {
                formatted = groups.join(' ');
            }
        }
        
        input.value = formatted;
        
        // Validation simple
        if (value.length > 0 && value.length < 8) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            $(input).siblings('.invalid-feedback').html(`
                <i class="fa fa-exclamation-circle me-1"></i> 
                Numéro incomplet (minimum 8 chiffres)
            `);
        } else if (value.length >= 8 && value.length <= 15) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            $(input).siblings('.invalid-feedback').html('');
        } else if (value.length > 15) {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            $(input).siblings('.invalid-feedback').html(`
                <i class="fa fa-exclamation-circle me-1"></i> 
                Numéro trop long
            `);
        }
    }
    
    // Événement de saisie
    $('#contact').on('input', function(e) {
        let cursorPos = this.selectionStart;
        let oldLength = this.value.length;
        
        formatPhoneNumber(this);
        
        // Ajuster la position du curseur
        let newLength = this.value.length;
        let diff = newLength - oldLength;
        this.setSelectionRange(cursorPos + diff, cursorPos + diff);
    });
    
    // Validation avant soumission
    function validatePhone(phone) {
        if (!phone) return false;
        
        let digits = phone.replace(/\D/g, '');
        // Accepter les numéros avec 8 à 15 chiffres
        return digits.length >= 8 && digits.length <= 15;
    }
    
    // Initialisation si valeur existante
    if ($('#contact').val()) {
        formatPhoneNumber($('#contact')[0]);
    }

    // ============================================
    // Soumission du formulaire
    // ============================================
    $('#formFournisseur').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let method = form.find('input[name="_method"]').val() || 'POST';
        let btn = form.find('button[type="submit"]');
        let phone = $('#contact').val().trim();
        let phoneDigits = phone.replace(/\D/g, '');
        
        // Validation finale du téléphone
        if (!validatePhone(phone)) {
            Swal.fire({
                icon: 'error',
                title: 'Numéro invalide',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Veuillez entrer un numéro valide :</strong></p>
                        <ul class="mb-0">
                            <li><code>+223 12 34 56 78</code> - Format international</li>
                            <li><code>12 34 56 78</code> - Format local</li>
                            <li><code>12345678</code> - 8 chiffres</li>
                        </ul>
                    </div>
                `,
                confirmButtonText: '<i class="fa fa-check me-1"></i> OK',
                confirmButtonColor: '#1bc5bd'
            });
            $('#contact').focus();
            return false;
        }

        // Désactiver le bouton pendant l'envoi
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Enregistrement en cours...');

        // Envoyer le numéro sans espaces
        let formData = form.serialize();
        // Remplacer le numéro formaté par sa version sans espaces
        formData = formData.replace(/contact=[^&]*/, 'contact=' + encodeURIComponent(phoneDigits));

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