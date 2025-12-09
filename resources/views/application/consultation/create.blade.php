@extends('layouts.app')

@section('titre', $consultation ? 'Modifier Consultation' : 'Consultation Patient')

@section('content')
    <div class="container mt-4">

        <!-- Sélection du patient -->
        <div class="d-flex mb-3">
            <select id="patientDropdown" class="form-select me-2" required>
                <option value="">-- Sélectionner un patient en attente --</option>
                @foreach($tickets as $ticket)
                    <option value="{{ $ticket->patient->id }}" data-ticket="{{ $ticket->id }}"
                            @if($consultation && $consultation->patient_id == $ticket->patient->id) selected @endif>
                        {{ $ticket->patient->nom }} {{ $ticket->patient->prenom }} - Tel: {{ $ticket->patient->telephone }} - Âge: {{ $ticket->patient->age }} - Ticket N° {{ $ticket->id }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Formulaire consultation -->
        <form id="consultationForm" method="POST" action="{{ $consultation ? route('consultations.update', $consultation->id) : route('consultations.store') }}">
            @csrf
            @if($consultation)
                @method('PUT')
            @endif

            <input type="hidden" name="medecin_id" value="{{ auth()->user()->id }}">
            <input type="hidden" name="patient_id" id="patient_id" value="{{ $consultation->patient_id ?? '' }}">
            <input type="hidden" name="ticket_id" id="ticket_id" value="{{ $consultation->ticket_id ?? '' }}">

            {{-- ⚖️ Constantes & Antécédents --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white">⚖️ Constantes & Antécédents</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Poids (kg)</label>
                            <input type="number" name="poids" class="form-control" min="1" step="0.1" value="{{ $consultation->poids ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>Taille (cm)</label>
                            <input type="number" name="taille" class="form-control" min="30" step="0.1" value="{{ $consultation->taille ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>IMC (auto)</label>
                            <input type="text" id="imc" class="form-control" readonly
                                   value="{{ $consultation && $consultation->taille > 0 ? number_format($consultation->poids/(($consultation->taille/100)**2),2) : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>Tension</label>
                            <input type="text" name="tension" class="form-control" placeholder="Ex: 12/8" value="{{ $consultation->tension ?? '' }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Groupe sanguin</label>
                            <select name="groupe_sanguin" class="form-select">
                                <option value="">-- Sélectionner --</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                                    <option value="{{ $gs }}" @if($consultation && $consultation->groupe_sanguin == $gs) selected @endif>{{ $gs }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label>Adresse du patient</label>
                            <input type="text" name="adresse_patient" class="form-control" placeholder="Adresse complète" value="{{ $consultation->adresse_patient ?? '' }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Antécédents médicaux</label>
                        <textarea name="antecedents" class="form-control" rows="2" placeholder="Antécédents...">{{ $consultation->antecedents ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ⚡ Symptômes & Maladie --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white"></div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-4">
                            <label for="symptomes">Symptômes</label>
                            <select id="symptomes" class="form-select" name="symptomes[]" multiple>
                                @foreach($symptomes as $symptome)
                                    <option value="{{ $symptome->id }}"
                                            @if($consultation && $consultation->symptomes->contains($symptome->id)) selected @endif>
                                        {{ $symptome->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="maladie_id">Maladie</label>
                            <select name="maladie_id" id="maladieSelect" class="form-select" required>
                                <option value="">-- Sélectionner la maladie --</option>
                                @foreach($maladies as $maladie)
                                    <option value="{{ $maladie->id }}"
                                            @if($consultation && $consultation->maladies->contains($maladie->id)) selected @endif>
                                        {{ $maladie->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-4">
                            <label for="diagnostic">Diagnostic</label>
                            <textarea class="form-control mb-2" name="diagnostic" placeholder="Saisir le diagnostic..." rows="3" required>{{ $consultation->diagnostic ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 💊 Prescriptions / Ordonnance --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
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
                                <th>Quantité</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($consultation && $consultation->ordonnances->count())
                                @foreach($consultation->ordonnances as $ordonnance)
                                    @foreach($ordonnance->medicaments as $medicament)
                                        <tr>
                                            <td>
                                                <select name="medicaments[]" class="form-control selectMedicament" required>
                                                    <option value="">-- Sélectionner --</option>
                                                    @foreach($medicaments as $med)
                                                        <option value="{{ $med->id }}" @if($med->id == $medicament->id) selected @endif>{{ $med->nom }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" name="posologies[]" class="form-control" value="{{ $medicament->pivot->posologie }}" required></td>
                                            <td><input type="number" name="duree_jours[]" class="form-control" min="1" value="{{ $medicament->pivot->duree_jours }}"></td>
                                            <td><input type="number" name="quantites[]" class="form-control" min="1" value="{{ $medicament->pivot->quantite }}"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">❌</button></td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @else
                                <tr id="emptyOrdonnanceRow"><td colspan="5" class="text-center text-muted">Aucun médicament ajouté</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- 🧪 Examens --}}
                <div class="col-6">
                    <div class="card shadow-lg mb-3 ">
                        <div class="card-header bg-primary text-dark d-flex justify-content-between">
                            🧪 Prescriptions / Examens
                            <button type="button" id="btnAjouterAnalyse" class="btn btn-light btn-sm">➕ Ajouter</button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group" id="analyseList">
                                @forelse($consultation->examens ?? [] as $ex)
                                    <li class="list-group-item">
                                        <input type="text" name="examens[]" class="form-control" placeholder="Nom de l'examen" value="{{ $ex->examen }}" required>
                                        <button type="button" class="btn btn-danger btn-sm mt-1 btnSupprimerAnalyse">❌</button>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="card shadow-lg mb-3">
                        <div class="card-header bg-primary text-white d-flex justify-content-between">
                            📅 Rendez-vous de suivi
                            <button type="button" id="btnAjouterRdv" class="btn btn-light btn-sm">➕ Ajouter</button>
                        </div>
                        <div class="card-body">
                            <ul class="list-group" id="rdvList">
                                @forelse($consultation->rendezVous ?? [] as $r)
                                    <li class="list-group-item">
                                        <div class="row g-2">
                                            <div class="col-md-5"><input type="text" name="rdv_motifs[]" class="form-control" placeholder="Motif" value="{{ $r->motif }}" required></div>
                                            <div class="col-md-3"><input type="date" name="rdv_dates[]" class="form-control" value="{{ \Carbon\Carbon::parse($r->date_heure)->format('Y-m-d') }}" required></div>
                                            <div class="col-md-3"><input type="time" name="rdv_heures[]" class="form-control" value="{{ \Carbon\Carbon::parse($r->date_heure)->format('H:i') }}" required></div>
                                            <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btnSupprimerRdv">❌</button></div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>


                {{-- 📅 RDV --}}

            </div>


            {{-- 📝 Certificat & Hospitalisation --}}
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white">📝 Certificat & Hospitalisation</div>
                <div class="card-body">
                    <textarea class="form-control mb-2" name="certificat" placeholder="Certificat éventuel..." rows="2">{{ $consultation->certificat->contenu ?? '' }}</textarea>
                    <div class="form-check">
                        <input type="checkbox" name="hospitalisation" class="form-check-input" id="hospitalisationCheck" value="1" @if($consultation && $consultation->hospitalisation) checked @endif>
                        <label class="form-check-label" for="hospitalisationCheck">Proposer une hospitalisation</label>
                    </div>

                    <div id="hospitalisationFields" style="{{ $consultation && $consultation->hospitalisation ? '' : 'display:none;' }}" class="mt-3">
                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <label>Date entrée</label>
                                <input type="date" name="date_entree" class="form-control" value="{{ $consultation->hospitalisation->date_entree ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label>Salle</label>
                                <select id="salleSelect" name="salle_id" class="form-select">
                                    <option value="">-- Sélectionner une salle --</option>
                                    @foreach($salles as $salle)
                                        <option value="{{ $salle->id }}" @if($consultation && $consultation->hospitalisation && $consultation->hospitalisation->salles_id == $salle->id) selected @endif>{{ $salle->nom }} ({{ $salle->type }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Lit</label>
                                <select id="litSelect" name="lit_id" class="form-select">
                                    <option value="">-- Sélectionner un lit --</option>
                                    @if($consultation && $consultation->hospitalisation && $consultation->hospitalisation->lit_id)
                                        <option value="{{ $consultation->hospitalisation->lit_id }}" selected>Lit #{{ $consultation->hospitalisation->lit_id }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Observations</label>
                                <textarea name="observations" class="form-control">{{ $consultation->hospitalisation->observations ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-success">✅ {{ $consultation ? 'Mettre à jour' : 'Enregistrer' }} Consultation</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            // Setup CSRF
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            /*** --------------------- Patient --------------------- ***/
            $('#patientDropdown').change(function(){
                let selected = $(this).find(':selected');
                $('#patient_id').val(selected.val());
                $('#ticket_id').val(selected.data('ticket'));
            });

            /*** --------------------- IMC --------------------- ***/
            $('input[name="poids"], input[name="taille"]').on('input', function(){
                let poids = parseFloat($('input[name="poids"]').val()) || 0;
                let taille = parseFloat($('input[name="taille"]').val()) || 0;
                $('#imc').val(taille>0 ? (poids/((taille/100)**2)).toFixed(2) : '');
            });

            /*** --------------------- Hospitalisation --------------------- ***/
            $('#hospitalisationCheck').change(function(){
                $('#hospitalisationFields').toggle(this.checked);
            });

            /*** --------------------- Médicaments --------------------- ***/
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
            <td><input type="number" name="duree_jours[]" class="form-control" min="1"></td>
            <td><input type="number" name="quantites[]" class="form-control" min="1"></td>
            <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">❌</button></td>
        </tr>`;
                $('#ordonnanceTable tbody').append(row);
                updateMedicamentOptions();
            });

            $(document).on('click','.btnSupprimer',function(){
                $(this).closest('tr').remove();
                if($('#ordonnanceTable tbody tr').length===0)
                    $('#ordonnanceTable tbody').append('<tr id="emptyOrdonnanceRow"><td colspan="5" class="text-center text-muted">Aucun médicament ajouté</td></tr>');
                updateMedicamentOptions();
            });

            $(document).on('change','.selectMedicament', updateMedicamentOptions);

            /*** --------------------- Examens --------------------- ***/
            $('#btnAjouterAnalyse').click(function(){
                $('#emptyAnalyseRow').remove();
                $('#analyseList').append('<li class="list-group-item"><input type="text" name="examens[]" class="form-control" required><button type="button" class="btn btn-danger btn-sm mt-1 btnSupprimerAnalyse">❌</button></li>');
            });

            $(document).on('click','.btnSupprimerAnalyse', function(){
                $(this).closest('li').remove();
                if($('#analyseList li').length===0)
                    $('#analyseList').append('<li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>');
            });

            /*** --------------------- RDV --------------------- ***/
            $('#btnAjouterRdv').click(function(){
                $('#emptyRdvRow').remove();
                let row = `<li class="list-group-item">
            <div class="row g-2">
                <div class="col-md-5"><input type="text" name="rdv_motifs[]" class="form-control" placeholder="Motif" required></div>
                <div class="col-md-3"><input type="date" name="rdv_dates[]" class="form-control" required></div>
                <div class="col-md-3"><input type="time" name="rdv_heures[]" class="form-control" required></div>
                <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm btnSupprimerRdv">❌</button></div>
            </div>
        </li>`;
                $('#rdvList').append(row);
            });

            $(document).on('click','.btnSupprimerRdv',function(){
                $(this).closest('li').remove();
                if($('#rdvList li').length===0)
                    $('#rdvList').append('<li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>');
            });
            /*** --------------------- AJAX Store / Update --------------------- ***/
            $('#consultationForm').submit(function(e){
                e.preventDefault();

                let form = $(this);
                let url = form.attr('action');
                let method = form.find('input[name="_method"]').val() || 'POST';
                let submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Enregistrement...');

                let formData = new FormData(this);

                // Si update, ajouter _method dans FormData
                if(method.toUpperCase() === 'PUT') {
                    formData.set('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST', // Toujours POST
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response){
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.data || 'Consultation enregistrée avec succès !',
                        }).then(() => {
                            if(!form.find('input[name="_method"]').length) {
                                form.trigger('reset');
                                $('#ordonnanceTable tbody').html('<tr id="emptyOrdonnanceRow"><td colspan="5" class="text-center text-muted">Aucun médicament ajouté</td></tr>');
                                $('#analyseList').html('<li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>');
                                $('#rdvList').html('<li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>');
                            }
                            if (response.redirect) {
                                window.location.href = response.redirect; // 🔥 Redirection ici
                            }
                        });
                    },
                    error: function(xhr){
                        let errors = xhr.responseJSON?.errors;
                        let errorMsg = '';
                        if(errors){
                            $.each(errors, function(key, value){
                                errorMsg += value[0] + '\n';
                            });
                        } else {
                            errorMsg = xhr.responseJSON?.error || 'Une erreur est survenue';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: errorMsg,
                        });
                    },
                    complete: function(){
                        submitBtn.prop('disabled', false).text('{{ $consultation ? "Mettre à jour" : "Enregistrer" }} Consultation');
                    }
                });
            });
            /*** --------------------- Gestion Salle/Lit (Version robuste) --------------------- ***/
            function chargerLitsPourSalle(salleId) {
                let litSelect = $('#litSelect');
                let currentLitId = $('#lit_id').val();


                console.log('🔧 Chargement lits - Salle:', salleId, 'Lit actuel:', currentLitId);

                if (!salleId) {
                    litSelect.html('<option value="">-- Sélectionner un lit --</option>');
                    return;
                }

                litSelect.html('<option value="">-- Chargement... --</option>').prop('disabled', true);

                $.ajax({
                    url: '{{ route("salles.litsLibres", ":salleId") }}'.replace(':salleId', salleId),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        litSelect.html('').prop('disabled', false);

                        if (response.length > 0) {
                            litSelect.append('<option value="">-- Sélectionner un lit --</option>');
                            $.each(response, function(index, lit) {
                                let isSelected = (lit.id == currentLitId) ? 'selected' : '';
                                litSelect.append(`<option value="${lit.id}" ${isSelected}>Lit #${lit.numero}</option>`);
                            });

                            // FORCER la sélection après l'ajout des options
                            // if (currentLitId) {
                            //     setTimeout(() => {
                            //         let option = litSelect.find('option[value="' + currentLitId + '"]');
                            //         if (option.length > 0) {
                            //             litSelect.val(currentLitId);
                            //             console.log('✅ Lit sélectionné avec force:', currentLitId);
                            //         } else {
                            //             console.log('❌ Lit non disponible:', currentLitId);
                            //         }
                            //     }, 150);
                            // }
                        } else {
                            litSelect.append('<option value="">Aucun lit disponible</option>');
                        }
                    },
                    error: function(xhr) {
                        litSelect.html('<option value="">Erreur de chargement</option>').prop('disabled', false);
                    }
                });
            }

            $('#salleSelect').change(function() {
                chargerLitsPourSalle($(this).val());
            });

// Initialisation
//             $(document).ready(function() {
//                 let salleSelected = $('#salleSelect').val();
//                 if (salleSelected) {
//                     setTimeout(() => {
//                         chargerLitsPourSalle(salleSelected);
//                     }, 500);
//                 }
//             });
        });
    </script>
@endsection
