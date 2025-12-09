<div class="col-xl-3 col-lg-4 mb-4">
    <div class="block block-rounded h-auto mb-0">
        <div class="block-header block-header-default">
            <h3 class="block-title">⚙️ Menu</h3>
        </div>
        <div class="block-content">
            <ul class="nav nav-pills flex-column push">
                <li class="nav-item mb-1 ">
                    <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                       href="{{ route('services.index') }}">
                        <i class="fa fa-stethoscope me-1"></i> Structures
                    </a>
                </li>
                <li class="nav-item mb-1 ">
                    <a class="nav-link {{ request()->routeIs('prestations.*') ? 'active' : '' }}"
                       href="{{ route('prestations.index') }}">
                        <i class="fa fa-stethoscope me-1"></i> Services
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                       href="{{ route('users.index') }}">
                        <i class="fa fa-users me-1"></i> Utilisateurs
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('salles.*') ? 'active' : '' }}"
                       href="{{ route('salles.index') }}">
                        <i class="fa fa-door-open me-1"></i> Salles
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('lits.*') ? 'active' : '' }}"
                       href="{{ route('lits.index') }}">
                        <i class="fa fa-bed me-1"></i> Lits
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('examens.*') ? 'active' : '' }}"
                       href="{{ route('examens.index') }}">
                        <i class="fa fa-vials me-1"></i> Examens
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('unites.*') ? 'active' : '' }}"
                       href="{{ route('unites.index') }}">
                        <i class="fa fa-layer-group me-1"></i> Unités
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('familles.*') ? 'active' : '' }}"
                       href="{{ route('familles.index') }}">
                        <i class="fa fa-users-cog me-1"></i> Famille
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('specialites.*') ? 'active' : '' }}"
                       href="#">
                        <i class="fa fa-user-md me-1"></i> Spécialités
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('securite.*') ? 'active' : '' }}"
                       href="#">
                        <i class="fa fa-id-card me-1"></i> Sécurité sociale
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('symptomes.*') ? 'active' : '' }}"
                       href="{{ route('symptomes.index') }}">
                        <i class="fa fa-notes-medical me-1"></i> Symptômes
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('maladies.*') ? 'active' : '' }}"
                       href="{{ route('maladies.index') }}">
                        <i class="fa fa-virus me-1"></i> Maladies
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
