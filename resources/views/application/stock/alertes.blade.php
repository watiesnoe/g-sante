@extends('layouts.app')

@section('title_page', 'Alertes de stock')

@section('content')
    <div class="content">
        <h2 class="h3 fw-bold mb-3"><i class="fa fa-exclamation-triangle text-warning me-2"></i>Alertes de stock</h2>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default bg-danger-light">
                        <h3 class="block-title">Lots périmés encore en stock ({{ $perimes->count() }})</h3>
                    </div>
                    <div class="block-content">
                        <p class="small text-muted">Ces lots ne peuvent plus être dispensés : à retirer par inventaire (perte).</p>
                        @include('application.stock.partials.lots', ['lots' => $perimes])
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default bg-warning-light">
                        <h3 class="block-title">Péremption dans moins de {{ $jours }} jours ({{ $bientot->count() }})</h3>
                    </div>
                    <div class="block-content">
                        @include('application.stock.partials.lots', ['lots' => $bientot])
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Ruptures ({{ $ruptures->count() }})</h3>
                    </div>
                    <div class="block-content">
                        @include('application.stock.partials.medicaments', ['medicaments' => $ruptures])
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="block block-rounded h-100">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Sous le seuil d'alerte ({{ $sousSeuil->count() }})</h3>
                    </div>
                    <div class="block-content">
                        @include('application.stock.partials.medicaments', ['medicaments' => $sousSeuil])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
