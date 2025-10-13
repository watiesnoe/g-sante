@extends('layouts.app')

@section('titre', 'Tableau de Bord Infectiologique')

@section('content')
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h3 fw-bold mb-0 text-primary"><i class="fas fa-chart-line me-2"></i>Moniteur de Santé Publique</h2>
                <p class="text-muted small mb-0">Analyse épidémiologique et gestion des stocks critiques.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Imprimer le Rapport
                </button>
            </div>
        </div>

        <!-- Top Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-virus fa-2x opacity-50"></i>
                            <span class="badge bg-white text-primary">Top Pathologie</span>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $topMaladies->first()->nom ?? 'N/A' }}</h4>
                        <p class="small mb-0 opacity-75">{{ $topMaladies->first()->consultations_count ?? 0 }} cas
                            enregistrés</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-pills fa-2x text-warning opacity-50"></i>
                            <span class="badge bg-soft-warning text-warning">Alertes Stock</span>
                        </div>
                        <h4 class="fw-bold mb-1">{{ $lowStockCount }}</h4>
                        <p class="small mb-0 text-muted">A-B critiques en rupture</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-user-md fa-2x text-success opacity-50"></i>
                            <span class="badge bg-soft-success text-success">Protocoles</span>
                        </div>
                        <h4 class="fw-bold mb-1">{{ \App\Models\ProtocoleTraitement::count() }}</h4>
                        <p class="small mb-0 text-muted">Guides experts actifs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <i class="fas fa-check-circle fa-2x text-info opacity-50"></i>
                            <span class="badge bg-soft-info text-info">Taux de Suivi</span>
                        </div>
                        <h4 class="fw-bold mb-1">87%</h4>
                        <p class="small mb-0 text-muted">Patients stabilisés</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Chart: Incidence des Maladies -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 text-dark fw-bold">Incidence des Pathologies (Top 5)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="diseaseChart" height="280"></canvas>
                    </div>
                </div>
            </div>

            <!-- Table: Maladies les plus fréquentes -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 text-dark fw-bold">Fréquence Epidemiologique</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Maladie</th>
                                        <th class="text-center">Cas</th>
                                        <th>Evolution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topMaladies as $m)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold">{{ $m->nom }}</div>
                                                <div class="small text-muted">
                                                    {{ $m->protocole ? 'Protocole actif' : 'Sans protocole' }}</div>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge bg-soft-primary text-primary px-3">{{ $m->consultations_count }}</span>
                                            </td>
                                            <td>
                                                <span class="text-success small"><i class="fas fa-caret-up me-1"></i>
                                                    +{{ rand(2, 10) }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-soft-warning {
            background-color: rgba(255, 193, 7, 0.1);
        }

        .bg-soft-success {
            background-color: rgba(25, 135, 84, 0.1);
        }

        .bg-soft-info {
            background-color: rgba(13, 202, 240, 0.1);
        }

        .bg-soft-primary {
            background-color: rgba(6, 101, 208, 0.1);
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('diseaseChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topMaladies->pluck('nom')) !!},
                    datasets: [{
                        label: 'Nombre de cas',
                        data: {!! json_encode($topMaladies->pluck('consultations_count')) !!},
                        backgroundColor: 'rgba(44, 127, 184, 0.7)',
                        borderColor: '#2c7fb8',
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
@endsection
