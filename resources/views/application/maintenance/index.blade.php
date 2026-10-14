@extends('layouts.app')

@section('title_page', 'Maintenance')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-tools me-2"></i>Équipements et maintenance</h2>
        @if($aPrevoir)<div class="alert alert-warning">{{ $aPrevoir }} équipement(s) à entretenir dans les 15 jours ou en retard.</div>@endif
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-sm table-bordered table-vcenter">
                <thead><tr><th>Équipement</th><th>Service</th><th>Statut</th><th>Prochaine maintenance</th><th>Interventions ouvertes</th><th>Signaler</th></tr></thead>
                <tbody>
                    @forelse($equipements as $e)
                        <tr>
                            <td>{{ $e->nom }} <div class="small text-muted">{{ $e->categorie }} {{ $e->numero_serie ? '— n° ' . $e->numero_serie : '' }}</div></td>
                            <td>{{ $e->service->nom ?? '-' }}</td>
                            <td><span class="badge bg-{{ ['en_service' => 'success', 'en_panne' => 'danger', 'hors_service' => 'dark'][$e->statut] }}">{{ \App\Models\Equipement::STATUTS[$e->statut] }}</span></td>
                            <td class="{{ $e->prochaine_maintenance && $e->prochaine_maintenance->lte(today()) ? 'text-danger fw-bold' : '' }}">{{ $e->prochaine_maintenance?->format('d/m/Y') ?? '—' }}</td>
                            <td class="small">
                                @foreach($e->maintenances as $m)
                                    <div class="mb-1">{{ $m->type === 'panne' ? 'Panne' : 'Préventive' }} : {{ $m->description }}
                                        @can('maintenance.gerer')
                                            <form method="POST" action="{{ route('maintenance.resoudre', $m) }}" class="d-flex gap-1 mt-1">@csrf
                                                <input type="text" name="intervention_realisee" class="form-control form-control-sm" placeholder="Intervention réalisée" required>
                                                <input type="number" name="cout" class="form-control form-control-sm" placeholder="Coût" style="width:90px">
                                                <button class="btn btn-sm btn-success">Clôturer</button>
                                            </form>
                                        @endcan
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <form method="POST" action="{{ route('maintenance.signaler', $e) }}" class="d-flex gap-1">@csrf
                                    <select name="type" class="form-select form-select-sm no-select2"><option value="panne">Panne</option><option value="preventive">Préventive</option></select>
                                    <input type="text" name="description" class="form-control form-control-sm" placeholder="Description" required>
                                    <button class="btn btn-sm btn-outline-danger">OK</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">Aucun équipement enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @can('maintenance.gerer')
                <form method="POST" action="{{ route('maintenance.store') }}" class="row g-2 mt-2">@csrf
                    <div class="col-md-3"><input type="text" name="nom" class="form-control" placeholder="Équipement" required></div>
                    <div class="col-md-2"><input type="text" name="categorie" class="form-control" placeholder="Catégorie"></div>
                    <div class="col-md-2"><input type="text" name="numero_serie" class="form-control" placeholder="N° de série"></div>
                    <div class="col-md-2"><select name="service_medical_id" class="form-select no-select2"><option value="">Service…</option>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select></div>
                    <div class="col-md-2"><input type="number" name="periodicite_jours" class="form-control" placeholder="Entretien tous les … jours"></div>
                    <div class="col-md-1"><button class="btn btn-primary w-100">Ajouter</button></div>
                </form>
            @endcan
        </div></div>
    </div>
@endsection
