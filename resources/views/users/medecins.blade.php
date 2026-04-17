@extends('layouts.app')

@section('titre', 'Liste des Médecins Actifs')

@section('content')
<div class="container mt-4">
    <div class="row">
        <!-- Sidebar gauche -->
        @include('layouts.partials.configside')

        <!-- Contenu principal -->
        <div class="col-xl-9 col-lg-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Liste des Médecins Actifs</h3>
                </div>
                <div class="block-content">
                    <form method="GET" action="{{ route('medecins.index') }}" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Rechercher un médecin..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Rechercher
                            </button>
                        </div>
                    </form>

                    <div class="row items-push">
                        @forelse($medecins as $medecin)
                        <div class="col-md-6 col-xl-4">
                            <div class="block block-rounded text-center shadow-sm h-100 mb-0">
                                <div class="block-content block-content-full bg-body-light border-bottom">
                                    <img class="img-avatar img-avatar-thumb" src="{{ $medecin->photo ? asset('storage/'.$medecin->photo) : 'https://ui-avatars.com/api/?name='.urlencode($medecin->prenom.' '.$medecin->nom).'&background=random' }}" alt="">
                                </div>
                                <div class="block-content block-content-full">
                                    <div class="fw-semibold">{{ $medecin->prenom }} {{ $medecin->nom }}</div>
                                    <div class="fs-sm text-muted">{{ $medecin->email }}</div>
                                    <div class="fs-sm text-muted mt-2">
                                        <i class="fa fa-phone me-1"></i> {{ $medecin->telephone ?? 'Non renseigné' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center py-5">
                            <p class="text-muted">Aucun médecin trouvé.</p>
                        </div>
                        @endforelse
                    </div>
                    
                    <div class="mt-4">
                        {{ $medecins->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
