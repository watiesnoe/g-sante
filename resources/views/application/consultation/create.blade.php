@extends('layouts.app')

@section('titre', $consultation ? 'Modifier Consultation' : 'Consultation Patient')

@section('content')
    <div class="container mt-4">

        <!-- Formulaire consultation -->
        <form id="consultationForm" method="POST" action="{{ $consultation ? route('consultations.update', $consultation->id) : route('consultations.store') }}">
            @csrf
            @if($consultation)
                @method('PUT')
            @endif

            <input type="hidden" name="medecin_id" value="{{ auth()->user()->id }}">
            <input type="hidden" name="patient_id" id="patient_id" value="{{ $consultation->patient_id ?? '' }}">
            <input type="hidden" name="ticket_id" id="ticket_id" value="{{ $consultation->ticket_id ?? '' }}">

            <!-- Carte principale -->
            <div class="card shadow-lg border-0">
                <!-- En-tête de la carte principale -->
                <div class="card-header bg-gradient-primary text-white border-0 py-3" style="background: linear-gradient(135deg, #0061f2 0%, #6900c7 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-white text-primary rounded-circle p-2 shadow-sm">
                                <i class="fas fa-stethoscope fa-2x"></i>
                            </div>
                            <div>
                                <h4 class="mb-0 fw-bold">{{ $consultation ? 'Modifier la consultation' : 'Nouvelle consultation' }}</h4>
                                <p class="mb-0 small opacity-75">Remplissez tous les champs nécessaires</p>
                            </div>
                        </div>
                        <div class="w-50">
                            <label class="form-label fw-semibold mb-1 text-white">👤 Patient</label>
                            <select id="patientDropdown" class="form-select form-select-sm border-0" required>
                                <option value="">-- Sélectionner un patient en attente --</option>
                                @foreach($tickets as $ticket)
                                    <option value="{{ $ticket->patient->id }}" data-ticket="{{ $ticket->id }}"
                                            @if($consultation && $consultation->patient_id == $ticket->patient->id) selected @endif>
                                        {{ $ticket->patient->nom }} {{ $ticket->patient->prenom }} - Tel: {{ $ticket->patient->telephone }} - Âge: {{ $ticket->patient->age }} ans - Ticket N° {{ $ticket->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 bg-light">

                    <!-- Carte 1: Constantes & Antécédents -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white rounded-circle p-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-heartbeat fa-sm"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-primary">CONSTANTES & ANTÉCÉDENTS</h6>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-secondary">Poids (kg)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"></span>
                                        <input type="number" name="poids" class="form-control form-control-sm" min="1" step="0.1" value="{{ $consultation->poids ?? '' }}" placeholder="kg">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-secondary">Taille (cm)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"></span>
                                        <input type="number" name="taille" class="form-control form-control-sm" min="30" step="0.1" value="{{ $consultation->taille ?? '' }}" placeholder="cm">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-secondary">IMC (auto)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"></span>
                                        <input type="text" id="imc" class="form-control form-control-sm bg-light" readonly
                                               value="{{ $consultation && $consultation->taille > 0 ? number_format($consultation->poids/(($consultation->taille/100)**2),2) : '' }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-secondary">Tension artérielle</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"></span>
                                        <input type="text" name="tension" class="form-control form-control-sm" placeholder="Ex: 12/8" value="{{ $consultation->tension ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">Groupe sanguin</label>
                                    <select name="groupe_sanguin" class="form-select form-select-sm bg-light">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                                            <option value="{{ $gs }}" @if($consultation && $consultation->groupe_sanguin == $gs) selected @endif>{{ $gs }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small text-secondary">Adresse du patient</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"></span>
                                        <input type="text" name="adresse_patient" class="form-control form-control-sm" placeholder="Adresse complète" value="{{ $consultation->adresse_patient ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold small text-secondary">Antécédents médicaux</label>
                                    <textarea name="antecedents" class="form-control form-control-sm" rows="2" placeholder="Antécédents médicaux, allergies, traitements en cours...">{{ $consultation->antecedents ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte 2: Symptômes & Diagnostic -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-success text-white rounded-circle p-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-notes-medical fa-sm"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-success"> SYMPTÔMES & DIAGNOSTIC</h6>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">Symptômes</label>
                                    <select id="symptomes" class="form-select form-select-sm bg-light" name="symptomes[]" multiple size="3">
                                        @foreach($symptomes as $symptome)
                                            <option value="{{ $symptome->id }}"
                                                    @if($consultation && $consultation->symptomes->contains($symptome->id)) selected @endif>
                                                {{ $symptome->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" style="font-size: 0.7rem;">Ctrl + clic pour sélectionner plusieurs</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">Maladie / Pathologie</label>
                                    <select name="maladie_id" id="maladieSelect" class="form-select form-select-sm bg-light" required>
                                        <option value="">-- Sélectionner la maladie --</option>
                                        @foreach($maladies as $maladie)
                                            <option value="{{ $maladie->id }}"
                                                    @if($consultation && $consultation->maladies->contains($maladie->id)) selected @endif>
                                                {{ $maladie->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">Diagnostic</label>
                                    <textarea class="form-control form-control-sm" name="diagnostic" placeholder="Saisir le diagnostic..." rows="3" required>{{ $consultation->diagnostic ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte 3: Prescriptions & Examens -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-info text-white rounded-circle p-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-prescription-bottle fa-sm"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-info"> PRESCRIPTIONS & EXAMENS</h6>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <!-- Ordonnance -->
                                <div class="col-md-7">
                                    <div class="border rounded p-3 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="fw-semibold small mb-0">Ordonnance médicale</label>
                                            <button type="button" id="btnAjouterMedicament" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="fas fa-plus me-1"></i> Ajouter médicament
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0" id="ordonnanceTable">
                                                <thead class="table-light">
                                                <tr style="font-size: 0.75rem;">
                                                    <th>Médicament</th>
                                                    <th>Posologie</th>
                                                    <th>Durée (j)</th>
                                                    <th>Qté</th>
                                                    <th style="width: 40px"></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @if($consultation && $consultation->ordonnances->count())
                                                    @foreach($consultation->ordonnances as $ordonnance)
                                                        @foreach($ordonnance->medicaments as $medicament)
                                                            <tr>
                                                                <td>
                                                                    <select name="medicaments[]" class="form-select form-select-sm selectMedicament" required>
                                                                        <option value="">-- Sélectionner --</option>
                                                                        @foreach($medicaments as $med)
                                                                            <option value="{{ $med->id }}" @if($med->id == $medicament->id) selected @endif>{{ $med->nom }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td><input type="text" name="posologies[]" class="form-control form-control-sm" value="{{ $medicament->pivot->posologie }}" placeholder="Ex: 2x/jour" required></td>
                                                                <td><input type="number" name="duree_jours[]" class="form-control form-control-sm" min="1" value="{{ $medicament->pivot->duree_jours }}" placeholder="Jours"></td>
                                                                <td><input type="number" name="quantites[]" class="form-control form-control-sm" min="1" value="{{ $medicament->pivot->quantite }}" placeholder="Qté"></td>
                                                                <td><button type="button" class="btn btn-link text-danger btn-sm btnSupprimer p-0"><i class="fas fa-trash-alt"></i></button></td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @else
                                                    <tr id="emptyOrdonnanceRow">
                                                        <td colspan="5" class="text-center text-muted py-3">
                                                            <i class="fas fa-pills me-2"></i>Aucun médicament ajouté
                                                        </td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- Examens et RDV -->
                                <div class="col-md-5">
                                    <div class="border rounded p-3 bg-white mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="fw-semibold small mb-0">Examens complémentaires</label>
                                            <button type="button" id="btnAjouterAnalyse" class="btn btn-warning btn-sm rounded-pill px-3">
                                                <i class="fas fa-plus me-1"></i> Ajouter
                                            </button>
                                        </div>
                                        <ul class="list-group list-group-flush" id="analyseList">
                                            @forelse($consultation->examens ?? [] as $ex)
                                                <li class="list-group-item px-0 d-flex gap-2 align-items-center">
                                                    <i class="fas fa-flask text-info"></i>
                                                    <input type="text" name="examens[]" class="form-control form-control-sm flex-grow-1" placeholder="Nom de l'examen" value="{{ $ex->examen }}" required>
                                                    <button type="button" class="btn btn-link text-danger btn-sm btnSupprimerAnalyse p-0"><i class="fas fa-times"></i></button>
                                                </li>
                                            @empty
                                                <li class="list-group-item text-center text-muted py-3" id="emptyAnalyseRow">
                                                    <i class="fas fa-flask me-2"></i>Aucune analyse ajoutée
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="border rounded p-3 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="fw-semibold small mb-0"> Rendez-vous de suivi</label>
                                            <button type="button" id="btnAjouterRdv" class="btn btn-secondary btn-sm rounded-pill px-3">
                                                <i class="fas fa-plus me-1"></i> Ajouter
                                            </button>
                                        </div>
                                        <ul class="list-group list-group-flush" id="rdvList">
                                            @forelse($consultation->rendezVous ?? [] as $r)
                                                <li class="list-group-item px-0">
                                                    <div class="row g-2 align-items-center">
                                                        <div class="col-5"><input type="text" name="rdv_motifs[]" class="form-control form-control-sm" placeholder="Motif" value="{{ $r->motif }}" required></div>
                                                        <div class="col-3"><input type="date" name="rdv_dates[]" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($r->date_heure)->format('Y-m-d') }}" required></div>
                                                        <div class="col-3"><input type="time" name="rdv_heures[]" class="form-control form-control-sm" value="{{ \Carbon\Carbon::parse($r->date_heure)->format('H:i') }}" required></div>
                                                        <div class="col-1 text-end"><button type="button" class="btn btn-link text-danger btn-sm btnSupprimerRdv p-0"><i class="fas fa-trash-alt"></i></button></div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="list-group-item text-center text-muted py-3" id="emptyRdvRow">
                                                    <i class="fas fa-calendar-check me-2"></i>Aucun rendez-vous planifié
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte 4: Certificat & Hospitalisation -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-danger text-white rounded-circle p-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-alt fa-sm"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-danger">CERTIFICAT & HOSPITALISATION</h6>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row g-3">
                                <div class="col-12">
                                    <textarea class="form-control form-control-sm" name="certificat" placeholder="Certificat médical (optionnel)..." rows="2">{{ $consultation->certificat->contenu ?? '' }}</textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="checkbox" name="hospitalisation" class="form-check-input" id="hospitalisationCheck" value="1" @if($consultation && $consultation->hospitalisation) checked @endif>
                                        <label class="form-check-label fw-semibold" for="hospitalisationCheck"> Proposer une hospitalisation</label>
                                    </div>
                                </div>
                                <div id="hospitalisationFields" style="{{ $consultation && $consultation->hospitalisation ? '' : 'display:none;' }}" class="col-12">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label small text-secondary">Date d'entrée</label>
                                            <input type="date" name="date_entree" class="form-control form-control-sm" value="{{ $consultation->hospitalisation->date_entree ?? '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-secondary">Salle</label>
                                            <select id="salleSelect" name="salle_id" class="form-select form-select-sm">
                                                <option value="">-- Sélectionner --</option>
                                                @foreach($salles as $salle)
                                                    <option value="{{ $salle->id }}" @if($consultation && $consultation->hospitalisation && $consultation->hospitalisation->salles_id == $salle->id) selected @endif>{{ $salle->nom }} ({{ $salle->type }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-secondary">Lit</label>
                                            <select id="litSelect" name="lit_id" class="form-select form-select-sm">
                                                <option value="">-- Sélectionner --</option>
                                                @if($consultation && $consultation->hospitalisation && $consultation->hospitalisation->lit_id)
                                                    <option value="{{ $consultation->hospitalisation->lit_id }}" selected>Lit #{{ $consultation->hospitalisation->lit_id }}</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-secondary">Observations</label>
                                            <input type="text" name="observations" class="form-control form-control-sm" placeholder="Observations" value="{{ $consultation->hospitalisation->observations ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- Fin card-body principal -->

                <!-- Pied de carte principale -->
                <div class="card-footer bg-white border-top-0 py-3">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>
                        <button type="submit" class="btn btn-success px-5 shadow-sm">
                            <i class="fas fa-save me-2"></i>{{ $consultation ? 'Mettre à jour' : 'Enregistrer' }} la consultation
                        </button>
                    </div>
                </div>
            </div> <!-- Fin carte principale -->
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
                        <select name="medicaments[]" class="form-select form-select-sm selectMedicament" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($medicaments as $med)
                <option value="{{ $med->id }}">{{ $med->nom }}</option>
                            @endforeach
                </select>
            </td>
            <td><input type="text" name="posologies[]" class="form-control form-control-sm" placeholder="Ex: 2x/jour" required></td>
            <td><input type="number" name="duree_jours[]" class="form-control form-control-sm" min="1" placeholder="Jours"></td>
            <td><input type="number" name="quantites[]" class="form-control form-control-sm" min="1" placeholder="Qté"></td>
            <td><button type="button" class="btn btn-link text-danger btn-sm btnSupprimer p-0"><i class="fas fa-trash-alt"></i></button></td>
        </tr>`;
                $('#ordonnanceTable tbody').append(row);
                updateMedicamentOptions();
            });

            $(document).on('click','.btnSupprimer',function(){
                $(this).closest('tr').remove();
                if($('#ordonnanceTable tbody tr').length===0)
                    $('#ordonnanceTable tbody').append('<tr id="emptyOrdonnanceRow"><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-pills me-2"></i>Aucun médicament ajouté</td></tr>');
                updateMedicamentOptions();
            });

            $(document).on('change','.selectMedicament', updateMedicamentOptions);

            /*** --------------------- Examens --------------------- ***/
            $('#btnAjouterAnalyse').click(function(){
                $('#emptyAnalyseRow').remove();
                $('#analyseList').append('<li class="list-group-item px-0 d-flex gap-2 align-items-center"><i class="fas fa-flask text-info"></i><input type="text" name="examens[]" class="form-control form-control-sm flex-grow-1" placeholder="Nom de l\'examen" required><button type="button" class="btn btn-link text-danger btn-sm btnSupprimerAnalyse p-0"><i class="fas fa-times"></i></button></li>');
            });

            $(document).on('click','.btnSupprimerAnalyse', function(){
                $(this).closest('li').remove();
                if($('#analyseList li').length===0)
                    $('#analyseList').append('<li class="list-group-item text-center text-muted py-3" id="emptyAnalyseRow"><i class="fas fa-flask me-2"></i>Aucune analyse ajoutée</li>');
            });

            /*** --------------------- RDV --------------------- ***/
            $('#btnAjouterRdv').click(function(){
                $('#emptyRdvRow').remove();
                let row = `<li class="list-group-item px-0">
                    <div class="row g-2 align-items-center">
                        <div class="col-5"><input type="text" name="rdv_motifs[]" class="form-control form-control-sm" placeholder="Motif" required></div>
                        <div class="col-3"><input type="date" name="rdv_dates[]" class="form-control form-control-sm" required></div>
                        <div class="col-3"><input type="time" name="rdv_heures[]" class="form-control form-control-sm" required></div>
                        <div class="col-1 text-end"><button type="button" class="btn btn-link text-danger btn-sm btnSupprimerRdv p-0"><i class="fas fa-trash-alt"></i></button></div>
                    </div>
                </li>`;
                $('#rdvList').append(row);
            });

            $(document).on('click','.btnSupprimerRdv',function(){
                $(this).closest('li').remove();
                if($('#rdvList li').length===0)
                    $('#rdvList').append('<li class="list-group-item text-center text-muted py-3" id="emptyRdvRow"><i class="fas fa-calendar-check me-2"></i>Aucun rendez-vous planifié</li>');
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

                if(method.toUpperCase() === 'PUT') {
                    formData.set('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
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
                                $('#ordonnanceTable tbody').html('<tr id="emptyOrdonnanceRow"><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-pills me-2"></i>Aucun médicament ajouté</td></tr>');
                                $('#analyseList').html('<li class="list-group-item text-center text-muted py-3" id="emptyAnalyseRow"><i class="fas fa-flask me-2"></i>Aucune analyse ajoutée</li>');
                                $('#rdvList').html('<li class="list-group-item text-center text-muted py-3" id="emptyRdvRow"><i class="fas fa-calendar-check me-2"></i>Aucun rendez-vous planifié</li>');
                            }
                            if (response.redirect) {
                                window.location.href = response.redirect;
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

            /*** --------------------- Gestion Salle/Lit --------------------- ***/
            function chargerLitsPourSalle(salleId) {
                let litSelect = $('#litSelect');
                let currentLitId = $('#lit_id').val();

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

            let salleInitiale = $('#salleSelect').val();
            let litInitial = @json($consultation->hospitalisation->lit_id ?? null);
            if (salleInitiale) {
                chargerLitsPourSalle(salleInitiale, litInitial);
            }
        });
    </script>
@endsection
