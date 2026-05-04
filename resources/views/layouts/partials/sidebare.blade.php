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

                <!-- Dashboard visible à tous -->
                <li class="nav-main-item">
                    <a class="nav-main-link active" href="{{ route('dashboard') }}">
                        <i class="nav-main-link-icon fa fa-home"></i>
                        <span class="nav-main-link-name">Dashboard</span>
                    </a>
                </li>

                @if(auth()->user()->hasModuleAccess('patient'))
                    @include('layouts.partials.menus.patient')
                @endif
                @if(auth()->user()->hasModuleAccess('ticket'))
                    @include('layouts.partials.menus.ticket')
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
                @if(auth()->user()->hasModuleAccess('stock'))
                    @include('layouts.partials.menus.stock')
                @endif
                @if(auth()->user()->hasModuleAccess('paiements'))
                    @include('layouts.partials.menus.paiements')
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
