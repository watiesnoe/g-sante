@extends('layouts.app')

@section('title_page', 'Rapport mensuel')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0">Rapport d'activité — {{ $rapport->periode_du->translatedFormat('F Y') }} (v{{ $rapport->version }})
                <span class="badge bg-{{ ['brouillon' => 'secondary', 'valide' => 'info', 'transmis' => 'success'][$rapport->statut] }}">{{ \App\Models\RapportPeriodique::STATUTS[$rapport->statut] }}</span></h2>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('rapports-mensuels.export', [$rapport, 'csv']) }}" class="btn btn-sm btn-outline-success"><i class="fa fa-file-csv"></i> CSV</a>
                <a href="{{ route('rapports-mensuels.export', [$rapport, 'dhis2']) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-exchange-alt"></i> Fichier DHIS2</a>
                @if($rapport->statut === 'brouillon')
                    <form method="POST" action="{{ route('rapports-mensuels.generer') }}">@csrf<input type="hidden" name="mois" value="{{ $rapport->periode_du->format('Y-m') }}"><button class="btn btn-sm btn-outline-primary">Recalculer</button></form>
                    @can('rapports.valider')<form method="POST" action="{{ route('rapports-mensuels.etape', $rapport) }}">@csrf<input type="hidden" name="etape" value="valider"><button class="btn btn-sm btn-info">Valider</button></form>@endcan
                @elseif($rapport->statut === 'valide')
                    @can('rapports.valider')<form method="POST" action="{{ route('rapports-mensuels.etape', $rapport) }}">@csrf<input type="hidden" name="etape" value="transmettre"><button class="btn btn-sm btn-success" onclick="return confirm('Après transmission, le rapport est figé. Continuer ?')">Marquer transmis</button></form>@endcan
                @else
                    <form method="POST" action="{{ route('rapports-mensuels.etape', $rapport) }}" onsubmit="this.motif.value = prompt('Motif de la correction :') || ''; return this.motif.value !== '';">@csrf<input type="hidden" name="etape" value="nouvelle_version"><input type="hidden" name="motif"><button class="btn btn-sm btn-warning">Nouvelle version</button></form>
                @endif
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($rapport->motif_nouvelle_version)<div class="alert alert-warning">Version corrigée : {{ $rapport->motif_nouvelle_version }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="block block-rounded"><div class="block-content">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Indicateur</th><th class="text-end">Valeur</th><th>Code DHIS2</th></tr></thead>
                        <tbody>
                            @foreach($libelles as $cle => $libelle)
                                <tr>
                                    <td>{{ $libelle }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($rapport->donnees['indicateurs'][$cle] ?? 0, 0, ',', ' ') }}</td>
                                    <td class="small">
                                        @can('rapports.valider')
                                            <form method="POST" action="{{ route('rapports-mensuels.correspondance') }}" class="d-flex gap-1">@csrf
                                                <input type="hidden" name="indicateur" value="{{ $cle }}">
                                                <input type="text" name="data_element" class="form-control form-control-sm" value="{{ $correspondances[$cle] ?? '' }}" placeholder="à fournir">
                                                <button class="btn btn-sm btn-outline-secondary">OK</button>
                                            </form>
                                        @else
                                            {{ $correspondances[$cle] ?? '—' }}
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="small text-muted">Calculé le {{ $rapport->donnees['calcule_le'] ?? '' }} par {{ $rapport->auteur->name ?? '' }}{{ $rapport->validateur ? ', validé par ' . $rapport->validateur->name : '' }}. Les codes DHIS2 sont à saisir d'après la correspondance officielle du district.</p>
                </div></div>
            </div>
            <div class="col-xl-5">
                <div class="block block-rounded"><div class="block-content">
                    <h5>Diagnostics les plus fréquents</h5>
                    <table class="table table-sm">
                        @forelse($rapport->donnees['diagnostics'] ?? [] as $d)
                            <tr><td>{{ $d['code'] }}</td><td>{{ $d['libelle'] }}</td><td class="text-end">{{ $d['total'] }}</td></tr>
                        @empty
                            <tr><td class="text-muted">Aucun diagnostic codifié sur la période.</td></tr>
                        @endforelse
                    </table>
                </div></div>
            </div>
        </div>
    </div>
@endsection
