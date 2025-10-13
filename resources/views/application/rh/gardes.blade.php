@extends('layouts.app')

@section('title_page', 'Gardes')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-user-clock me-2"></i>Gardes du {{ $debut->format('d/m') }} au {{ $fin->format('d/m/Y') }}</h2>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('gardes.index', ['semaine' => $debut->copy()->subWeek()->toDateString()]) }}">‹ Semaine précédente</a>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('gardes.index', ['semaine' => $debut->copy()->addWeek()->toDateString()]) }}">Semaine suivante ›</a>
            </div>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="row g-3">
            <div class="col-xl-8">
                @for($jour = $debut->copy(); $jour->lte($fin); $jour->addDay())
                    @php $duJour = $gardes->filter(fn($g) => $g->debut->lte($jour->copy()->endOfDay()) && $g->fin->gte($jour->copy()->startOfDay())); @endphp
                    <div class="block block-rounded mb-2">
                        <div class="block-content py-2">
                            <strong class="d-inline-block" style="width:140px">{{ $jour->translatedFormat('l d/m') }}</strong>
                            @forelse($duJour as $g)
                                <span class="badge bg-{{ $g->type === 'garde' ? 'primary' : ($g->type === 'astreinte' ? 'warning' : 'info') }} me-1">
                                    {{ $g->user->name ?? '?' }} — {{ \App\Models\Garde::TYPES[$g->type] }} {{ $g->debut->format('H:i') }}→{{ $g->fin->format('H:i') }}{{ $g->service ? ' — ' . $g->service->nom : '' }}
                                </span>
                            @empty
                                <span class="text-muted small">Personne de garde</span>
                            @endforelse
                        </div>
                    </div>
                @endfor
            </div>
            @can('rh.gardes.gerer')
            <div class="col-xl-4">
                <form method="POST" action="{{ route('gardes.store') }}" class="block block-rounded">
                    @csrf
                    <div class="block-header block-header-default"><h3 class="block-title">Programmer une garde</h3></div>
                    <div class="block-content">
                        <div class="mb-2"><select name="user_id" class="form-select js-select2" required style="width:100%"><option value="">Personne…</option>@foreach($personnel as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
                        <div class="mb-2"><select name="type" class="form-select no-select2">@foreach(\App\Models\Garde::TYPES as $c => $l)<option value="{{ $c }}">{{ $l }}</option>@endforeach</select></div>
                        <div class="mb-2"><select name="service_medical_id" class="form-select no-select2"><option value="">Service…</option>@foreach($services as $s)<option value="{{ $s->id }}">{{ $s->nom }}</option>@endforeach</select></div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><label class="form-label small">Début</label><input type="datetime-local" name="debut" class="form-control" required></div>
                            <div class="col-6"><label class="form-label small">Fin</label><input type="datetime-local" name="fin" class="form-control" required></div>
                        </div>
                        <div class="mb-3"><input type="text" name="observation" class="form-control" placeholder="Observation"></div>
                        <div class="text-end mb-3"><button class="btn btn-primary">Enregistrer</button></div>
                    </div>
                </form>
            </div>
            @endcan
        </div>
    </div>
@endsection
