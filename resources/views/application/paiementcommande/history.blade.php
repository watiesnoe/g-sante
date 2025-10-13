@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card card-payment mb-4">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Historique des paiements</h5>
                    <small class="text-muted">Commande : <strong>{{ $commande->reference }}</strong></small>
                </div>
                <a href="{{ route('paiementscommande.create', $commande->uuid) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus me-1"></i> Nouveau Paiement
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle w-100" id="paymentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Commande</th>
                                <th>Fournisseur</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Date Paiement</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paiements as $p)
                                <tr>
                                    <td>{{ $p->reference }}</td>
                                    <td>{{ $p->commande->reference ?? '-' }}</td>
                                    <td>{{ $p->commande->fournisseur->nom ?? '-' }}</td>
                                    <td>
                                        {{ number_format($p->montant, 2, ',', ' ') }} F
                                        @if($p->statut === 'annule')
                                            <span class="badge bg-danger" title="{{ $p->motif_annulation }}">Annulé</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($p->mode) }}</td>
                                    <td>{{ $p->date_paiement }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <a href="{{ route('paiementscommande.show', $p) }}" class="btn btn-sm btn-info" title="Voir">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            @if($p->statut !== 'annule')
                                            <form action="{{ route('paiementscommande.destroy', $p) }}" method="POST" class="d-inline form-annuler-paiement">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="motif">
                                                <button class="btn btn-sm btn-danger" title="Annuler le paiement">
                                                    <i class="fa fa-ban"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Annulation d'un paiement : motif obligatoire (RG-01)
            $(document).on('submit', '.form-annuler-paiement', function (e) {
                const form = this;
                if (form.motif.value) return true;
                e.preventDefault();
                const motif = prompt('Motif de l\'annulation du paiement (obligatoire) :');
                if (motif && motif.trim()) {
                    form.motif.value = motif.trim();
                    form.submit();
                }
            });

            $('#paymentsTable').DataTable({
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
                },
                "order": [[5, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ]
            });
        });
    </script>
@endsection
