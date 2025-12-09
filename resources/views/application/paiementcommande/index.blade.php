@extends('layouts.app')

@section('title', 'Dashboard Paiements')

@section('content')
    <div class="content">
        <!-- Quick Stats -->
            <!-- Commandes avec état de paiement -->
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">État des Paiements par Commande</h3>
                <div class="block-options">
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-alt-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-filter me-1"></i> Filtrer
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="?statut=">Tous les statuts</a>
                            <a class="dropdown-item" href="?statut=total">Totalement payées</a>
                            <a class="dropdown-item" href="?statut=partielle">Partiellement payées</a>
                            <a class="dropdown-item" href="?statut=en_cours">En cours</a>
                        </div>
                    </div>
                    <a href="{{ route('paiementscommande.create') }}" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus me-1"></i> Nouveau Paiement
                    </a>
                </div>
            </div>
            <div class="block-content">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter js-dataTable-full">
                        <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Fournisseur</th>
                            <th>Total Commande</th>
                            <th>Montant Payé</th>
                            <th>Reste à Payer</th>
                            <th>Progression</th>
                            <th>Statut Paiement</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($commandes as $commande)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $commande->reference }}</div>
                                    <div class="fs-sm text-muted">
                                        {{ \Carbon\Carbon::parse($commande->date_commande)->format('d/m/Y') }}
                                    </div>
                                </td>
                                <td>{{ $commande->fournisseur->nom ?? 'N/A' }}</td>
                                <td class="fw-bold">{{ number_format($commande->total, 2) }} €</td>
                                <td class="text-success">{{ number_format($commande->montantPaye(), 2) }} €</td>
                                <td class="text-warning">{{ number_format($commande->reste_a_payer, 2) }} €</td>
                                <td>
                                    @php
                                        $progress = $commande->total > 0 ? ($commande->montantPaye() / $commande->total) * 100 : 0;
                                        $progressClass = $commande->StatutPaiement == 'total' ? 'bg-success' :
                                                       ($commande->StatutPaiement == 'partielle' ? 'bg-warning' : 'bg-danger');
                                    @endphp
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $progressClass }}"
                                             role="progressbar"
                                             style="width: {{ $progress }}%"
                                             aria-valuenow="{{ $progress }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ number_format($progress, 1) }}%</small>
                                </td>
                                <td>
                                <span class="badge bg-{{ $commande->payment_status_color }}">
                                    <i class="fa fa-{{ $commande->StatutPaiement == 'total' ? 'check-circle' :
                                                     ($commande->StatutPaiement == 'partielle' ? 'clock' : 'exclamation-triangle') }} me-1"></i>
                                    {{ $commande->payment_status_text }}
                                </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('paiementscommande.create', ['commande_id' => $commande->id]) }}"
                                           class="btn btn-sm btn-primary" data-toggle="tooltip" title="Ajouter Paiement">
                                            <i class="fa fa-credit-card"></i>
                                        </a>
                                        <a href="{{ route('commandes.show', $commande->id) }}"
                                           class="btn btn-sm btn-secondary" data-toggle="tooltip" title="Voir Commande">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('paiementscommande.history', $commande->id) }}"
                                           class="btn btn-sm btn-info" data-toggle="tooltip" title="Historique Paiements">
                                            <i class="fa fa-history"></i>
                                        </a>
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart.js Implementation
        const ctx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Paiements (€)',
                    data: @json($chartData['data']),
                    backgroundColor: '#667eea',
                    borderColor: '#5a6fd8',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Montant: ' + context.parsed.y.toFixed(2) + ' €';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' €';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>

    <style>
        .bg-payment {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-payment {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card-payment:hover {
            transform: translateY(-5px);
        }
        .payment-stat {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .bg-paid {
            background: linear-gradient(45deg, #56ab2f, #a8e6cf);
        }
        .bg-pending {
            background: linear-gradient(45deg, #ffd89b, #19547b);
        }
        .bg-overdue {
            background: linear-gradient(45deg, #ff5f6d, #ffc371);
        }
        .progress {
            background-color: #e9ecef;
            border-radius: 4px;
        }
        .progress-bar {
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection
