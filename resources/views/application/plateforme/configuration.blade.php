@extends('layouts.admin')

@section('title_page', 'Configuration')

@section('content')
    <div class="content">
        <div class="row">
        @include('application.plateforme._menu')
        <div class="col-xl-9 col-lg-8">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h2 class="h3 fw-bold mb-0"><i class="fa fa-sliders-h me-2"></i>{{ $etablissement->nom }}</h2>
                    <div class="text-muted small">Type, modules, règles de fonctionnement et serveur en ligne de l'établissement. Son propriétaire ne gère que son identité (nom, coordonnées, logo).</div>
                </div>
                <a href="{{ route('plateforme.index') }}" class="btn btn-alt-secondary"><i class="fa fa-arrow-left me-1"></i> Établissements</a>
            </div>

            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            <form action="{{ route('plateforme.configuration.update', $etablissement) }}" method="POST">
                @csrf
                @method('PUT')
                @include('application.parametre._configuration')
                <div class="text-end mb-4">
                    <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Enregistrer la configuration</button>
                </div>
            </form>

            @include('application.parametre._serveur_en_ligne')
        </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            // Historique masqué par défaut ; le choix de l'utilisateur est retenu sur ce poste
            const cleHistorique = 'gsante.etablissement.historique';
            let historiqueVisible = false;
            try { historiqueVisible = localStorage.getItem(cleHistorique) === '1'; } catch (e) {}
            $('#afficherHistorique').prop('checked', historiqueVisible);
            $('#historiqueContenu').toggleClass('d-none', !historiqueVisible);
            $('#afficherHistorique').on('change', function () {
                $('#historiqueContenu').toggleClass('d-none', !this.checked);
                try { localStorage.setItem(cleHistorique, this.checked ? '1' : '0'); } catch (e) {}
            });

            const profils = @json($profils);

            // Changer de type pré-coche les modules du profil
            $('.type-etablissement').on('change', function () {
                const modules = profils[$(this).val()] || [];
                $('.module-etablissement').each(function () {
                    $(this).prop('checked', modules.includes($(this).val()));
                });
            });
        });
    </script>
@endsection
