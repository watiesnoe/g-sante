@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Liste des paiements</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('paiementscommande.create') }}" class="btn btn-primary mb-3">+ Nouveau Paiement</a>

        <table class="table table-bordered">
            <thead class="table-light">
            <tr>
                <th>Référence</th>
                <th>Commande</th>
                <th>Fournisseur</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Date Paiement</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($paiements as $p)
                <tr>
                    <td>{{ $p->reference }}</td>
                    <td>{{ $p->commande->reference ?? '-' }}</td>
                    <td>{{ $p->commande->fournisseur->nom ?? '-' }}</td>
                    <td>{{ number_format($p->montant, 2, ',', ' ') }} F</td>
                    <td>{{ ucfirst($p->mode) }}</td>
                    <td>{{ $p->date_paiement }}</td>
                    <td>
                        <a href="{{ route('paiementscommande.show', $p->id) }}" class="btn btn-sm btn-info">Voir</a>
                        <a href="{{ route('paiementscommande.edit', $p->id) }}" class="btn btn-sm btn-warning">Modifier</a>
                        <form action="{{ route('paiementscommande.destroy', $p->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce paiement ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
