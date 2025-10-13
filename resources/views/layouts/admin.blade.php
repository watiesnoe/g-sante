<!doctype html>
<html lang="fr" class="remember-theme">
<head>
    @include('layouts.partials.head')
    <style>
        /* Administration de la plateforme : barre latérale sombre, réduite aux icônes */
        #page-container.admin-plateforme #sidebar { width: 76px; }
        @media (min-width: 992px) {
            #page-container.admin-plateforme.sidebar-o #page-header, #page-container.admin-plateforme.sidebar-o #main-container,
            #page-container.admin-plateforme.sidebar-o #page-footer { padding-left: 76px !important; }
            /* Le gabarit réserve 240 px pour sa grande barre latérale : ici, seule la barre d'icônes (76 px) */
            #page-container.admin-plateforme.sidebar-o { padding-left: 0 !important; }
        }
        @media (max-width: 991.98px) {
            #page-container.admin-plateforme.sidebar-o #page-header, #page-container.admin-plateforme.sidebar-o #main-container,
            #page-container.admin-plateforme.sidebar-o #page-footer { padding-left: 0; }
        }
        .admin-rail { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 14px 0; height: 100%; }
        .admin-rail a { width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; border-radius: 10px;
            color: rgba(255,255,255,.65); font-size: 1.15rem; text-decoration: none; transition: background .15s, color .15s; }
        .admin-rail a:hover, .admin-rail a.active { background: rgba(255,255,255,.12); color: #fff; }
        .admin-rail .logo { margin-bottom: 14px; }
        .admin-rail .bas { margin-top: auto; }
        .admin-fil { font-size: .95rem; }
        /* Contenu sur toute la largeur, menu Administration collé à la barre d'icônes */
        .admin-plateforme #main-container .content { max-width: none; }
    </style>
</head>
<body>
@php
    $retardsAdmin = \App\Models\Abonnement::whereIn('statut', ['grace', 'restreint'])->count();
    $sectionAdmin = request()->routeIs('plateforme.tableau') ? 'tableau'
        : (request()->routeIs('plateforme.index', 'plateforme.create', 'plateforme.formules', 'plateforme.recu') ? 'espaces' : 'acces');
@endphp
<div id="page-container" class="admin-plateforme sidebar-o sidebar-dark enable-page-overlay side-scroll page-header-fixed">

    <nav id="sidebar" aria-label="Administration de la plateforme" class="bg-primary-darker">
        <div class="admin-rail">
            <a href="{{ route('plateforme.tableau') }}" class="logo" title="Administration G-SANTÉ"><i class="fa fa-cubes fa-lg text-white"></i></a>
            <a href="{{ route('plateforme.tableau') }}" class="{{ $sectionAdmin === 'tableau' ? 'active' : '' }}" title="Tableau de bord" data-bs-toggle="tooltip" data-bs-placement="right"><i class="fa fa-gauge-high"></i></a>
            <a href="{{ route('plateforme.index') }}" class="{{ $sectionAdmin === 'espaces' ? 'active' : '' }}" title="Établissements et abonnements" data-bs-toggle="tooltip" data-bs-placement="right"><i class="fa fa-layer-group"></i></a>
            <a href="{{ route('plateforme.proprietaires') }}" class="{{ $sectionAdmin === 'acces' ? 'active' : '' }}" title="Gestion des accès" data-bs-toggle="tooltip" data-bs-placement="right"><i class="fa fa-shield-halved"></i></a>
            <a href="{{ route('dashboard') }}" class="bas" title="Retour à l'application de l'établissement" data-bs-toggle="tooltip" data-bs-placement="right"><i class="fa fa-hospital"></i></a>
        </div>
    </nav>

    <header id="page-header">
        <div class="content-header">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-alt-secondary d-lg-none" data-toggle="layout" data-action="sidebar_toggle"><i class="fa fa-fw fa-bars"></i></button>
                <a href="{{ route('plateforme.tableau') }}" class="admin-fil text-body">Accueil</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('plateforme.tableau') }}" class="btn btn-sm btn-alt-secondary position-relative" title="Abonnements en retard">
                    <i class="fa fa-fw fa-bell"></i>
                    @if($retardsAdmin)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $retardsAdmin }}</span>@endif
                </a>
                <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="layout" data-action="dark_mode_toggle" title="Mode sombre"><i class="fa fa-fw fa-moon"></i></button>
                <button type="button" class="btn btn-sm btn-alt-secondary" id="pleinEcran" title="Plein écran"><i class="fa fa-fw fa-expand"></i></button>
                <div class="dropdown d-inline-block">
                    <button type="button" class="btn btn-sm btn-alt-secondary d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width:26px;height:26px;font-size:.8rem">{{ mb_strtoupper(mb_substr(auth()->user()->prenom ?? auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-sm-inline">{{ auth()->user()->prenom ?? auth()->user()->name }}</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0">
                        <div class="px-3 py-2 border-bottom small"><strong>{{ auth()->user()->name }}</strong><div class="text-muted">Administrateur de la plateforme</div></div>
                        <div class="p-2">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="far fa-fw fa-user me-1"></i> Mon profil</a>
                            <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fa fa-fw fa-hospital me-1"></i> Application de l'établissement</a>
                            <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('form-deconnexion-admin').submit();"><i class="fa fa-fw fa-sign-out-alt me-1"></i> Déconnexion</a>
                            <form id="form-deconnexion-admin" action="{{ url('/logout') }}" method="POST" class="d-none">@csrf</form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main id="main-container">
        <div class="d-flex justify-content-end px-4 pt-3 small text-muted">/ @yield('title_page', 'Administration')</div>
        @yield('content')
    </main>

    @include('layouts.partials.footer')
</div>

@include('layouts.partials.js')
<script src="{{ asset('admin/js/crud-helper.js') }}"></script>
<script>
    $(function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        $('#pleinEcran').on('click', function () {
            document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen?.();
        });
        // Tableaux de l'administration : affichage par page, recherche et tri (comme « Show 10 entries »)
        $('table.table-admin').each(function () {
            if ($.fn.DataTable && !$.fn.DataTable.isDataTable(this) && $(this).find('tbody tr').length > 0 && !$(this).find('tbody td[colspan]').length) {
                $(this).DataTable({
                    pageLength: 10, lengthMenu: [10, 25, 50, 100], order: [], autoWidth: false,
                    language: {
                        lengthMenu: 'Afficher _MENU_ lignes', search: 'Rechercher :', info: '_START_ à _END_ sur _TOTAL_',
                        infoEmpty: 'Aucune ligne', infoFiltered: '(filtré sur _MAX_)', zeroRecords: 'Aucun résultat',
                        paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                    },
                });
            }
        });
    });
</script>
@yield('scripts')
</body>
</html>
