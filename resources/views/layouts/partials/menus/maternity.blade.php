<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-baby"></i>
        <span class="nav-main-link-name">Maternité</span>
    </a>
    <ul class="nav-main-submenu">
        <li class="nav-main-item">
            <a class="nav-main-link" href="{{ route('maternity.index') }}">
                <span class="nav-main-link-name">Suivi Grossesses</span>
            </a>
        </li>
        <li class="nav-main-item">
            <a class="nav-main-link" href="{{ route('maternity.create') }}">
                <span class="nav-main-link-name">Nouveau Suivi</span>
            </a>
        </li>
    </ul>
</li>
