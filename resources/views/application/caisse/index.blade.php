@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold mb-0">Supervision des Caisses</h2>
        </div>

        <div class="block block-rounded">
            <div class="block-header block-header-default">
                <h3 class="block-title">Toutes les Sessions de Caisse</h3>
            </div>
            <div class="block-content block-content-full">
                <table class="table table-bordered table-striped table-vcenter" id="sessions-table">
                    <thead>
                        <tr>
                            <th>Responsable</th>
                            <th>Ouverture</th>
                            <th>Fonds Initial</th>
                            <th>Solde Théorique</th>
                            <th>Solde Réel</th>
                            <th>Écart</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s->user->name }}</td>
                            <td>{{ $s->opened_at->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format($s->solde_initial, 0, ',', ' ') }} F</td>
                            <td>{{ number_format($s->solde_theorique, 0, ',', ' ') }} F</td>
                            <td>{{ $s->solde_reel ? number_format($s->solde_reel, 0, ',', ' ') . ' F' : '-' }}</td>
                            <td>
                                @if($s->statut == 'fermee')
                                    @if($s->ecart == 0)
                                        <span class="text-success fw-bold">0 F</span>
                                    @elseif($s->ecart > 0)
                                        <span class="text-success fw-bold">+{{ number_format($s->ecart, 0, ',', ' ') }} F</span>
                                    @else
                                        <span class="text-danger fw-bold">{{ number_format($s->ecart, 0, ',', ' ') }} F</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($s->statut == 'ouverte')
                                    <span class="badge bg-success">Ouverte</span>
                                @else
                                    <span class="badge bg-secondary">Fermée</span>
                                @endif
                            </td>
                            <td>
                                <a href="javascript:void(0)" class="btn-sm" title="Voir détails">
                                    <i class="fa fa-eye text-primary"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#sessions-table').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
            },
            "order": [[1, "desc"]]
        });
    });
</script>
@endsection
