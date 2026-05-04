@extends('layouts.app')
@section('title', 'Tableau de Bord')

@section('content')
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de Bord</a></li>
                    <li class="breadcrumb-item active">
                        @hasrole('super_admin') Super Admin
                        @elsehasrole('admin') Admin
                        @elsehasrole('secretaire') Secrétaire
                        @elsehasrole('medecin') Médecin
                        @elsehasrole('client') Patient
                        @else Accueil
                        @endhasrole
                    </li>
                </ol>
            </div>
            <div class="page-header-actions">
                <button id="refresh-dashboard" class="btn btn-sm btn-outline-primary rounded-pill">
                    <i class="fas fa-sync-alt me-1"></i> Actualiser
                </button>
            </div>
        </div>

        <!-- Alert Role -->
        <div class="alert alert-primary mb-4 border-0 shadow-sm d-flex align-items-center">
            <div class="avatar-stat bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 10px;">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                Vous êtes connecté en tant que <strong class="text-uppercase">{{ auth()->user()->getRoleNames()->first() ?? 'Utilisateur' }}</strong>.
                Bienvenue dans votre espace sécurisé.
            </div>
        </div>

        @hasrole('super_admin')
            @include('dashbords.superadmin')
        @elsehasrole('admin')
            @include('dashbords.admin')
        @elsehasrole('secretaire')
            @include('dashbords.secretaire')
        @elsehasrole('medecin')
            @include('dashbords.medecin')
        @elsehasrole('client')
            @include('dashbords.client')
        @else
            @include('dashbords.default')
        @endhasrole
    </div>
@endsection

@section('styles')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4670ff 0%, #254bdc 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
            --secondary-gradient: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
        }

        .welcome-banner {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(70, 112, 255, 0.3);
            margin-bottom: 2rem;
        }
        
        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .card-statistic {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 16px;
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06) !important;
        }

        .card-statistic:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04) !important;
        }

        .avatar-stat {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.25rem;
        }

        .hover-bg:hover {
            background-color: #f8fafc;
        }

        .hover-lift {
            transition: transform 0.2s;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh Button
            const refreshBtn = document.getElementById('refresh-dashboard');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>...';
                    location.reload();
                });
            }

            @hasrole('super_admin')
                const activityCtx = document.getElementById('activityChart');
                if (activityCtx) {
                    new Chart(activityCtx, {
                        type: 'line',
                        data: {
                            labels: @json($last7Days->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))),
                            datasets: [
                                {
                                    label: 'Consultations',
                                    data: @json($last7Days->pluck('consultations')),
                                    borderColor: '#4670ff',
                                    backgroundColor: 'rgba(70, 128, 255, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                },
                                {
                                    label: 'Rendez-vous',
                                    data: @json($last7Days->pluck('rendezvous')),
                                    borderColor: '#28a745',
                                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }

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
                            cutout: '70%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            @endhasrole

            @hasrole('medecin')
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
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            @endhasrole
        });
    </script>
@endsection
