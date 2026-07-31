@extends('layouts.app')

@section('titre', 'Emploi du Temps des Médecins')

@section('styles')
<style>
    /* ── Variables ── */
    :root {
        --edt-primary:    #2c7fb8;
        --edt-primary-dk: #1a5a8a;
        --edt-secondary:  #7fcdbb;
        --edt-success:    #27ae60;
        --edt-warning:    #f39c12;
        --edt-danger:     #e74c3c;
        --edt-light:      #f0f7ff;
        --edt-border:     #dee2e6;
        --edt-text:       #2c3e50;
    }

    /* ── Layout ── */
    .edt-wrapper { padding: 1.5rem; }

    /* ── Tabs jours ── */
    .day-tabs { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .day-tab {
        padding: .55rem 1.2rem;
        border-radius: 30px;
        border: 2px solid var(--edt-border);
        background: #fff;
        color: var(--edt-text);
        font-weight: 600;
        font-size: .85rem;
        cursor: pointer;
        transition: all .25s;
        position: relative;
    }
    .day-tab:hover { border-color: var(--edt-primary); color: var(--edt-primary); }
    .day-tab.active {
        background: var(--edt-primary);
        border-color: var(--edt-primary);
        color: #fff;
        box-shadow: 0 4px 12px rgba(44,127,184,.35);
    }
    .day-tab .badge-count {
        position: absolute;
        top: -8px; right: -8px;
        background: var(--edt-danger);
        color: #fff;
        border-radius: 50%;
        width: 20px; height: 20px;
        font-size: .7rem;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
    }
    .day-tab.today-tab { border-color: var(--edt-warning); }
    .day-tab.today-tab.active { background: var(--edt-warning); border-color: var(--edt-warning); }

    /* ── Grille médecins ── */
    .medecins-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }

    /* ── Card médecin ── */
    .medecin-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        border: 1px solid var(--edt-border);
        overflow: hidden;
        transition: transform .25s, box-shadow .25s;
        animation: fadeInUp .4s ease both;
    }
    .medecin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(44,127,184,.18);
    }
    @keyframes fadeInUp {
        from { opacity:0; transform: translateY(20px); }
        to   { opacity:1; transform: translateY(0); }
    }

    .card-avatar-band {
        background: linear-gradient(135deg, var(--edt-primary), var(--edt-primary-dk));
        padding: 1.2rem 1.2rem .8rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        color: #fff;
    }
    .medecin-avatar {
        width: 56px; height: 56px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,.35);
        object-fit: cover;
        background: rgba(255,255,255,.2);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; font-weight: 700; color: #fff;
        flex-shrink: 0;
    }
    .medecin-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
    .medecin-info h6 { margin: 0; font-size: 1rem; font-weight: 700; }
    .medecin-info small { opacity: .8; font-size: .78rem; }

    /* ── Créneaux ── */
    .creneaux-list { padding: 1rem 1.2rem; }
    .creneau-item {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .55rem .75rem;
        border-radius: 9px;
        background: var(--edt-light);
        border-left: 4px solid var(--edt-secondary);
        margin-bottom: .5rem;
        font-size: .82rem;
        transition: background .2s;
    }
    .creneau-item:hover { background: #deedf9; }
    .creneau-time { font-weight: 700; color: var(--edt-primary); min-width: 90px; }
    .creneau-meta { color: #5a6878; }
    .creneau-meta .badge-service {
        background: rgba(44,127,184,.12);
        color: var(--edt-primary);
        border-radius: 5px;
        padding: 2px 7px;
        font-size: .75rem;
        font-weight: 600;
    }

    /* ── Vide ── */
    .no-disponible {
        text-align: center;
        padding: 3rem;
        color: #adb5bd;
    }
    .no-disponible i { font-size: 3rem; margin-bottom: .75rem; }

    /* ── Vue tableau (vue par médecin) ── */
    .table-edt th, .table-edt td { vertical-align: middle; font-size: .83rem; }
    .jour-pill {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: .78rem;
        font-weight: 600;
        background: var(--edt-light);
        color: var(--edt-primary);
    }

    /* ── Header toolbar ── */
    .edt-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: .75rem;
    }
    .edt-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--edt-primary-dk);
        display: flex; align-items: center; gap: .5rem;
    }
    .view-toggle { display: flex; gap: .4rem; }
    .view-toggle .btn { border-radius: 8px !important; }

    /* ── Toggle panel ── */
    #panel-par-jour, #panel-par-medecin { display: none; }
    #panel-par-jour.show, #panel-par-medecin.show { display: block; }

    /* ── Modal formulaire ── */
    .modal-header-edt {
        background: linear-gradient(135deg, var(--edt-primary), var(--edt-primary-dk));
        color: #fff;
        border-radius: 0;
    }
    .modal-header-edt .btn-close { filter: invert(1); }
</style>
@endsection

@section('content')
<div class="edt-wrapper">

    {{-- Toolbar --}}
    <div class="edt-toolbar">
        <div class="edt-title">
            <i class="fa fa-calendar-week"></i>
            Emploi du Temps des Médecins
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="view-toggle">
                <button id="btnVueJour" class="btn btn-sm btn-primary" onclick="setView('jour')">
                    <i class="fa fa-th-large me-1"></i> Par Jour
                </button>
                <button id="btnVueMedecin" class="btn btn-sm btn-outline-primary" onclick="setView('medecin')">
                    <i class="fa fa-user-md me-1"></i> Par Médecin
                </button>
            </div>
            @if(Auth::user()->hasRole(['admin','super_admin']))
            <button class="btn btn-sm btn-success" onclick="openModal()">
                <i class="fa fa-plus me-1"></i> Ajouter un créneau
            </button>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════
         PANEL : Vue par Jour
    ══════════════════════════════════════════ --}}
    <div id="panel-par-jour" class="show">

        {{-- Onglets Jours --}}
        <div class="day-tabs" id="dayTabs">
            @foreach ($jours as $num => $nom)
                @php
                    $count = isset($parJour[$num]) ? $parJour[$num]->count() : 0;
                    $isToday = $num == $jourCourant;
                @endphp
                <button
                    class="day-tab {{ $isToday ? 'today-tab' : '' }} {{ $isToday ? 'active' : '' }}"
                    data-jour="{{ $num }}"
                    onclick="switchDay({{ $num }})"
                >
                    {{ $nom }}
                    @if($isToday)<span style="font-size:.7rem;opacity:.8;"> (auj.)</span>@endif
                    @if($count > 0)<span class="badge-count">{{ $count }}</span>@endif
                </button>
            @endforeach
        </div>

        {{-- Panels jours --}}
        @foreach ($jours as $num => $nom)
        <div class="day-panel" id="jour-{{ $num }}" style="display: {{ $num == $jourCourant ? 'block' : 'none' }};">
            @if(isset($parJour[$num]) && $parJour[$num]->count() > 0)
                <div class="medecins-grid">
                    @foreach ($parJour[$num] as $medecin)
                        @php $creneauxDuJour = $medecin->emploiDuTemps->where('jour_semaine', $num)->sortBy('heure_debut'); @endphp
                        <div class="medecin-card">
                            <div class="card-avatar-band">
                                <div class="medecin-avatar">
                                    @if($medecin->photo)
                                        <img src="{{ asset('storage/'.$medecin->photo) }}" alt="Photo">
                                    @else
                                        {{ strtoupper(substr($medecin->prenom, 0, 1) . substr($medecin->nom, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="medecin-info">
                                    <h6>Dr. {{ $medecin->prenom }} {{ $medecin->nom }}</h6>
                                    <small><i class="fa fa-briefcase-medical me-1"></i>{{ $medecin->service ? $medecin->service->nom : 'Médecin' }}</small>
                                </div>
                            </div>
                            <div class="creneaux-list">
                                @foreach ($creneauxDuJour as $creneau)
                                <div class="creneau-item">
                                    <span class="creneau-time">
                                        <i class="fa fa-clock me-1" style="opacity:.6;"></i>
                                        {{ substr($creneau->heure_debut,0,5) }} – {{ substr($creneau->heure_fin,0,5) }}
                                    </span>
                                    <div class="creneau-meta">
                                        @if($creneau->service)
                                            <span class="badge-service">{{ $creneau->service }}</span>
                                        @endif
                                        @if($creneau->lieu)
                                            <div class="mt-1"><i class="fa fa-map-marker-alt me-1" style="opacity:.5;"></i>{{ $creneau->lieu }}</div>
                                        @endif
                                    </div>
                                    @if(Auth::user()->hasRole(['admin','super_admin']))
                                    <div class="ms-auto d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-info p-1" style="font-size:.7rem;" onclick="editCreneau({{ $creneau->id }})" title="Modifier">
                                            <i class="fa fa-pencil-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger p-1" style="font-size:.7rem;" onclick="deleteCreneau({{ $creneau->id }})" title="Supprimer">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-disponible">
                    <i class="fa fa-calendar-times d-block mb-2 text-muted"></i>
                    <p class="text-muted mb-0">Aucun médecin disponible ce jour-là.</p>
                </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════
         PANEL : Vue par Médecin (tableau)
    ══════════════════════════════════════════ --}}
    <div id="panel-par-medecin">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-table me-1"></i> Planning par Médecin</h3>
            </div>
            <div class="block-content block-content-full">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-edt" id="edt-table">
                        <thead class="table-light">
                            <tr>
                                <th>Médecin</th>
                                <th>Jour</th>
                                <th>Heure début</th>
                                <th>Heure fin</th>
                                <th>Service</th>
                                <th>Lieu</th>
                                @if(Auth::user()->hasRole(['admin','super_admin']))
                                <th class="text-center">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($medecins as $medecin)
                                @foreach ($medecin->emploiDuTemps->sortBy(['jour_semaine','heure_debut']) as $idx => $creneau)
                                <tr>
                                    @if($idx === 0)
                                    <td rowspan="{{ $medecin->emploiDuTemps->count() }}" style="vertical-align:middle;">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($medecin->photo)
                                                <img src="{{ asset('storage/'.$medecin->photo) }}" class="rounded-circle" width="36" height="36" style="object-fit:cover;">
                                            @else
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:36px;height:36px;font-size:.85rem;font-weight:700;background:var(--edt-primary)!important;">
                                                    {{ strtoupper(substr($medecin->prenom,0,1).substr($medecin->nom,0,1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <strong>Dr. {{ $medecin->prenom }} {{ $medecin->nom }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    @endif
                                    <td><span class="jour-pill">{{ $creneau->jour_nom }}</span></td>
                                    <td>{{ substr($creneau->heure_debut,0,5) }}</td>
                                    <td>{{ substr($creneau->heure_fin,0,5) }}</td>
                                    <td>{{ $creneau->service ?? '-' }}</td>
                                    <td>{{ $creneau->lieu ?? '-' }}</td>
                                    @if(Auth::user()->hasRole(['admin','super_admin']))
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info" onclick="editCreneau({{ $creneau->id }})" title="Modifier"><i class="fa fa-pencil-alt"></i></button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteCreneau({{ $creneau->id }})" title="Supprimer"><i class="fa fa-trash"></i></button>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Aucun planning enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════
     MODAL : Ajouter / Modifier un créneau
══════════════════════════════════════════ --}}
@if(Auth::user()->hasRole(['admin','super_admin']))
<div class="modal fade" id="edtModal" tabindex="-1" aria-labelledby="edtModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="edtForm">
            @csrf
            <input type="hidden" name="id" id="creneau_id">
            <div class="modal-content">
                <div class="modal-header modal-header-edt">
                    <h5 class="modal-title" id="edtModalLabel"><i class="fa fa-calendar-plus me-2"></i>Ajouter un créneau</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Médecin <span class="text-danger">*</span></label>
                        <select name="medecin_id" id="medecin_id" class="form-select" required>
                            <option value="">-- Sélectionner un médecin --</option>
                            @foreach ($medecins as $m)
                                <option value="{{ $m->id }}">Dr. {{ $m->prenom }} {{ $m->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Jour de la semaine <span class="text-danger">*</span></label>
                        <select name="jour_semaine" id="jour_semaine" class="form-select" required>
                            <option value="">-- Choisir un jour --</option>
                            @foreach($jours as $num => $nom)
                                <option value="{{ $num }}">{{ $nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Heure début <span class="text-danger">*</span></label>
                            <input type="time" name="heure_debut" id="heure_debut" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Heure fin <span class="text-danger">*</span></label>
                            <input type="time" name="heure_fin" id="heure_fin" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type de service</label>
                        <input type="text" name="service" id="service" class="form-control" placeholder="Ex: Consultation, Garde, Urgence…">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lieu / Salle</label>
                        <input type="text" name="lieu" id="lieu" class="form-control" placeholder="Ex: Cabinet 3, Salle A…">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Informations complémentaires…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
/* ── Données JSON pour les créneaux (pour edition) ── */
const allCreneaux = @json(
    \App\Models\EmploiDuTemps::with('medecin')->get()->map(fn($c) => [
        'id'           => $c->id,
        'medecin_id'   => $c->medecin_id,
        'jour_semaine' => $c->jour_semaine,
        'heure_debut'  => substr($c->heure_debut, 0, 5),
        'heure_fin'    => substr($c->heure_fin, 0, 5),
        'service'      => $c->service,
        'lieu'         => $c->lieu,
        'notes'        => $c->notes,
    ])
);

/* ════════════════════════════════════════
   Gestion des vues (par jour / par médecin)
════════════════════════════════════════ */
function setView(view) {
    if (view === 'jour') {
        document.getElementById('panel-par-jour').classList.add('show');
        document.getElementById('panel-par-medecin').classList.remove('show');
        document.getElementById('btnVueJour').className = 'btn btn-sm btn-primary';
        document.getElementById('btnVueMedecin').className = 'btn btn-sm btn-outline-primary';
    } else {
        document.getElementById('panel-par-medecin').classList.add('show');
        document.getElementById('panel-par-jour').classList.remove('show');
        document.getElementById('btnVueMedecin').className = 'btn btn-sm btn-primary';
        document.getElementById('btnVueJour').className = 'btn btn-sm btn-outline-primary';
    }
}

/* ════════════════════════════════════════
   Basculer entre les jours
════════════════════════════════════════ */
function switchDay(jour) {
    // Masquer tous les panels
    document.querySelectorAll('.day-panel').forEach(el => el.style.display = 'none');
    // Désactiver tous les onglets
    document.querySelectorAll('.day-tab').forEach(el => el.classList.remove('active'));
    // Afficher le bon panel et activer l'onglet
    document.getElementById('jour-' + jour).style.display = 'block';
    document.querySelector('.day-tab[data-jour="' + jour + '"]').classList.add('active');
}

/* ════════════════════════════════════════
   Modal : Ouvrir (nouveau)
════════════════════════════════════════ */
function openModal() {
    document.getElementById('edtForm').reset();
    document.getElementById('creneau_id').value = '';
    document.querySelector('#edtModalLabel').innerHTML = '<i class="fa fa-calendar-plus me-2"></i>Ajouter un créneau';
    new bootstrap.Modal(document.getElementById('edtModal')).show();
}

/* ════════════════════════════════════════
   Modal : Éditer un créneau
════════════════════════════════════════ */
function editCreneau(id) {
    const c = allCreneaux.find(x => x.id == id);
    if (!c) return;
    document.getElementById('creneau_id').value    = c.id;
    document.getElementById('medecin_id').value    = c.medecin_id;
    document.getElementById('jour_semaine').value  = c.jour_semaine;
    document.getElementById('heure_debut').value   = c.heure_debut;
    document.getElementById('heure_fin').value     = c.heure_fin;
    document.getElementById('service').value       = c.service || '';
    document.getElementById('lieu').value          = c.lieu || '';
    document.getElementById('notes').value         = c.notes || '';
    document.querySelector('#edtModalLabel').innerHTML = '<i class="fa fa-pencil-alt me-2"></i>Modifier le créneau';
    new bootstrap.Modal(document.getElementById('edtModal')).show();
}

/* ════════════════════════════════════════
   Supprimer un créneau
════════════════════════════════════════ */
function deleteCreneau(id) {
    Swal.fire({
        title: 'Supprimer ce créneau ?',
        text: 'Cette action est irréversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#e74c3c',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: '/emploi-du-temps/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(r) {
                Swal.fire({ icon: 'success', title: 'Supprimé !', text: r.message, timer: 1800, showConfirmButton: false });
                setTimeout(() => location.reload(), 1800);
            },
            error: function() { Swal.fire('Erreur', 'Impossible de supprimer.', 'error'); }
        });
    });
}

/* ════════════════════════════════════════
   Soumission du formulaire (store)
════════════════════════════════════════ */
document.getElementById('edtForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const data = $(this).serialize();
    $.ajax({
        url: '{{ route("emploi-du-temps.store") }}',
        method: 'POST',
        data: data,
        success: function(r) {
            bootstrap.Modal.getInstance(document.getElementById('edtModal'))?.hide();
            Swal.fire({ icon: 'success', title: 'Succès', text: r.message, timer: 1800, showConfirmButton: false });
            setTimeout(() => location.reload(), 1800);
        },
        error: function(xhr) {
            const errors = xhr.responseJSON?.errors;
            let msg = "Erreur de validation.";
            if (errors) msg = Object.values(errors).flat().join('\n');
            Swal.fire('Erreur', msg, 'error');
        }
    });
});
</script>
@endsection
