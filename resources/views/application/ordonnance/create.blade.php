@extends('layouts.app')

@section('titre', 'Consultation Patient')

@section('content')
    <div class="container mt-4">

        <!-- Sélection du patient -->
        <div class="d-flex mb-3">
            <select id="patientDropdown" class="form-select me-2" required>
                <option value="">-- Sélectionner un patient en attente --</option>
                @foreach($tickets as $ticket)
                    <option value="{{ $ticket->patient->id }}" data-ticket="{{ $ticket->id }}">
                        {{ $ticket->patient->nom }} {{ $ticket->patient->prenom }}
                        - Tel: {{ $ticket->patient->telephone }}
                        - Âge: {{ $ticket->patient->age }}
                        - Ticket N° {{ $ticket->id }}
                    </option>
                @endforeach
            </select>
           </div>

        <!-- Formulaire consultation -->
        <form id="consultationForm" >
            @csrf
            <input type="hidden" name="medecin_id" value="{{ auth()->user()->id }}">
            <input type="hidden" name="patient_id" id="patient_id">
            <input type="hidden" name="ticket_id" id="ticket_id">
            {{-- ⚖️ Constantes & Antécédents --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-secondary text-white">⚖️ Constantes & Antécédents</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Poids (kg)</label>
                            <input type="number" name="poids" class="form-control" min="1" step="0.1">
                        </div>
                        <div class="col-md-3">
                            <label>Taille (cm)</label>
                            <input type="number" name="taille" class="form-control" min="30" step="0.1">
                        </div>
                        <div class="col-md-3">
                            <label>IMC (auto)</label>
                            <input type="text" id="imc" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Tension</label>
                            <input type="text" name="tension" class="form-control" placeholder="Ex: 12/8">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Groupe sanguin</label>
                            <select name="groupe_sanguin" class="form-select">
                                <option value="">-- Sélectionner --</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                                    <option value="{{ $gs }}">{{ $gs }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label>Adresse du patient</label>
                            <input type="text" name="adresse_patient" class="form-control" placeholder="Adresse complète">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Antécédents médicaux</label>
                        <textarea name="antecedents" class="form-control" rows="2" placeholder="Antécédents..."></textarea>
                    </div>
                </div>
            </div>

            {{-- 🩺 Diagnostic --}}


            {{-- ⚡ Symptômes --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-info text-white">⚡ Symptômes</div>
                <div class="card-body">
                    <select id="symptomes" class="form-select" name="symptomes[]" multiple>
                        @foreach($symptomes as $symptome)
                            <option value="{{ $symptome->id }}">{{ $symptome->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- 🧾 Maladie concernée --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-danger text-white">🧾 Maladie concernée</div>
                <div class="card-body">
                    <select name="maladie_id" id="maladieSelect" class="form-select" required>
                        <option value="">-- Sélectionner la maladie --</option>
                    </select>
                </div>
            </div>
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white">🩺 Diagnostic</div>
                <div class="card-body">
                    <textarea class="form-control mb-2" name="diagnostic" placeholder="Saisir le diagnostic..." rows="3" required></textarea>
                </div>
            </div>
            {{-- Onglets Ordonnance / Examens / RDV / Certificat --}}
            <ul class="nav nav-tabs mb-3" id="consultationTabs" role="tablist">
                @foreach(['ordonnance'=>'💊 Ordonnance','analyse'=>'🧪 Examens','rdv'=>'📅 Rendez-vous','certificat'=>'📝 Certificat & Hospitalisation'] as $key => $label)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link @if($key=='ordonnance') active @endif"
                                id="{{ $key }}-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#{{ $key }}"
                                type="button">
                            {{ $label }}
                        </button>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="consultationTabsContent">
                {{-- 💊 Ordonnance --}}
                <div class="tab-pane fade show active" id="ordonnance">
                    <div class="card shadow-lg mb-3">
                        <div class="card-header bg-success text-white d-flex justify-content-between">
                            💊 Prescriptions / Ordonnance
                            <button type="button" id="btnAjouterMedicament" class="btn btn-light btn-sm">➕ Ajouter</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="ordonnanceTable">
                                    <thead>
                                    <tr>
                                        <th>Médicament</th>
                                        <th>Posologie</th>
                                        <th>Durée (jours)</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr id="emptyOrdonnanceRow">
                                        <td colspan="4" class="text-center text-muted">Aucun médicament ajouté</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🧪 Examens --}}
                <div class="tab-pane fade" id="analyse">
                    <div class="card shadow-lg mb-3">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between">
                            🧪 Prescriptions / Examens
                            <button type="button" id="btnAjouterAnalyse" class="btn btn-light btn-sm">➕ Ajouter</button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group" id="analyseList">
                                <li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- 📅 Rendez-vous --}}
                <div class="tab-pane fade" id="rdv">
                    <div class="card shadow-lg mb-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between">
                            📅 Rendez-vous de suivi
                            <button type="button" id="btnAjouterRdv" class="btn btn-light btn-sm">➕ Ajouter</button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group" id="rdvList">
                                <li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- 📝 Certificat & Hospitalisation --}}
                <div class="tab-pane fade" id="certificat">
                    <div class="card shadow-lg mb-3">
                        <div class="card-header bg-secondary text-white">📝 Certificat & Hospitalisation</div>
                        <div class="card-body">
                            <textarea class="form-control mb-2" name="certificat" placeholder="Certificat éventuel..." rows="2"></textarea>
                            <div class="form-check">
                                <input type="checkbox" name="hospitalisation" class="form-check-input" id="hospitalisationCheck" value="1">
                                <label class="form-check-label" for="hospitalisationCheck">Proposer une hospitalisation</label>
                            </div>

                            <div id="hospitalisationFields" style="display:none;" class="mt-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label>Date entrée</label>
                                        <input type="date" name="date_entree" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Date sortie</label>
                                        <input type="date" name="date_sortie" class="form-control">
                                    </div>
                                </div>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6">
                                        <label>Salle</label>
                                        <select id="salleSelect" name="salle_id" class="form-select">
                                            <option value="">-- Sélectionner une salle --</option>
                                            @foreach($salles as $salle)
                                                <option value="{{ $salle->id }}">{{ $salle->nom }} ({{ $salle->type }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Lit</label>
                                        <select id="litSelect" name="lit_id" class="form-select">
                                            <option value="">-- Sélectionner un lit --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label>Observations</label>
                                    <textarea name="observations" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-success">✅ Enregistrer</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            // === CSRF pour AJAX ===
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            // Patient sélection
            $('#patientDropdown').change(function(){
                let selected = $(this).find(':selected');
                $('#patient_id').val(selected.val());           // ID patient
                $('#ticket_id').val(selected.data('ticket'));   // ID ticket
            });


            // Calcul IMC
            $('input[name="poids"], input[name="taille"]').on('input', function(){
                let poids = parseFloat($('input[name="poids"]').val()) || 0;
                let taille = parseFloat($('input[name="taille"]').val()) || 0;
                $('#imc').val(taille>0 ? (poids/((taille/100)**2)).toFixed(2) : '');
            });

            // Toggle hospitalisation
            $('#hospitalisationCheck').change(function(){
                $('#hospitalisationFields').toggle(this.checked);
            });

            // Maladies dynamiques selon symptômes
            const symptomeMaladieMap = @json($symptomeMaladieMap);
            const maladies = @json($maladies);
            $('#symptomes').change(function(){
                let selected = $(this).val() || [];
                let maladieIds = new Set();
                $.each(selected, (_, sid) => {
                    $.each(symptomeMaladieMap[sid] || [], (_, mid)=> maladieIds.add(mid));
                });
                let select = $('#maladieSelect').empty().append('<option value="">-- Sélectionner la maladie --</option>');
                $.each(maladies, (_, m)=>{
                    if(maladieIds.has(m.id)) select.append('<option value="'+m.id+'">'+m.nom+'</option>');
                });
            });

            // Médicaments dynamiques
            function updateMedicamentOptions(){
                let selected = [];
                $('.selectMedicament').each(function(){ if($(this).val()) selected.push($(this).val()); });
                $('.selectMedicament').each(function(){
                    let currentVal = $(this).val();
                    $(this).find('option').each(function(){
                        if($(this).val()!=="" && selected.includes($(this).val()) && $(this).val()!==currentVal) $(this).hide();
                        else $(this).show();
                    });
                });
            }
            $('#btnAjouterMedicament').click(function(){
                $('#emptyOrdonnanceRow').remove();
                let row = `<tr>
            <td>
                <select name="medicaments[]" class="form-control selectMedicament" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($medicaments as $med)
                <option value="{{ $med->id }}">{{ $med->nom }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="text" name="posologies[]" class="form-control" required></td>
            <td><input type="number" name="duree_jours[]" class="form-control" min="1" value="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">❌</button></td>
        </tr>`;
                $('#ordonnanceTable tbody').append(row);
                updateMedicamentOptions();
            });
            $(document).on('change', '.selectMedicament', updateMedicamentOptions);
            $(document).on('click', '.btnSupprimer', function(){
                $(this).closest('tr').remove();
                if($('#ordonnanceTable tbody tr').length===0)
                    $('#ordonnanceTable tbody').append('<tr id="emptyOrdonnanceRow"><td colspan="4" class="text-center text-muted">Aucun médicament ajouté</td></tr>');
                updateMedicamentOptions();
            });

            // Examens dynamiques
            $('#btnAjouterAnalyse').click(function(){
                $('#emptyAnalyseRow').remove();
                $('#analyseList').append(`<li class="list-group-item">
            <input type="text" name="examens[]" class="form-control" placeholder="Nom de l'examen" required>
            <button type="button" class="btn btn-danger btn-sm mt-1 btnSupprimerAnalyse">❌</button>
        </li>`);
            });
            $(document).on('click', '.btnSupprimerAnalyse', function(){
                $(this).closest('li').remove();
                if($('#analyseList li').length===0)
                    $('#analyseList').append('<li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>');
            });

            // RDV dynamiques
            $('#btnAjouterRdv').click(function(){
                $('#emptyRdvRow').remove();
                $('#rdvList').append(`<li class="list-group-item">
            <div class="row g-2">
                <div class="col-md-5"><input type="text" name="rdv_motifs[]" class="form-control" placeholder="Motif" required></div>
                <div class="col-md-3"><input type="date" name="rdv_dates[]" class="form-control" required></div>
                <div class="col-md-3"><input type="time" name="rdv_heures[]" class="form-control" required></div>
                <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btnSupprimerRdv">❌</button></div>
            </div>
        </li>`);
            });
            $(document).on('click', '.btnSupprimerRdv', function(){
                $(this).closest('li').remove();
                if($('#rdvList li').length===0)
                    $('#rdvList').append('<li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>');
            });

            // Lits dynamiques
            $('#salleSelect').change(function(){
                let salleId = $(this).val();
                $('#litSelect').html('<option value="">Chargement...</option>');
                if(salleId){
                    $.get("{{ url('/salle') }}/"+salleId+"/lits-libres", function(lits){
                        $('#litSelect').html('<option value="">-- Sélectionner un lit --</option>');
                        lits.forEach(lit => $('#litSelect').append('<option value="'+lit.id+'">'+lit.numero+'</option>'));
                    }).fail(function(){ $('#litSelect').html('<option value="">Erreur de chargement</option>'); });
                } else $('#litSelect').html('<option value="">-- Sélectionner un lit --</option>');
            });

            // Submit AJAX
            $('#consultationForm').submit(function(e){
                e.preventDefault();

                let patientVal = $('#patientDropdown').val();
                if(!patientVal){
                    alert('Veuillez sélectionner un patient !');
                    return;
                }
                $('#patient_id').val(patientVal);

                let formData = new FormData(this);
                formData.append('_token', '{{ csrf_token() }}'); // ✅ token ajouté

                $.ajax({
                    url: "{{ route('consultations.store') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res){
                        alert(res.data);
                        $('#consultationForm')[0].reset();
                        $('#ordonnanceTable tbody').html('<tr id="emptyOrdonnanceRow"><td colspan="4" class="text-center text-muted">Aucun médicament ajouté</td></tr>');
                        $('#analyseList').html('<li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>');
                        $('#rdvList').html('<li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>');
                        $('#maladieSelect').empty().append('<option value="">-- Sélectionner la maladie --</option>');
                    },
                    error: function(xhr){
                        let errors = xhr.responseJSON?.errors;
                        if(errors){
                            let msg = Object.values(errors).map(e => e.join(', ')).join("\n");
                            alert('Erreurs :\n' + msg);
                        } else alert('Erreur serveur !');
                    }
                });
            });


        });
    </script>
@endsection
