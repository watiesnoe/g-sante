@extends('layouts.app')

@section('title_page', 'Bordereau ' . $bordereau->numero)

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0">Bordereau {{ $bordereau->numero }} — {{ $bordereau->assurance->nom }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('bordereaux.pdf', $bordereau) }}" class="btn btn-outline-danger"><i class="fa fa-file-pdf me-1"></i> PDF</a>
                <a href="{{ route('bordereaux.index') }}" class="btn btn-secondary">Retour</a>
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="block block-rounded"><div class="block-content block-content-full">
                    <p>Période du {{ $bordereau->periode_du->format('d/m/Y') }} au {{ $bordereau->periode_au->format('d/m/Y') }} — {{ \App\Models\Bordereau::STATUTS[$bordereau->statut] }} — émis par {{ $bordereau->user->name ?? '-' }}</p>
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Date</th><th>Patient</th><th>Acte</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            @foreach($bordereau->lignes as $l)
                                <tr>
                                    <td>{{ $l->date_acte->format('d/m/Y') }}</td>
                                    <td>{{ $l->patient ? mb_strtoupper($l->patient->nom) . ' ' . $l->patient->prenom : '-' }}</td>
                                    <td>{{ $l->libelle }}</td>
                                    <td class="text-end">{{ number_format($l->montant, 0, ',', ' ') }} F</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr class="fw-bold"><td colspan="3">Total</td><td class="text-end">{{ number_format($bordereau->montant, 0, ',', ' ') }} F</td></tr></tfoot>
                    </table>
                </div></div>
            </div>
            <div class="col-xl-4">
                <div class="block block-rounded">
                    <div class="block-header block-header-default"><h3 class="block-title">Recouvrements — reste dû {{ number_format($bordereau->resteDu(), 0, ',', ' ') }} F</h3></div>
                    <div class="block-content">
                        @foreach($bordereau->recouvrements as $r)
                            <div class="small mb-1">{{ $r->date_recouvrement->format('d/m/Y') }} — {{ number_format($r->montant, 0, ',', ' ') }} F ({{ $r->mode }}{{ $r->reference ? ', ' . $r->reference : '' }})</div>
                        @endforeach
                        @if($bordereau->resteDu() > 0)
                            <form method="POST" action="{{ route('bordereaux.recouvrer', $bordereau) }}" class="mt-2">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-6"><input type="number" name="montant" class="form-control" placeholder="Montant" max="{{ $bordereau->resteDu() }}" required></div>
                                    <div class="col-6"><input type="date" name="date_recouvrement" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                                    <div class="col-6"><select name="mode" class="form-select no-select2"><option value="virement">Virement</option><option value="cheque">Chèque</option><option value="especes">Espèces</option><option value="mobile">Mobile</option></select></div>
                                    <div class="col-6"><input type="text" name="reference" class="form-control" placeholder="Référence"></div>
                                </div>
                                <div class="text-end mb-3"><button class="btn btn-success btn-sm">Enregistrer le règlement</button></div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
