<nav id="sidebar" aria-label="Main Navigation">
    <!-- En-tête -->
    <div class="bg-header-dark">
        <div class="content-header bg-white-5">
            <a class="fw-semibold text-white tracking-wide d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="{{ asset('image/logo/logo1.png') }}" class="smini-hidden ms-2 px-2 py-1 rounded"
                    style="max-height: 50px; background-color: transparent;" alt="Mali Kènèya Hub">
            </a>
            <div class="d-flex align-items-center gap-1">
                <div class="dropdown">
                    <button class="btn btn-sm btn-alt-secondary" data-bs-toggle="dropdown">
                        <i class="far fa-fw fa-moon"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end smini-hide border-0">
                        <button class="dropdown-item" data-toggle="layout" data-action="dark_mode_off">Clair</button>
                        <button class="dropdown-item" data-toggle="layout" data-action="dark_mode_on">Sombre</button>
                        <button class="dropdown-item" data-toggle="layout" data-action="dark_mode_system">Système</button>
                    </div>
                </div>
                <button class="btn btn-sm btn-alt-secondary d-lg-none" data-toggle="layout" data-action="sidebar_close">
                    <i class="fa fa-times-circle"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Contenu -->
    <div class="js-sidebar-scroll">
        <div class="content-side">
            <ul class="nav-main">

                @can('etablissements.gerer')
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('plateforme.*', 'admin.roles.*', 'admin.permissions.*') ? 'active' : '' }}" href="{{ route('plateforme.tableau') }}">
                        <i class="nav-main-link-icon fa fa-cogs"></i>
                        <span class="nav-main-link-name">Administration</span>
                    </a>
                </li>
                @endcan

                <!-- Dashboard visible à tous -->
                <li class="nav-main-item">
                    <a class="nav-main-link active" href="{{ route('dashboard') }}">
                        <i class="nav-main-link-icon fa fa-home"></i>
                        <span class="nav-main-link-name">Dashboard</span>
                    </a>
                </li>

                @can('syntheses.view')
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('syntheses.*') ? 'active' : '' }}" href="{{ route('syntheses.index') }}">
                        <i class="nav-main-link-icon fa fa-route"></i>
                        <span class="nav-main-link-name">Fiches de synthèse</span>
                    </a>
                </li>
                @endcan

                @can('referentiel.view')
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('guide-clinique.*') ? 'active' : '' }}" href="{{ route('guide-clinique.index') }}">
                        <i class="nav-main-link-icon fa fa-stethoscope"></i>
                        <span class="nav-main-link-name">Guide clinique</span>
                    </a>
                </li>
                @endcan

                @can('referentiel.view')
                <li class="nav-main-item">
                    <a class="nav-main-link {{ request()->routeIs('referentiel.*') ? 'active' : '' }}" href="{{ route('referentiel.index') }}">
                        <i class="nav-main-link-icon fa fa-book-medical"></i>
                        <span class="nav-main-link-name">Guide des médicaments</span>
                    </a>
                </li>
                @endcan

                @if(auth()->user()->hasModuleAccess('patient'))
                    @include('layouts.partials.menus.patient')
                @endif

                @if(auth()->user()->hasModuleAccess('ticket'))
                    @include('layouts.partials.menus.ticket')
                @endif

                @if(auth()->user()->hasModuleAccess('urgences'))
                    @include('layouts.partials.menus.urgences')
                @endif

                @if(auth()->user()->hasModuleAccess('references'))
                    @include('layouts.partials.menus.references')
                @endif

                @if(auth()->user()->hasModuleAccess('consultation'))
                    @include('layouts.partials.menus.consultation')
                @endif

                @if(auth()->user()->hasModuleAccess('rendezvous'))
                    @include('layouts.partials.menus.rendezvous')
                @endif

                @if(auth()->user()->hasModuleAccess('ordonnance'))
                    @include('layouts.partials.menus.ordonnance')
                @endif

                @if(auth()->user()->hasModuleAccess('examens'))
                    @include('layouts.partials.menus.examens')
                @endif

                @if(auth()->user()->hasModuleAccess('hospitalisation'))
                    @include('layouts.partials.menus.hospitalisation')
                @endif

                @if(auth()->user()->hasModuleAccess('bloc'))
                    @include('layouts.partials.menus.bloc')
                @endif

                @if(auth()->user()->hasModuleAccess('stock'))
                    @include('layouts.partials.menus.stock')
                @endif

                @if(auth()->user()->hasModuleAccess('paiements'))
                    @include('layouts.partials.menus.paiements')
                @endif

                @if(auth()->user()->can('rapports.mensuel'))
                    <li class="nav-main-item">
                        <a class="nav-main-link" href="{{ route('rapports-mensuels.index') }}">
                            <i class="nav-main-link-icon fa fa-file-medical-alt"></i>
                            <span class="nav-main-link-name">Rapports mensuels</span>
                        </a>
                    </li>
                @endif

                @can('rapports.view')
                    <li class="nav-main-item">
                        <a class="nav-main-link" href="{{ route('rapports.index') }}">
                            <i class="nav-main-link-icon fa fa-chart-bar"></i>
                            <span class="nav-main-link-name">Rapports</span>
                        </a>
                    </li>
                @endcan

                @if(auth()->user()->hasModuleAccess('maintenance'))
                    @include('layouts.partials.menus.maintenance')
                @endif

                @if(auth()->user()->hasModuleAccess('rh'))
                    @include('layouts.partials.menus.rh')
                @endif

                @if(auth()->user()->hasModuleAccess('tiers_payants'))
                    @include('layouts.partials.menus.tiers_payants')
                @endif

                @if(auth()->user()->hasModuleAccess('finances'))
                    @include('layouts.partials.menus.finances')
                @endif

                @if(auth()->user()->hasModuleAccess('caisse'))
                    @include('layouts.partials.menus.caisse')
                @endif

                @if(auth()->user()->hasModuleAccess('maternity'))
                    @include('layouts.partials.menus.maternity')
                @endif

                @if(auth()->user()->hasModuleAccess('infectiologie'))
                    @include('layouts.partials.menus.infectiologie')
                @endif

                @if(auth()->user()->hasModuleAccess('transfert'))
                    @include('layouts.partials.menus.transfert')
                @endif

            

                @if(auth()->user()->hasModuleAccess('parametre'))
                    <!-- Paramètres -->
                    <li class="nav-main-item">
                        <a class="nav-main-link" href="{{ route('services.index') }}">
                            <i class="nav-main-link-icon fa fa-cogs"></i>
                            <span class="nav-main-link-name">Paramètres</span>
                        </a>
                    </li>
                @endif

            </ul>
        </div>
    </div>
</nav>
