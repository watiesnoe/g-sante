@extends('layouts.app')

@section('title_page', 'Agenda')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-calendar-alt me-2"></i>Agenda {{ $vue === 'semaine' ? 'du ' . $debut->format('d/m') . ' au ' . $fin->format('d/m/Y') : 'du ' . $date->translatedFormat('l d F Y') }}</h2>
            <form method="GET" class="d-flex flex-wrap gap-2">
                <select name="medecin_id" class="form-select form-select-sm no-select2" style="width:auto" onchange="this.form.submit()">
                    <option value="">Tous les professionnels</option>
                    @foreach($medecins as $m)<option value="{{ $m->id }}" {{ $medecinId == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>@endforeach
                </select>
                <select name="vue" class="form-select form-select-sm no-select2" style="width:auto" onchange="this.form.submit()">
                    <option value="jour" {{ $vue === 'jour' ? 'selected' : '' }}>Jour</option>
                    <option value="semaine" {{ $vue === 'semaine' ? 'selected' : '' }}>Semaine</option>
                </select>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date->toDateString() }}" onchange="this.form.submit()">
                <a href="{{ route('agenda.index', ['medecin_id' => $medecinId, 'vue' => $vue]) }}" class="btn btn-sm btn-outline-primary">Aujourd'hui</a>
            </form>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        @forelse($rendezvous as $jour => $liste)
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">{{ \Illuminate\Support\Carbon::parse($jour)->translatedFormat('l d F') }} — {{ $liste->count() }} rendez-vous</h3>
                </div>
                <div class="block-content">
                    <table class="table table-sm table-vcenter">
                        <tbody>
                            @foreach($liste as $r)
                                <tr>
                                    <td class="fw-bold" style="width:70px">{{ \Illuminate\Support\Carbon::parse($r->date_heure)->format('H:i') }}</td>
                                    <td>{{ mb_strtoupper($r->patient->nom ?? '') }} {{ $r->patient->prenom ?? '' }} <div class="small text-muted">{{ $r->patient->telephone ?? '' }}</div></td>
                                    <td>{{ $r->motif ?? '-' }}</td>
                                    <td>{{ $r->medecin->name ?? '-' }}</td>
                                    <td><span class="badge bg-{{ \App\Models\RendezVous::COULEURS[$r->statut] ?? 'secondary' }}">{{ \App\Models\RendezVous::STATUTS[$r->statut] ?? $r->statut }}</span></td>
                                    <td class="text-end">
                                        @if(in_array($r->statut, ['prevu', 'en_attente']) && auth()->user()->can('rendezvous.confirm'))
                                            @foreach(['realise' => 'success', 'absent' => 'dark', 'annule' => 'outline-danger'] as $s => $c)
                                                <form method="POST" action="{{ route('agenda.statut', $r) }}" class="d-inline">@csrf<input type="hidden" name="statut" value="{{ $s }}"><button class="btn btn-sm btn-{{ $c }}">{{ \App\Models\RendezVous::STATUTS[$s] }}</button></form>
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="block block-rounded"><div class="block-content text-muted pb-3">Aucun rendez-vous sur cette période.</div></div>
        @endforelse
    </div>
@endsection
