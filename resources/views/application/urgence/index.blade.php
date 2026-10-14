@extends('layouts.app')

@section('title_page', 'Urgences')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-ambulance text-danger me-2"></i>Registre des urgences</h2>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="du" class="form-control form-control-sm" value="{{ $du->toDateString() }}">
                <input type="date" name="au" class="form-control form-control-sm" value="{{ $au->toDateString() }}">
                <button class="btn btn-sm btn-primary">Afficher</button>
                <button name="format" value="csv" class="btn btn-sm btn-outline-success"><i class="fa fa-file-csv"></i> Registre</button>
            </form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @can('urgences.gerer')
        <form method="POST" action="{{ route('urgences.store') }}" class="block block-rounded border-start border-4 border-danger">
            @csrf
            <div class="block-content">
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                        <select id="patientUrgence" name="patient_id" class="form-select" style="width:100%" required></select>
                        <div class="form-text">Patient inconnu ? <a href="{{ route('patients.create') }}">créer le dossier</a> (le paiement n'est jamais demandé avant la prise en charge).</div>
                    </div>
                    <div class="col-md-4"><label class="form-label">Motif <span class="text-danger">*</span></label><input type="text" name="motif" class="form-control" required></div>
                    @if($triage)
                        <div class="col-md-2"><label class="form-label">Priorité</label>
                            <select name="priorite" class="form-select no-select2"><option value="1">P1 — vitale</option><option value="2">P2 — urgent</option><option value="3" selected>P3</option><option value="4">P4</option></select></div>
                    @endif
                    <div class="col-md-1"><button class="btn btn-danger w-100">Enregistrer</button></div>
                </div>
            </div>
        </form>
        @endcan

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-sm table-bordered table-vcenter">
                <thead><tr><th>Passage</th><th>Patient</th><th>Arrivée</th><th>Prise en charge</th><th>Sortie</th>@if($triage)<th>Prio.</th>@endif<th>Motif</th><th>Décision / orientation</th><th>Facture</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse($passages as $p)
                        <tr class="{{ $p->termine_at ? 'text-muted' : '' }}">
                            <td class="text-nowrap">{{ $p->numero }}</td>
                            <td>{{ mb_strtoupper($p->patient->nom ?? '') }} {{ $p->patient->prenom ?? '' }}</td>
                            <td>{{ $p->arrive_at->format('d/m H:i') }}</td>
                            <td>{{ $p->pris_en_charge_at?->format('H:i') ?? '—' }}@if($p->pris_en_charge_at)<div class="small text-muted">{{ $p->arrive_at->diffInMinutes($p->pris_en_charge_at) }} min</div>@endif</td>
                            <td>{{ $p->termine_at?->format('d/m H:i') ?? '—' }}</td>
                            @if($triage)<td>{!! $p->priorite ? '<span class="badge bg-' . ([1 => 'danger', 2 => 'warning'][$p->priorite] ?? 'info') . '">P' . $p->priorite . '</span>' : '—' !!}</td>@endif
                            <td>{{ $p->motif }}</td>
                            <td>{{ \App\Models\Passage::DECISIONS[$p->decision] ?? '—' }}<div class="small">{{ $p->orientation }}</div></td>
                            <td>
                                @if($p->ticket)
                                    <span class="badge bg-success">{{ $p->ticket->numero }}</span>
                                @elseif(auth()->user()->can('tickets.create'))
                                    <a href="{{ route('tickets.create', ['patient' => $p->patient->uuid, 'passage' => $p->id]) }}" class="btn btn-sm btn-outline-success">Régulariser</a>
                                @else
                                    <span class="badge bg-warning text-dark">à régulariser</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('urgences.gerer')
                                    @if(in_array($p->statut, ['enregistre', 'trie']))
                                        <form method="POST" action="{{ route('urgences.prise_en_charge', $p) }}">@csrf<button class="btn btn-sm btn-primary">Prendre en charge</button></form>
                                    @elseif($p->statut === 'en_consultation')
                                        <form method="POST" action="{{ route('urgences.decision', $p) }}" class="d-flex gap-1">@csrf
                                            <select name="decision" class="form-select form-select-sm no-select2" required>@foreach(\App\Models\Passage::DECISIONS as $c => $l)<option value="{{ $c }}">{{ $l }}</option>@endforeach</select>
                                            <input type="text" name="orientation" class="form-control form-control-sm" placeholder="Orientation">
                                            <button class="btn btn-sm btn-success">OK</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-muted">Aucun passage aux urgences sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('#patientUrgence').select2({
                placeholder: 'Nom, téléphone ou n° de dossier…', minimumInputLength: 2,
                ajax: { url: "{{ route('patients.search') }}", dataType: 'json', delay: 250, data: (p) => ({ q: p.term }),
                    processResults: (data) => ({ results: data.map(p => ({ id: p.id, text: `${p.nom} ${p.prenom} — ${p.numero_dossier ?? ''}` })) }) }
            });
        });
    </script>
@endsection
