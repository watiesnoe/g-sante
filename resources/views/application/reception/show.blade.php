@extends('layouts.app')

@section('titre', 'Détails de la Réception')

@section('content')
    <div class="container mt-4">
        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Réception: {{ $reception->reference_reception }}</h3>
                <div class="block-options">
                    <a href="{{ route('receptions.index') }}" class="btn btn-alt-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
            <div class="block-content">
                <div class="row mb-4">
                    <div class="col-sm-12">
                        <h5 class="mb-1">Informations</h5>
                        <p class="text-muted mb-0">
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($reception->date_reception)->format('d/m/Y') }}<br>
                            <strong>Commande:</strong> 
                            @if($reception->commande)
                                <a href="{{ route('commandes.show', $reception->commande) }}">
                                    {{ $reception->commande->reference ?? 'CMD-'.$reception->commande_id }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                            <br>
                            <strong>Fournisseur:</strong> {{ $reception->fournisseur->nom ?? $reception->commande->fournisseur->nom ?? 'N/A' }}<br>
                            <strong>Utilisateur:</strong> {{ $reception->user->name ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcenter">
                        <thead class="table-dark">
                            <tr>
                                <th>Médicament</th>
                                <th>Lot</th>
                                <th>Date de péremption</th>
                                <th>Qte Commandée</th>
                                <th>Qte Reçue</th>
                                <th>Prix Unitaire</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reception->lignes as $ligne)
                            <tr>
                                <td>{{ $ligne->medicament->nom ?? 'N/A' }}</td>
                                <td>{{ $ligne->lot ?? 'N/A' }}</td>
                                <td>{{ $ligne->date_peremption ? \Carbon\Carbon::parse($ligne->date_peremption)->format('d/m/Y') : 'N/A' }}</td>
                                <td>{{ $ligne->quantite_commandee }}</td>
                                <td>
                                    <span class="badge bg-success">{{ $ligne->quantite_recue }}</span>
                                </td>
                                <td>{{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
