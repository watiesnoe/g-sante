@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Système de Santé
@endsection

@section('content')
    <div class="content">
        <div class="row">
            <!-- Sidebar gauche -->
            @include('layouts.partials.configside')
            <!-- Contenu principal -->
            <div class="col-xl-9 col-lg-8 ">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h5 class="mb-0 text-primary fw-bold">
                            {{ isset($salle) ? '✏️ Modifier la salle' : '➕ Ajouter une nouvelle salle' }}
                        </h5>
                    </div>
                    <div class="block-content">
                        <form id="salleForm"
                              action="{{ isset($salle) ? route('salles.update', $salle->id) : route('salles.store') }}"
                              method="POST" class="mb-3">
                            @csrf
                            @if(isset($salle))
                                @method('PUT')
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Nom de la salle</label>
                                <input type="text" name="nom" class="form-control"
                                       value="{{ old('nom', $salle->nom ?? '') }}"
                                       placeholder="Ex : Salle 101" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type de salle</label>
                                <select name="type" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="Consultation" {{ old('type', $salle->type ?? '') == 'Consultation' ? 'selected' : '' }}>Consultation</option>
                                    <option value="Hospitalisation" {{ old('type', $salle->type ?? '') == 'Hospitalisation' ? 'selected' : '' }}>Hospitalisation</option>
                                    <option value="Blocoperatoire" {{ old('type', $salle->type ?? '') == 'Blocoperatoire' ? 'selected' : '' }}>Bloc opératoire</option>
                                    <option value="Laboratoire" {{ old('type', $salle->type ?? '') == 'Laboratoire' ? 'selected' : '' }}>Laboratoire</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Service médical</label>
                                <select name="service_medical_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($services as $service)
                                        <option value="{{ $service->id }}"
                                            {{ old('service_medical_id', $salle->service_medical_id ?? '') == $service->id ? 'selected' : '' }}>
                                            {{ $service->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Capacité</label>
                                <input type="number" name="capacite" class="form-control" min="1"
                                       value="{{ old('capacite', $salle->capacite ?? 1) }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill">
                                {{ isset($salle) ? '✏️ Mettre à jour' : '💾 Enregistrer la salle' }}
                            </button>
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
            $('#salleForm').on('submit', function(e) {
                e.preventDefault();

                let formData = $(this).serialize();

                $.ajax({
                    url: $(this).attr('action'),
                    method: $(this).find('input[name="_method"]').val() || 'POST',
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Succès !',
                                text: response.message,
                                confirmButtonText: 'OK'
                            });

                            if(!$('#salleForm').find('input[name="_method"]').length) {
                                // reset uniquement si c'est un "create"
                                $('#salleForm')[0].reset();
                            }
                        }
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessage = '';
                        $.each(errors, function(key, value) {
                            errorMessage += value + '\n';
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endsection
