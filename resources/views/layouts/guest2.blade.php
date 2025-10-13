@extends('layouts.app')

@section('titre', 'Consultation Patient')

@section('content')
    <div class="container mt-4">
        <form id="consultationForm" action="{{ route('consultations.store') }}" method="POST">
            @csrf

            <!-- 🔍 Patient & Liste d’attente -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white">
                    👤 Sélection du Patient & Liste d’attente
                </div>
                <div class="card-body">
                    <!-- 🔎 Recherche patient -->
                    <input type="text" class="form-control mb-3" id="search_patient" placeholder="Nom ou téléphone du patient...">

                    <!-- 🔽 Sélection patient -->
                    <select class="form-select mb-3" id="patient" name="patient_id" required>
                        <option value="">-- Sélectionner un patient --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">
                                {{ $patient->nom }} {{ $patient->prenom }} - {{ $patient->telephone }}
                            </option>
                        @endforeach
                    </select>

                    <!-- 🕒 Liste des tickets en attente -->
                    <h6 class="mt-3">🕒 Liste d'attente</h6>
                    <ul class="list-group" id="ticketList">
                        @foreach($tickets as $ticket)
                            <li class="list-group-item ticket-item"
                                data-patient-id="{{ $ticket->patient->id }}"
                                data-symptomes='@json($ticket->symptomes->pluck("id"))'>
                                {{ $ticket->patient->nom }} {{ $ticket->patient->prenom }} - {{ $ticket->patient->telephone }}
                                <span class="badge bg-primary float-end">Ticket #{{ $ticket->id }}</span>
                            </li>
                        @endforeach
                        @if($tickets->isEmpty())
                            <li class="list-group-item text-center text-muted">Aucun ticket en attente</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- 🩺 Diagnostic -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-primary text-white">🩺 Diagnostic</div>
                <div class="card-body">
                    <textarea class="form-control mb-2" name="diagnostic" placeholder="Saisir le diagnostic..." rows="3" required></textarea>
                </div>
            </div>

            <!-- ⚡ Symptômes -->
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

            <!-- 🧾 Maladie concernée -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-danger text-white">🧾 Maladie concernée</div>
                <div class="card-body">
                    <select name="maladie_id" id="maladieSelect" class="form-select" required>
                        <option value="">-- Sélectionner la maladie --</option>
                    </select>
                </div>
            </div>

            <!-- 💊 Ordonnance -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-success text-white d-flex justify-content-between">
                    <span>💊 Prescriptions / Ordonnance</span>
                    <button type="button" id="btnAjouterMedicament" class="btn btn-light btn-sm">➕ Ajouter</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="ordonnanceTable">
                            <thead>
                            <tr>
                                <th>Médicament</th>
                                <th>Dosage</th>
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

            <!-- 🧪 Analyses / Examens -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-warning text-dark d-flex justify-content-between">
                    <span>🧪 Analyses / Examens</span>
                    <button type="button" id="btnAjouterAnalyse" class="btn btn-light btn-sm">➕ Ajouter</button>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="analyseList">
                        <li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>
                    </ul>
                </div>
            </div>

            <!-- 📅 Rendez-vous -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-info text-white d-flex justify-content-between">
                    <span>📅 Rendez-vous de suivi</span>
                    <button type="button" id="btnAjouterRdv" class="btn btn-light btn-sm">➕ Ajouter</button>
                </div>
                <div class="card-body">
                    <ul class="list-group" id="rdvList">
                        <li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>
                    </ul>
                </div>
            </div>

            <!-- 📝 Certificat & Hospitalisation -->
            <div class="card shadow-lg mb-3">
                <div class="card-header bg-secondary text-white">📝 Certificat & Hospitalisation</div>
                <div class="card-body">
                    <textarea class="form-control mb-2" name="certificat" placeholder="Certificat éventuel..." rows="2"></textarea>
                    <div class="form-check">
                        <input type="checkbox" name="hospitalisation" class="form-check-input" id="hospitalisationCheck">
                        <label class="form-check-label" for="hospitalisationCheck">Proposer une hospitalisation</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-success">✅ Enregistrer Consultation</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        const symptomeMaladieMap = @json($symptomeMaladieMap);
        const maladies = @json($maladies);

        // 🔹 Affichage maladies selon symptômes sélectionnés
        $('#symptomes').on('change', function() {
            let selected = $(this).val() || [];
            let maladieIds = new Set();

            selected.forEach(symptomeId => {
                (symptomeMaladieMap[symptomeId] || []).forEach(mId => maladieIds.add(mId));
            });

            let select = $('#maladieSelect');
            select.empty();
            select.append('<option value="">-- Sélectionner la maladie --</option>');

            if(maladieIds.size === 0){
                select.append('<option value="" disabled>Aucune maladie détectée</option>');
            } else {
                maladies.forEach(m => {
                    if(maladieIds.has(m.id)){
                        select.append(`<option value="${m.id}">${m.nom}</option>`);
                    }
                });
            }
        });

        // 🔹 Ordonnance dynamique
        $('#btnAjouterMedicament').on('click', function() {
            const table = $('#ordonnanceTable tbody');
            $('#emptyOrdonnanceRow').remove();
            const row = `<tr>
            <td><input type="text" name="medicaments[]" class="form-control" required></td>
            <td><input type="text" name="dosages[]" class="form-control" required></td>
            <td><input type="number" name="durees[]" class="form-control" min="1" required></td>
            <td><button type="button" class="btn btn-danger btn-sm btnSupprimer">❌</button></td>
        </tr>`;
            table.append(row);
        });

        $(document).on('click', '.btnSupprimer', function() {
            $(this).closest('tr').remove();
            if($('#ordonnanceTable tbody tr').length === 0){
                $('#ordonnanceTable tbody').append('<tr id="emptyOrdonnanceRow"><td colspan="4" class="text-center text-muted">Aucun médicament ajouté</td></tr>');
            }
        });

        // 🔹 Analyses dynamiques
        $('#btnAjouterAnalyse').on('click', function() {
            const list = $('#analyseList');
            $('#emptyAnalyseRow').remove();
            const item = `<li class="list-group-item">
            <input type="text" name="analyses[]" class="form-control" placeholder="Nom de l'analyse" required>
            <button type="button" class="btn btn-danger btn-sm mt-1 btnSupprimerAnalyse">❌</button>
        </li>`;
            list.append(item);
        });

        $(document).on('click', '.btnSupprimerAnalyse', function() {
            $(this).closest('li').remove();
            if($('#analyseList li').length === 0) {
                $('#analyseList').append('<li class="list-group-item text-center text-muted" id="emptyAnalyseRow">Aucune analyse ajoutée</li>');
            }
        });

        // 🔹 Rendez-vous dynamiques
        $('#btnAjouterRdv').on('click', function() {
            const list = $('#rdvList');
            $('#emptyRdvRow').remove();
            const item = `<li class="list-group-item">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" name="rdv_motifs[]" class="form-control" placeholder="Motif du rendez-vous" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="rdv_dates[]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <input type="time" name="rdv_heures[]" class="form-control" required>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm btnSupprimerRdv">❌</button>
                </div>
            </div>
        </li>`;
            list.append(item);
        });

        $(document).on('click', '.btnSupprimerRdv', function() {
            $(this).closest('li').remove();
            if($('#rdvList li').length === 0) {
                $('#rdvList').append('<li class="list-group-item text-center text-muted" id="emptyRdvRow">Aucun rendez-vous planifié</li>');
            }
        });
    </script>
@endsection
