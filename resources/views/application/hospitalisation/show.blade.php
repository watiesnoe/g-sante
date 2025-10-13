@extends('layouts.app')

@section('title_page', 'Facture Hospitalisation')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5>Facture Hospitalisation #{{ $hospitalisation->id }}</h5>
                <a href="{{ route('hospitalisations.pdf', $hospitalisation->id) }}" class="btn btn-light btn-sm">
                    <i class="fa fa-file-pdf"></i> Télécharger PDF
                </a>
            </div>

            <div class="card-body">
                <h6 class="fw-bold mb-3">Informations Patient</h6>
                <p>
                    <strong>Nom :</strong> {{ $hospitalisation->consultation->patient->nom ?? '-' }}<br>
                    <strong>Service :</strong> {{ $hospitalisation->service->nom ?? '-' }}<br>
                    <strong>Salle :</strong> {{ $hospitalisation->salle->nom ?? '-' }}<br>
                    <strong>Lit :</strong> {{ $hospitalisation->lit->numero ?? '-' }}<br>
                    <strong>Date entrée :</strong> {{ $hospitalisation->date_entree }}<br>
                    <strong>Date sortie :</strong> {{ $hospitalisation->date_sortie ?? 'En cours' }}
                </p>

                <hr>

                <h6 class="fw-bold mb-3">Détails des Paiements</h6>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Mode</th>
                        <th>Total</th>
                        <th>Reçu</th>
                        <th>Restant</th>
                        <th>Statut</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($hospitalisation->paiements as $p)
                        <tr>
                            <td>{{ $p->date_paiement }}</td>
                            <td>{{ ucfirst($p->mode_paiement) }}</td>
                            <td>{{ number_format($p->montant_total, 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($p->montant_recu, 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($p->montant_restant, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge
                                    @if($p->statut == 'payé') bg-success
                                    @elseif($p->statut == 'partiel') bg-warning
                                    @else bg-secondary @endif">
                                    {{ strtoupper($p->statut) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Aucun paiement effectué</td></tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-4 text-end">
                    <h5><strong>Total : </strong>{{ number_format($montant_total, 0, ',', ' ') }} FCFA</h5>
                    <h6><strong>Montant Reçu : </strong>{{ number_format($montant_recu, 0, ',', ' ') }} FCFA</h6>
                    <h6><strong>Montant Restant : </strong>{{ number_format($montant_restant, 0, ',', ' ') }} FCFA</h6>
                </div>
            </div>
        </div>
    </div>
@endsection
