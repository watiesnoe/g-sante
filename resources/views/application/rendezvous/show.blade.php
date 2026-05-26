@extends('layouts.app')

@section('title_page', 'Détails du Rendez-vous')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-calendar-check me-2"></i> Rendez-vous #{{ $rendezvous->id }}</h5>
                <div>
                    @if(Auth::user()->can('rendezvous.edit'))
                        <a href="{{ route('rendezvous.edit', $rendezvous->uuid) }}" class="btn btn-light btn-sm">
                            <i class="fa fa-edit"></i> Modifier
                        </a>
                    @endif
                    <a href="{{ route('rendezvous.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <!-- Informations du Patient -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="fa fa-user me-2"></i> Informations du Patient</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 40%">Patient :</th>
                                <td>{{ $rendezvous->patient->nom ?? '-' }} {{ $rendezvous->patient->prenom ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>Contact :</th>
                                <td>{{ $rendezvous->patient->telephone ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Détails du Rendez-vous -->
                    <div class="col-md-6 mb-4">
                        <h6 class="fw-bold text-primary mb-3"><i class="fa fa-info-circle me-2"></i> Détails du Rendez-vous</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 40%">Médecin :</th>
                                <td>{{ $rendezvous->medecin->name ?? 'Non assigné' }}</td>
                            </tr>
                            <tr>
                                <th>Date & Heure :</th>
                                <td>{{ \Carbon\Carbon::parse($rendezvous->date_heure)->format('d/m/Y à H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Motif :</th>
                                <td>{{ $rendezvous->motif ?? 'Non précisé' }}</td>
                            </tr>
                            <tr>
                                <th>Statut :</th>
                                <td>
                                    @php
                                        $badges = [
                                            'en_attente' => 'bg-warning',
                                            'realise' => 'bg-success',
                                            'annule' => 'bg-danger'
                                        ];
                                        $badgeClass = $badges[$rendezvous->statut] ?? 'bg-secondary';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $rendezvous->statut)) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($rendezvous->consultation)
                <hr>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold text-success mb-3"><i class="fa fa-stethoscope me-2"></i> Consultation Associée</h6>
                        <p>Ce rendez-vous est lié à la consultation <strong>#{{ $rendezvous->consultation->id }}</strong> du {{ \Carbon\Carbon::parse($rendezvous->consultation->created_at)->format('d/m/Y') }}.</p>
                        @if(Auth::user()->can('consultations.view'))
                            <a href="{{ route('consultations.show', $rendezvous->consultation->uuid) }}" class="btn btn-outline-success btn-sm">
                                <i class="fa fa-eye"></i> Voir la consultation
                            </a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
