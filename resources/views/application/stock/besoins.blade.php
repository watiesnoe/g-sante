@extends('layouts.app')

@section('title_page', 'État des besoins')

@section('content')
    @php
        $statuts = ['rupture' => ['Rupture', 'danger'], 'a_commander' => ['À commander', 'warning'], 'surstock' => ['Surstock', 'info'], 'ok' => ['Suffisant', 'success']];
    @endphp
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-calculator me-2"></i>État des besoins</h2>
            <form method="GET" class="d-flex gap-2">
                @foreach((array) $ids as $id)<input type="hidden" name="ids[]" value="{{ $id }}">@endforeach
                <select name="filtre" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                    <option value="a_commander" @selected($filtre === 'a_commander')>À commander ({{ ($compteurs['rupture'] ?? 0) + ($compteurs['a_commander'] ?? 0) }})</option>
                    <option value="surstock" @selected($filtre === 'surstock')>Surstock ({{ $compteurs['surstock'] ?? 0 }})</option>
                    <option value="tous" @selected($filtre === 'tous')>Tous les produits suivis</option>
                </select>
            </form>
        </div>
        <div class="alert alert-light small">
            <strong>Calcul :</strong> CMM = consommation des {{ $parametres['periode'] }} derniers mois (dispensations et dotations aux services), hors jours de rupture.
            Point de commande = CMM × {{ $parametres['delai'] + $parametres['securite'] }} mois (délai {{ $parametres['delai'] }} + sécurité {{ $parametres['securite'] }}).
            Stock maximum = CMM × {{ $parametres['delai'] + $parametres['securite'] + $parametres['couverture'] }} mois (+ {{ $parametres['couverture'] }} mois couverts).
            Quantité proposée = stock maximum − disponible − déjà commandé.
            @can('parametres.etablissement')<a href="{{ route('etablissement.edit') }}">Modifier ces durées</a>@endcan
        </div>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('besoins.proposer') }}">
            @csrf
            <div class="block block-rounded"><div class="block-content block-content-full">
                <div class="table-responsive">
                <table class="table table-sm table-bordered table-vcenter">
                    <thead>
                        <tr>
                            <th class="text-center"><input type="checkbox" id="toutCocher" class="form-check-input"></th>
                            <th>Produit</th><th>Statut</th><th class="text-end">CMM</th><th class="text-end">Disponible</th><th class="text-end">Mois de stock</th>
                            <th class="text-end">Déjà commandé</th><th class="text-end">Point de commande</th><th class="text-end">Stock max</th>
                            <th style="width:170px">À commander</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lignes as $l)
                            @php $m = $l['medicament']; $s = $statuts[$l['statut']]; @endphp
                            <tr>
                                <td class="text-center"><input type="checkbox" name="choix[]" value="{{ $m->id }}" class="form-check-input choix" @checked(in_array($l['statut'], ['rupture', 'a_commander']) && $l['propose_unite'] > 0)></td>
                                <td>
                                    {{ $m->nom }}
                                    @if($l['base'] === 'seuil')<div class="small text-muted">sans consommation : seuil manuel ({{ $m->stock_min }})</div>@endif
                                    @if($l['base'] === 'aucune')<div class="small text-muted">ni consommation ni seuil : quantité à saisir</div>@endif
                                    @if($l['jours_rupture'] > 0)<div class="small text-danger">{{ $l['jours_rupture'] }} j de rupture sur la période</div>@endif
                                </td>
                                <td><span class="badge bg-{{ $s[1] }}">{{ $s[0] }}</span></td>
                                <td class="text-end">{{ $l['cmm'] ?: '—' }}</td>
                                <td class="text-end">{{ $l['disponible'] }}</td>
                                <td class="text-end">{{ $l['mois_stock'] ?? '—' }}</td>
                                <td class="text-end">{{ $l['en_commande'] ?: '' }}</td>
                                <td class="text-end">{{ $l['point_commande'] ?: '' }}</td>
                                <td class="text-end">{{ $l['stock_max'] ?: '' }}</td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="quantites[{{ $m->id }}]" class="form-control" min="0" value="{{ $l['propose_unite'] }}">
                                        <span class="input-group-text">{{ $l['unite']->nom ?? 'unité' }}</span>
                                    </div>
                                    @if(($l['unite']->facteur ?? 1) > 1)<div class="small text-muted">= {{ $l['propose_base'] }} unités de base proposées</div>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-muted">Aucun produit dans cette sélection. Les produits apparaissent dès qu'ils ont une consommation enregistrée ou un seuil d'alerte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                @if($lignes->isNotEmpty() && auth()->user()->can('achats.demander'))
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <input type="text" name="motif" class="form-control form-control-sm" style="max-width:420px" placeholder="Motif (ex. commande trimestrielle)">
                        <button class="btn btn-primary btn-sm"><i class="fa fa-file-signature me-1"></i>Créer la proposition de commande</button>
                        <span class="small text-muted">Corrigez les quantités si besoin : la proposition partira à l'approbation.</span>
                    </div>
                @endif
            </div></div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $('#toutCocher').on('change', function () { $('.choix').prop('checked', this.checked); });
    </script>
@endsection
