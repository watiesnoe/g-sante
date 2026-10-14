@extends('layouts.app')

@section('title_page', 'Registre des accouchements')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-baby me-2"></i>Registre des accouchements</h2>
            <div class="d-flex gap-2">
                <form method="GET" class="d-flex gap-2">
                    <input type="date" name="du" class="form-control form-control-sm" value="{{ $du->toDateString() }}">
                    <input type="date" name="au" class="form-control form-control-sm" value="{{ $au->toDateString() }}">
                    <button class="btn btn-sm btn-outline-primary">Afficher</button>
                </form>
                @can('maternity.accouchement')<a href="{{ route('accouchements.create') }}" class="btn btn-sm btn-primary">+ Accouchement</a>@endcan
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-sm table-bordered">
                <thead><tr><th>Date</th><th>Mère</th><th>Terme</th><th>Mode</th><th>Mère</th><th>Nouveau-né(s)</th><th>Accoucheur</th></tr></thead>
                <tbody>
                    @forelse($accouchements as $a)
                        <tr>
                            <td>{{ $a->date_heure->format('d/m/Y H:i') }}</td>
                            <td>{{ mb_strtoupper($a->patient->nom ?? '') }} {{ $a->patient->prenom ?? '' }}</td>
                            <td>{{ $a->terme_sa ? $a->terme_sa . ' SA' : '-' }}</td>
                            <td>{{ \App\Models\Accouchement::MODES[$a->mode] ?? $a->mode }}</td>
                            <td>{{ \App\Models\Accouchement::ETATS_MERE[$a->etat_mere] ?? $a->etat_mere }}</td>
                            <td class="small">@foreach($a->nouveauNes as $n){{ $n->sexe === 'M' ? 'Garçon' : ($n->sexe === 'F' ? 'Fille' : '?') }} — {{ \App\Models\NouveauNe::ETATS[$n->etat] }}{{ $n->poids_g ? ', ' . $n->poids_g . ' g' : '' }}{{ $n->apgar_5 !== null ? ', Apgar ' . $n->apgar_1 . '/' . $n->apgar_5 : '' }}<br>@endforeach</td>
                            <td>{{ $a->accoucheur->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Aucun accouchement sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
@endsection
