@extends('layouts.app')

@section('titre', 'Référentiel des Pathogènes')

@section('content')
<div class="container mt-4 mb-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary">
                <i class="fas fa-bacteria me-2"></i>Atlas des Pathogènes & Germes
            </h2>
            <p class="text-muted small mb-0">Base de données microbiologique et protocoles thérapeutiques associés.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary shadow-sm rounded-pill px-4" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Exporter
            </button>
        </div>
    </div>

    <!-- Stats summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white hover-lift">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-soft-primary text-primary rounded-3 p-3 me-3">
                        <i class="fas fa-vial fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small fw-bold text-muted">Pathologies</div>
                        <div class="h4 mb-0 fw-bold">{{ $pathologies->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white hover-lift">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-soft-success text-success rounded-3 p-3 me-3">
                        <i class="fas fa-microscope fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small fw-bold text-muted">Germes Listés</div>
                        <div class="h4 mb-0 fw-bold">120+</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white hover-lift">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-soft-warning text-warning rounded-3 p-3 me-3">
                        <i class="fas fa-shield-virus fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small fw-bold text-muted">Aide Prescription</div>
                        <div class="h4 mb-0 fw-bold">Active</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main DataTable Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Répertoire Microbien & Thérapeutique</h5>
            <div id="table-search-container"></div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="pathogensTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 25%;">Pathologie</th>
                            <th style="width: 30%;">Germes / Pathogènes</th>
                            <th style="width: 30%;">Traitement (1ère Ligne)</th>
                            <th class="text-center pe-4" style="width: 15%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pathologies as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary mb-1">{{ $p->nom }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;">
                                    {{ $p->protocole->signes ?? 'Aucun signe décrit' }}
                                </div>
                            </td>
                            <td>
                                @if($p->protocole && $p->protocole->germes_adulte)
                                    @php
                                        $germs = explode(',', $p->protocole->germes_adulte);
                                    @endphp
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($germs as $germ)
                                            <span class="badge bg-soft-info text-info border border-info-subtle small-badge">
                                                <i class="fas fa-bug me-1"></i>{{ trim($germ) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small italic">Données non renseignées</span>
                                @endif
                            </td>
                            <td>
                                @if($p->protocole && $p->protocole->traitement_principal)
                                    <div class="d-flex align-items-center">
                                        <div class="bg-soft-success text-success rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                            <i class="fas fa-pills fa-xs"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small text-dark">{{ $p->protocole->traitement_principal }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ Str::limit($p->protocole->posologie_principale, 40) }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">Aucun protocole défini</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                @if($p->protocole)
                                    <a href="{{ route('infectiologie.protocoles.show', $p->protocole) }}" class="btn btn-sm btn-light border rounded-pill px-3 shadow-none">
                                        <i class="fas fa-eye me-1"></i> Détails
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary: #0665d0;
        --soft-primary: rgba(6, 101, 208, 0.1);
        --soft-success: rgba(46, 204, 113, 0.1);
        --soft-info: rgba(13, 202, 240, 0.1);
        --soft-warning: rgba(255, 193, 7, 0.1);
    }
    
    .bg-soft-primary { background-color: var(--soft-primary); }
    .bg-soft-success { background-color: var(--soft-success); }
    .bg-soft-info { background-color: var(--soft-info); }
    .bg-soft-warning { background-color: var(--soft-warning); }

    .small-badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        font-weight: 500;
        letter-spacing: 0.2px;
    }

    .hover-lift { transition: transform 0.2s cubic-bezier(.17,.67,.83,.67); }
    .hover-lift:hover { transform: translateY(-4px); }

    table.dataTable.no-footer { border-bottom: none !important; }
    #pathogensTable_wrapper .dataTables_filter { display: none; } /* Hide default filter to use custom search box */
    
    #pathogensTable thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #7f8c8d;
        font-weight: 600;
        border-bottom: 2px solid #f1f4f8;
        padding-top: 15px;
        padding-bottom: 15px;
    }

    #pathogensTable tbody tr {
        transition: background-color 0.2s;
    }

    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#pathogensTable')) {
        $('#pathogensTable').DataTable().destroy();
    }
    
    var table = $('#pathogensTable').DataTable({
        "language": {
//"url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json"
        },
        "pageLength": 10,
        "dom": '<"d-flex justify-content-between align-items-center mb-3"f>t<"d-flex justify-content-between align-items-center p-4"ip>',
        "columnDefs": [
            { "orderable": false, "targets": [3] }
        ]
    });

    // Custom search implementation
 
    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
});
</script>
@endsection