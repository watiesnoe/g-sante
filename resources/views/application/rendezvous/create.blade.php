@extends('layouts.app')

@section('titre', 'Nouveau Rendez-vous')

@section('content')
    <div class="container mt-4">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0"><i class="fa fa-calendar-plus me-2 text-primary"></i>Nouveau Rendez-vous</h4>
            <a href="{{ route('rendezvous.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Retour à la liste
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fa fa-calendar-check me-2"></i>Planifier un Rendez-vous</h5>
            </div>
            <div class="card-body p-4">

                {{-- Infos patient pré-rempli (venant de la maternité) --}}
                @if($patient)
                    <div class="alert alert-info border-0 d-flex align-items-center mb-4">
                        <i class="fa fa-user-circle fa-2x me-3 text-info"></i>
                        <div>
                            <div class="fw-bold">Patient : {{ $patient->nom }} {{ $patient->prenom }}</div>
                            <small class="text-muted">Ce rendez-vous sera automatiquement lié à ce patient.</small>
                        </div>
                    </div>
                @endif

                <form id="rdvForm">
                    @csrf

                    {{-- Patient (caché si pré-rempli, sinon sélectionnable) --}}
                    @if($patient)
                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    @else
                        <div class="mb-3">
                            <label for="patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="patient_id" class="form-select" required>
                                <option value="">-- Sélectionnez un patient --</option>
                            </select>
                            <small class="text-muted">Commencez à taper pour rechercher un patient.</small>
                        </div>
                    @endif

                    {{-- Médecin --}}
                    <div class="mb-3">
                        <label for="medecin_id" class="form-label fw-semibold">Médecin responsable <span class="text-danger">*</span></label>
                        <select name="medecin_id" id="medecin_id" class="form-select" required>
                            <option value="">-- Sélectionnez un médecin --</option>
                            @foreach($medecins as $med)
                                <option value="{{ $med->id }}" {{ (isset($preselectedMedecinId) && $preselectedMedecinId == $med->id) ? 'selected' : '' }}>
                                    {{ $med->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date & Heure --}}
                    <div class="mb-3">
                        <label for="date_heure" class="form-label fw-semibold">Date et heure du rendez-vous <span class="text-danger">*</span></label>
                        <input type="datetime-local"
                               name="date_heure"
                               id="date_heure"
                               class="form-control"
                               value="{{ now()->addDay()->format('Y-m-d\TH:i') }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               required>
                    </div>

                    {{-- Motif --}}
                    <div class="mb-4">
                        <label for="motif" class="form-label fw-semibold">Motif du rendez-vous</label>
                        <textarea name="motif"
                                  id="motif"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Ex: Consultation de suivi, CPN, contrôle post-natal...">{{ old('motif') }}</textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" id="btnSave" class="btn btn-primary px-4">
                            <i class="fa fa-save me-2"></i>Enregistrer le rendez-vous
                        </button>
                        <a href="{{ route('rendezvous.index') }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    @unless($patient)
    // Select2 pour la recherche de patient
    $('#patient_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'Rechercher un patient...',
        minimumInputLength: 2,
        ajax: {
            url: '{{ route("patients.search") }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return {
                    results: data.map(function (p) {
                        return { id: p.id, text: p.nom + ' ' + p.prenom };
                    })
                };
            }
        }
    });
    @endunless

    // Soumission AJAX
    $('#rdvForm').on('submit', function (e) {
        e.preventDefault();

        const btn = $('#btnSave');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i>Enregistrement...');

        $.ajax({
            url: '{{ route("rendezvous.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: 'Rendez-vous enregistré avec succès !',
                        confirmButtonText: 'Voir la liste'
                    }).then(() => {
                        window.location.href = '{{ route("rendezvous.index") }}';
                    });
                } else {
                    Swal.fire('Erreur', res.message ?? 'Une erreur est survenue.', 'error');
                    btn.prop('disabled', false).html('<i class="fa fa-save me-2"></i>Enregistrer le rendez-vous');
                }
            },
            error: function (xhr) {
                let msg = 'Une erreur est survenue.';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        msg = Object.values(errors).flat().join('<br>');
                    }
                } else if (xhr.status === 403) {
                    msg = 'Accès non autorisé.';
                }
                Swal.fire({ icon: 'error', title: 'Erreur', html: msg });
                btn.prop('disabled', false).html('<i class="fa fa-save me-2"></i>Enregistrer le rendez-vous');
            }
        });
    });
});
</script>
@endsection
