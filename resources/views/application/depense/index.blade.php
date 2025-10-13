@extends('layouts.app')

@section('title_page', 'Dépenses')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-file-invoice-dollar me-2"></i>Dépenses {{ session('exercice_year', date('Y')) }}</h2>
            @can('depenses.create')
                <a href="{{ route('depenses.create') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Nouvelle dépense</a>
            @endcan
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row g-2 mb-3">
            @foreach(\App\Models\Depense::STATUTS as $code => $libelle)
                <div class="col-6 col-md-4 col-xl-2">
                    <a href="{{ route('depenses.index', ['statut' => $code]) }}" class="block block-rounded block-link-pop mb-0 text-center py-2 {{ request('statut') === $code ? 'border border-primary' : '' }}">
                        <div class="fw-bold">{{ number_format($totaux[$code]->total ?? 0, 0, ',', ' ') }} F</div>
                        <div class="small text-muted">{{ $libelle }} ({{ $totaux[$code]->nombre ?? 0 }})</div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <table class="table table-bordered table-striped table-vcenter js-dataTable-full w-100">
                    <thead>
                        <tr>
                            <th>N°</th><th>Date</th><th>Catégorie</th><th>Objet</th><th>Service</th>
                            <th class="text-end">Montant</th><th>Statut</th><th>Saisie par</th><th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($depenses as $d)
                            <tr>
                                <td class="text-nowrap">{{ $d->numero }}</td>
                                <td>{{ $d->date_depense->format('d/m/Y') }}</td>
                                <td>{{ $d->categorie->nom ?? '-' }}</td>
                                <td>
                                    {{ $d->objet }}
                                    @if($d->beneficiaire)<div class="small text-muted">{{ $d->beneficiaire }}</div>@endif
                                    @if($d->motif_rejet)<div class="small text-danger">Rejet : {{ $d->motif_rejet }}</div>@endif
                                </td>
                                <td>{{ $d->service->nom ?? '-' }}</td>
                                <td class="text-end fw-semibold">{{ number_format($d->montant, 0, ',', ' ') }} F</td>
                                <td><span class="badge bg-{{ \App\Models\Depense::COULEURS[$d->statut] }}">{{ \App\Models\Depense::STATUTS[$d->statut] }}</span></td>
                                <td>{{ $d->auteur->name ?? '-' }}</td>
                                <td class="text-center">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($d->piece_justificative)
                                            <a href="{{ route('depenses.piece', $d) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Pièce justificative"><i class="fa fa-paperclip"></i></a>
                                        @endif
                                        @if($d->statut === 'brouillon')
                                            <form method="POST" action="{{ route('depenses.action', [$d, 'soumettre']) }}">@csrf<button class="btn btn-sm btn-outline-warning">Soumettre</button></form>
                                        @endif
                                        @if($d->statut === 'soumise' && auth()->user()->can('depenses.valider'))
                                            <form method="POST" action="{{ route('depenses.action', [$d, 'valider']) }}">@csrf<button class="btn btn-sm btn-success">Valider</button></form>
                                            <form method="POST" action="{{ route('depenses.action', [$d, 'rejeter']) }}" class="form-rejet">@csrf<input type="hidden" name="motif"><button class="btn btn-sm btn-outline-danger">Rejeter</button></form>
                                        @endif
                                        @if($d->statut === 'validee' && auth()->user()->can('depenses.payer'))
                                            <form method="POST" action="{{ route('depenses.action', [$d, 'payer']) }}" class="d-flex gap-1">@csrf
                                                <select name="mode_paiement" class="form-select form-select-sm no-select2" style="width:auto">
                                                    @foreach(\App\Models\Etablissement::courant()->parametre('caisse.modes') as $mode)
                                                        <option value="{{ $mode }}">{{ \App\Models\CaisseMouvement::MODES[$mode] ?? $mode }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm btn-primary" onclick="return confirm('Payer cette dépense depuis votre caisse ?')">Payer</button>
                                            </form>
                                        @endif
                                        @if(in_array($d->statut, ['brouillon', 'soumise']) && ($d->created_by === auth()->id() || auth()->user()->can('depenses.valider')))
                                            <form method="POST" action="{{ route('depenses.action', [$d, 'annuler']) }}">@csrf<button class="btn btn-sm btn-outline-dark" onclick="return confirm('Annuler cette dépense ?')">Annuler</button></form>
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
@endsection

@section('scripts')
    <script>
        $(document).on('submit', '.form-rejet', function (e) {
            if (this.motif.value) return true;
            e.preventDefault();
            const motif = prompt('Motif du rejet (obligatoire) :');
            if (motif && motif.trim()) { this.motif.value = motif.trim(); this.submit(); }
        });
    </script>
@endsection
