@extends('layouts.app')

@section('title', 'Détails du Ticket')

@section('content')
    <div class="container mt-4">
        <!-- Carte principale englobante -->
        <div class="card border-0 shadow-lg">
            <!-- En-tête de la carte -->
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">
                            <i class="fa fa-receipt text-primary me-2"></i>Détails du Ticket #{{ $ticket->id }}
                        </h5>
                        <p class="text-muted mb-0 small">
                            <i class="fa fa-calendar me-1"></i>Créé le {{ $ticket->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('tickets.print', $ticket->id) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fa fa-print me-1"></i>Imprimer
                        </a>
                        <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit me-1"></i>Modifier
                        </a>
                        <a href="{{ route('tickets.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>

            <!-- Corps de la carte -->
            <div class="card-body p-4">
                <!-- Première ligne : Informations principales -->
                <div class="row mb-4">
                    <!-- Carte Informations Ticket -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card border-start border-primary border-3 h-100">
                            <div class="card-header bg-primary bg-opacity-10 py-2">
                                <h6 class="mb-0">
                                    <i class="fa fa-file-alt text-primary me-2"></i>Informations du Ticket
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Numéro :</span>
                                            <span class="fw-bold">#{{ $ticket->id }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Date :</span>
                                            <span class="fw-bold">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Prestations :</span>
                                            <span class="fw-bold text-primary">
                                            <i class="fa fa-list-alt me-1"></i>{{ $ticket->nombre_prestations }}
                                        </span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Utilisateur :</span>
                                            <span class="fw-bold">
                                            <i class="fa fa-user-circle me-1"></i>{{ $ticket->user->name ?? 'N/A' }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Informations Patient -->
                    <div class="col-lg-4 col-md-6 mb-3">
                        <div class="card border-start border-info border-3 h-100">
                            <div class="card-header bg-info bg-opacity-10 py-2">
                                <h6 class="mb-0">
                                    <i class="fa fa-user text-info me-2"></i>Informations du Patient
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Nom & Prénom :</span>
                                            <span class="fw-bold">{{ $ticket->patient->nom ?? '' }} {{ $ticket->patient->prenom ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Téléphone :</span>
                                            <span class="fw-bold">{{ $ticket->patient->telephone ?? 'Non renseigné' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Âge :</span>
                                            <span class="fw-bold">{{ $ticket->patient->age ?? 'N/A' }} ans</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carte Consultation -->
                    <div class="col-lg-4 col-md-12 mb-3">
                        <div class="card border-start border-success border-3 h-100">
                            <div class="card-header bg-success bg-opacity-10 py-2">
                                <h6 class="mb-0">
                                    <i class="fa fa-stethoscope text-success me-2"></i>Consultation
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($ticket->consultation)
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <p class="mb-1"><strong>Motif :</strong></p>
                                            <p class="text-muted mb-2">{{ $ticket->consultation->motif }}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="mb-1"><strong>Diagnostic :</strong></p>
                                            <p class="text-muted mb-0">{{ $ticket->consultation->diagnostic }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0"><em>Aucune consultation liée.</em></p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carte des Prestations -->
                <div class="card mb-4 border shadow-sm">
                    <div class="card-header bg-warning bg-opacity-10 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="fa fa-list text-warning me-2"></i>Prestations ({{ $ticket->nombre_prestations }} prestation(s))
                            </h6>
                            <span class="badge bg-primary fs-6">
                            Total: {{ number_format($ticket->total, 0, ',', ' ') }} FCFA
                        </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                <tr class="text-center">
                                    <th class="py-2">#</th>
                                    <th class="py-2">Prestation</th>
                                    <th class="py-2">Service Médical</th>
                                    <th class="py-2">Prix Unitaire</th>
                                    <th class="py-2">Quantité</th>
                                    <th class="py-2">Remise</th>
                                    <th class="py-2">Sous-Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $totalGeneral = 0; @endphp
                                @foreach($ticket->items as $index => $item)
                                    @php $totalGeneral += $item->sous_total; @endphp
                                    <tr>
                                        <td class="text-center align-middle">{{ $index + 1 }}</td>
                                        <td class="align-middle">{{ $item->prestation->nom ?? 'N/A' }}</td>
                                        <td class="align-middle">{{ $item->prestation->serviceMedical->nom ?? 'N/A' }}</td>
                                        <td class="text-end align-middle">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-center align-middle">{{ $item->quantite }}</td>
                                        <td class="text-end align-middle">{{ number_format($item->remise, 0, ',', ' ') }} FCFA</td>
                                        <td class="text-end align-middle fw-bold text-primary">
                                            {{ number_format($item->sous_total, 0, ',', ' ') }} FCFA
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end fw-bold py-3">Total Général :</td>
                                    <td class="text-end fw-bold text-primary fs-5 py-3">
                                        {{ number_format($totalGeneral, 0, ',', ' ') }} FCFA
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Carte Récapitulatif Financier -->
                <div class="card mb-4 border shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fa fa-calculator me-2"></i>Récapitulatif Financier
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Nombre Prestations -->
                            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                <div class="card border-0 bg-primary bg-opacity-10 h-100">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fa fa-list-alt fa-2x text-primary"></i>
                                        </div>
                                        <h6 class="text-muted mb-2">Nombre Prestations</h6>
                                        <h2 class="fw-bold text-primary">{{ $ticket->nombre_prestations }}</h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Ticket -->
                            <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                <div class="card border-0 bg-success bg-opacity-10 h-100">
                                    <div class="card-body text-center py-4">
                                        <div class="mb-3">
                                            <i class="fa fa-file-invoice-dollar fa-2x text-success"></i>
                                        </div>
                                        <h6 class="text-muted mb-2">Total Ticket</h6>
                                        <h2 class="fw-bold text-success">
                                            {{ number_format($ticket->total, 0, ',', ' ') }} FCFA
                                        </h2>
                                    </div>
                                </div>
                            </div>

                            <!-- Montant Payé -->
                            @if(isset($ticket->montant_paye))
                                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                    <div class="card border-0 bg-info bg-opacity-10 h-100">
                                        <div class="card-body text-center py-4">
                                            <div class="mb-3">
                                                <i class="fa fa-money-check-alt fa-2x text-info"></i>
                                            </div>
                                            <h6 class="text-muted mb-2">Montant Payé</h6>
                                            <h2 class="fw-bold text-info">
                                                {{ number_format($ticket->montant_paye, 0, ',', ' ') }} FCFA
                                            </h2>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reste à Payer -->
                                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                                    <div class="card border-0 bg-danger bg-opacity-10 h-100">
                                        <div class="card-body text-center py-4">
                                            <div class="mb-3">
                                                <i class="fa fa-hand-holding-usd fa-2x text-danger"></i>
                                            </div>
                                            <h6 class="text-muted mb-2">Reste à Payer</h6>
                                            <h2 class="fw-bold text-danger">
                                                {{ number_format($ticket->reste, 0, ',', ' ') }} FCFA
                                            </h2>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Si pas de paiement, étendre sur 2 colonnes -->
                                <div class="col-xl-6 col-lg-12 mb-3">
                                    <div class="card border-0 bg-warning bg-opacity-10 h-100">
                                        <div class="card-body text-center py-4">
                                            <div class="mb-3">
                                                <i class="fa fa-exclamation-circle fa-2x text-warning"></i>
                                            </div>
                                            <h6 class="text-muted mb-2">Statut Paiement</h6>
                                            <h2 class="fw-bold text-warning">À PAYER</h2>
                                            <p class="text-muted mb-0">Total: {{ number_format($ticket->total, 0, ',', ' ') }} FCFA</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions en bas -->
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="{{ route('tickets.print', $ticket->id) }}" class="btn btn-primary px-4" target="_blank">
                                <i class="fa fa-print me-2"></i>Imprimer le Ticket
                            </a>
                            <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-warning px-4">
                                <i class="fa fa-edit me-2"></i>Modifier
                            </a>
                            <a href="{{ route('tickets.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fa fa-list me-2"></i>Voir tous les Tickets
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Style CSS additionnel pour améliorer l'apparence -->
    <style>
        .card {
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .border-start {
            border-left-width: 4px !important;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .table td {
            vertical-align: middle;
        }
        .bg-opacity-10 {
            background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
        }
        .card-header h6 {
            font-size: 1rem;
        }
        .badge {
            font-size: 0.9em;
            padding: 0.4em 0.8em;
        }
    </style>
@endsection
