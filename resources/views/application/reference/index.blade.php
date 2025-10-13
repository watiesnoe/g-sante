@extends('layouts.app')

@section('title_page', 'Références et évacuations')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-route me-2"></i>Références et évacuations</h2>
            <div class="btn-group">
                <a href="{{ route('references.index') }}" class="btn btn-sm btn-outline-secondary {{ !request('sens') ? 'active' : '' }}">Toutes</a>
                <a href="{{ route('references.index', ['sens' => 'entrante']) }}" class="btn btn-sm btn-outline-secondary {{ request('sens') === 'entrante' ? 'active' : '' }}">Entrantes</a>
                <a href="{{ route('references.index', ['sens' => 'sortante']) }}" class="btn btn-sm btn-outline-secondary {{ request('sens') === 'sortante' ? 'active' : '' }}">Sortantes</a>
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        @can('references.gerer')
        <form method="POST" action="{{ route('references.store') }}" class="block block-rounded">
            @csrf
            <div class="block-header block-header-default"><h3 class="block-title">Nouvelle référence</h3></div>
            <div class="block-content">
                <div class="row g-2 mb-3">
                    <div class="col-md-2"><select name="sens" id="sens" class="form-select no-select2"><option value="entrante">Entrante (reçue)</option><option value="sortante">Sortante (émise)</option></select></div>
                    <div class="col-md-4"><select id="patientRef" name="patient_id" class="form-select" style="width:100%" required></select></div>
                    <div class="col-md-3 champ-origine"><input type="text" name="structure_origine" class="form-control" placeholder="Structure d'origine (ex. CSCom de …)"></div>
                    <div class="col-md-3 champ-destination d-none"><input type="text" name="structure_destination" class="form-control" placeholder="Structure de destination"></div>
                    <div class="col-md-3"><input type="datetime-local" name="date_reference" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                    <div class="col-md-6"><textarea name="motif" class="form-control" rows="2" placeholder="Motif de la référence" required></textarea></div>
                    <div class="col-md-6"><textarea name="soins_realises" class="form-control" rows="2" placeholder="Soins réalisés avant la référence"></textarea></div>
                    <div class="col-md-3"><select name="transport" class="form-select no-select2"><option value="">Transport…</option>@foreach(\App\Models\Reference::TRANSPORTS as $c => $l)<option value="{{ $c }}">{{ $l }}</option>@endforeach</select></div>
                    <div class="col-md-3 form-check pt-2 ms-2"><input type="hidden" name="evacuation" value="0"><input class="form-check-input" type="checkbox" name="evacuation" value="1" id="evac"><label class="form-check-label" for="evac">Évacuation sanitaire</label></div>
                    <div class="col-md-2 ms-auto"><button class="btn btn-primary w-100">Enregistrer</button></div>
                </div>
            </div>
        </form>
        @endcan

        <div class="block block-rounded"><div class="block-content block-content-full">
            <table class="table table-sm table-bordered table-vcenter">
                <thead><tr><th>N°</th><th>Date</th><th>Sens</th><th>Patient</th><th>De → vers</th><th>Motif</th><th>Statut</th><th>Devenir</th><th class="text-center">Actions</th></tr></thead>
                <tbody>
                    @forelse($references as $r)
                        <tr>
                            <td class="text-nowrap">{{ $r->numero }}@if($r->evacuation)<br><span class="badge bg-danger">évacuation</span>@endif</td>
                            <td>{{ $r->date_reference->format('d/m/Y H:i') }}</td>
                            <td>{{ $r->sens === 'entrante' ? 'Entrante' : 'Sortante' }}</td>
                            <td>{{ mb_strtoupper($r->patient->nom ?? '') }} {{ $r->patient->prenom ?? '' }}</td>
                            <td class="small">{{ $r->structure_origine }} → {{ $r->structure_destination }}</td>
                            <td class="small">{{ \Illuminate\Support\Str::limit($r->motif, 60) }}</td>
                            <td>{{ \App\Models\Reference::STATUTS[$r->statut] ?? $r->statut }}</td>
                            <td class="small">
                                {{ $r->devenir ?? '—' }}
                                @can('references.gerer')
                                    <form method="POST" action="{{ route('references.devenir', $r) }}" class="mt-1" onsubmit="this.devenir.value = prompt('Devenir du patient :', this.devenir.value) || ''; return this.devenir.value !== '';">@csrf<input type="hidden" name="devenir" value="{{ $r->devenir }}"><button class="btn btn-link btn-sm p-0">Renseigner</button></form>
                                @endcan
                            </td>
                            <td class="text-center">
                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                    <a href="{{ route('references.fiche', [$r, 'reference']) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Fiche de référence"><i class="fa fa-file-pdf"></i></a>
                                    @if($r->contre_reference)<a href="{{ route('references.fiche', [$r, 'contre-reference']) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Contre-référence"><i class="fa fa-reply"></i></a>@endif
                                    @can('references.gerer')
                                        @if($r->statut === 'emise')
                                            <form method="POST" action="{{ route('references.etape', $r) }}">@csrf<input type="hidden" name="vers" value="recue"><button class="btn btn-sm btn-outline-primary">Reçue</button></form>
                                        @elseif($r->statut === 'recue')
                                            <form method="POST" action="{{ route('references.etape', $r) }}">@csrf<input type="hidden" name="vers" value="prise_en_charge"><button class="btn btn-sm btn-outline-primary">Prise en charge</button></form>
                                        @elseif($r->statut === 'prise_en_charge' && $r->sens === 'entrante')
                                            <form method="POST" action="{{ route('references.etape', $r) }}" onsubmit="this.contre_reference.value = prompt('Contre-référence (diagnostic, traitement, suite à donner) :') || ''; return this.contre_reference.value !== '';">@csrf<input type="hidden" name="vers" value="contre_reference_envoyee"><input type="hidden" name="contre_reference"><button class="btn btn-sm btn-success">Contre-référence</button></form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-muted">Aucune référence.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div></div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            $('#patientRef').select2({
                placeholder: 'Patient…', minimumInputLength: 2,
                ajax: { url: "{{ route('patients.search') }}", dataType: 'json', delay: 250, data: (p) => ({ q: p.term }),
                    processResults: (data) => ({ results: data.map(p => ({ id: p.id, text: `${p.nom} ${p.prenom} — ${p.numero_dossier ?? ''}` })) }) }
            });
            $('#sens').on('change', function () {
                const entrante = this.value === 'entrante';
                $('.champ-origine').toggleClass('d-none', !entrante);
                $('.champ-destination').toggleClass('d-none', entrante);
            });
        });
    </script>
@endsection
