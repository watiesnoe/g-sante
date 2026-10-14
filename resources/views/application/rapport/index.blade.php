@extends('layouts.app')

@section('title_page', 'Rapports')

@section('content')
    <div class="content">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-chart-bar me-2"></i>Rapport du {{ $rapport['du']->format('d/m/Y') }} au {{ $rapport['au']->format('d/m/Y') }}</h2>
            <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                <input type="date" name="du" class="form-control form-control-sm" value="{{ $rapport['du']->toDateString() }}">
                <input type="date" name="au" class="form-control form-control-sm" value="{{ $rapport['au']->toDateString() }}">
                <select name="service_id" class="form-select form-select-sm no-select2" style="width:auto">
                    <option value="">Tous les services</option>
                    @foreach($services as $s)<option value="{{ $s->id }}" {{ $serviceId == $s->id ? 'selected' : '' }}>{{ $s->nom }}</option>@endforeach
                </select>
                <button class="btn btn-sm btn-primary">Afficher</button>
                <button name="format" value="xlsx" class="btn btn-sm btn-outline-success"><i class="fa fa-file-excel"></i> Excel</button>
                <button name="format" value="pdf" class="btn btn-sm btn-outline-danger"><i class="fa fa-file-pdf"></i> PDF</button>
            </form>
        </div>
        <div class="block block-rounded"><div class="block-content pb-3">
            @include('application.rapport._contenu')
        </div></div>
    </div>
@endsection
