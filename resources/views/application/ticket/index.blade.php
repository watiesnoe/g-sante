@extends('layouts.app')

@section('titre', 'Liste des Tickets Prestation')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 Tickets Prestation</h5>
                <a href="{{ route('tickets.create') }}" class="btn btn-light btn-sm">➕ Nouveau Ticket</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Nombre de prestations</th>
                            <th>Total (XOF)</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td>{{ $ticket->patient->nom ?? '-' }} {{ $ticket->patient->prenom ?? '' }}</td>
                                <td>{{ $ticket->items->count() }}</td>
                                <td>{{ number_format($ticket->items->sum('sous_total'), 0, ',', ' ') }}</td>
                                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn btn-info btn-sm">👁️ Voir</a>
                                    <a href="{{ route('tickets.edit', $ticket->id) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
                                    <form action="{{ route('tickets.destroy', $ticket->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Supprimer ce ticket ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm">🗑️ Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucun ticket enregistré</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
