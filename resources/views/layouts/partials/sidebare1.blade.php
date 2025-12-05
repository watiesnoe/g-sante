<nav id="sidebar" aria-label="Main Navigation">
    <!-- Side Header -->
    <div class="bg-header-dark">
        <div class="content-header bg-white-5">
            <a class="fw-semibold text-white tracking-wide" href="{{route('dashboard')}}">
                <span class="smini-visible">HC</span>
                <span class="smini-hidden">Health<span class="opacity-75">Care</span></span>
            </a>
            <div class="d-flex align-items-center gap-1">
                <!-- Dark Mode Dropdown -->
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
                <!-- Mobile Close -->
                <button class="btn btn-sm btn-alt-secondary d-lg-none" data-toggle="layout" data-action="sidebar_close">
                    <i class="fa fa-times-circle"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Sidebar Scrolling -->
    <div class="js-sidebar-scroll">
        <div class="content-side">
            <ul class="nav-main">

                <!-- Dashboard -->
                <li class="nav-main-item">
                    <a class="nav-main-link active" href="{{route('dashboard')}}">
                        <i class="nav-main-link-icon fa fa-home"></i>
                        <span class="nav-main-link-name">Dashboard</span>
                    </a>
                </li>

                <!-- Dossiers Patients -->


                <!-- Tickets -->

                <!-- Consultations -->

                <!-- Rendez-vous -->


                <!-- Ordonnances -->


                <!-- Examens -->


                <!-- Hospitalisation -->


                <!-- Stock & Fournisseurs -->


                <!-- Paiements -->


                <!-- Paramètres -->
                <li class="nav-main-item">
                    <a class="nav-main-link" href="{{route('services.index')}}">
                        <i class="nav-main-link-icon fa fa-cogs"></i>
                        <span class="nav-main-link-name">Paramètres</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>
