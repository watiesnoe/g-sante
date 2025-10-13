@extends('layouts.app')

@section('titre', 'Rédiger une Ordonnance')

@section('content')
<div class="container mt-4">
    <div class="card shadow-lg border-0">
        <!-- Header -->
        <div class="card-header text-white border-0 py-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white text-emerald rounded-circle p-2 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fa fa-pills fa-lg" style="color: #10b981;"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold">Nouvelle Ordonnance</h4>
                    <p class="mb-0 small opacity-75">Prescrire un traitement pour le patient</p>
                </div>
            </div>
        </div>

        <div class="card-body p-4 bg-light">
            <form id="ordonnanceForm" action="{{ route('ordonnances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="medecin_id" value="{{ auth()->user()->id }}">
                <input type="hidden" name="patient_id" id="patient_id" value="{{ $patient->id ?? '' }}">
                <input type="hidden" name="grossesse_id" value="{{ $grossesse_id ?? '' }}">

                <!-- Patient Selection Section -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary">👤 Patient</label>
                                @if(isset($patient))
                                    <div class="form-control bg-light fw-bold">
                                        {{ $patient->nom }} {{ $patient->prenom }} 
                                        <span class="badge bg-info ms-2">{{ $patient->age }} ans</span>
                                        @if($grossesse_id)
                                            <span class="badge bg-danger ms-2"><i class="fa fa-female me-1"></i> Suivi Maternité</span>
                                        @endif
                                    </div>
                                @else
                                    <select id="patientSelect" class="form-select" required>
                                        <option value="">-- Sélectionnez un patient --</option>
                                        @foreach($patients as $p)
                                            <option value="{{ $p->id }}">{{ $p->nom }} {{ $p->prenom }} ({{ $p->telephone ?? 'Pas de tél' }})</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                            <div class="col-md-6 text-end d-none d-md-block">
                                <span class="text-muted small">Date de prescription : <strong>{{ now()->format('d/m/Y') }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prescription Section -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-emerald" style="color: #059669;"><i class="fa fa-prescription me-2"></i>Médicaments prescrits</h6>
                        <button type="button" id="btnAjouterMedicament" class="btn btn-emerald btn-sm text-white" style="background-color: #10b981; border: none;">
                            <i class="fa fa-plus me-1"></i> Ajouter un médicament
                        </button>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="ordonnanceTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Médicament <span class="text-danger">*</span></th>
                                        <th>Posologie <span class="text-danger">*</span></th>
                                        <th style="width: 150px;">Durée (jours)</th>
                                        <th style="width: 120px;">Quantité</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="emptyOrdonnanceRow">
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fa fa-pills fa-2x mb-2 opacity-50"></i><br>
                                            Aucun médicament prescrit pour le moment. Cliquez sur "Ajouter un médicament".
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <a href="{{ route('ordonnances.index') }}" class="btn btn-outline-secondary px-4">
                        <i class="fa fa-times me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-success px-5 shadow-sm" style="background-color: #059669; border: none;">
                        <i class="fa fa-save me-2"></i>Enregistrer l'Ordonnance
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Patient selection change handler
    $('#patientSelect').change(function() {
        $('#patient_id').val($(this).val());
    });

    // Add row to medicines table
    $('#btnAjouterMedicament').click(function() {
        $('#emptyOrdonnanceRow').remove();
        
        let row = `
        <tr>
            <td>
                <select name="medicaments[]" class="form-select selectMedicament" required>
                    <option value="">-- Sélectionner le médicament --</option>
                    @foreach($medicaments as $med)
                        <option value="{{ $med->id }}" data-stock="{{ $med->stock }}">
                            {{ $med->nom }} (Stock: {{ $med->stock }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="posologies[]" class="form-control" placeholder="Ex: 1 comp matin et soir" required>
            </td>
            <td>
                <input type="number" name="duree_jours[]" class="form-control" min="1" placeholder="Nb jours">
            </td>
            <td>
                <input type="number" name="quantites[]" class="form-control" min="1" value="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-link text-danger btnSupprimer p-0">
                    <i class="fa fa-trash-alt"></i>
                </button>
            </td>
        </tr>`;
        
        $('#ordonnanceTable tbody').append(row);
        updateMedicamentOptions();
    });

    // Remove row
    $(document).on('click', '.btnSupprimer', function() {
        $(this).closest('tr').remove();
        if ($('#ordonnanceTable tbody tr').length === 0) {
            $('#ordonnanceTable tbody').append(`
                <tr id="emptyOrdonnanceRow">
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fa fa-pills fa-2x mb-2 opacity-50"></i><br>
                        Aucun médicament prescrit pour le moment. Cliquez sur "Ajouter un médicament".
                    </td>
                </tr>
            `);
        }
        updateMedicamentOptions();
    });

    // Disable already selected medicines in other dropdowns
    function updateMedicamentOptions() {
        let selected = [];
        $('.selectMedicament').each(function() {
            if ($(this).val()) selected.push($(this).val());
        });
        
        $('.selectMedicament').each(function() {
            let currentVal = $(this).val();
            $(this).find('option').each(function() {
                if ($(this).val() !== "" && selected.includes($(this).val()) && $(this).val() !== currentVal) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });
    }

    $(document).on('change', '.selectMedicament', updateMedicamentOptions);

    // Form submit handler via AJAX
    $('#ordonnanceForm').submit(function(e) {
        e.preventDefault();
        
        let patientVal = $('#patient_id').val();
        if (!patientVal) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Veuillez sélectionner un patient !'
            });
            return;
        }

        if ($('.selectMedicament').length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Veuillez ajouter au moins un médicament à l\'ordonnance !'
            });
            return;
        }

        let formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: formData,
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = res.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: res.error || 'Erreur serveur !'
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    let msg = Object.values(errors).map(e => e.join(', ')).join("<br>");
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation',
                        html: msg
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: xhr.responseJSON?.error || 'Erreur lors de la création de l\'ordonnance'
                    });
                }
            }
        });
    });
});
</script>
@endsection
