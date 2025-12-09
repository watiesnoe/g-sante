@extends('layouts.app')
@section('titre','Réceptions')
@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between mb-3">
            <h3>Liste des Réceptions</h3>
            <a href="{{ route('receptions.create') }}" class="btn btn-primary">➕ Nouvelle Réception</a>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Commande</th>
                <th>Médicament</th>
                <th>Quantité Reçue</th>
                <th>Lieu</th>
                <th>Date Réception</th>
            </tr>
            </thead>
            <tbody>
            @foreach($receptions as $r)
                <tr>
                    <td>{{ $r->commande->id }}</td>
                    <td>{{ $r->medicament->nom }}</td>
                    <td>{{ $r->quantite_recue }}</td>
                    <td>{{ $r->lieu_reception }}</td>
                    <td>{{ $r->date_reception }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
