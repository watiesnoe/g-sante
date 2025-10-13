<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-folder-open"></i>
        <span class="nav-main-link-name">Dossiers Patients</span>
    </a>
    <ul class="nav-main-submenu">
        @can('accueil.view')
        <li class="nav-main-item">
            <a class="nav-main-link" href="{{ route('passages.index') }}">
                <span class="nav-main-link-name">Accueil / file d'attente</span>
            </a>
        </li>
        @endcan
        <li class="nav-main-item">
            <a class="nav-main-link" href="{{ route('patients.index') }}">
                <span class="nav-main-link-name">Liste des Patients</span>
            </a>
        </li>
    </ul>
</li>
