@extends('layouts.app')

@section('titre', 'Gestion Experte des Antibiotiques')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-capsules me-2"></i>Inventaire Infectiologique</h2>
            <p class="text-muted small mb-0">Contrôle des stocks et surveillance des molécules critiques.</p>
        </div>
        <div>
            <a href="{{ route('receptions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Réapprovisionner
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small mb-1 opacity-75">Molécules</div>
                            <h3 class="fw-bold mb-0">{{ $antibiotiques->count() }}</h3>
                        </div>
                        <i class="fas fa-vial fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-uppercase small mb-1 opacity-75">Stock Critique</div>
                            <h3 class="fw-bold mb-0">{{ $antibiotiques->where('stock', '<=', 'stock_min')->count() }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-shield-virus text-warning me-2"></i>
                        <h6 class="mb-0 text-uppercase small fw-bold">Note de Résistance Régionale (Estimative)</h6>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="small text-muted mt-2 mb-0">Niveau de résistance bas (15%). Surveillance active requise sur les quinolones.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">Liste des Molécules Disponibles</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="atbTable">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Médicament</th>
                        <th>Famille</th>
                        <th>Stock Actuel</th>
                        <th>Prix Vente</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antibiotiques as $atb)
                    @php
                        $isLow = $atb->stock <= $atb->stock_min;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-soft-primary text-primary rounded-circle p-2 me-3 d-none d-md-flex" style="width: 35px; height: 35px; align-items:center; justify-content:center;">
                                    <i class="fas fa-tablets fa-sm"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $atb->nom }}</div>
                                    <div class="small text-muted">{{ $atb->unite->nom ?? 'Unité' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-soft-info text-info border border-info-subtle">{{ $atb->famille->nom }}</span>
                        </td>
                        <td>
                            <div class="@if($isLow) text-danger fw-bold @else text-dark @endif">
                                {{ $atb->stock }}
                            </div>
                            <div class="small text-muted">Min: {{ $atb->stock_min }}</div>
                        </td>
                        <td>{{ number_format($atb->prix_vente, 0, ',', ' ') }} FCFA</td>
                        <td class="text-center">
                            @if($isLow)
                                <span class="badge rounded-pill bg-danger">Rupture imminente</span>
                            @else
                                <span class="badge rounded-pill bg-success">Disponible</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-alt-secondary" title="Évaluer la résistance">
                                    <i class="fas fa-microscope"></i>
                                </button>
                                <button class="btn btn-sm btn-alt-primary" title="Historique">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Aucun antibiotique trouvé dans l'inventaire.</p>
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
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .btn-alt-secondary { background-color: #f1f4f8; border: none; }
    .btn-alt-primary { background-color: #e6efff; color: #0665d0; border: none; }
    .badge { font-weight: 500; font-size: 0.75rem; }
</style>
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#atbTable').DataTable({
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
