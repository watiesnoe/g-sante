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
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-folder-open"></i>
                        <span class="nav-main-link-name">Dossiers Patients</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item">
                            <a class="nav-main-link" href="{{ route('patients.index') }}">
                                <span class="nav-main-link-name">Liste des Patients</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Tickets -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-ticket-alt"></i>
                        <span class="nav-main-link-name">Tickets</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('tickets.index')}}"><span class="nav-main-link-name">Liste des Tickets</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('tickets.create')}}"><span class="nav-main-link-name">Nouveau Ticket</span></a></li>
                    </ul>
                </li>

                <!-- Consultations -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-stethoscope"></i>
                        <span class="nav-main-link-name">Consultations</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('consultations.index')}}"><span class="nav-main-link-name">Liste des Consultations</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('consultations.create')}}"><span class="nav-main-link-name">Nouvelle Consultation</span></a></li>
                    </ul>
                </li>

                <!-- Rendez-vous -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-calendar-alt"></i>
                        <span class="nav-main-link-name">Rendez-vous</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('rendezvous.index')}}"><span class="nav-main-link-name">Liste des Rendez-vous</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('rendezvous.disponible')}}"><span class="nav-main-link-name">Rendez-vous realisés</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('rendezvous.annuler')}}"><span class="nav-main-link-name">Rendez-vous annulés</span></a></li>
                    </ul>
                </li>

                <!-- Ordonnances -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-notes-medical"></i>
                        <span class="nav-main-link-name">Ordonnances</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('ordonnances.index')}}"><span class="nav-main-link-name">Ordonnances disponibles</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('ordonnances.lespayer')}}"><span class="nav-main-link-name">Ordonnances payées</span></a></li>
                    </ul>
                </li>

                <!-- Examens -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-vials"></i>
                        <span class="nav-main-link-name">Examens</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('prescriptions.index')}}"><span class="nav-main-link-name">Analyses supplémentaires</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('reponses.index')}}"><span class="nav-main-link-name">Réponses analyses</span></a></li>
                    </ul>
                </li>

                <!-- Hospitalisation -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-procedures"></i>
                        <span class="nav-main-link-name">Hospitalisations</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route("hospitalisations.index")}}"><span class="nav-main-link-name">Liste</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('hospitalisations.realise')}}"><span class="nav-main-link-name">Liste des hospitalisés</span></a></li>
                    </ul>
                </li>

                <!-- Stock & Fournisseurs -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-boxes"></i>
                        <span class="nav-main-link-name">Stock</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('fournisseurs.index')}}"><span class="nav-main-link-name">Fournisseurs</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('medicaments.index')}}"><span class="nav-main-link-name">Médicaments</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('commandes.index')}}"><span class="nav-main-link-name">Commandes</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('receptions.index')}}"><span class="nav-main-link-name">Réceptions</span></a></li>
                    </ul>
                </li>

                <!-- Paiements -->
                <li class="nav-main-item">
                    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
                        <i class="nav-main-link-icon fa fa-credit-card"></i>
                        <span class="nav-main-link-name">Paiements</span>
                    </a>
                    <ul class="nav-main-submenu">
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('paiementscommande.dashboard')}}"><span class="nav-main-link-name">Liste des paiements</span></a></li>
                        <li class="nav-main-item"><a class="nav-main-link" href="{{route('paiementscommande.create')}}"><span class="nav-main-link-name">Faire paiement</span></a></li>
                    </ul>
                </li>

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
