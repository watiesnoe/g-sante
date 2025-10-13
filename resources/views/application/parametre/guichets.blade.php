@extends('layouts.app')

@section('titre')
    ⚙️ Configuration - Guichets de caisse
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row">
            @include('layouts.partials.configside')
            <div class="col-xl-9 col-lg-8">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="block block-rounded">
                    <div class="block-header block-header-default"><h3 class="block-title">Guichets de caisse</h3></div>
                    <div class="block-content">
                        @unless(\App\Models\Etablissement::courant()->parametre('caisse.multi_guichets'))
                            <div class="alert alert-info small">La règle « Plusieurs guichets de caisse » est désactivée : les guichets ne sont pas demandés à l'ouverture.</div>
                        @endunless
                        <form method="POST" action="{{ route('guichets.store') }}" class="d-flex gap-2 mb-3">
                            @csrf
                            <input type="text" name="nom" class="form-control" placeholder="Ex. Guichet consultations, Guichet pharmacie" required>
                            <button class="btn btn-primary text-nowrap">Ajouter</button>
                        </form>
                        <table class="table table-sm table-bordered">
                            @forelse($guichets as $g)
                                <tr>
                                    <td>{{ $g->nom }}</td>
                                    <td class="text-center">{!! $g->actif ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-secondary">Inactif</span>' !!}</td>
                                    <td class="text-end"><form method="POST" action="{{ route('guichets.basculer', $g) }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ $g->actif ? 'Désactiver' : 'Activer' }}</button></form></td>
                                </tr>
                            @empty
                                <tr><td class="text-muted">Aucun guichet.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
