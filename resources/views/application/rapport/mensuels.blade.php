@extends('layouts.app')

@section('title_page', 'Rapports mensuels')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-file-medical-alt me-2"></i>Rapports mensuels d'activité</h2>
            <form method="POST" action="{{ route('rapports-mensuels.generer') }}" class="d-flex gap-2">@csrf
                <input type="month" name="mois" class="form-control" value="{{ now()->subMonth()->format('Y-m') }}" required>
                <button class="btn btn-primary text-nowrap">Calculer</button>
            </form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-sm table-bordered">
                <thead><tr><th>Mois</th><th>Version</th><th>Statut</th><th>Calculé par</th><th>Validé par</th><th>Transmis le</th><th></th></tr></thead>
                <tbody>
                    @forelse($rapports as $r)
                        <tr>
                            <td>{{ $r->periode_du->translatedFormat('F Y') }}</td>
                            <td>v{{ $r->version }}</td>
                            <td><span class="badge bg-{{ ['brouillon' => 'secondary', 'valide' => 'info', 'transmis' => 'success'][$r->statut] }}">{{ \App\Models\RapportPeriodique::STATUTS[$r->statut] }}</span></td>
                            <td>{{ $r->auteur->name ?? '-' }}</td>
                            <td>{{ $r->validateur->name ?? '—' }}</td>
                            <td>{{ $r->transmis_at?->format('d/m/Y') ?? '—' }}</td>
                            <td><a href="{{ route('rapports-mensuels.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">Aucun rapport.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
@endsection
