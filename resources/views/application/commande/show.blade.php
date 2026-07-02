@extends('layouts.app')

@section('title_page', 'Détails de la Commande')
@section('page_link')
    <a href="{{ route('commandes.index') }}">Commandes</a>
@endsection
@section('page_name', 'Détails de la Commande')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0 mb-4 mt-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-receipt"></i> Commande {{ $commande->reference }}
                </h5>
                <a href="{{ route('commandes.index') }}" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Fournisseur :</strong> {{ $commande->fournisseur->nom ?? 'N/A' }}</p>
                        <p><strong>Date de commande :</strong> {{ \Carbon\Carbon::parse($commande->date_commande)->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Statut :</strong>
                            @php
                                $statutMap = [
                                    'en_cours'   => ['label' => 'En cours',    'class' => 'bg-primary'],
                                    'en_attente' => ['label' => 'En attente',  'class' => 'bg-warning text-dark'],
                                    'valide'     => ['label' => 'Validée',     'class' => 'bg-success'],
                                    'validée'    => ['label' => 'Validée',     'class' => 'bg-success'],
                                    'annuler'    => ['label' => 'Annulée',     'class' => 'bg-danger'],
                                    'annulée'    => ['label' => 'Annulée',     'class' => 'bg-danger'],
                                    'livraison'  => ['label' => 'En livraison','class' => 'bg-info'],
                                ];
                                $si = $statutMap[$commande->statut] ?? [
                                    'label' => ucfirst(str_replace('_', ' ', $commande->statut)),
                                    'class' => 'bg-secondary',
                                ];
                            @endphp
                            <span class="badge {{ $si['class'] }}">{{ $si['label'] }}</span>
                        </p>

                        <p><strong>Montant total :</strong>
                            <span class="text-success fw-bold">{{ number_format($commande->total, 0, ',', ' ') }} F CFA</span>
                        </p>
                    </div>
                </div>

                <hr>

                <h5 class="mt-4 mb-3"><i class="bi bi-capsule"></i> Médicaments commandés</h5>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Nom du Médicament</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire (F CFA)</th>
                            <th>Total (F CFA)</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($commande->lignes as $index => $ligne)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $ligne->medicament->nom ?? 'N/A' }}</td>
                                <td>{{ $ligne->quantite }}</td>
                                <td>{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }}</td>
                                <td>{{ number_format($ligne->total, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aucun médicament dans cette commande.</td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
