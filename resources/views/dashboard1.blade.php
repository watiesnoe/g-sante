@extends('layouts.app')

@section('titre')
    Tableau de bord
@endsection

@section('content')
    <div class="content">
        <!-- En-tête -->
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1">🏥 Tableau de bord médical</h2>
                <div class="text-muted">Vue d'ensemble des activités et ressources</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="text" class="form-control" value="{{ now()->format('d/m/Y') }}" readonly>
                </div>
                <button class="btn btn-primary"><i class="fa fa-sync-alt me-2"></i>Rafraîchir</button>
            </div>
        </div>

        @php
            // Valeurs par défaut si non fournies par le contrôleur
            $stats = $stats ?? [
                'patients' => 1245,
                'consultations_today' => 18,
                'pending_payments' => 7,
                'low_stock' => 5,
                'revenue_today' => 285000,
            ];
            $consultationsJour = $consultationsJour ?? [12, 18, 14, 22, 17, 19, 25]; // sur 7 jours
            $labelsJour = $labelsJour ?? ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
            $revenueParService = $revenueParService ?? [
                ['service' => 'Consultations', 'montant' => 150000],
                ['service' => 'Examens', 'montant' => 90000],
                ['service' => 'Injections', 'montant' => 30000],
                ['service' => 'Certificats', 'montant' => 15000],
            ];
            $consultationsParStatut = $consultationsParStatut ?? [
                'Programmée' => 10,
                'En cours' => 5,
                'Terminée' => 20,
                'Annulée' => 2,
            ];
            $consultationsAuj = $consultationsAuj ?? [
                ['heure' => '08:30', 'patient' => 'Diop Awa', 'medecin' => 'Dr. Traoré', 'statut' => 'Programmée'],
                ['heure' => '09:00', 'patient' => 'Keita Moussa', 'medecin' => 'Dr. Diallo', 'statut' => 'En cours'],
                ['heure' => '10:15', 'patient' => 'Sow Mariam', 'medecin' => 'Dr. Koné', 'statut' => 'Terminée'],
                ['heure' => '11:00', 'patient' => 'Ba Amadou', 'medecin' => 'Dr. Traoré', 'statut' => 'Programmée'],
            ];
            $stockFaible = $stockFaible ?? [
                ['nom' => 'Paracétamol 500mg', 'qte' => 12, 'seuil' => 20],
                ['nom' => 'Amoxicilline 1g', 'qte' => 8, 'seuil' => 15],
                ['nom' => 'Gants Medium', 'qte' => 30, 'seuil' => 50],
            ];
            function statutBadge($s){
                return match($s){
                    'Programmée' => 'badge bg-warning',
                    'En cours' => 'badge bg-info',
                    'Terminée' => 'badge bg-success',
                    default => 'badge bg-secondary'
                };
            }
        @endphp

            <!-- Stats -->
        <div class="row g-3">
            <div class="col-6 col-xxl-3">
                <div class="block block-rounded h-100">
                    <div class="block-content d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm text-muted">Patients</div>
                            <div class="fs-2 fw-bold">{{ number_format($stats['patients'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="item item-rounded bg-body-extra text-primary"><i class="fa fa-users"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xxl-3">
                <div class="block block-rounded h-100">
                    <div class="block-content d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm text-muted">Consultations aujourd'hui</div>
                            <div class="fs-2 fw-bold">{{ $stats['consultations_today'] }}</div>
                        </div>
                        <div class="item item-rounded bg-body-extra text-info"><i class="fa fa-stethoscope"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xxl-3">
                <div class="block block-rounded h-100">
                    <div class="block-content d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm text-muted">Paiements en attente</div>
                            <div class="fs-2 fw-bold">{{ $stats['pending_payments'] }}</div>
                        </div>
                        <div class="item item-rounded bg-body-extra text-warning"><i class="fa fa-receipt"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xxl-3">
                <div class="block block-rounded h-100">
                    <div class="block-content d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fs-sm text-muted">Articles en stock faible</div>
                            <div class="fs-2 fw-bold">{{ $stats['low_stock'] }}</div>
                        </div>
                        <div class="item item-rounded bg-body-extra text-danger"><i class="fa fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques & Actions rapides -->
        <div class="row g-3 mt-1">
            <div class="col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-chart-line me-2"></i>Consultations sur 7 jours</h3>
                    </div>
                    <div class="block-content p-3">
                        <canvas id="chartConsultations"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-bolt me-2"></i>Actions rapides</h3>
                    </div>
                    <div class="block-content d-grid gap-2">
                        <button class="btn btn-primary w-100"><i class="fa fa-user-plus me-2"></i>Nouveau patient</button>
                        <button class="btn btn-info w-100"><i class="fa fa-file-medical me-2"></i>Nouvelle consultation</button>
                        <button class="btn btn-success w-100"><i class="fa fa-prescription-bottle-alt me-2"></i>Créer une ordonnance</button>
                        <button class="btn btn-warning w-100"><i class="fa fa-cash-register me-2"></i>Enregistrer un paiement</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deuxième rang de widgets -->
        <div class="row g-3 mt-1">
            <div class="col-xl-6">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-chart-bar me-2"></i>Revenus par service (aujourd'hui)</h3>
                    </div>
                    <div class="block-content p-3">
                        <canvas id="chartRevenus"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-chart-pie me-2"></i>Statut des consultations</h3>
                    </div>
                    <div class="block-content p-3">
                        <canvas id="chartStatuts"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listes: Consultations du jour & Stock faible -->
        <div class="row g-3 mt-1">
            <div class="col-xl-7">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-notes-medical me-2"></i>Consultations du jour</h3>
                        <div class="block-options">
                            <button type="button" class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-download me-1"></i> Exporter
                            </button>
                        </div>
                    </div>
                    <div class="block-content p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-vcenter mb-0">
                                <thead>
                                <tr>
                                    <th class="fw-semibold">Heure</th>
                                    <th class="fw-semibold">Patient</th>
                                    <th class="fw-semibold">Médecin</th>
                                    <th class="fw-semibold text-center">Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($consultationsAuj as $c)
                                    <tr>
                                        <td>{{ $c['heure'] }}</td>
                                        <td>{{ $c['patient'] }}</td>
                                        <td>{{ $c['medecin'] }}</td>
                                        <td class="text-center"><span class="{{ statutBadge($c['statut']) }}">{{ $c['statut'] }}</span></td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-alt-primary"><i class="fa fa-eye"></i></button>
                                                <button class="btn btn-sm btn-alt-success"><i class="fa fa-check"></i></button>
                                                <button class="btn btn-sm btn-alt-danger"><i class="fa fa-times"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-boxes me-2"></i>Articles en stock faible</h3>
                    </div>
                    <div class="block-content p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-vcenter mb-0">
                                <thead>
                                <tr>
                                    <th class="fw-semibold">Article</th>
                                    <th class="fw-semibold text-center">Quantité</th>
                                    <th class="fw-semibold text-center">Seuil</th>
                                    <th class="text-end">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($stockFaible as $s)
                                    <tr class="{{ $s['qte'] < $s['seuil'] ? 'table-warning' : '' }}">
                                        <td>{{ $s['nom'] }}</td>
                                        <td class="text-center">{{ $s['qte'] }}</td>
                                        <td class="text-center">{{ $s['seuil'] }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-alt-warning"><i class="fa fa-shopping-cart"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="block-content bg-body-light d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">Total du jour</div>
                        <div class="fs-5 fw-bold">{{ number_format($stats['revenue_today'], 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title"><i class="fa fa-bell me-2"></i>Alertes & Notifications</h3>
                    </div>
                    <div class="block-content">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="alert alert-warning d-flex align-items-center mb-0" role="alert">
                                    <i class="fa fa-exclamation-triangle me-2"></i>
                                    <div>Le stock d'<strong>Amoxicilline 1g</strong> est inférieur au seuil.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <div>3 consultations programmées commencent dans 15 minutes.</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                                    <i class="fa fa-check-circle me-2"></i>
                                    <div>Les paiements de la matinée ont été enregistrés.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Chart.js depuis CDN (compatible Dashmix) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Line: Consultations 7 jours
            const ctx1 = document.getElementById('chartConsultations');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: @json($labelsJour),
                        datasets: [{
                            label: 'Consultations',
                            data: @json($consultationsJour),
                            fill: false,
                            tension: 0.35
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: true } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            // Bar: Revenus par service
            const ctx2 = document.getElementById('chartRevenus');
            if (ctx2) {
                const labels = @json(collect($revenueParService)->pluck('service'));
                const data = @json(collect($revenueParService)->pluck('montant'));
                new Chart(ctx2, {
                    type: 'bar',
                    data: { labels, datasets: [{ label: 'FCFA', data }] },
                    options: { responsive: true, scales: { y: { beginAtZero: true } } }
                });
            }

            // Doughnut: Statuts consultations
            const ctx3 = document.getElementById('chartStatuts');
            if (ctx3) {
                const labels = @json(array_keys($consultationsParStatut));
                const data = @json(array_values($consultationsParStatut));
                new Chart(ctx3, {
                    type: 'doughnut',
                    data: { labels, datasets: [{ data }] },
                    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
                });
            }
        });
    </script>
@endpush
