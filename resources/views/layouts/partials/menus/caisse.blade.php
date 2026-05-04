<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true" aria-expanded="false" href="#">
        <i class="nav-main-link-icon fa fa-cash-register"></i>
        <span class="nav-main-link-name">Caisse</span>
    </a>
    <ul class="nav-main-submenu">
        <li class="nav-main-item">
            <a class="nav-main-link" href="{{ route('caisse.my_session') }}">
                <span class="nav-main-link-name">Ma Caisse</span>
            </a>
        </li>
        @hasrole('admin|super_admin')
        <li class="nav-main-item">
            <a class="nav-main-link" href="{{ route('caisse.index') }}">
                <span class="nav-main-link-name">Supervision Caisses</span>
            </a>
        </li>
        @endif
    </ul>
</li>
