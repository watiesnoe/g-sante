@extends('layouts.app')

@section('title_page', 'Réquisitions des services')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-dolly me-2"></i>Réquisitions des services</h2>
            <form method="GET">
                <select name="statut" class="form-select form-select-sm no-select2" onchange="this.form.submit()">
                    <option value="a_traiter" {{ $statut === 'a_traiter' ? 'selected' : '' }}>À servir</option>
                    @foreach(\App\Models\Requisition::STATUTS as $code => $libelle)
                        @continue($code === 'soumise')
                        <option value="{{ $code }}" {{ $statut === $code ? 'selected' : '' }}>{{ $libelle }}</option>
                    @endforeach
                    <option value="toutes" {{ $statut === 'toutes' ? 'selected' : '' }}>Toutes</option>
                </select>
            </form>
        </div>
        <p class="text-muted small">Un service demande des produits à la pharmacie, qui le sert depuis son stock. Pour un achat auprès d'un fournisseur, c'est la pharmacie qui prépare une proposition de commande.</p>

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @can('stock.requisition')
        <form method="POST" action="{{ route('requisitions.store') }}" class="block block-rounded">
            @csrf
            <div class="block-header block-header-default"><h3 class="block-title">Nouvelle réquisition</h3></div>
            <div class="block-content">
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <select name="service_medical_id" class="form-select no-select2" required>
                            <option value="">Service demandeur…</option>
                            @foreach($services as $s)<option value="{{ $s->id }}" @selected(old('service_medical_id') == $s->id)>{{ $s->nom }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-8"><input type="text" name="motif" class="form-control" value="{{ old('motif') }}" placeholder="Motif (ex. dotation hebdomadaire de la maternité)"></div>
                </div>
                <table class="table table-sm" id="lignesRequisition">
                    <thead><tr><th>Produit</th><th style="width:170px">Quantité (unités)</th></tr></thead>
                    <tbody>
                        <tr>
                            <td><select name="lignes[0][medicament_id]" class="form-select js-select2" required style="width:100%"><option value="">Produit…</option>@foreach($medicaments as $m)<option value="{{ $m->id }}">{{ $m->nom }}</option>@endforeach</select></td>
                            <td><input type="number" name="lignes[0][quantite]" class="form-control" min="1" placeholder="Quantité" required></td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ajouterLigne">+ Ligne</button>
                    <button class="btn btn-primary">Envoyer à la pharmacie</button>
                </div>
            </div>
        </form>
        @endcan

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-bordered table-vcenter">
                <thead><tr><th>N°</th><th>Date</th><th>Service</th><th>Produits</th><th>Demandeur</th><th>Statut</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse($requisitions as $r)
                        @php $aServir = $r->statut === 'soumise' && auth()->user()->can('stock.servir'); @endphp
                        <tr>
                            <td class="text-nowrap">{{ $r->numero }}</td>
                            <td class="text-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $r->service->nom ?? '-' }}<div class="small text-muted">{{ $r->motif }}</div></td>
                            <td class="small">
                                @foreach($r->lignes as $l)
                                    {{ $l->medicament->nom ?? '?' }} × {{ $l->quantite_demandee }}
                                    @if(in_array($r->statut, ['servie', 'partielle']))<span class="{{ $l->quantite_servie < $l->quantite_demandee ? 'text-danger' : 'text-success' }}">(servi {{ $l->quantite_servie }})</span>@endif
                                    <br>
                                @endforeach
                            </td>
                            <td>{{ $r->demandeur->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ \App\Models\Requisition::COULEURS[$r->statut] ?? 'secondary' }}">{{ \App\Models\Requisition::STATUTS[$r->statut] ?? $r->statut }}</span>
                                @if($r->servie_at)<div class="small text-muted">{{ $r->servie_at->format('d/m H:i') }} — {{ $r->serveur->name ?? '' }}@if($r->magasin) · {{ $r->magasin->nom }}@endif</div>@endif
                                @if($r->motif_rejet)<div class="small text-danger">{{ $r->motif_rejet }}</div>@endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    @if($aServir)
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="collapse" data-bs-target="#servir-{{ $r->id }}">Servir</button>
                                        <form method="POST" action="{{ route('requisitions.rejeter', $r) }}" onsubmit="this.motif.value = prompt('Motif du rejet :') || ''; return this.motif.value !== '';">@csrf<input type="hidden" name="motif"><button class="btn btn-sm btn-outline-danger">Rejeter</button></form>
                                    @endif
                                    @if($r->statut === 'soumise' && $r->demandeur_id === auth()->id())
                                        <form method="POST" action="{{ route('requisitions.annuler', $r) }}" onsubmit="return confirm('Annuler cette réquisition ?');">@csrf<button class="btn btn-sm btn-outline-secondary">Annuler</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($aServir)
                            <tr class="collapse" id="servir-{{ $r->id }}">
                                <td colspan="7" class="bg-body-light">
                                    <form method="POST" action="{{ route('requisitions.servir', $r) }}">
                                        @csrf
                                        <div class="row g-2 align-items-center mb-2">
                                            <div class="col-auto"><label class="form-label mb-0">Servi depuis</label></div>
                                            <div class="col-auto">
                                                <select name="magasin_id" class="form-select form-select-sm no-select2">
                                                    @foreach($magasins as $mag)<option value="{{ $mag->id }}" @selected(($r->magasin_id ?? null) === $mag->id)>{{ $mag->nom }}</option>@endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <table class="table table-sm mb-2">
                                            <thead><tr><th>Produit</th><th>Demandé</th><th>Disponible (non périmé)</th><th style="width:150px">Servi</th></tr></thead>
                                            <tbody>
                                                @foreach($r->lignes as $l)
                                                    @php $dispo = $disponibles[$l->medicament_id] ?? 0; @endphp
                                                    <tr>
                                                        <td>{{ $l->medicament->nom ?? '?' }}</td>
                                                        <td>{{ $l->quantite_demandee }}</td>
                                                        <td class="{{ $dispo < $l->quantite_demandee ? 'text-danger fw-bold' : '' }}">{{ $dispo }}</td>
                                                        <td><input type="number" name="quantites[{{ $l->id }}]" class="form-control form-control-sm" min="0" max="{{ $l->quantite_demandee }}" value="{{ min($l->quantite_demandee, max(0, $dispo)) }}"></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <button class="btn btn-sm btn-success">Valider la sortie de stock</button>
                                        <span class="small text-muted ms-2">Les lots qui périment le plus tôt sortent en premier.</span>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="7" class="text-muted">Aucune réquisition.</td></tr>
                    @endforelse
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
                const ligne = $('#lignesRequisition tbody tr:first').clone();
                ligne.find('.select2').remove();
                ligne.find('select').attr('name', `lignes[${n}][medicament_id]`).removeClass('select2-hidden-accessible').removeAttr('data-select2-id').val('');
                ligne.find('input').attr('name', `lignes[${n}][quantite]`).val('');
                $('#lignesRequisition tbody').append(ligne);
                ligne.find('select').select2({ width: '100%' });
                n++;
            });
        });
    </script>
@endsection
