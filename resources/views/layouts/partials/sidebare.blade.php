<nav id="sidebar" aria-label="Main Navigation">
    <!-- En-tête -->
    <div class="bg-header-dark">
        <div class="content-header bg-white-5">
            <a class="fw-semibold text-white tracking-wide d-flex align-items-center" href="{{ route('dashboard') }}">
                <img src="{{ asset('image/logo/logo.png') }}" class="smini-visible" style="max-height: 30px; border-radius: 50%;" alt="Logo">
                <img src="{{ asset('image/logo/logo.png') }}" class="smini-hidden ms-2 px-2 py-1 rounded" style="max-height: 50px; background-color: transparent;" alt="Mali Kènèya Hub">
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

                {{-- ✅ Si SUPADMIN : affiche tout --}}
                @if(auth()->user()->role === 'superadmin')

                    @include('layouts.partials.menus.patient')
                    @include('layouts.partials.menus.ticket')
                    @include('layouts.partials.menus.consultation')
                    @include('layouts.partials.menus.rendezvous')
                    @include('layouts.partials.menus.ordonnance')
                    @include('layouts.partials.menus.examens')
                    @include('layouts.partials.menus.hospitalisation')
                    @include('layouts.partials.menus.stock')
                    @include('layouts.partials.menus.paiements')
                    @include('layouts.partials.menus.caisse')
                    @include('layouts.partials.menus.maternity')
                    @include('layouts.partials.menus.infectiologie')
                    @include('layouts.partials.menus.transfert')

                    <!-- Paramètres -->
                    <li class="nav-main-item">
                        <a class="nav-main-link" href="{{ route('services.index') }}">
                            <i class="nav-main-link-icon fa fa-cogs"></i>
                            <span class="nav-main-link-name">Paramètres</span>
                        </a>
                    </li>

                    {{-- ✅ Si SECRÉTAIRE : seulement Tickets + Interface de gestion patient --}}
                @elseif(auth()->user()->role === 'secretaire')

{{--                    @include('layouts.partials.menus.patient')--}}
                    @include('layouts.partials.menus.ticket')
{{--                    @include('layouts.partials.menus.rendezvous')--}}
                    @include('layouts.partials.menus.hospitalisation')
                    {{-- ✅ Si DOCTEUR : consultations + ordonnances + examens + hospitalisation --}}
                @elseif(auth()->user()->role === 'docteur'|| auth()->user()->role === 'medecin')

                    @include('layouts.partials.menus.consultation')
                    @include('layouts.partials.menus.ordonnance')
                    @include('layouts.partials.menus.examens')
                    @include('layouts.partials.menus.hospitalisation')
                    @include('layouts.partials.menus.maternity')
                    @include('layouts.partials.menus.infectiologie')
                    @include('layouts.partials.menus.transfert')

               @elseif(auth()->user()->role === 'pharmacien')


                    @include('layouts.partials.menus.ordonnance')
                    @include('layouts.partials.menus.hospitalisation')
                    @include('layouts.partials.menus.stock')
                    @include('layouts.partials.menus.paiements')
                    @include('layouts.partials.menus.caisse')
                @endif

            </ul>
        </div>
    </div>
</nav>
