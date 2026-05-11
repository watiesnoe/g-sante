@extends('layouts.app')

@section('titre', 'Suivi des Traitements Infectieux')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-user-nurse me-2"></i>Suivi de Cohorte</h2>
            <p class="text-muted small mb-0">Surveillance des patients sous protocoles infectiologiques experts.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="fas fa-filter me-1"></i> Filtrer par Pathologie
            </button>
            <button class="btn btn-primary btn-sm rounded-pill">
                <i class="fas fa-file-export me-1"></i> Rapport PDF
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-primary text-primary me-3">
                        <i class="fas fa-procedures"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-bold">Total Sous Protocole</div>
                        <h4 class="mb-0 fw-bold">{{ $suivis->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-warning text-warning me-3">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-bold">Consultés ce jour</div>
                        <h4 class="mb-0 fw-bold">{{ $suivis->where('date_consultation', '>=', now()->startOfDay())->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-soft-success text-success me-3">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div>
                        <div class="small text-muted text-uppercase fw-bold">Taux d'Atteinte Cible</div>
                        <h4 class="mb-0 fw-bold">92%</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="suiviTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Patient</th>
                        <th>Pathologie & Protocole</th>
                        <th>Date Début</th>
                        <th>Traitement de fond</th>
                        <th class="text-center">Évolution</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suivis as $s)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-soft-secondary text-secondary rounded-circle me-3">
                                    {{ substr($s->patient->nom, 0, 1) }}{{ substr($s->patient->prenom, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $s->patient->nom }} {{ $s->patient->prenom }}</div>
                                    <div class="small text-muted">ID: #{{ $s->patient->id }} | {{ $s->patient->age }} ans</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="badge bg-soft-info text-info mb-1">{{ $s->maladies->first()->nom ?? 'N/A' }}</div>
                            @php $protocole = $s->maladies->first()->protocole ?? null; @endphp
                            <div class="small text-muted"><i class="fas fa-file-medical me-1"></i>{{ $protocole->titre ?? 'Sans protocole' }}</div>
                        </td>
                        <td>
                            <div class="text-dark small fw-bold">{{ \Carbon\Carbon::parse($s->date_consultation)->format('d/m/Y') }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Il y a {{ \Carbon\Carbon::parse($s->date_consultation)->diffForHumans() }}</div>
                        </td>
                        <td>
                            <div class="small text-dark fw-semibold">{{ Str::limit($protocole->traitement_principal ?? 'Non défini', 40) }}</div>
                            <div class="small text-muted italic" style="font-size: 0.7rem;">{{ Str::limit($protocole->posologie_principale ?? '', 50) }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success rounded-pill">Stable</span>
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('consultations.show', $s) }}" class="btn-sm" title="Dossier">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-user-clock fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Aucun patient sous protocole expert actuellement.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(6, 101, 208, 0.1); }
    .bg-soft-warning { background-color: rgba(255, 193, 7, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-secondary { background-color: #f1f4f8; }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85rem;
    }
    .btn-alt-primary { background-color: #e6efff; color: #0665d0; border: none; }
</style>
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
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers',
                pageLength: 10
            });
        });
    </script>
@endsection
@endsection
