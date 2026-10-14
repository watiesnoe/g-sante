@extends('layouts.app')

@section('title_page', 'Indicateurs hospitaliers')

@section('content')
    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h3 fw-bold mb-0"><i class="fa fa-chart-line me-2"></i>Indicateurs hospitaliers</h2>
            <form method="GET" class="d-flex gap-2">
                <input type="date" name="du" class="form-control form-control-sm" value="{{ $du->toDateString() }}">
                <input type="date" name="au" class="form-control form-control-sm" value="{{ $au->toDateString() }}">
                <button class="btn btn-sm btn-primary">Calculer</button>
            </form>
        </div>
        <div class="row g-3">
            @foreach([
                ['Taux d\'occupation', $i['taux_occupation'] . ' %', $i['journees_occupees'] . ' journées / (' . $i['lits'] . ' lits × ' . $i['jours'] . ' jours)'],
                ['Durée moyenne de séjour', $i['duree_moyenne_sejour'] . ' j', $i['sorties'] . ' sortie(s)'],
                ['Admissions', $i['admissions'], 'sur la période'],
                ['Décès', $i['deces'], $i['taux_deces'] . ' % des sorties'],
            ] as [$titre, $valeur, $detail])
                <div class="col-md-3">
                    <div class="block block-rounded text-center py-3 mb-0">
                        <div class="fs-2 fw-bold">{{ $valeur }}</div>
                        <div class="fw-semibold">{{ $titre }}</div>
                        <div class="small text-muted">{{ $detail }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        <p class="small text-muted mt-3">Journée d'hospitalisation : une par nuit passée (au moins une par séjour). Les lits en maintenance ne comptent pas dans la capacité.</p>
    </div>
@endsection
