@extends('layouts.app')
@section('title', 'Tableau de Bord')

@section('content')
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
{{--            <h4 class="page-title">Tableau de Bord--}}
{{--                @switch(auth()->user()->role)--}}
{{--                    @case('superadmin') Super Admin @break--}}
{{--                    @case('admin') Administratif @break--}}
{{--                    @case('secretaire') Secrétaire @break--}}
{{--                    @case('medecin') Médical @break--}}
{{--                    @case('client') Patient @break--}}
{{--                    @default Général--}}
{{--                @endswitch--}}
{{--            </h4>--}}
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de Bord</a></li>
                    <li class="breadcrumb-item active">
                        @switch(auth()->user()->role)
                            @case('superadmin') Super Admin @break
                            @case('admin') Admin @break
                            @case('secretaire') Secrétaire @break
                            @case('medecin') Médecin @break
                            @case('client') Patient @break
                            @default Accueil
                        @endswitch
                    </li>
                </ol>
            </div>
        </div>

        <!-- Alert Role -->
        <div class="alert alert-primary mb-4">
            <i class="fas fa-user-shield me-2"></i>
            Vous êtes connecté en tant que <strong>{{ ucfirst(auth()->user()->role) }}</strong>.
        </div>

        @switch(auth()->user()->role)
            @case('superadmin')
                @include('dashbords.superadmin')
                @break

            @case('admin')
                @include('dashbords.admin')
                @break

            @case('secretaire')
                @include('dashbords.secretaire')
                @break

            @case('medecin')
                @include('dashbords.medecin')
                @break

            @case('client')
                @include('dashbords.client')
                @break

            @default
                @include('dashbords.default')
        @endswitch
    </div>
@endsection

@if(auth()->user()->role == 'superadmin')
    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Graphique d'activité
                const activityCtx = document.getElementById('activityChart');
                if (activityCtx) {
                    new Chart(activityCtx, {
                        type: 'line',
                        data: {
                            labels: ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
                            datasets: [
                                {
                                    label: 'Consultations',
                                    data: [12, 19, 15, 22, 18, 25, 20],
                                    borderColor: '#4670ff',
                                    backgroundColor: 'rgba(70, 128, 255, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                },
                                {
                                    label: 'Rendez-vous',
                                    data: [8, 12, 10, 15, 12, 18, 14],
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Graphique des utilisateurs
                const usersCtx = document.getElementById('usersChart');
                if (usersCtx) {
                    new Chart(usersCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Médecins', 'Patients', 'Secrétaires', 'Admins'],
                            datasets: [{
                                data: [
                                    {{ $stats['total_medecins'] ?? 0 }},
                                    {{ $stats['total_patients'] ?? 0 }},
                                    {{ $stats['total_secretaires'] ?? 0 }},
                                    {{ $stats['total_admins'] ?? 0 }}
                                ],
                                backgroundColor: ['#28a745', '#17a2b8', '#6c757d', '#343a40'],
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                }

                // Gestion de la période du graphique
                document.querySelectorAll('.chart-period').forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        const period = this.getAttribute('data-period');
                        document.getElementById('chartPeriod').textContent = this.textContent;
                        // Ici vous pouvez mettre à jour le graphique avec la nouvelle période
                    });
                });

                // Bouton d'actualisation
                document.getElementById('refresh-dashboard').addEventListener('click', function() {
                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualisation...';
                    btn.disabled = true;

                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            });
        </script>
    @endsection
@elseif(auth()->user()->role == 'medecin')
    @section('styles')
        <style>
            .card-statistic {
                transition: all 0.3s ease;
                border-radius: 12px;
                overflow: hidden;
            }
            .card-statistic:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
            }
            .card-statistic-sm {
                border-radius: 8px;
            }
            .hover-lift:hover {
                transform: translateY(-2px);
            }
            .hover-scale:hover {
                transform: scale(1.02);
            }
            .alert-gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                color: white;
            }
            .avatar {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 50px;
                height: 50px;
            }
            .page-title {
                font-size: 1.8rem;
                font-weight: 700;
                color: #2c3e50;
            }
            .quick-action-card {
                transition: all 0.3s ease;
                border: 1px solid #e9ecef;
            }
            .quick-action-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                border-color: #4670ff;
            }
            .table th {
                font-weight: 600;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #6c757d;
            }
        </style>
    @endsection

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Graphique des consultations
                const ctx = document.getElementById('consultations-chart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($consultationStats['months'] ?? []),
                            datasets: [{
                                label: 'Consultations',
                                data: @json($consultationStats['counts'] ?? []),
                                backgroundColor: 'rgba(70, 128, 255, 0.1)',
                                borderColor: '#4680ff',
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointBackgroundColor: '#4680ff',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: 'rgba(0,0,0,0.8)',
                                    titleFont: { size: 14 },
                                    bodyFont: { size: 13 },
                                    padding: 12
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: { color: 'rgba(0,0,0,0.05)' },
                                    ticks: { font: { size: 11 } }
                                },
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                }
                            }
                        }
                    });
                }

                // Bouton d'actualisation
                document.getElementById('refresh-dashboard').addEventListener('click', function() {
                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualisation...';
                    btn.disabled = true;

                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
            });
        </script>
    @endsection
@endif
