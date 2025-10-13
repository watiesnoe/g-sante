@extends('layouts.app')

@section('title_page', 'Bordereaux et créances')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-file-invoice me-2"></i>Bordereaux et créances des organismes</h2>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row g-3 mb-3">
            <div class="col-xl-8">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default"><h3 class="block-title">Créances par organisme</h3></div>
                    <div class="block-content">
                        <table class="table table-sm table-bordered">
                            <thead><tr><th>Organisme</th><th class="text-end">À facturer</th><th class="text-end">Facturé</th><th class="text-end">Recouvré</th><th class="text-end">Reste dû</th></tr></thead>
                            <tbody>
                                @forelse($creances as $c)
                                    <tr>
                                        <td>{{ $c['assurance']->nom }}</td>
                                        <td class="text-end">{{ number_format($c['non_facture'], 0, ',', ' ') }} F</td>
                                        <td class="text-end">{{ number_format($c['facture'], 0, ',', ' ') }} F</td>
                                        <td class="text-end">{{ number_format($c['recouvre'], 0, ',', ' ') }} F</td>
                                        <td class="text-end fw-bold">{{ number_format($c['facture'] - $c['recouvre'], 0, ',', ' ') }} F</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted">Aucune prise en charge enregistrée.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <form method="POST" action="{{ route('bordereaux.generer') }}" class="block block-rounded h-100">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Émettre un bordereau</h3></div>
                    <div class="block-content">
                        <div class="mb-2"><label class="form-label">Organisme</label>
                            <select name="assurance_id" class="form-select no-select2" required>@foreach($assurances as $a)<option value="{{ $a->id }}">{{ $a->nom }}</option>@endforeach</select></div>
                        <div class="row g-2 mb-3">
                            <div class="col-6"><label class="form-label">Du</label><input type="date" name="du" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}" required></div>
                            <div class="col-6"><label class="form-label">Au</label><input type="date" name="au" class="form-control" value="{{ now()->toDateString() }}" required></div>
                        </div>
                        <div class="text-end mb-3"><button class="btn btn-primary">Émettre</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="block block-rounded">
            <div class="block-content block-content-full">
                <table class="table table-bordered table-striped table-vcenter">
                    <thead><tr><th>N°</th><th>Organisme</th><th>Période</th><th class="text-end">Montant</th><th class="text-end">Recouvré</th><th>Statut</th><th></th></tr></thead>
                    <tbody>
                        @foreach($bordereaux as $b)
                            <tr>
                                <td>{{ $b->numero }}</td>
                                <td>{{ $b->assurance->nom ?? '-' }}</td>
                                <td>{{ $b->periode_du->format('d/m/Y') }} → {{ $b->periode_au->format('d/m/Y') }}</td>
                                <td class="text-end">{{ number_format($b->montant, 0, ',', ' ') }} F</td>
                                <td class="text-end">{{ number_format($b->montant_recouvre, 0, ',', ' ') }} F</td>
                                <td>{{ \App\Models\Bordereau::STATUTS[$b->statut] }}</td>
                                <td><a href="{{ route('bordereaux.show', $b) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
