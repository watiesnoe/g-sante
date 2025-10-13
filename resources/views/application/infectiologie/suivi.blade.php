@extends('layouts.app')

@section('titre', 'Suivi des Traitements Infectieux')

@section('content')
@php
    $badgeStatut = ['En cours' => 'bg-primary', 'Guéri' => 'bg-success', 'Perdu de vue' => 'bg-danger'];
    $badgeEvolution = ['Guérison' => 'bg-success', 'Amélioration' => 'bg-primary', 'Stagnation' => 'bg-warning text-dark', 'Aggravation' => 'bg-danger'];
@endphp
<div class="content">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-user-nurse me-2"></i>Suivi de Cohorte</h2>
            <p class="text-muted small mb-0">Une ligne par patient et par pathologie, exercice {{ session('exercice_year', date('Y')) }}.</p>
        </div>
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <select name="maladie_id" class="form-select form-select-sm no-select2" style="min-width: 180px; max-width: 100%;" onchange="this.form.submit()">
                <option value="">Toutes les pathologies</option>
                @foreach($maladies as $m)
                    <option value="{{ $m->id }}" @selected($maladieId == $m->id)>{{ $m->nom }}</option>
                @endforeach
            </select>
            @if($maladieId)
                <a href="{{ route('infectiologie.suivi') }}" class="btn btn-outline-secondary btn-sm" title="Réinitialiser"><i class="fas fa-times"></i></a>
            @endif
            <a href="{{ route('infectiologie.suivi.pdf', array_filter(['maladie_id' => $maladieId])) }}" class="btn btn-primary btn-sm text-nowrap">
                <i class="fas fa-file-pdf me-1"></i> Rapport PDF
            </a>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden"><div class="card-body d-flex align-items-center p-3">
                <div class="icon-box bg-soft-primary text-primary me-3"><i class="fas fa-procedures"></i></div>
                <div class="min-w-0"><div class="small text-muted text-uppercase fw-bold text-truncate">Sous traitement</div>
                <h4 class="mb-0 fw-bold">{{ $enCours }}</h4>
                <div class="small text-muted">sur {{ $totalCas }} cas</div></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden"><div class="card-body d-flex align-items-center p-3">
                <div class="icon-box bg-soft-success text-success me-3"><i class="fas fa-check-double"></i></div>
                <div class="min-w-0"><div class="small text-muted text-uppercase fw-bold text-truncate">Taux d'atteinte cible</div>
                <h4 class="mb-0 fw-bold">{{ $tauxAtteinte !== null ? $tauxAtteinte.'%' : '—' }}</h4>
                <div class="small text-muted">{{ $atteinte }} / {{ $evalues }} cas évalués</div></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden"><div class="card-body d-flex align-items-center p-3">
                <div class="icon-box bg-soft-success text-success me-3"><i class="fas fa-heart"></i></div>
                <div class="min-w-0"><div class="small text-muted text-uppercase fw-bold text-truncate">Guéris</div>
                <h4 class="mb-0 fw-bold">{{ $gueris }}</h4></div>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden"><div class="card-body d-flex align-items-center p-3">
                <div class="icon-box bg-soft-danger text-danger me-3"><i class="fas fa-user-slash"></i></div>
                <div class="min-w-0"><div class="small text-muted text-uppercase fw-bold text-truncate">Perdus de vue</div>
                <h4 class="mb-0 fw-bold">{{ $perdusDeVue }}</h4>
                <div class="small text-muted">sans contact &gt; {{ $joursPerduDeVue }} j</div></div>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0 w-100 suivi-table" id="suiviTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Patient</th>
                        <th>Pathologie & Protocole</th>
                        <th>Début</th>
                        <th>Traitement de fond</th>
                        <th>Dernier contact</th>
                        <th>Prochain RDV</th>
                        <th class="text-center">Évolution</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center pe-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cohorte as $c)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-soft-secondary text-secondary rounded-circle me-3">
                                    {{ mb_substr($c['patient']->nom, 0, 1) }}{{ mb_substr($c['patient']->prenom, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $c['patient']->nom }} {{ $c['patient']->prenom }}</div>
                                    <div class="small text-muted">{{ $c['patient']->age }} ans · {{ $c['patient']->telephone }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="badge bg-soft-info text-info mb-1">{{ $c['maladie']->nom }}</div>
                            <div class="small text-muted"><i class="fas fa-file-medical me-1"></i>{{ $c['protocole']->titre }}</div>
                        </td>
                        <td data-order="{{ $c['debut']->timestamp }}">
                            <div class="text-dark small fw-bold">{{ $c['debut']->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ $c['nb_consultations'] }} consultation(s)</div>
                        </td>
                        <td>
                            <div class="small text-dark fw-semibold">{{ Str::limit($c['protocole']->traitement_principal ?? 'Non défini', 40) }}</div>
                            <div class="small text-muted fst-italic" style="font-size: 0.7rem;">{{ Str::limit($c['protocole']->posologie_principale ?? '', 50) }}</div>
                        </td>
                        <td data-order="{{ $c['dernier_contact']->timestamp }}">
                            <div class="small fw-bold">{{ $c['dernier_contact']->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ $c['dernier_contact']->locale('fr')->diffForHumans() }} · {{ $c['nb_suivis'] }} suivi(s)</div>
                        </td>
                        <td data-order="{{ $c['prochain_rdv']?->timestamp ?? 0 }}">
                            @if($c['prochain_rdv'])
                                <span class="small fw-bold">{{ $c['prochain_rdv']->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeEvolution[$c['evolution']] ?? 'bg-secondary' }} rounded-pill">{{ $c['evolution'] ?? 'Non évalué' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeStatut[$c['statut']] }} rounded-pill">{{ $c['statut'] }}</span>
                        </td>
                        <td class="text-center pe-3">
                            <a href="{{ route('consultations.show', $c['consultation_uuid']) }}" class="btn-sm" title="Dernière consultation">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    #suiviTable_wrapper { padding: 1rem; }
    #suiviTable_wrapper .dataTables_length, #suiviTable_wrapper .dataTables_filter { margin-bottom: .75rem; }
    #suiviTable_wrapper .dataTables_info, #suiviTable_wrapper .dataTables_paginate { margin-top: .75rem; }
    #suiviTable_wrapper .dataTables_filter input { margin-left: .5rem; }
    #suiviTable_wrapper .dataTables_length select { margin: 0 .5rem; }
    .min-w-0 { min-width: 0; }
    .suivi-table { font-size: .85rem; }
    .suivi-table td, .suivi-table th { white-space: normal; }
    .suivi-table th:nth-child(n+5), .suivi-table td:nth-child(n+5) { white-space: nowrap; }
    .bg-soft-primary { background-color: rgba(6, 101, 208, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-secondary { background-color: #f1f4f8; }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .icon-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
    .avatar { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#suiviTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                paginate: {
                    previous: '<i class="fa fa-chevron-left"></i>',
                    next: '<i class="fa fa-chevron-right"></i>'
                }
            },
            order: [[4, 'desc']],
            columnDefs: [{ orderable: false, targets: -1 }],
            pageLength: 10
        });
    });
</script>
@endsection
