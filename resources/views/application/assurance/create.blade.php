@extends('layouts.app')

@section('titre')
    {{ isset($assurance) ? 'Modifier l\'assurance' : 'Nouvelle Assurance' }}
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">
                            <i class="fa fa-id-card me-1"></i>
                            {{ isset($assurance) ? 'Modifier : ' . $assurance->nom : 'Ajouter une assurance' }}
                        </h3>
                        <div class="block-options">
                            <a href="{{ route('assurances.index') }}" class="btn btn-sm btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                    <div class="block-content p-4">
                        <form id="assuranceForm" action="{{ isset($assurance) ? route('assurances.update', $assurance) : route('assurances.store') }}" method="POST">
                            @csrf
                            @if(isset($assurance))
                                @method('PUT')
                            @endif

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nom de l'assurance <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" 
                                           value="{{ old('nom', $assurance->nom ?? '') }}" placeholder="Ex: CNAM, Mutuelle..." required>
                                    @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Taux de prise en charge (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="taux" class="form-control @error('taux') is-invalid @enderror" 
                                           value="{{ old('taux', $assurance->taux ?? '80') }}" min="0" max="100" required>
                                    @error('taux') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Téléphone</label>
                                    <input type="tel" name="telephone" class="form-control phone-input @error('telephone') is-invalid @enderror" 
                                           value="{{ old('telephone', $assurance->telephone ?? '') }}">
                                    @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Adresse</label>
                                    <input type="text" name="adresse" class="form-control @error('adresse') is-invalid @enderror" 
                                           value="{{ old('adresse', $assurance->adresse ?? '') }}" placeholder="Adresse complète">
                                    @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="text-end border-top pt-3">
                                <button type="reset" class="btn btn-alt-secondary me-1">Réinitialiser</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i>
                                    {{ isset($assurance) ? 'Mettre à jour' : 'Enregistrer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#assuranceForm').on('submit', function(e) {
                e.preventDefault();

                let form = $(this);
                let method = form.find('input[name="_method"]').val() || 'POST';
                let btn = form.find('button[type="submit"]');

                // Désactiver le bouton pendant l'envoi
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Enregistrement...');

                // Récupérer le numéro de téléphone nettoyé/formaté
                let telephoneInput = form.find('input.phone-input');
                let telephone = '';
                if (telephoneInput.length && typeof window.getPhoneNumber === 'function') {
                    telephone = window.getPhoneNumber(telephoneInput[0]);
                }

                // Préparer les données
                let formData = form.serializeArray();
                if (telephone) {
                    formData = formData.map(function(item) {
                        if (item.name === 'telephone') {
                            return { name: 'telephone', value: telephone };
                        }
                        return item;
                    });
                }

                $.ajax({
                    url: form.attr('action'),
                    method: method,
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès !',
                            text: response.message || 'Opération réussie avec succès.',
                            timer: 2000,
                            timerProgressBar: true,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('assurances.index') }}";
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> {{ isset($assurance) ? "Mettre à jour" : "Enregistrer" }}');
                        
                        let errorMessage = 'Une erreur est survenue lors de l\'enregistrement';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += '<div><i class="fa fa-times-circle me-1 text-danger"></i> ' + value[0] + '</div>';
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            html: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endsection
