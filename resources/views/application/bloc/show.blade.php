@extends('layouts.app')

@section('title_page', 'Intervention')

@section('content')
    @php $i = $intervention; @endphp
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0">{{ $i->libelle }} — {{ mb_strtoupper($i->patient->nom) }} {{ $i->patient->prenom }}
                <span class="badge bg-{{ ['programmee' => 'secondary', 'en_cours' => 'warning', 'terminee' => 'success', 'annulee' => 'dark'][$i->statut] }}">{{ \App\Models\Intervention::STATUTS[$i->statut] }}</span></h2>
            <a href="{{ route('bloc.index', ['date' => $i->debut_prevu->toDateString()]) }}" class="btn btn-secondary">Programme du jour</a>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="block block-rounded"><div class="block-content pb-3">
                    <p class="mb-1"><strong>Salle :</strong> {{ $i->salle->nom }} — <strong>prévu :</strong> {{ $i->debut_prevu->format('d/m/Y H:i') }} ({{ $i->duree_prevue_min }} min)</p>
                    <p class="mb-1"><strong>Chirurgien :</strong> {{ $i->chirurgien->name ?? '-' }} — <strong>Anesthésiste :</strong> {{ $i->anesthesiste->name ?? '-' }} {{ $i->type_anesthesie ? '(' . $i->type_anesthesie . ')' : '' }}</p>
                    <p class="mb-1"><strong>Réel :</strong> {{ $i->debut_reel?->format('H:i') ?? '—' }} → {{ $i->fin_reelle?->format('H:i') ?? '—' }}</p>
                    @if($i->prestation)<p class="mb-1"><strong>Acte facturé :</strong> {{ $i->prestation->nom }}</p>@endif
                    @if($i->motif_annulation)<p class="text-danger">Annulée : {{ $i->motif_annulation }}</p>@endif
                    @can('bloc.programmer')
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @if($i->statut === 'programmee')
                                <form method="POST" action="{{ route('bloc.action', [$i, 'demarrer']) }}">@csrf<button class="btn btn-warning">Démarrer</button></form>
                                <form method="POST" action="{{ route('bloc.action', [$i, 'annuler']) }}" onsubmit="this.motif.value = prompt('Motif de l\'annulation :') || ''; return this.motif.value !== '';">@csrf<input type="hidden" name="motif"><button class="btn btn-outline-dark">Annuler</button></form>
                            @endif
                        </div>
                        @if($i->statut === 'en_cours')
                            <form method="POST" action="{{ route('bloc.action', [$i, 'terminer']) }}" class="mt-3">@csrf
                                <label class="form-label">Compte rendu opératoire</label>
                                <textarea name="compte_rendu" class="form-control mb-2" rows="6" required></textarea>
                                <button class="btn btn-success">Terminer l'intervention</button>
                            </form>
                        @endif
                    @endcan
                    @if($i->compte_rendu)<h5 class="mt-3">Compte rendu</h5><div>{!! nl2br(e($i->compte_rendu)) !!}</div>@endif
                </div></div>
            </div>
            <div class="col-xl-6">
                <div class="block block-rounded"><div class="block-content pb-3">
                    <h5>Consommables</h5>
                    <table class="table table-sm">
                        @forelse($i->consommables as $c)
                            <tr><td>{{ $c->medicament->nom ?? '' }}</td><td class="text-end">{{ $c->quantite }}</td><td class="text-end">{{ number_format($c->quantite * $c->prix_unitaire, 0, ',', ' ') }} F</td></tr>
                        @empty
                            <tr><td class="text-muted">Aucun.</td></tr>
                        @endforelse
                    </table>
                    @if(in_array($i->statut, ['en_cours', 'terminee']) && auth()->user()->can('bloc.programmer'))
                        <form method="POST" action="{{ route('bloc.action', [$i, 'consommer']) }}" class="d-flex gap-2">@csrf
                            <select name="medicament_id" class="form-select js-select2" style="width:100%" required><option value="">Produit…</option>@foreach($medicaments as $m)<option value="{{ $m->id }}">{{ $m->nom }} ({{ $m->stock }})</option>@endforeach</select>
                            <input type="number" name="quantite" class="form-control" min="1" value="1" style="width:90px">
                            <button class="btn btn-outline-primary">Ajouter</button>
                        </form>
                    @endif
                </div></div>
            </div>
        </div>
    </div>
@endsection
