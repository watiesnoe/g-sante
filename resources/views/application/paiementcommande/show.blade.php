@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Détails du Paiement : {{ $paiement->reference }}</h3>

        <ul class="list-group">
            <li class="list-group-item"><strong>Commande :</strong> {{ $paiement->commande->reference ?? '-' }}</li>
            <li class="list-group-item"><strong>Fournisseur :</strong> {{ $paiement->commande->fournisseur->nom ?? '-' }}</li>
            <li class="list-group-item"><strong>Montant :</strong> {{ number_format($paiement->montant, 2, ',', ' ') }} F</li>
            <li class="list-group-item"><strong>Mode :</strong> {{ ucfirst($paiement->mode) }}</li>
            <li class="list-group-item"><strong>Date :</strong> {{ $paiement->date_paiement }}</li>
            <li class="list-group-item"><strong>Observations :</strong> {{ $paiement->observations ?? 'Aucune' }}</li>
        </ul>

        <a href="{{ route('paiements.index') }}" class="btn btn-secondary mt-3">⬅ Retour</a>
    </div>
@endsection
