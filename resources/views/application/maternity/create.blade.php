@extends('layouts.app')

@section('content')
<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 font-weight-bold"><i class="fa fa-baby-carriage me-2"></i> Initialiser un Suivi de Grossesse</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('maternity.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Choix de la Patiente</label>
                                <select name="patient_id" class="form-select select2" required>
                                    <!-- Options will be populated by AJAX -->
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date des dernières règles (DDR)</label>
                                <input type="date" name="ddr" class="form-control" required id="ddr_input">
                                <small class="text-primary italic" id="dpa_preview">La DPA sera calculée automatiquement.</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Gestité (G)</label>
                                <input type="number" name="gestite" class="form-control" placeholder="ex: 1">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Parité (P)</label>
                                <input type="number" name="parite" class="form-control" placeholder="ex: 0">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Antécédents & Observations particulières</label>
                                <textarea name="antecedents_particuliers" class="form-control" rows="3" placeholder="Hypertension, diabète gestationnel, césarienne antérieure..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                            <a href="{{ route('maternity.index') }}" class="btn btn-light px-4">Annuler</a>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">
                                <i class="fa fa-check me-2"></i> Initialiser le Suivi
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
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: "Rechercher une patiente par nom ou téléphone...",
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('patients.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        gender: 'F,Féminin,Feminin,Femme', // only female
                        min_age: 15 // min age 15
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nom + ' ' + item.prenom + ' (' + item.age + ' ans)',
                                id: item.id
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });
    });

    document.getElementById('ddr_input').addEventListener('change', function() {
        if (this.value) {
            let ddr = new Date(this.value);
            // Calcul DPA: DDR + 9 mois + 7 jours
            let dpa = new Date(ddr);
            dpa.setMonth(dpa.getMonth() + 9);
            dpa.setDate(dpa.getDate() + 7);
            
            let options = { day: 'numeric', month: 'long', year: 'numeric' };
            document.getElementById('dpa_preview').innerHTML = "<b>DPA estimée : " + dpa.toLocaleDateString('fr-FR', options) + "</b>";
            document.getElementById('dpa_preview').classList.replace('text-primary', 'text-success');
        }
    });
</script>
@endsection
