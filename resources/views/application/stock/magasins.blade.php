@extends('layouts.app')

@section('title_page', 'Magasins')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-warehouse me-2"></i>Magasins et transferts</h2>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-5">
                <div class="block block-rounded"><div class="block-content">
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Magasin</th><th>Type</th><th class="text-end">Références</th><th class="text-end">Unités</th></tr></thead>
                        <tbody>
                            @foreach($magasins as $m)
                                <tr>
                                    <td>{{ $m->nom }} @if($m->par_defaut)<span class="badge bg-primary">dispensation par défaut</span>@endif</td>
                                    <td>{{ $m->type === 'central' ? 'Central' : 'Dispensation' }}</td>
                                    <td class="text-end">{{ $stocks[$m->id]->references_ ?? 0 }}</td>
                                    <td class="text-end">{{ $stocks[$m->id]->unites ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @can('stock.inventaire')
                        <form method="POST" action="{{ route('magasins.store') }}" class="d-flex gap-2 mb-3">@csrf
                            <input type="text" name="nom" class="form-control" placeholder="Nouveau magasin" required>
                            <select name="type" class="form-select no-select2" style="width:auto"><option value="central">Central</option><option value="dispensation">Dispensation</option></select>
                            <button class="btn btn-outline-primary">Ajouter</button>
                        </form>
                    @endcan
                </div></div>
            </div>
            <div class="col-xl-7">
                @can('stock.inventaire')
                <form method="POST" action="{{ route('magasins.transferer') }}" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Transfert entre magasins</h3></div>
                    <div class="block-content">
                        <div class="row g-2 mb-3">
                            <div class="col-md-6"><select name="medicament_id" class="form-select js-select2" style="width:100%" required><option value="">Produit…</option>@foreach($medicaments as $med)<option value="{{ $med->id }}">{{ $med->nom }} ({{ $med->stock }})</option>@endforeach</select></div>
                            <div class="col-md-2"><input type="number" name="quantite" class="form-control" min="1" placeholder="Qté" required></div>
                            <div class="col-md-2"><select name="de" class="form-select no-select2">@foreach($magasins as $m)<option value="{{ $m->id }}">De : {{ $m->nom }}</option>@endforeach</select></div>
                            <div class="col-md-2"><select name="vers" class="form-select no-select2">@foreach($magasins as $m)<option value="{{ $m->id }}" {{ $loop->last ? 'selected' : '' }}>Vers : {{ $m->nom }}</option>@endforeach</select></div>
                            <div class="col-md-10"><input type="text" name="motif" class="form-control" placeholder="Motif"></div>
                            <div class="col-md-2"><button class="btn btn-primary w-100">Transférer</button></div>
                        </div>
                    </div>
                </form>
                @endcan
                <div class="block block-rounded"><div class="block-content">
                    <h5>Derniers transferts reçus</h5>
                    <table class="table table-sm">
                        @forelse($transferts as $t)
                            <tr><td>{{ $t->created_at->format('d/m H:i') }}</td><td>{{ $t->medicament->nom ?? '' }}</td><td>lot {{ $t->lot->numero ?? '' }}</td><td>→ {{ $t->lot->magasin->nom ?? '' }}</td><td class="text-end">{{ $t->quantite }}</td><td>{{ $t->user->name ?? '' }}</td></tr>
                        @empty
                            <tr><td class="text-muted">Aucun transfert.</td></tr>
                        @endforelse
                    </table>
                </div></div>
            </div>
        </div>
    </div>
@endsection
