@extends('layouts.app')

@section('titre', $consultation ? 'Modifier Consultation' : 'Consultation Patient')

@section('content')
    <div class="container mt-4">

        {{-- ═══════════════════════════════════════════════════════════════
         FORMULAIRE PRINCIPAL
    ═══════════════════════════════════════════════════════════════ --}}
        <form id="consultationForm" method="POST"
            action="{{ $consultation ? route('consultations.update', $consultation->id) : route('consultations.store') }}">
            @csrf
            @if ($consultation)
                @method('PUT')
            @endif
            <input type="hidden" name="medecin_id" value="{{ auth()->user()->id }}">
            <input type="hidden" name="patient_id" id="patient_id" value="{{ $consultation->patient_id ?? '' }}">
            <input type="hidden" name="ticket_id" id="ticket_id" value="{{ $selectedTicketId ?? '' }}">
            <input type="hidden" name="protocole_id" id="protocole_id" value="">

            <div class="row g-4 mb-4">

                {{-- ══════════════════════════════════════════════════════
                 COLONNE PRINCIPALE (9/12)
            ══════════════════════════════════════════════════════ --}}
                <div class="col-lg-9">
                    <div class="card shadow-lg border-0">

                        {{-- ──── HEADER ──── --}}
                        <div class="card-header bg-primary text-white border-0 py-3"
                            style="background: linear-gradient(135deg, #0061f2 0%, #6900c7 100%);">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white text-primary rounded-circle p-2 shadow-sm">
                                            <i class="fas fa-stethoscope fa-2x"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-0 fw-bold">
                                                {{ $consultation ? 'Modifier la consultation' : 'Nouvelle consultation' }}
                                            </h4>
                                            <p class="mb-0 small opacity-75">Remplissez tous les champs nécessaires</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-1 text-white">👤 Patient</label>
                                    <select id="patientDropdown" class="form-select form-select-sm border-0" required>
                                        <option value="">-- Sélectionner un patient en attente --</option>
                                        @foreach ($tickets as $ticket)
                                            <option value="{{ $ticket->patient->id }}" data-ticket="{{ $ticket->id }}"
                                                @if (
                                                    ($consultation && $consultation->patient_id == $ticket->patient->id) ||
                                                        (isset($selectedTicketId) && $selectedTicketId == $ticket->id)) selected @endif>
                                                {{ $ticket->patient->nom }} {{ $ticket->patient->prenom }} - Ticket N°
                                                {{ $ticket->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4 bg-light">

                            {{-- ╔═══════════════════════════════════════════════════╗
                             ║  ÉTAPE 1 : CONSTANTES & ANTÉCÉDENTS              ║
                             ╚═══════════════════════════════════════════════════╝ --}}
                            <div class="card shadow-sm mb-4 border-0">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:35px;height:35px;">
                                            <i class="fas fa-heartbeat fa-sm"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-primary">ÉTAPE 1 — CONSTANTES & ANTÉCÉDENTS</h6>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-secondary">Motif de
                                                consultation</label>
                                            <textarea name="motif" class="form-control" rows="2" placeholder="Motif de consultation...">{{ $consultation->motif ?? '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small text-secondary">Antécédents
                                                médicaux</label>
                                            <textarea name="antecedents" class="form-control" rows="2"
                                                placeholder="Antécédents médicaux, allergies, traitements en cours...">{{ $consultation->antecedents ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small text-secondary">Poids (kg)</label>
                                            <input type="number" name="poids" class="form-control" min="1"
                                                step="0.1" value="{{ $consultation->poids ?? '' }}" placeholder="kg">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small text-secondary">Taille (cm)</label>
                                            <input type="number" name="taille" class="form-control" min="30"
                                                step="0.1" value="{{ $consultation->taille ?? '' }}" placeholder="cm">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small text-secondary">IMC (auto)</label>
                                            <input type="text" id="imc" class="form-control bg-light" readonly
                                                value="{{ $consultation && $consultation->taille > 0 ? number_format($consultation->poids / ($consultation->taille / 100) ** 2, 2) : '' }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold small text-secondary">Tension
                                                artérielle</label>
                                            <input type="text" name="tension" class="form-control" placeholder="Ex: 12/8"
                                                value="{{ $consultation->tension ?? '' }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold small text-secondary">Groupe
                                                sanguin</label>
                                            <select name="groupe_sanguin" class="form-select js-select2">
                                                <option value="">-- Sélectionner --</option>
                                                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $gs)
                                                    <option value="{{ $gs }}"
                                                        @if ($consultation && $consultation->groupe_sanguin == $gs) selected @endif>
                                                        {{ $gs }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold small text-secondary">Adresse du
                                                patient</label>
                                            <input type="text" name="adresse_patient" class="form-control"
                                                placeholder="Adresse complète"
                                                value="{{ $consultation->adresse_patient ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ╔═══════════════════════════════════════════════════╗
                             ║  ÉTAPE 2 : INTERROGATOIRE & DIAGNOSTIC           ║
                             ╚═══════════════════════════════════════════════════╝ --}}
                            <div class="card shadow-sm mb-4 border-0">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:35px;height:35px;">
                                            <i class="fas fa-notes-medical fa-sm"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-success">ÉTAPE 2 — INTERROGATOIRE & DIAGNOSTIC</h6>
                                    </div>
                                </div>
                                <div class="card-body">

                                    {{-- Select caché pour compatibilité POST --}}
                                    <select id="symptomes" name="symptomes[]" multiple class="d-none">
                                        @foreach ($symptomes as $s)
                                            <option value="{{ $s->id }}">{{ $s->nom }}</option>
                                        @endforeach
                                    </select>

                                    {{-- Analyse IA (pleine largeur) --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-bold text-info text-uppercase opacity-75">
                                            <i class="fas fa-brain me-2"></i>Analyse de Probabilité
                                            <span id="symptomCounter" class="badge bg-success ms-2">0 coché(s)</span>
                                        </h6>
                                    </div>
                                    <div id="diagnosticAssistantContainer" class="bg-white rounded shadow-sm p-3 mb-3"
                                        style="min-height:120px;">
                                        <div id="diagnosticAssistantEmpty" class="text-center py-4 text-muted small">
                                            <i class="fas fa-arrow-right fa-2x mb-2 opacity-25"></i><br>
                                            Cochez les symptômes dans le panneau latéral <strong>→</strong> pour lancer
                                            l'analyse.
                                        </div>
                                        <div id="diagnosticAssistantContent"></div>
                                    </div>
                                    <div id="suggestionsInputs" style="display:none;"></div>

                                    {{-- Ligne 2 : Diagnostic final + Observations --}}
                                    <div class="row g-3 mt-4 pt-4 border-top">
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold small text-secondary">DIAGNOSTIC FINAL</label>
                                            <select name="maladie_id" id="maladieSelect" class="form-select js-select2"
                                                required>
                                                <option value="">-- Conclure sur une pathologie --</option>
                                                @foreach ($maladies as $maladie)
                                                    <option value="{{ $maladie->id }}"
                                                        @if ($consultation && $consultation->maladies->contains($maladie->id)) selected @endif>
                                                        {{ $maladie->nom }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            {{-- Console Protocole (apparaît après sélection) --}}
                                            <div id="protocoleContainer" class="mt-3" style="display:none;">
                                                <div
                                                    class="card border-0 shadow-lg border-start border-5 border-warning overflow-hidden bg-white">
                                                    <div class="card-header bg-warning py-2">
                                                        <h6 class="mb-0 text-white fw-bold">
                                                            <i class="fas fa-microscope me-2"></i>PROTOCOLE :
                                                            <span id="protocoleTitre" class="text-uppercase"></span>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-3">
                                                        <div class="small fw-bold text-success text-uppercase mb-1">
                                                            Traitement recommandé :</div>
                                                        <div id="protocoleTraitement" class="h6 fw-bold mb-1"></div>
                                                        <div id="protocolePosologie" class="small text-muted mb-2"></div>
                                                        <span id="stockBadge" class="badge bg-secondary mb-2"></span>
                                                        <div class="d-none" id="multiProtocolSelector">
                                                            <select id="selectProtocoleAlt"
                                                                class="form-select form-select-sm mt-2"></select>
                                                        </div>
                                                        <button type="button" id="btnApplyProtocole"
                                                            class="btn btn-success btn-sm w-100 shadow-sm mt-2">
                                                            <i class="fas fa-check-circle me-1"></i>Appliquer à
                                                            l'Ordonnance
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label fw-bold small text-secondary">RÉSUMÉ CLINIQUE /
                                                OBSERVATIONS</label>
                                            <textarea class="form-control" name="diagnostic" placeholder="Saisir la synthèse de la consultation..."
                                                rows="5" required>{{ $consultation->diagnostic ?? '' }}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- ╔═══════════════════════════════════════════════════╗
                             ║  ÉTAPE 3 : ORDONNANCE MÉDICALE                   ║
                             ╚═══════════════════════════════════════════════════╝ --}}
                            <div class="card shadow-sm mb-4 border-0">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:35px;height:35px;">
                                            <i class="fas fa-prescription-bottle fa-sm"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-info">ÉTAPE 3 — ORDONNANCE MÉDICALE</h6>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="fw-semibold mb-0">Ordonnance médicale</label>
                                            <button type="button" id="btnAjouterMedicament"
                                                class="btn btn-primary btn-sm mb-2 mt-3">
                                                <i class="fas fa-plus me-1"></i> Ajouter médicament
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0" id="ordonnanceTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Médicament</th>
                                                        <th>Posologie</th>
                                                        <th>Durée (Jrs)</th>
                                                        <th>Qté</th>
                                                        <th style="width:50px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($consultation && $consultation->ordonnances->count())
                                                        @foreach ($consultation->ordonnances as $ordonnance)
                                                            @foreach ($ordonnance->medicaments as $medicament)
                                                                <tr>

                                                                    <td>
                                                                        <select name="medicaments[]"
                                                                            class="form-select selectMedicament" required>
                                                                            <option value="">-- Sélectionner --
                                                                            </option>
                                                                            @foreach ($medicaments as $med)
                                                                                <option value="{{ $med->id }}"
                                                                                    data-prix="{{ $med->prix_vente }}"
                                                                                    data-stock="{{ $med->stock }}"
                                                                                    @if ($med->id == $medicament->id) selected @endif>
                                                                                    {{ $med->nom }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td><input type="text" name="posologies[]"
                                                                            class="form-control form-control-sm"
                                                                            value="{{ $medicament->pivot->posologie }}"
                                                                            required></td>
                                                                    <td><input type="number" name="duree_jours[]"
                                                                            class="form-control form-control-sm"
                                                                            value="{{ $medicament->pivot->duree_jours }}">
                                                                    </td>
                                                                    <td><input type="number" name="quantites[]"
                                                                            class="form-control form-control-sm input-qty"
                                                                            value="{{ $medicament->pivot->quantite }}">
                                                                    </td>
                                                                    <td class="text-center"><button type="button"
                                                                            class="btn btn-link text-danger btn-sm btnSupprimer p-0"><i
                                                                                class="fas fa-trash-alt"></i></button></td>
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
                                                {{-- Footer retiré (plus de prix) --}}
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Examens + RDV --}}
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <label class="fw-semibold mb-0">Examens complémentaires</label>
                                                    <button type="button" id="btnAjouterAnalyse"
                                                        class="btn btn-warning btn-sm">
                                                        <i class="fas fa-plus me-1"></i> Ajouter
                                                    </button>
                                                </div>
                                                <ul class="list-group list-group-flush" id="analyseList">
                                                    @forelse($consultation->examens ?? [] as $ex)
                                                        <li class="list-group-item px-0 d-flex gap-2 align-items-center">
                                                            <i class="fas fa-flask text-info"></i>
                                                            <input type="text" name="examens[]"
                                                                class="form-control flex-grow-1"
                                                                value="{{ $ex->examen }}" required>
                                                            <button type="button"
                                                                class="btn btn-link text-danger btn-sm btnSupprimerAnalyse p-0"><i
                                                                    class="fas fa-times"></i></button>
                                                        </li>
                                                    @empty
                                                        <li class="list-group-item text-center text-muted py-3"
                                                            id="emptyAnalyseRow">
                                                            <i class="fas fa-flask me-2"></i>Aucune analyse ajoutée
                                                        </li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="border rounded p-3 bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <label class="fw-semibold mb-0">Rendez-vous de suivi</label>
                                                    <button type="button" id="btnAjouterRdv"
                                                        class="btn btn-secondary btn-sm">
                                                        <i class="fas fa-plus me-1"></i> Ajouter
                                                    </button>
                                                </div>
                                                <ul class="list-group list-group-flush" id="rdvList">
                                                    @forelse($consultation->rendezVous ?? [] as $r)
                                                        <li class="list-group-item px-0">
                                                            <div class="row g-2">
                                                                <div class="col-5"><input type="text"
                                                                        name="rdv_motifs[]" class="form-control"
                                                                        value="{{ $r->motif }}" required></div>
                                                                <div class="col-3"><input type="date"
                                                                        name="rdv_dates[]" class="form-control"
                                                                        value="{{ \Carbon\Carbon::parse($r->date_heure)->format('Y-m-d') }}"
                                                                        required></div>
                                                                <div class="col-3"><input type="time"
                                                                        name="rdv_heures[]" class="form-control"
                                                                        value="{{ \Carbon\Carbon::parse($r->date_heure)->format('H:i') }}"
                                                                        required></div>
                                                                <div class="col-1"><button type="button"
                                                                        class="btn btn-link text-danger btn-sm btnSupprimerRdv p-0"><i
                                                                            class="fas fa-trash-alt"></i></button></div>
                                                            </div>
                                                        </li>
                                                    @empty
                                                        <li class="list-group-item text-center text-muted py-3"
                                                            id="emptyRdvRow">
                                                            <i class="fas fa-calendar-check me-2"></i>Aucun rendez-vous
                                                            planifié
                                                        </li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ╔═══════════════════════════════════════════════════╗
                             ║  ÉTAPE 4 : CERTIFICAT & HOSPITALISATION          ║
                             ╚═══════════════════════════════════════════════════╝ --}}
                            <div class="card shadow-sm mb-4 border-0">
                                <div class="card-header bg-white border-0 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:35px;height:35px;">
                                            <i class="fas fa-file-alt fa-sm"></i>
                                        </div>
                                        <h6 class="mb-0 fw-bold text-danger">ÉTAPE 4 — CERTIFICAT & HOSPITALISATION</h6>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="row g-3">
                                        <div class="col-12 mt-3">
                                            <textarea class="form-control mt-2" name="certificat" placeholder="Certificat médical (optionnel)..." rows="2">{{ $consultation->certificat->contenu ?? '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input type="checkbox" name="hospitalisation" class="form-check-input"
                                                    id="hospitalisationCheck" value="1"
                                                    @if ($consultation && $consultation->hospitalisation) checked @endif>
                                                <label class="form-check-label fw-semibold"
                                                    for="hospitalisationCheck">Proposer une hospitalisation</label>
                                            </div>
                                        </div>
                                        <div id="hospitalisationFields" class="col-12"
                                            style="{{ $consultation && $consultation->hospitalisation ? '' : 'display:none;' }}">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small text-secondary">Date d'entrée</label>
                                                    <input type="date" name="date_entree" class="form-control"
                                                        value="{{ $consultation->hospitalisation->date_entree ?? '' }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small text-secondary">Salle</label>
                                                    <select id="salleSelect" name="salle_id"
                                                        class="form-select js-select2">
                                                        <option value="">-- Sélectionner --</option>
                                                        @foreach ($salles as $salle)
                                                            <option value="{{ $salle->id }}"
                                                                @if ($consultation && $consultation->hospitalisation && $consultation->hospitalisation->salles_id == $salle->id) selected @endif>
                                                                {{ $salle->nom }} ({{ $salle->type }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small text-secondary">Lit</label>
                                                    <select id="litSelect" name="lit_id" class="form-select">
                                                        <option value="">-- Sélectionner --</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small text-secondary">Observations</label>
                                                    <input type="text" name="observations" class="form-control"
                                                        placeholder="Observations"
                                                        value="{{ $consultation->hospitalisation->observations ?? '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- fin card-body bg-light --}}

                        {{-- ──── FOOTER (Boutons) ──── --}}
                        <div class="card-footer bg-white border-top-0 py-3">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('consultations.index') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-success px-5 shadow-sm">
                                    <i class="fas fa-save me-2"></i>{{ $consultation ? 'Mettre à jour' : 'Enregistrer' }}
                                </button>
                            </div>
                        </div>

                    </div>{{-- fin card shadow-lg --}}
                </div>{{-- fin col-lg-9 --}}

                {{-- ══════════════════════════════════════════════════════
                 SIDEBAR DROITE — SYMPTÔMES + RÉFÉRENCIEL (3/12)
            ══════════════════════════════════════════════════════ --}}
                <div class="col-lg-3">
                    <div class="card shadow-sm border-0 sticky-top" style="top:70px; z-index:10;">

                        {{-- Onglets --}}
                        <div class="card-header bg-dark text-white p-0 border-0">
                            <ul class="nav nav-tabs nav-fill border-0" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active text-white border-0 rounded-0 py-2 small fw-bold"
                                        style="background:transparent;" data-bs-toggle="tab"
                                        data-bs-target="#tabSymptomes" type="button">
                                        <i class="fas fa-stethoscope me-1"></i> Symptômes
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link text-white border-0 rounded-0 py-2 small fw-bold"
                                        style="background:transparent;" data-bs-toggle="tab" data-bs-target="#tabAtlas"
                                        type="button">
                                        <i class="fas fa-book-medical me-1"></i> Atlas
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            {{-- TAB 1 : Symptômes --}}
                            <div class="tab-pane fade show active" id="tabSymptomes">
                                <div class="p-2 border-bottom bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-bold text-success"><i
                                                class="fas fa-check-circle me-1"></i>Interrogatoire</span>
                                        <button type="button" id="btnResetQuestions"
                                            class="btn btn-outline-secondary py-0 px-2"
                                            style="font-size:0.65rem;">Réinitialiser</button>
                                    </div>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 py-0"><i
                                                class="fas fa-search text-muted" style="font-size:0.7rem;"></i></span>
                                        <input type="text" id="symptomSearch" class="form-control border-start-0 py-1"
                                            placeholder="Rechercher..." style="font-size:0.8rem;">
                                    </div>
                                </div>
                                <div class="overflow-auto" id="symptomListSidebar"
                                    style="max-height: calc(100vh - 220px); scrollbar-width:thin;">
                                    @foreach ($symptomes as $symptome)
                                        <div class="symptom-item border-bottom">
                                            <label
                                                class="d-flex justify-content-between align-items-center px-3 py-2 m-0 symptom-sidebar-label"
                                                for="s_check_{{ $symptome->id }}" style="cursor:pointer;">
                                                <span class="small text-dark symptom-label">{{ $symptome->nom }}</span>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input symptom-checkbox shadow-sm"
                                                        type="checkbox" id="s_check_{{ $symptome->id }}" value="yes"
                                                        data-symptom-id="{{ $symptome->id }}"
                                                        style="cursor:pointer; width:2em; height:1em;">
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- TAB 2 : Atlas / Référenciel --}}
                            <div class="tab-pane fade" id="tabAtlas">
                                <div class="p-2 border-bottom bg-light">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 py-0"><i
                                                class="fas fa-search text-muted" style="font-size:0.7rem;"></i></span>
                                        <input type="text" id="atlasSearch" class="form-control border-start-0 py-1"
                                            placeholder="Chercher une maladie..." style="font-size:0.8rem;">
                                    </div>
                                </div>
                                <div class="list-group list-group-flush overflow-auto" id="atlasList"
                                    style="max-height: calc(100vh - 220px);">
                                    @foreach ($maladies as $m)
                                        @if ($m->protocole)
                                            <div class="list-group-item p-0 border-0 mb-1">
                                                <div class="d-flex align-items-center">
                                                    <div class="ps-2">
                                                        <input type="checkbox" class="form-check-input compare-check"
                                                            data-id="{{ $m->id }}" data-nom="{{ $m->nom }}"
                                                            style="width:0.9em;height:0.9em;">
                                                    </div>
                                                    <a href="javascript:void(0)"
                                                        class="list-group-item-action px-2 py-2 flex-grow-1 select-atlas-maladie"
                                                        data-id="{{ $m->id }}">
                                                        <span class="fw-bold text-dark"
                                                            style="font-size:0.8rem;">{{ $m->nom }}</span>
                                                        <div class="text-muted text-truncate" style="font-size:0.65rem;">
                                                            <i class="fas fa-pills me-1 text-success"></i>
                                                            {{ $m->protocole->traitement_principal }}
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>{{-- fin tab-content --}}

                        <div id="compareBar" class="card-footer bg-primary text-white py-2 d-none">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small"><span id="compareCount">0</span> sélectionné(s)</span>
                                <button type="button" id="btnLaunchCompare" class="btn btn-sm btn-light py-0">Comparer
                                    <i class="fas fa-columns ms-1"></i></button>
                            </div>
                        </div>
                        <div class="card-footer bg-light py-1 text-center">
                            <small class="text-muted" style="font-size:0.6rem;">Symptômes → IA • Atlas → Ordonnance
                                auto</small>
                        </div>
                    </div>
                </div>{{-- fin col-lg-3 --}}

            </div>{{-- fin row --}}
        </form>

    </div>{{-- fin container --}}

    {{-- ═══════════════════════════════════════════════════════════════
     MODAL — COMPARATEUR DE PROTOCOLES
═══════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="compareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title"><i class="fas fa-balance-scale me-2"></i> Comparateur de Protocoles</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="compareTable" style="table-layout:fixed;">
                            <thead class="bg-light align-middle text-center">
                                <tr>
                                    <th style="width:15%">Critères</th>
                                    <th id="compTitle1" class="text-primary fw-bold" style="width:42.5%">Pathologie A
                                    </th>
                                    <th id="compTitle2" class="text-success fw-bold" style="width:42.5%">Pathologie B
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="bg-light fw-bold small">Rappel Clinique</td>
                                    <td id="compSignes1" class="small text-muted p-3"></td>
                                    <td id="compSignes2" class="small text-muted p-3"></td>
                                </tr>
                                <tr>
                                    <td class="bg-light fw-bold small">Diagnostics / Examens</td>
                                    <td id="compDx1" class="small text-muted p-3"></td>
                                    <td id="compDx2" class="small text-muted p-3"></td>
                                </tr>
                                <tr>
                                    <td class="bg-light fw-bold small">Traitement Principal</td>
                                    <td class="p-3">
                                        <div id="compTr1" class="fw-bold mb-2"></div>
                                        <div id="compPos1" class="small fst-italic text-muted mb-2"></div>
                                        <div id="compStock1"></div>
                                    </td>
                                    <td class="p-3">
                                        <div id="compTr2" class="fw-bold mb-2"></div>
                                        <div id="compPos2" class="small fst-italic text-muted mb-2"></div>
                                        <div id="compStock2"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bg-light fw-bold small">Action</td>
                                    <td class="text-center p-3">
                                        <button type="button" class="btn btn-primary btn-sm rounded-pill"
                                            id="btnApplyComp1">Appliquer cette Ordonnance</button>
                                    </td>
                                    <td class="text-center p-3">
                                        <button type="button" class="btn btn-success btn-sm rounded-pill"
                                            id="btnApplyComp2">Appliquer cette Ordonnance</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
     STYLES LOCAUX
═══════════════════════════════════════════════════════════════ --}}
    <style>
        .list-group-item-action:hover {
            background-color: #f8fafc;
            border-left: 3px solid #0061f2;
        }

        .bg-soft-success {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        #compareTable td {
            vertical-align: top;
            line-height: 1.6;
        }

        /* Sidebar symptom items */
        .symptom-sidebar-label:hover {
            background-color: #f0f4ff;
        }

        .symptom-item.bg-soft-success .symptom-label {
            font-weight: 600;
            color: #198754 !important;
        }

        /* Active tab */
        .nav-tabs .nav-link.active {
            background: rgba(255, 255, 255, 0.15) !important;
            border-bottom: 3px solid #fff !important;
        }

        .nav-tabs .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.08) !important;
        }

        /* Diagnostic cards */
        .select-suggested-maladie:hover {
            transform: translateY(-3px);
            background-color: #f8f9fa;
        }
    </style>
@endsection

{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════ --}}
@section('scripts')
    <script>
        $(document).ready(function() {

            // ═══════════════════════════════════════════════════════════
            // 0. SETUP
            // ═══════════════════════════════════════════════════════════
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const medicamentsList = @json($medicaments);
            const symptomeMaladieMap = @json($symptomeMaladieMap);
            const maladieSymptomesDetails = @json($maladieSymptomesDetails);


            // ═══════════════════════════════════════════════════════════
            // 1. PATIENT & TICKET
            // ═══════════════════════════════════════════════════════════
            function updatePatientTicket() {
                let selected = $('#patientDropdown').find(':selected');
                $('#patient_id').val(selected.val());
                $('#ticket_id').val(selected.data('ticket'));
            }
            $('#patientDropdown').change(updatePatientTicket);
            updatePatientTicket();


            // ═══════════════════════════════════════════════════════════
            // 2. IMC AUTO
            // ═══════════════════════════════════════════════════════════
            function updateIMC() {
                let poids = parseFloat($('input[name="poids"]').val()) || 0;
                let taille = parseFloat($('input[name="taille"]').val()) || 0;
                $('#imc').val(taille > 0 ? (poids / ((taille / 100) ** 2)).toFixed(2) : '');
            }
            $('input[name="poids"], input[name="taille"]').on('input', updateIMC);
            updateIMC();


            // ═══════════════════════════════════════════════════════════
            // 3. HOSPITALISATION
            // ═══════════════════════════════════════════════════════════
            $('#hospitalisationCheck').change(function() {
                $('#hospitalisationFields').toggle(this.checked);
            });

            function chargerLitsPourSalle(salleId, litId = null) {
                let litSelect = $('#litSelect');
                let currentLitId = litId || $('#lit_id').val();
                if (!salleId) {
                    litSelect.html('<option value="">-- Sélectionner un lit --</option>');
                    return;
                }
                litSelect.html('<option value="">-- Chargement... --</option>').prop('disabled', true);
                $.ajax({
                    url: '{{ route('salles.litsLibres', ':salleId') }}'.replace(':salleId', salleId),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        litSelect.html('').prop('disabled', false);
                        if (response.length > 0) {
                            litSelect.append('<option value="">-- Sélectionner un lit --</option>');
                            $.each(response, function(i, lit) {
                                let sel = (lit.id == currentLitId) ? 'selected' : '';
                                litSelect.append(
                                    `<option value="${lit.id}" ${sel}>Lit #${lit.numero}</option>`
                                    );
                            });
                        } else {
                            litSelect.append('<option value="">Aucun lit disponible</option>');
                        }
                    },
                    error: function() {
                        litSelect.html('<option value="">Erreur de chargement</option>').prop(
                            'disabled', false);
                    }
                });
            }
            $('#salleSelect').change(function() {
                chargerLitsPourSalle($(this).val());
            });
            let salleInitiale = $('#salleSelect').val();
            let litInitial = @json($consultation->hospitalisation->lit_id ?? null);
            if (salleInitiale) chargerLitsPourSalle(salleInitiale, litInitial);


            // ═══════════════════════════════════════════════════════════
            // 4. INTERROGATOIRE (CHECKBOXES) → DIAGNOSTIC IA
            // ═══════════════════════════════════════════════════════════
            // Recherche symptômes
            $('#symptomSearch').on('keyup', function() {
                let val = $(this).val().toLowerCase();
                $('.symptom-item').each(function() {
                    let label = $(this).find('.symptom-label').text().toLowerCase();
                    $(this).toggle(label.indexOf(val) > -1);
                });
            });

            function updateSymptomCounter() {
                let count = $('.symptom-checkbox:checked').length;
                $('#symptomCounter').text(count + ' coché(s)');
            }

            $('.symptom-checkbox').on('change', function() {
                let symptomId = $(this).data('symptom-id');
                let isChecked = $(this).is(':checked');
                let item = $(this).closest('.symptom-item');

                item.removeClass('bg-soft-success');
                if (isChecked) {
                    item.addClass('bg-soft-success');
                    $(`#symptomes option[value="${symptomId}"]`).prop('selected', true);
                } else {
                    $(`#symptomes option[value="${symptomId}"]`).prop('selected', false);
                }
                updateSymptomCounter();
                $('#symptomes').trigger('change');
            });

            $('#btnResetQuestions').click(function() {
                $('.symptom-checkbox').prop('checked', false);
                $('.symptom-item').removeClass('bg-soft-success');
                $('#symptomes option').prop('selected', false);
                updateSymptomCounter();
                $('#symptomSearch').val('');
                $('.symptom-item').show();
                $('#symptomes').trigger('change');
            });

            // Analyse IA des probabilités
            $('#symptomes').change(function() {
                let selectedSymptomes = ($(this).val() || []).map(id => parseInt(id));
                let emptyState = $('#diagnosticAssistantEmpty');
                let content = $('#diagnosticAssistantContent');

                if (selectedSymptomes.length === 0) {
                    emptyState.show();
                    content.hide();
                    return;
                }
                emptyState.hide();
                content.show();

                let scores = [];
                for (let maladieId in maladieSymptomesDetails) {
                    let maladie = maladieSymptomesDetails[maladieId];
                    let req = maladie.symptomes;
                    if (!req || req.length === 0) continue;
                    let inter = req.filter(sId => selectedSymptomes.includes(sId));
                    if (inter.length > 0) {
                        scores.push({
                            id: maladieId,
                            nom: maladie.nom,
                            score: Math.round((inter.length / req.length) * 100),
                            matchedCount: inter.length,
                            totalCount: req.length
                        });
                    }
                }

                if (scores.length > 0) {
                    scores.sort((a, b) => b.score - a.score);
                    let html = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">';
                    scores.forEach(function(s) {
                        let color = s.score >= 70 ? 'success' : (s.score >= 40 ? 'warning' :
                            'secondary');
                        let prog = s.score >= 70 ? 'bg-success' : (s.score >= 40 ? 'bg-warning' :
                            'bg-secondary');
                        html += `
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm select-suggested-maladie" style="cursor:pointer; transition: transform 0.2s;" data-id="${s.id}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-dark mb-0 small">${s.nom}</h6>
                                <span class="badge bg-${color} rounded-pill" style="font-size: 0.65rem;">${s.score}%</span>
                            </div>
                            <div class="progress mb-2" style="height:5px;"><div class="progress-bar ${prog} progress-bar-striped progress-bar-animated" style="width:${s.score}%"></div></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" style="font-size: 0.7rem;">${s.matchedCount}/${s.totalCount} signes</small>
                                <small class="text-primary fw-bold" style="font-size: 0.7rem;">Détails <i class="fas fa-chevron-right ms-1"></i></small>
                            </div>
                        </div>
                    </div>
                </div>`;
                    });
                    html += '</div>';
                    content.html(html);

                    if (scores[0].score >= 80 && $('#maladieSelect').val() != scores[0].id) {
                        Toast.fire({
                            icon: 'info',
                            title: `Forte suspicion de ${scores[0].nom}`
                        });
                    }
                } else {
                    content.html(
                        '<div class="text-center py-5 text-muted small">Aucune correspondance trouvée.</div>'
                        );
                }
            });

            // Clic sur suggestion IA → sélection pathologie
            $(document).on('click', '.select-suggested-maladie', function() {
                $('#maladieSelect').val($(this).data('id')).trigger('change');
            });


            // ═══════════════════════════════════════════════════════════
            // 5. SÉLECTION PATHOLOGIE → PROTOCOLE → AUTO-ORDONNANCE
            // ═══════════════════════════════════════════════════════════
            $('#maladieSelect').change(function() {
                let maladieId = $(this).val();
                if (!maladieId) {
                    $('#protocoleContainer').hide();
                    return;
                }

                $.ajax({
                    url: '/infectiologie/api/protocoles/' + maladieId,
                    type: 'GET',
                    success: function(response) {
                        if (response.success && response.protocoles && response.protocoles
                            .length > 0) {
                            let protocols = response.protocoles;
                            let p = protocols[0];

                            if (protocols.length > 1) {
                                let opts = '';
                                protocols.forEach((item, idx) => {
                                    opts +=
                                        `<option value="${idx}">${item.titre}</option>`;
                                });
                                $('#selectProtocoleAlt').html(opts);
                                $('#multiProtocolSelector').show();
                                $('#selectProtocoleAlt').off('change').on('change', function() {
                                    updateProtocoleUI(protocols[$(this).val()]);
                                });
                            } else {
                                $('#multiProtocolSelector').hide();
                            }

                            updateProtocoleUI(p);
                            $('#protocoleContainer').slideDown();

                            // Auto-prescription
                            setTimeout(() => {
                                $('#btnApplyProtocole').trigger('click');
                            }, 300);
                        } else {
                            $('#protocoleContainer').hide();
                            $('#btnApplyProtocole').data('protocole', null);
                        }
                    },
                    error: function() {
                        $('#protocoleContainer').hide();
                    }
                });
            });

            function updateProtocoleUI(p) {
                $('#protocoleTitre').text(p.titre || 'Protocole');
                $('#protocoleTraitement').text(p.traitement_principal || 'N/A');
                $('#protocolePosologie').text(p.posologie_principale || '');

                // Stock check
                if (p.traitement_principal) {
                    let meds = p.traitement_principal.split(/[\+\,]/);
                    let inStock = 0;
                    meds.forEach(mName => {
                        if (medicamentsList.find(m => m.nom.toLowerCase().includes(mName.trim()
                            .toLowerCase()) && m.stock > 0)) inStock++;
                    });
                    let badge = $('#stockBadge');
                    if (inStock === meds.length) badge.removeClass('bg-danger bg-warning').addClass('bg-success')
                        .text('En stock ✅');
                    else if (inStock > 0) badge.removeClass('bg-success bg-danger').addClass('bg-warning text-dark')
                        .text('Stock partiel ⚠️');
                    else badge.removeClass('bg-success bg-warning').addClass('bg-danger').text('Indisponible ❌');
                }
                $('#btnApplyProtocole').data('protocole', p);
            }

            // Bouton "Appliquer à l'Ordonnance"
            $('#btnApplyProtocole').click(function() {
                let p = $(this).data('protocole');
                if (!p) return;

                // Diagnostic texte
                let diagArea = $('textarea[name="diagnostic"]');
                let currentDiag = diagArea.val() || '';
                if (!currentDiag.includes(p.titre)) {
                    diagArea.val((currentDiag ? currentDiag + '\n' : '') + `• PATHOLOGIE : ${p.titre}`);
                }

                // Génération ordonnance
                if (p.traitement_principal) {
                    if ($('#ordonnanceTable tbody tr').length <= 1 && $('#emptyOrdonnanceRow').length > 0) {
                        $('#ordonnanceTable tbody').empty();
                    }

                    let medsToSearch = p.traitement_principal.split(/[\+\,]/);
                    medsToSearch.forEach(medName => {
                        let searchTerm = medName.trim().toLowerCase();
                        if (!searchTerm) return;

                        let matchedMed = medicamentsList.find(m => m.nom.toLowerCase().includes(
                            searchTerm) || searchTerm.includes(m.nom.toLowerCase()));

                        // Anti-doublon
                        let alreadyExists = false;
                        if (matchedMed) {
                            $('.selectMedicament').each(function() {
                                if ($(this).val() == matchedMed.id) alreadyExists = true;
                            });
                        }

                        if (!alreadyExists) {
                            $('#emptyOrdonnanceRow').remove();
                            $('#ordonnanceFooter').removeClass('d-none');

                            let label = matchedMed ? '-- Sélectionner --' :
                                '-- Choisir l\'équivalent (' + medName.trim() + ') --';
                            let opts = '<option value="">' + label + '</option>';
                            medicamentsList.forEach(m => {
                                let sel = (matchedMed && m.id == matchedMed.id) ?
                                    'selected' : '';
                                opts +=
                                    `<option value="${m.id}" data-prix="${m.prix_vente}" data-stock="${m.stock}" ${sel}>${m.nom}</option>`;
                            });

                            let rowClass = matchedMed ? 'table-success' : 'table-warning';
                            let alertHtml = !matchedMed ?
                                '<small class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> Non trouvé en stock</small>' :
                                '';
                            let row = `<tr class="${rowClass} auto-added-row">
                        <td>
                            <select name="medicaments[]" class="form-select js-select2 form-select-sm selectMedicament" required>${opts}</select>
                            ${alertHtml}
                        </td>
                        <td><input type="text" name="posologies[]" class="form-control form-control-sm" value="${p.posologie_principale || ''}" required></td>
                        <td><input type="number" name="duree_jours[]" class="form-control form-control-sm" min="1" value="7"></td>
                        <td><input type="number" name="quantites[]" class="form-control form-control-sm input-qty" min="1" value="1"></td>
                        <td class="text-center"><button type="button" class="btn btn-link text-danger btn-sm btnSupprimer p-0"><i class="fas fa-trash-alt"></i></button></td>
                    </tr>`;
                            let newRow = $(row);
                            $('#ordonnanceTable tbody').append(newRow);
                            if ($.fn.select2) newRow.find('.js-select2').select2({
                                width: '100%',
                                allowClear: true
                            });
                        }
                    });
                    calculateTotals();
                    updateMedicamentOptions();
                }

                $('#protocole_id').val(p.id);
                Toast.fire({
                    icon: 'success',
                    title: 'Traitement généré !'
                });
                setTimeout(() => {
                    $('.auto-added-row').removeClass('table-success');
                }, 3000);
            });


            // ═══════════════════════════════════════════════════════════
            // 6. ORDONNANCE — AJOUT MANUEL / CALCULS / SUPPRESSION
            // ═══════════════════════════════════════════════════════════
            function updateMedicamentOptions() {
                let selected = [];
                $('.selectMedicament').each(function() {
                    if ($(this).val()) selected.push($(this).val());
                });
                $('.selectMedicament').each(function() {
                    let cur = $(this).val();
                    $(this).find('option').each(function() {
                        $(this).toggle($(this).val() === "" || !selected.includes($(this).val()) ||
                            $(this).val() === cur);
                    });
                });
            }

            $('#btnAjouterMedicament').click(function() {
                $('#emptyOrdonnanceRow').remove();
                $('#ordonnanceFooter').removeClass('d-none');
                let row = `<tr>
            <td><select name="medicaments[]" class="form-select js-select2 form-select-sm selectMedicament" required>
                <option value="">-- Sélectionner --</option>
                @foreach ($medicaments as $med)
                <option value="{{ $med->id }}" data-prix="{{ $med->prix_vente }}" data-stock="{{ $med->stock }}">{{ $med->nom }}</option>
                @endforeach
            </select></td>
            <td><input type="text" name="posologies[]" class="form-control form-control-sm" placeholder="Ex: 2x/jour" required></td>
            <td><input type="number" name="duree_jours[]" class="form-control form-control-sm" min="1" placeholder="Jours"></td>
            <td><input type="number" name="quantites[]" class="form-control form-control-sm input-qty" min="1" placeholder="Qté"></td>
            <td class="text-center"><button type="button" class="btn btn-link text-danger btn-sm btnSupprimer p-0"><i class="fas fa-trash-alt"></i></button></td>
        </tr>`;
                let newRow = $(row);
                $('#ordonnanceTable tbody').append(newRow);
                newRow.find('.js-select2').select2({
                    width: '100%',
                    placeholder: "-- Sélectionner --",
                    allowClear: true
                });
                updateMedicamentOptions();
            });

            function calculateTotals() {
                $('#ordonnanceTable tbody tr').each(function() {
                    let row = $(this);
                    let select = row.find('.selectMedicament option:selected');
                    let qty = parseFloat(row.find('.input-qty').val()) || 0;
                    let stock = parseFloat(select.data('stock')) || 0;
                    if (qty > stock) row.find('.input-qty').addClass('is-invalid').attr('title',
                        'Stock insuffisant ! (Max: ' + stock + ')');
                    else row.find('.input-qty').removeClass('is-invalid').removeAttr('title');
                });
            }

            $(document).on('change', '.selectMedicament, .input-qty', calculateTotals);
            $(document).on('click', '.btnSupprimer', function() {
                $(this).closest('tr').remove();
                if ($('#ordonnanceTable tbody tr').length === 0) {
                    $('#ordonnanceTable tbody').html(
                        '<tr id="emptyOrdonnanceRow"><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-pills me-2"></i>Aucun médicament ajouté</td></tr>'
                        );
                }
                updateMedicamentOptions();
                calculateTotals();
            });
            $(document).on('change', '.selectMedicament', updateMedicamentOptions);


            // ═══════════════════════════════════════════════════════════
            // 7. EXAMENS & RDV
            // ═══════════════════════════════════════════════════════════
            $('#btnAjouterAnalyse').click(function() {
                $('#emptyAnalyseRow').remove();
                $('#analyseList').append(
                    '<li class="list-group-item px-0 d-flex gap-2 align-items-center"><i class="fas fa-flask text-info"></i><input type="text" name="examens[]" class="form-control form-control-sm flex-grow-1" placeholder="Nom de l\'examen" required><button type="button" class="btn btn-link text-danger btn-sm btnSupprimerAnalyse p-0"><i class="fas fa-times"></i></button></li>'
                    );
            });
            $(document).on('click', '.btnSupprimerAnalyse', function() {
                $(this).closest('li').remove();
                if ($('#analyseList li').length === 0) $('#analyseList').html(
                    '<li class="list-group-item text-center text-muted py-3" id="emptyAnalyseRow"><i class="fas fa-flask me-2"></i>Aucune analyse</li>'
                    );
            });

            $('#btnAjouterRdv').click(function() {
                $('#emptyRdvRow').remove();
                $('#rdvList').append(`<li class="list-group-item px-0"><div class="row g-2 align-items-center">
            <div class="col-5"><input type="text" name="rdv_motifs[]" class="form-control form-control-sm" placeholder="Motif" required></div>
            <div class="col-3"><input type="date" name="rdv_dates[]" class="form-control form-control-sm" required></div>
            <div class="col-3"><input type="time" name="rdv_heures[]" class="form-control form-control-sm" required></div>
            <div class="col-1 text-end"><button type="button" class="btn btn-link text-danger btn-sm btnSupprimerRdv p-0"><i class="fas fa-trash-alt"></i></button></div>
        </div></li>`);
            });
            $(document).on('click', '.btnSupprimerRdv', function() {
                $(this).closest('li').remove();
                if ($('#rdvList li').length === 0) $('#rdvList').html(
                    '<li class="list-group-item text-center text-muted py-3" id="emptyRdvRow"><i class="fas fa-calendar-check me-2"></i>Aucun rendez-vous</li>'
                    );
            });


            // ═══════════════════════════════════════════════════════════
            // 8. ATLAS — SIDEBAR ACTIONS
            // ═══════════════════════════════════════════════════════════
            $('#atlasSearch').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $('#atlasList .list-group-item').each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $(document).on('click', '.select-atlas-maladie', function() {
                let id = $(this).data('id');
                $('#maladieSelect').val(id).trigger('change');
                $('html, body').animate({
                    scrollTop: $("#ordonnanceTable").offset().top - 100
                }, 800);
            });


            // ═══════════════════════════════════════════════════════════
            // 9. COMPARATEUR DE PROTOCOLES
            // ═══════════════════════════════════════════════════════════
            let selectedForCompare = [];

            $(document).on('change', '.compare-check', function() {
                let id = $(this).data('id'),
                    nom = $(this).data('nom');
                if ($(this).is(':checked')) {
                    if (selectedForCompare.length >= 2) {
                        $(this).prop('checked', false);
                        Toast.fire({
                            icon: 'warning',
                            title: 'Max 2 protocoles'
                        });
                        return;
                    }
                    selectedForCompare.push({
                        id,
                        nom
                    });
                } else {
                    selectedForCompare = selectedForCompare.filter(i => i.id != id);
                }
                updateCompareBar();
            });

            function updateCompareBar() {
                $('#compareCount').text(selectedForCompare.length);
                selectedForCompare.length > 0 ? $('#compareBar').removeClass('d-none') : $('#compareBar').addClass(
                    'd-none');
                $('#btnLaunchCompare').prop('disabled', selectedForCompare.length !== 2);
            }

            $('#btnLaunchCompare').click(function() {
                if (selectedForCompare.length !== 2) return;
                $.when(
                    $.get('/infectiologie/api/protocoles/' + selectedForCompare[0].id),
                    $.get('/infectiologie/api/protocoles/' + selectedForCompare[1].id)
                ).then(function(res1, res2) {
                    fillCompareColumn(1, res1[0].protocoles[0]);
                    fillCompareColumn(2, res2[0].protocoles[0]);
                    $('#compareModal').modal('show');
                });
            });

            function fillCompareColumn(idx, p) {
                $(`#compTitle${idx}`).text(p.titre || 'Inconnu');
                $(`#compSignes${idx}`).text(p.signes || 'N/A');
                $(`#compDx${idx}`).text(p.diagnostics || 'N/A');
                $(`#compTr${idx}`).text(p.traitement_principal || 'N/A');
                $(`#compPos${idx}`).text(p.posologie_principale || 'N/A');

                let meds = (p.traitement_principal || '').split(/[\+\,]/);
                let inStock = medicamentsList.filter(m => meds.some(s => m.nom.toLowerCase().includes(s.trim()
                    .toLowerCase()) && m.stock > 0)).length;
                let badgeClass = inStock === meds.length ? 'text-success' : (inStock > 0 ? 'text-warning' :
                    'text-danger');
                let badgeText = inStock === meds.length ? 'En stock' : (inStock > 0 ? 'Stock partiel' : 'Rupture');
                $(`#compStock${idx}`).html(
                    `<span class="badge bg-light ${badgeClass} border"><i class="fas fa-box me-1"></i> ${badgeText}</span>`
                    );

                $(`#btnApplyComp${idx}`).off('click').on('click', function() {
                    $('#maladieSelect').val(p.maladie_id).trigger('change');
                    setTimeout(() => {
                        if ($('#btnApplyProtocole').is(':visible')) $('#btnApplyProtocole').trigger(
                            'click');
                    }, 800);
                    $('#compareModal').modal('hide');
                    $('.compare-check').prop('checked', false);
                    selectedForCompare = [];
                    updateCompareBar();
                });
            }


            // ═══════════════════════════════════════════════════════════
            // 10. AJAX SUBMIT
            // ═══════════════════════════════════════════════════════════
            $('#consultationForm').submit(function(e) {
                e.preventDefault();
                let form = $(this),
                    url = form.attr('action');
                let method = form.find('input[name="_method"]').val() || 'POST';
                let submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).text('Enregistrement...');

                let formData = new FormData(this);
                if (method.toUpperCase() === 'PUT') formData.set('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Succès',
                            text: response.data || 'Consultation enregistrée !'
                        }).then(() => {
                            if (!form.find('input[name="_method"]').length) {
                                form.trigger('reset');
                                $('#ordonnanceTable tbody').html(
                                    '<tr id="emptyOrdonnanceRow"><td colspan="7" class="text-center text-muted py-3"><i class="fas fa-pills me-2"></i>Aucun médicament ajouté</td></tr>'
                                    );
                                $('#analyseList').html(
                                    '<li class="list-group-item text-center text-muted py-3" id="emptyAnalyseRow"><i class="fas fa-flask me-2"></i>Aucune analyse</li>'
                                    );
                                $('#rdvList').html(
                                    '<li class="list-group-item text-center text-muted py-3" id="emptyRdvRow"><i class="fas fa-calendar-check me-2"></i>Aucun rendez-vous</li>'
                                    );
                                updatePatientTicket();
                                updateIMC();
                            }
                            if (response.redirect) window.location.href = response
                                .redirect;
                        });
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON?.errors,
                            msg = '';
                        if (errors) $.each(errors, function(k, v) {
                            msg += v[0] + '\n';
                        });
                        else msg = xhr.responseJSON?.error || 'Une erreur est survenue';
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: msg
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save me-2"></i>{{ $consultation ? 'Mettre à jour' : 'Enregistrer' }}'
                            );
                    }
                });
            });

            // Init
            if ($('#maladieSelect').val()) $('#maladieSelect').trigger('change');

        });
    </script>
@endsection
