@extends('layouts.app')

@section('title', 'Dashboard Paiements')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Dashboard Paiements Commandes</h2>
                <p class="text-muted mb-0">Suivi des paiements fournisseurs et des soldes restants.</p>
            </div>
            <a href="{{ route('paiementscommande.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Nouveau paiement
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-2">
                <div class="card card-payment bg-payment text-white h-100">
                    <div class="card-body">
                        <div class="small text-uppercase">Commandes</div>
                        <div class="payment-stat">{{ $stats['total_orders'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card card-payment bg-paid text-white h-100">
                    <div class="card-body">
                        <div class="small text-uppercase">Paiement total</div>
                        <div class="payment-stat">{{ $stats['fully_paid_orders'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card card-payment bg-pending text-white h-100">
                    <div class="card-body">
                        <div class="small text-uppercase">Paiement partiel</div>
                        <div class="payment-stat">{{ $stats['partially_paid_orders'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card card-payment bg-overdue text-white h-100">
                    <div class="card-body">
                        <div class="small text-uppercase">Impayées</div>
                        <div class="payment-stat">{{ $stats['unpaid_orders'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card card-payment h-100">
                    <div class="card-body">
                        <div class="small text-uppercase text-muted">Montant payé</div>
                        <div class="payment-stat text-success">{{ number_format($stats['total_paid'] ?? 0, 0, ',', ' ') }}</div>
                        <div class="text-muted small">MRU</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-2">
                <div class="card card-payment h-100">
                    <div class="card-body">
                        <div class="small text-uppercase text-muted">Reste à payer</div>
                        <div class="payment-stat text-danger">{{ number_format($stats['total_remaining'] ?? 0, 0, ',', ' ') }}</div>
                        <div class="text-muted small">MRU</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card card-payment h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Évolution des paiements</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentChart" height="110"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-payment h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Paiements récents</h5>
                    </div>
                    <div class="card-body">
                        @forelse ($recentPayments as $paiement)
                            <div class="border rounded p-3 mb-3">
                                <div class="fw-bold">{{ $paiement->commande->reference ?? 'N/A' }}</div>
                                <div class="text-muted small">{{ $paiement->commande->fournisseur->nom ?? 'Fournisseur inconnu' }}</div>
                                <div class="mt-2">
                                    <span class="badge bg-success">
                                        {{ number_format($paiement->montant, 0, ',', ' ') }} MRU
                                    </span>
                                </div>
                                <div class="small text-muted mt-2">
                                    {{ optional($paiement->date_paiement)->format('d/m/Y') ?? '-' }} • {{ ucfirst($paiement->mode) }}
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info mb-0">
                                Aucun paiement récent.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-payment">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Commandes avec état de paiement</h5>
                <span class="text-muted small">{{ $commandes->count() }} commande(s)</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Fournisseur</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Montant payé</th>
                                <th>Reste</th>
                                <th>Statut paiement</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commandes as $commande)
                                <tr>
                                    <td class="fw-semibold">{{ $commande->reference }}</td>
                                    <td>{{ $commande->fournisseur->nom ?? 'N/A' }}</td>
                                    <td>{{ optional($commande->date_commande)->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ number_format($commande->total, 0, ',', ' ') }} MRU</td>
                                    <td>{{ number_format($commande->montantPaye(), 0, ',', ' ') }} MRU</td>
                                    <td>{{ number_format($commande->reste_a_payer, 0, ',', ' ') }} MRU</td>
                                    <td>
                                        <span class="badge bg-{{ $commande->payment_status_color }}">
                                            {{ $commande->payment_status_text }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('paiementscommande.create', ['commande_id' => $commande->id]) }}"
                                               class="btn-sm"
                                               title="Payer">
                                                <i class="fa fa-money-bill text-primary"></i>
                                            </a>

                                            <a href="{{ route('paiementscommande.history', $commande->id) }}"
                                               class="btn-sm"
                                               title="Historique paiements">
                                                <i class="fa fa-history text-info"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Aucune commande disponible.
                                    </td>
                                </tr>
                            @endforelse
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
        const chartElement = document.getElementById('paymentChart');

        if (chartElement) {
            const ctx = chartElement.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartData['labels'] ?? []),
                    datasets: [{
                        label: 'Paiements (MRU)',
                        data: @json($chartData['data'] ?? []),
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
                                    return 'Montant: ' + context.parsed.y.toLocaleString('fr-FR') + ' MRU';
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
                                    return value + ' MRU';
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
        }
    </script>

    <style>
        .bg-payment {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-payment {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .card-payment:hover {
            transform: translateY(-3px);
        }

        .payment-stat {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .bg-paid {
            background: linear-gradient(45deg, #56ab2f, #a8e6cf);
        }

        .bg-pending {
            background: linear-gradient(45deg, #f2994a, #f2c94c);
        }

        .bg-overdue {
            background: linear-gradient(45deg, #eb5757, #f2994a);
        }

        .badge {
            font-size: 0.75rem;
            padding: 0.45em 0.7em;
        }
    </style>
@endsection
