@extends('layouts.app')

@section('title_page', 'Demandes d\'achat')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-shopping-basket me-2"></i>Demandes d'achat</h2>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @can('achats.demander')
        <form method="POST" action="{{ route('achats.store') }}" class="block block-rounded" id="formDemande">
            @csrf
            <div class="block-header block-header-default"><h3 class="block-title">Nouvelle demande</h3></div>
            <div class="block-content">
                <div class="row g-2 mb-2">
                    <div class="col-md-4"><select name="service_medical_id" class="form-select no-select2"><option value="">Service demandeur…</option>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select></div>
                    <div class="col-md-8"><input type="text" name="motif" class="form-control" placeholder="Motif (ex. réapprovisionnement mensuel)"></div>
                </div>
                <table class="table table-sm" id="lignesDemande">
                    <tbody>
                        <tr>
                            <td><select name="lignes[0][medicament_id]" class="form-select js-select2" required style="width:100%"><option value="">Produit…</option>@foreach($medicaments as $m)<option value="{{ $m->id }}">{{ $m->nom }} (stock {{ $m->stock }}, seuil {{ $m->stock_min }})</option>@endforeach</select></td>
                            <td style="width:140px"><input type="number" name="lignes[0][quantite]" class="form-control" min="1" placeholder="Quantité" required></td>
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
                            <td class="small">@foreach($d->lignes as $l){{ $l->medicament->nom ?? '?' }} × {{ $l->quantite }}<br>@endforeach</td>
                            <td>{{ $d->demandeur->name ?? '-' }}</td>
                            <td>{{ \App\Models\DemandeAchat::STATUTS[$d->statut] }}
                                @if($d->motif_rejet)<div class="small text-danger">{{ $d->motif_rejet }}</div>@endif
                                @if($d->commande)<div class="small"><a href="{{ route('commandes.show', $d->commande) }}">{{ $d->commande->reference }}</a></div>@endif
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
                                        <form method="POST" action="{{ route('achats.action', [$d, 'commander']) }}" class="d-flex gap-1">@csrf
                                            <select name="fournisseur_id" class="form-select form-select-sm no-select2" required><option value="">Fournisseur…</option>@foreach($fournisseurs as $f)<option value="{{ $f->id }}">{{ $f->nom }}</option>@endforeach</select>
                                            <button class="btn btn-sm btn-primary">Commander</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
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
