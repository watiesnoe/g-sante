@extends('layouts.app')

@section('title_page', 'Budget')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-balance-scale me-2"></i>Exécution budgétaire {{ $exercice }}</h2>
            <form method="GET"><input type="number" name="exercice" class="form-control" value="{{ $exercice }}" onchange="this.form.submit()" style="width:110px"></form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-bordered table-sm table-vcenter">
                <thead><tr><th>Code</th><th>Libellé</th><th class="text-end">Prévu</th><th class="text-end">Engagé</th><th class="text-end">Réalisé</th><th class="text-end">Disponible</th><th style="width:160px">Consommation</th></tr></thead>
                <tbody>
                    @forelse($lignes as $l)
                        @php $taux = $l['ligne']->montant_prevu > 0 ? min(100, round($l['engage'] / $l['ligne']->montant_prevu * 100)) : 100; @endphp
                        <tr>
                            <td>{{ $l['ligne']->code }}</td>
                            <td>{{ $l['ligne']->libelle }}</td>
                            <td class="text-end">{{ number_format($l['ligne']->montant_prevu, 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($l['engage'], 0, ',', ' ') }}</td>
                            <td class="text-end">{{ number_format($l['realise'], 0, ',', ' ') }}</td>
                            <td class="text-end fw-bold {{ $l['ecart'] < 0 ? 'text-danger' : '' }}">{{ number_format($l['ecart'], 0, ',', ' ') }}</td>
                            <td><div class="progress" style="height:8px"><div class="progress-bar bg-{{ $taux >= 100 ? 'danger' : ($taux >= 80 ? 'warning' : 'success') }}" style="width: {{ $taux }}%"></div></div><small>{{ $taux }} %</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Aucune ligne budgétaire pour cet exercice.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @can('budget.gerer')
                <form method="POST" action="{{ route('budget.store') }}" class="row g-2">
                    @csrf
                    <input type="hidden" name="exercice" value="{{ $exercice }}">
                    <div class="col-md-2"><input type="text" name="code" class="form-control" placeholder="Code" required></div>
                    <div class="col-md-5"><input type="text" name="libelle" class="form-control" placeholder="Libellé" required></div>
                    <div class="col-md-3"><input type="number" name="montant_prevu" class="form-control" placeholder="Montant prévu" min="0" required></div>
                    <div class="col-md-2"><button class="btn btn-primary w-100">Ajouter</button></div>
                </form>
            @endcan
        </div></div>
    </div>
@endsection
