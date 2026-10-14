@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold mb-0">Supervision des Caisses</h2>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

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
                                @if($s->statut != 'ouverte')
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
                                @elseif($s->statut == 'fermee')
                                    <span class="badge bg-warning text-dark">À valider</span>
                                @else
                                    <span class="badge bg-secondary" title="Validée par {{ $s->valideePar->name ?? '-' }}">Validée</span>
                                @endif
                                @if($s->justification_ecart)
                                    <div class="small text-muted mt-1" title="{{ $s->justification_ecart }}"><i class="fa fa-comment"></i> {{ \Illuminate\Support\Str::limit($s->justification_ecart, 40) }}</div>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('caisse.show', $s->uuid) }}" class="btn btn-sm btn-alt-secondary" title="Voir détails">
                                    <i class="fa fa-eye text-primary"></i>
                                </a>
                                @if($s->statut == 'fermee' && auth()->user()->can('caisse.valider_cloture'))
                                    <form action="{{ route('caisse.valider', $s->uuid) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Valider la clôture" onclick="return confirm('Valider la clôture de la caisse de {{ $s->user->name }} ?')">
                                            <i class="fa fa-check"></i> Valider
                                        </button>
                                    </form>
                                @endif
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
