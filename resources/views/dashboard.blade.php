@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4">Tableau de bord</h2>

        {{-- Statistiques rapides --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5>Patients</h5>
                        <h3>{{ $patientsCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5>Consultations aujourd'hui</h5>
                        <h3>{{ $consultationsToday }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5>Hospitalisations actives</h5>
                        <h3>{{ $hospitalisationsActives }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5>Total Paiements</h5>
                        <h3>{{ number_format($totalPaiements, 0, ',', ' ') }} FCFA</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <canvas id="consultationsChart"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="paiementsChart"></canvas>
            </div>
        </div>

        {{-- Derniers enregistrements --}}
        <div class="row mt-4">
            <div class="col-md-6">
                <h5>Derniers patients</h5>
                <ul class="list-group">
                    @foreach($derniersPatients as $patient)
                        <li class="list-group-item">{{ $patient->nom }} {{ $patient->prenom }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="col-md-6">
                <h5>Tickets en attente</h5>
                <ul class="list-group">
                    @foreach($ticketsEnAttente as $ticket)
                        <li class="list-group-item">#{{ $ticket->id }} - {{ $ticket->type }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Graphique consultations par mois
        new Chart(document.getElementById('consultationsChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode($consultationsParMois->keys()) !!},
                datasets: [{
                    label: 'Consultations',
                    data: {!! json_encode($consultationsParMois->values()) !!},
                    borderColor: 'blue',
                    fill: false
                }]
            }
        });

        // Graphique paiements par service
        new Chart(document.getElementById('paiementsChart'), {
            type: 'pie',
            data: {
                labels: {!! json_encode($paiementsParService->keys()) !!},
                datasets: [{
                    data: {!! json_encode($paiementsParService->values()) !!},
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
                }]
            }
        });
    </script>
@endsection
