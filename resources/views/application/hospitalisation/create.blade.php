@extends('layouts.app')

@section('titre', 'Nouvelle Hospitalisation')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fa fa-bed me-2"></i> Nouvelle Hospitalisation</h4>
                <a href="{{ route('hospitalisations.index') }}" class="btn btn-light btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Retour
                </a>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('hospitalisations.store') }}" method="POST" id="hospitalisationForm">
                    @csrf

                    <div class="row g-3">

                        {{-- Consultation (Patient) --}}
                        <div class="col-md-6">
                            <label for="consultation_id" class="form-label fw-semibold">
                                <i class="fa fa-user-md me-1"></i> Consultation / Patient <span class="text-danger">*</span>
                            </label>
                            <select name="consultation_id" id="consultation_id" class="form-select select2" required>
                                <option value="">-- Sélectionnez une consultation --</option>
                                @foreach($consultations as $c)
                                    <option value="{{ $c->id }}" {{ old('consultation_id') == $c->id ? 'selected' : '' }}>
                                        #{{ $c->id }} — {{ $c->patient->nom ?? '?' }} {{ $c->patient->prenom ?? '' }}
                                        ({{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Service médical --}}
                        <div class="col-md-6">
                            <label for="service_id" class="form-label fw-semibold">
                                <i class="fa fa-hospital me-1"></i> Service médical <span class="text-danger">*</span>
                            </label>
                            <select name="service_id" id="service_id" class="form-select" required>
                                <option value="">-- Sélectionnez un service --</option>
                                @foreach($services as $s)
                                    <option value="{{ $s->id }}" {{ old('service_id') == $s->id ? 'selected' : '' }}>
                                        {{ $s->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Salle --}}
                        <div class="col-md-6">
                            <label for="salles_id" class="form-label fw-semibold">
                                <i class="fa fa-door-open me-1"></i> Salle <span class="text-danger">*</span>
                            </label>
                            <select name="salles_id" id="salles_id" class="form-select" required>
                                <option value="">-- Sélectionnez une salle --</option>
                                @foreach($salles as $salle)
                                    <option value="{{ $salle->id }}"
                                        data-prix="{{ $salle->prix }}"
                                        {{ old('salles_id') == $salle->id ? 'selected' : '' }}>
                                        {{ $salle->nom }} — {{ number_format($salle->prix, 0, ',', ' ') }} FCFA/jour
                                    </option>
                                @endforeach
                            </select>
                            <div id="prix-salle-info" class="form-text text-success fw-semibold mt-1" style="display:none;">
                                Prix/jour : <span id="prix-salle-val">0</span> FCFA
                            </div>
                        </div>

                        {{-- Lit --}}
                        <div class="col-md-6">
                            <label for="lit_id" class="form-label fw-semibold">
                                <i class="fa fa-bed me-1"></i> Lit <span class="text-danger">*</span>
                            </label>
                            <select name="lit_id" id="lit_id" class="form-select" required>
                                <option value="">-- Sélectionnez un lit --</option>
                                @foreach($lits as $lit)
                                    <option value="{{ $lit->id }}"
                                        data-salle="{{ $lit->salle_id }}"
                                        {{ old('lit_id') == $lit->id ? 'selected' : '' }}>
                                        Lit N° {{ $lit->numero }}
                                        @if($lit->salle) — {{ $lit->salle->nom }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Date d'entrée --}}
                        <div class="col-md-6">
                            <label for="date_entree" class="form-label fw-semibold">
                                <i class="fa fa-calendar-alt me-1"></i> Date d'entrée <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="date_entree" id="date_entree" class="form-control"
                                   value="{{ old('date_entree', now()->format('Y-m-d')) }}" required>
                        </div>

                        {{-- Motif --}}
                        <div class="col-md-6">
                            <label for="motif" class="form-label fw-semibold">
                                <i class="fa fa-stethoscope me-1"></i> Motif d'hospitalisation
                            </label>
                            <input type="text" name="motif" id="motif" class="form-control"
                                   placeholder="Ex : Surveillance post-opératoire"
                                   value="{{ old('motif') }}">
                        </div>

                        {{-- Observations --}}
                        <div class="col-12">
                            <label for="observations" class="form-label fw-semibold">
                                <i class="fa fa-notes-medical me-1"></i> Observations
                            </label>
                            <textarea name="observations" id="observations" class="form-control" rows="3"
                                      placeholder="Notes cliniques, remarques...">{{ old('observations') }}</textarea>
                        </div>

                    </div>{{-- /.row --}}

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="btnSubmit">
                            <i class="fa fa-save me-1"></i> Enregistrer l'hospitalisation
                        </button>
                        <a href="{{ route('hospitalisations.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times me-1"></i> Annuler
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

    // Afficher le prix de la salle sélectionnée
    $('#salles_id').on('change', function () {
        const prix = $(this).find(':selected').data('prix');
        if (prix !== undefined && prix !== '') {
            $('#prix-salle-val').text(parseFloat(prix).toLocaleString('fr-FR'));
            $('#prix-salle-info').show();
        } else {
            $('#prix-salle-info').hide();
        }

        // Filtrer les lits selon la salle choisie
        const salleId = $(this).val();
        filterLits(salleId);
    });

    // Filtrer les lits : si une salle est sélectionnée, n'afficher que ses lits
    function filterLits(salleId) {
        $('#lit_id option').each(function () {
            const litSalle = $(this).data('salle');
            if (!salleId || !litSalle || String(litSalle) === String(salleId)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        // Réinitialiser la sélection si la valeur actuelle n'est plus visible
        const currentLit = $('#lit_id option:selected');
        const currentLitSalle = currentLit.data('salle');
        if (salleId && currentLitSalle && String(currentLitSalle) !== String(salleId)) {
            $('#lit_id').val('');
        }
    }

    // Déclencher au chargement si old() est rempli
    const initialSalle = $('#salles_id').val();
    if (initialSalle) {
        const prix = $('#salles_id').find(':selected').data('prix');
        if (prix) {
            $('#prix-salle-val').text(parseFloat(prix).toLocaleString('fr-FR'));
            $('#prix-salle-info').show();
        }
        filterLits(initialSalle);
    }

    // Select2 pour la consultation si disponible
    if (typeof $.fn.select2 !== 'undefined') {
        $('#consultation_id').select2({
            placeholder: '-- Sélectionnez une consultation --',
            width: '100%',
        });
    }

    // Confirmation avant envoi
    $('#hospitalisationForm').on('submit', function (e) {
        const btn = $('#btnSubmit');
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Enregistrement...');
    });
});
</script>
@endsection
