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
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Choix de la Patiente <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select no-select2" required>
                                    <option value=""></option>
                                    @if($oldPatient)
                                        <option value="{{ $oldPatient->id }}" selected>{{ $oldPatient->nom }} {{ $oldPatient->prenom }} ({{ $oldPatient->age }} ans)</option>
                                    @endif
                                </select>
                                <small class="text-muted">Tapez au moins 2 lettres du nom, prénom ou téléphone (femmes de 15 ans et plus).</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date des dernières règles (DDR)</label>
                                <input type="date" name="ddr" class="form-control" required id="ddr_input" value="{{ old('ddr') }}" max="{{ date('Y-m-d') }}">
                                <small class="text-primary fst-italic" id="dpa_preview">La DPA sera calculée automatiquement.</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Gestité (G)</label>
                                <input type="number" name="gestite" class="form-control" min="1" max="20" value="{{ old('gestite') }}" placeholder="ex: 1">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Parité (P)</label>
                                <input type="number" name="parite" class="form-control" min="0" value="{{ old('parite') }}" placeholder="ex: 0">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Antécédents & Observations particulières</label>
                                <textarea name="antecedents_particuliers" class="form-control" rows="3" placeholder="Hypertension, diabète gestationnel, césarienne antérieure...">{{ old('antecedents_particuliers') }}</textarea>
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
        $('#patient_id').select2({
            placeholder: "Rechercher une patiente par nom ou téléphone...",
            allowClear: true,
            width: '100%',
            minimumInputLength: 2,
            language: {
                inputTooShort: function () { return 'Saisissez au moins 2 caractères...'; },
                noResults: function () { return 'Aucune patiente trouvée'; },
                searching: function () { return 'Recherche en cours...'; }
            },
            ajax: {
                url: "{{ route('patients.search') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, gender: 'F', min_age: 15 };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.nom + ' ' + item.prenom + ' (' + item.age + ' ans)' + (item.telephone ? ' - ' + item.telephone : '')
                            };
                        })
                    };
                }
            }
        });

        // Ouvre directement le champ de saisie à l'ouverture de la liste
        $(document).on('select2:open', function () {
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        });
    });

    document.getElementById('ddr_input').addEventListener('change', function() {
        if (this.value) {
            let ddr = new Date(this.value);
            // Calcul DPA: DDR + 280 jours
            let dpa = new Date(ddr);
            dpa.setDate(dpa.getDate() + 280);
            
            let options = { day: 'numeric', month: 'long', year: 'numeric' };
            document.getElementById('dpa_preview').innerHTML = "<b>DPA estimée : " + dpa.toLocaleDateString('fr-FR', options) + "</b>";
            document.getElementById('dpa_preview').classList.replace('text-primary', 'text-success');
        }
    });
</script>
@endsection
