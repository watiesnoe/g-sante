@extends('layouts.app')

@section('title_page', 'Propositions de commande')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-shopping-basket me-2"></i>Propositions de commande</h2>
            @can('stock.besoins')<a href="{{ route('besoins.index') }}" class="btn btn-sm btn-primary"><i class="fa fa-calculator me-1"></i>Préparer depuis l'état des besoins</a>@endcan
        </div>
        <p class="text-muted small">La pharmacie propose, un responsable approuve, puis chaque produit est commandé à son fournisseur (une commande par fournisseur). Les services demandent leurs produits par une <a href="{{ route('requisitions.index') }}">réquisition</a>.</p>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @can('achats.demander')
        <form method="POST" action="{{ route('achats.store') }}" class="block block-rounded" id="formDemande">
            @csrf
            <div class="block-header block-header-default"><h3 class="block-title">Proposition saisie à la main</h3></div>
            <div class="block-content">
                <div class="row g-2 mb-2">
                    <div class="col-md-4"><select name="service_medical_id" class="form-select no-select2"><option value="">Pour la pharmacie (aucun service)</option>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select></div>
                    <div class="col-md-8"><input type="text" name="motif" class="form-control" placeholder="Motif (ex. réapprovisionnement mensuel)"></div>
                </div>
                <table class="table table-sm" id="lignesDemande">
                    <tbody>
                        <tr>
                            <td><select name="lignes[0][medicament_id]" class="form-select js-select2" required style="width:100%"><option value="">Produit…</option>@foreach($medicaments as $m)<option value="{{ $m->id }}">{{ $m->nom }} (stock {{ $m->stock }}, seuil {{ $m->stock_min }}){{ $m->uniteDefault ? ' — en ' . $m->uniteDefault->nom : '' }}</option>@endforeach</select></td>
                            <td style="width:180px"><input type="number" name="lignes[0][quantite]" class="form-control" min="1" placeholder="Qté (unité d'achat)" required></td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ajouterLigne">+ Ligne</button>
                    <div>
                        <button class="btn btn-outline-primary">Brouillon</button>
                        <button name="soumettre" value="1" class="btn btn-primary">Soumettre</button>
                    </div>
                </div>
            </div>
        </form>
        @endcan

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-bordered table-striped table-vcenter">
                <thead><tr><th>N°</th><th>Date</th><th>Service</th><th>Produits</th><th>Demandeur</th><th>Statut</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @foreach($demandes as $d)
                        <tr>
                            <td>{{ $d->numero }}</td>
                            <td>{{ $d->created_at->format('d/m/Y') }}</td>
                            <td>{{ $d->service->nom ?? '-' }}</td>
                            <td class="small">@foreach($d->lignes as $l){{ $l->medicament->nom ?? '?' }} × {{ $l->quantite }} {{ $l->medicament->uniteDefault->nom ?? '' }}@if($l->fournisseur) <span class="text-muted">— {{ $l->fournisseur->nom }}</span>@endif<br>@endforeach</td>
                            <td>{{ $d->demandeur->name ?? '-' }}</td>
                            <td>{{ \App\Models\DemandeAchat::STATUTS[$d->statut] }}
                                @if($d->motif_rejet)<div class="small text-danger">{{ $d->motif_rejet }}</div>@endif
                                @forelse($d->commandes as $c)
                                    <div class="small"><a href="{{ route('commandes.show', $c) }}">{{ $c->reference }}</a></div>
                                @empty
                                    @if($d->commande)<div class="small"><a href="{{ route('commandes.show', $d->commande) }}">{{ $d->commande->reference }}</a></div>@endif
                                @endforelse
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @if($d->statut === 'brouillon')
                                        <form method="POST" action="{{ route('achats.action', [$d, 'soumettre']) }}">@csrf<button class="btn btn-sm btn-outline-warning">Soumettre</button></form>
                                    @endif
                                    @if($d->statut === 'soumise' && auth()->user()->can('achats.approuver'))
                                        <form method="POST" action="{{ route('achats.action', [$d, 'approuver']) }}">@csrf<button class="btn btn-sm btn-success">Approuver</button></form>
                                        <form method="POST" action="{{ route('achats.action', [$d, 'rejeter']) }}" onsubmit="this.motif.value = prompt('Motif du rejet :') || ''; return this.motif.value !== '';">@csrf<input type="hidden" name="motif"><button class="btn btn-sm btn-outline-danger">Rejeter</button></form>
                                    @endif
                                    @if($d->statut === 'approuvee' && auth()->user()->can('stock.commandes'))
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#commander-{{ $d->id }}">Commander</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($d->statut === 'approuvee' && auth()->user()->can('stock.commandes'))
                            <tr class="collapse" id="commander-{{ $d->id }}">
                                <td colspan="7" class="bg-body-light">
                                    <form method="POST" action="{{ route('achats.action', [$d, 'commander']) }}">@csrf
                                        <table class="table table-sm mb-2">
                                            <thead><tr><th>Produit</th><th>Quantité</th><th style="width:280px">Fournisseur</th></tr></thead>
                                            <tbody>
                                                @foreach($d->lignes as $l)
                                                    @php $defaut = $l->fournisseur_id ?? ($derniersFournisseurs[$l->medicament_id] ?? null); @endphp
                                                    <tr>
                                                        <td>{{ $l->medicament->nom ?? '?' }}</td>
                                                        <td>{{ $l->quantite }} {{ $l->medicament->uniteDefault->nom ?? '' }}</td>
                                                        <td><select name="fournisseurs[{{ $l->id }}]" class="form-select form-select-sm no-select2" required><option value="">Fournisseur…</option>@foreach($fournisseurs as $f)<option value="{{ $f->id }}" @selected($defaut == $f->id)>{{ $f->nom }}</option>@endforeach</select></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button class="btn btn-sm btn-primary">Passer les commandes</button>
                                        <span class="small text-muted ms-2">Une commande par fournisseur, au prix d'achat de l'unité ; les prix restent modifiables sur chaque commande.</span>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div></div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            let n = 1;
            $('#ajouterLigne').on('click', function () {
                const ligne = $('#lignesDemande tbody tr:first').clone();
                ligne.find('.select2').remove();
                ligne.find('select').attr('name', `lignes[${n}][medicament_id]`).removeClass('select2-hidden-accessible').removeAttr('data-select2-id').val('');
                ligne.find('input').attr('name', `lignes[${n}][quantite]`).val('');
                $('#lignesDemande tbody').append(ligne);
                ligne.find('select').select2({ width: '100%' });
                n++;
            });
        });
    </script>
@endsection
