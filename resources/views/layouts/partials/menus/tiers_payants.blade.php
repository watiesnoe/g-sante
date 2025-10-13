<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-handshake"></i>
        <span class="nav-main-link-name">Tiers payants</span>
    </a>
    <ul class="nav-main-submenu">
        @can('tiers_payants.conventions')
            <li class="nav-main-item"><a class="nav-main-link" href="{{ route('conventions.index') }}"><span class="nav-main-link-name">Conventions</span></a></li>
        @endcan
        @can('tiers_payants.bordereaux')
            <li class="nav-main-item"><a class="nav-main-link" href="{{ route('bordereaux.index') }}"><span class="nav-main-link-name">Bordereaux et créances</span></a></li>
        @endcan
    </ul>
</li>
