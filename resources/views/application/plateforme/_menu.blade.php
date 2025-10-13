{{-- Menu de l'espace d'administration de la plateforme (super-admin) --}}
<div class="col-xl-3 col-lg-4 mb-4">
    <div class="block block-rounded h-auto mb-0">
        <div class="block-header bg-primary-dark">
            <h3 class="block-title text-white"><i class="fa fa-cogs me-1"></i> Administration</h3>
        </div>
        <div class="block-content">
            <ul class="nav nav-pills flex-column push">
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('plateforme.tableau') ? 'active' : '' }}" href="{{ route('plateforme.tableau') }}"><i class="fa fa-chart-line me-1"></i> Tableau de bord</a></li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('plateforme.index', 'plateforme.create') ? 'active' : '' }}" href="{{ route('plateforme.index') }}"><i class="fa fa-hospital me-1"></i> Établissements</a></li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('plateforme.formules') ? 'active' : '' }}" href="{{ route('plateforme.formules') }}"><i class="fa fa-layer-group me-1"></i> Formules d'abonnement</a></li>

                <li class="nav-main-heading text-muted small text-uppercase mt-3 mb-1 px-2">Gestion des accès</li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('plateforme.proprietaires') ? 'active' : '' }}" href="{{ route('plateforme.proprietaires') }}"><i class="fa fa-user-tie me-1"></i> Propriétaires</a></li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('plateforme.administrateurs') ? 'active' : '' }}" href="{{ route('plateforme.administrateurs') }}"><i class="fa fa-user-shield me-1"></i> Administrateurs</a></li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}"><i class="fa fa-users-cog me-1"></i> Rôles</a></li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}"><i class="fa fa-key me-1"></i> Permissions</a></li>

                <li class="nav-main-heading text-muted small text-uppercase mt-3 mb-1 px-2">Outils</li>
                <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}"><i class="fa fa-history me-1"></i> Journal d'audit</a></li>
                <li class="nav-item mb-1"><a class="nav-link" target="_blank" href="{{ route('admin.migrations.report-pdf') }}"><i class="fa fa-file-pdf me-1"></i> Rapport des migrations</a></li>
            </ul>
        </div>
    </div>
</div>
