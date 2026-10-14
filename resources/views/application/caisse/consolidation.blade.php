@extends('layouts.app')

@section('content')
<main id="main-container">
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0">Consolidation des caisses du {{ $jour->format('d/m/Y') }}</h2>
            <form method="GET"><input type="date" name="date" class="form-control" value="{{ $jour->toDateString() }}" onchange="this.form.submit()"></form>
        </div>
        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <table class="table table-bordered table-sm table-vcenter">
                    <thead><tr><th>Guichet</th><th>Caissier</th><th>Ouverture</th><th class="text-end">Fonds</th><th class="text-end">Théorique</th><th class="text-end">Compté</th><th class="text-end">Écart</th><th>Statut</th></tr></thead>
                    <tbody>
                        @forelse($sessions as $l)
                            @php $s = $l['session']; @endphp
                            <tr>
                                <td>{{ $s->guichet->nom ?? '—' }}</td>
                                <td>{{ $s->user->name ?? '-' }}</td>
                                <td>{{ $s->opened_at->format('H:i') }}{{ $s->closed_at ? ' → ' . $s->closed_at->format('H:i') : '' }}</td>
                                <td class="text-end">{{ number_format($s->solde_initial, 0, ',', ' ') }}</td>
                                <td class="text-end">{{ number_format($l['theorique'], 0, ',', ' ') }}</td>
                                <td class="text-end">{{ $s->solde_reel !== null ? number_format($s->solde_reel, 0, ',', ' ') : '—' }}</td>
                                <td class="text-end {{ ($s->ecart ?? 0) != 0 ? 'text-danger fw-bold' : '' }}">{{ $s->ecart !== null ? number_format($s->ecart, 0, ',', ' ') : '—' }}</td>
                                <td>{{ \App\Models\CaisseSession::STATUTS[$s->statut] ?? $s->statut }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted">Aucune caisse ouverte ce jour.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="fw-bold">
                        <tr>
                            <td colspan="3">Total</td>
                            <td class="text-end">{{ number_format($sessions->sum(fn($l) => $l['session']->solde_initial), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($sessions->sum('theorique'), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($sessions->sum(fn($l) => $l['session']->solde_reel ?? 0), 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($sessions->sum(fn($l) => $l['session']->ecart ?? 0), 0, ',', ' ') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <h5 class="mt-3">Par mode de paiement</h5>
                <table class="table table-sm table-bordered w-auto">
                    <thead><tr><th>Mode</th><th class="text-end">Entrées</th><th class="text-end">Sorties</th><th class="text-end">Net</th></tr></thead>
                    <tbody>
                        @foreach($totauxModes as $mode => $t)
                            <tr><td>{{ \App\Models\CaisseMouvement::MODES[$mode] ?? $mode }}</td><td class="text-end">{{ number_format($t['entree'] ?? 0, 0, ',', ' ') }}</td><td class="text-end">{{ number_format($t['sortie'] ?? 0, 0, ',', ' ') }}</td><td class="text-end">{{ number_format(($t['entree'] ?? 0) - ($t['sortie'] ?? 0), 0, ',', ' ') }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
