@extends('layouts.app')

@section('title', 'Dashboard Paiements')

@section('content')
    <div class="container mt-4">
        <!-- Quick Stats -->
            <!-- Commandes avec état de paiement -->
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('paiementscommande.create') }}" class="btn-sm" title="Détails"><i class="fa fa-eye text-primary"></i></a>
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
