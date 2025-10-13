@extends('layouts.app')

@section('title_page', 'Bloc opératoire')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-user-md me-2"></i>Programme du bloc — {{ $jour->translatedFormat('l d F Y') }}</h2>
            <form method="GET"><input type="date" name="date" class="form-control" value="{{ $jour->toDateString() }}" onchange="this.form.submit()"></form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
        @if($salles->isEmpty())<div class="alert alert-warning">Aucune salle d'opération : créez-en une dans Paramètres → Salles, avec le type « Bloc opératoire ».</div>@endif

        <div class="row g-3">
            <div class="col-xl-7">
                <div class="block block-rounded"><div class="block-content block-content-full">
                    <table class="table table-sm table-bordered table-vcenter">
                        <thead><tr><th>Heure</th><th>Salle</th><th>Patient</th><th>Intervention</th><th>Chirurgien</th><th>Statut</th><th></th></tr></thead>
                        <tbody>
                            @forelse($interventions as $i)
                                <tr>
                                    <td>{{ $i->debut_prevu->format('H:i') }}–{{ $i->finPrevue()->format('H:i') }}</td>
                                    <td>{{ $i->salle->nom ?? '-' }}</td>
                                    <td>{{ mb_strtoupper($i->patient->nom ?? '') }} {{ $i->patient->prenom ?? '' }}</td>
                                    <td>{{ $i->libelle }} @if($i->urgence === 'urgente')<span class="badge bg-danger">urgence</span>@endif</td>
                                    <td>{{ $i->chirurgien->name ?? '-' }}</td>
                                    <td>{{ \App\Models\Intervention::STATUTS[$i->statut] }}</td>
                                    <td><a href="{{ route('bloc.show', $i) }}" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted">Aucune intervention ce jour.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div></div>
            </div>
            @can('bloc.programmer')
            <div class="col-xl-5">
                <form method="POST" action="{{ route('bloc.store') }}" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Programmer une intervention</h3></div>
                    <div class="block-content">
                        <div class="mb-2"><select id="patientBloc" name="patient_id" class="form-select" style="width:100%" required></select></div>
                        <div class="mb-2"><input type="text" name="libelle" class="form-control" placeholder="Intervention (ex. Césarienne)" value="{{ old('libelle') }}" required></div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><select name="salle_id" class="form-select no-select2" required>@foreach($salles as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select></div>
                            <div class="col-6"><select name="urgence" class="form-select no-select2"><option value="programmee">Programmée</option><option value="urgente">Urgente</option></select></div>
                            <div class="col-7"><input type="datetime-local" name="debut_prevu" class="form-control" value="{{ old('debut_prevu', $jour->copy()->setTime(8, 0)->format('Y-m-d\TH:i')) }}" required></div>
                            <div class="col-5"><input type="number" name="duree_prevue_min" class="form-control" value="{{ old('duree_prevue_min', 60) }}" min="10" placeholder="Durée (min)"></div>
                            <div class="col-6"><select name="chirurgien_id" class="form-select js-select2" style="width:100%" required><option value="">Chirurgien…</option>@foreach($chirurgiens as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                            <div class="col-6"><select name="anesthesiste_id" class="form-select js-select2" style="width:100%"><option value="">Anesthésiste…</option>@foreach($chirurgiens as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                            <div class="col-6"><input type="text" name="type_anesthesie" class="form-control" placeholder="Anesthésie (AG, rachi…)"></div>
                            <div class="col-6"><select name="prestation_id" class="form-select js-select2" style="width:100%"><option value="">Acte facturé…</option>@foreach($prestations as $p)<option value="{{ $p->id }}">{{ $p->nom }}</option>@endforeach</select></div>
                        </div>
                        <div class="text-end mb-3"><button class="btn btn-primary">Programmer</button></div>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('#patientBloc').select2({
                placeholder: 'Patient…', minimumInputLength: 2,
                ajax: { url: "{{ route('patients.search') }}", dataType: 'json', delay: 250, data: (p) => ({ q: p.term }),
                    processResults: (data) => ({ results: data.map(p => ({ id: p.id, text: `${p.nom} ${p.prenom} — ${p.numero_dossier ?? ''}` })) }) }
            });
        });
    </script>
@endsection
