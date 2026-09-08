@extends('layouts.app')

@section('titre', 'Détails de la Réception')

@section('content')
    <div class="container mt-4">
        <div class="block block-rounded shadow-sm">
            <div class="block-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="block-title mb-0 text-white">
                    <i class="fa fa-truck-loading me-2"></i> Réception: {{ $reception->reference_reception }}
                </h3>
                <div class="d-flex gap-2">
                    <a href="{{ route('receptions.pdf', $reception) }}" target="_blank" class="btn btn-danger btn-sm">
                        <i class="fa fa-file-pdf me-1"></i> Imprimer PDF
                    </a>
                    <a href="{{ route('receptions.index') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
            </div>
            <div class="block-content p-4">
                {{-- Résumé des Informations --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block text-uppercase fw-bold">Référence Réception</small>
                            <span class="fs-6 fw-bold text-dark">{{ $reception->reference_reception }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block text-uppercase fw-bold">Date de Réception</small>
                            <span class="fs-6 fw-bold text-dark">{{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block text-uppercase fw-bold">Commande Liée</small>
                            @if($reception->commande)
                                <a href="{{ route('commandes.show', $reception->commande) }}" class="fw-bold text-primary">
                                    {{ $reception->commande->reference ?? 'CMD-'.$reception->commande_id }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block text-uppercase fw-bold">Fournisseur</small>
                            <span class="fs-6 fw-bold text-dark">{{ $reception->fournisseur->nom ?? $reception->commande->fournisseur->nom ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mb-3">
                    <i class="fa fa-boxes text-primary me-2"></i> Produits Réceptionnés
                </h5>

                @php
                    $lignesRecues = $reception->lignes->filter(function($l) {
                        return $l->quantite_recue > 0;
                    });
                    $totalRecu = 0;
                    $totalMontant = 0;
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Médicament</th>
                                <th>Unité</th>
                                <th>N° Lot</th>
                                <th>Date Péremption</th>
                                <th class="text-center">Qte Commandée</th>
                                <th class="text-center">Qte Reçue</th>
                                <th class="text-end">Prix Unitaire</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lignesRecues as $index => $ligne)
                            @php
                                $totalLigne = $ligne->quantite_recue * $ligne->prix_unitaire;
                                $totalRecu += $ligne->quantite_recue;
                                $totalMontant += $totalLigne;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-bold">{{ $ligne->medicament->nom ?? 'N/A' }}</td>
                                <td>
                                    @if($ligne->unite)
                                        <span class="badge bg-secondary">{{ $ligne->unite->nom }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $ligne->lot ?? '-' }}</td>
                                <td>{{ $ligne->date_peremption ? \Carbon\Carbon::parse($ligne->date_peremption)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">{{ $ligne->quantite_commandee }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6 px-3 py-1">{{ $ligne->quantite_recue }}</span>
                                </td>
                                <td class="text-end">{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                                <td class="text-end fw-bold text-success">{{ number_format($totalLigne, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fa fa-info-circle me-1"></i> Aucun produit reçu enregistré pour cette réception.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($lignesRecues->count() > 0)
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="5" class="text-end">Total Réceptionné :</td>
                                <td class="text-center fs-6 text-primary">{{ number_format($totalRecu, 0, ',', ' ') }}</td>
                                <td class="text-end">Total Montant :</td>
                                <td class="text-end fs-6 text-success">{{ number_format($totalMontant, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                @if($reception->user)
                <div class="mt-3 text-muted small">
                    <i class="fa fa-user me-1"></i> Enregistré par : <strong>{{ $reception->user->name }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
