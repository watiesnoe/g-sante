@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Établissements
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')

            <div class="col-xl-9 col-lg-8">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="block block-rounded">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Établissements</h3>
                        <a href="{{ route('etablissements.create') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus me-1"></i> Nouvel établissement</a>
                    </div>
                    <div class="block-content block-content-full">
                        <table class="table table-bordered table-striped table-vcenter">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>District</th>
                                    <th class="text-center">Comptes</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($etablissements as $e)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $e->code }}</span></td>
                                        <td class="fw-semibold">
                                            {{ $e->nom }}
                                            @if($e->id === \App\Models\Etablissement::courantId())<span class="badge bg-info ms-1">courant</span>@endif
                                        </td>
                                        <td>{{ \App\Models\Etablissement::TYPES[$e->type] ?? $e->type }}</td>
                                        <td>{{ $e->district ?? '-' }}</td>
                                        <td class="text-center">{{ $e->utilisateurs_count }}</td>
                                        <td class="text-center">{!! $e->actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="text-muted small mb-0">Pour configurer un établissement (modules, règles, coordonnées), sélectionnez-le dans la barre du haut puis ouvrez Paramètres → Établissement.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
