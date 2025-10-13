<div class="col-xl-3 col-lg-4 mb-4">
    <div class="block block-rounded h-auto mb-0">
        <div class="block-header block-header-default">
            <h3 class="block-title">⚙️ Menu</h3>
        </div>
        <div class="block-content">
            <ul class="nav nav-pills flex-column push">

                {{-- Établissement : identité, type et modules --}}
                @can('parametres.etablissement')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('etablissement.*') ? 'active' : '' }}"
                       href="{{ route('etablissement.edit') }}">
                        <i class="fa fa-hospital me-1"></i> Établissement
                    </a>
                </li>
                @endcan

{{-- Établissements, rôles, permissions : uniquement dans l'espace Administration (super-admin) --}}

                {{-- Structures / Services médicaux --}}
                @can('parametres.services')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}"
                       href="{{ route('services.index') }}">
                        <i class="fa fa-stethoscope me-1"></i> Structures
                    </a>
                </li>
                @endcan

                {{-- Prestations --}}
                @can('parametres.prestations')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('prestations.*') ? 'active' : '' }}"
                       href="{{ route('prestations.index') }}">
                        <i class="fa fa-hand-holding-medical me-1"></i> Prestations
                    </a>
                </li>
                @endcan

                {{-- Salles --}}
                @can('parametres.salles')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('salles.*') ? 'active' : '' }}"
                       href="{{ route('salles.index') }}">
                        <i class="fa fa-door-open me-1"></i> Salles
                    </a>
                </li>
                @endcan

                {{-- Lits --}}
                @can('parametres.lits')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('lits.*') ? 'active' : '' }}"
                       href="{{ route('lits.index') }}">
                        <i class="fa fa-bed me-1"></i> Lits
                    </a>
                </li>
                @endcan

                {{-- Configuration Examens --}}
                {{-- @can('parametres.examens_config')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('examens.*') ? 'active' : '' }}"
                       href="{{ route('examens.index') }}">
                        <i class="fa fa-vials me-1"></i> Examens
                    </a>
                </li>
                @endcan --}}

                {{-- Assurances / Sécurité sociale --}}
                @can('parametres.assurances')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('assurances.*') ? 'active' : '' }}"
                       href="{{ route('assurances.index') }}">
                        <i class="fa fa-id-card me-1"></i> Sécurité sociale
                    </a>
                </li>
                @endcan

                {{-- Symptômes --}}
                @can('parametres.symptomes')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('symptomes.*') ? 'active' : '' }}"
                       href="{{ route('symptomes.index') }}">
                        <i class="fa fa-notes-medical me-1"></i> Symptômes
                    </a>
                </li>
                @endcan

                {{-- Maladies --}}
                @can('parametres.maladies')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('maladies.*') ? 'active' : '' }}"
                       href="{{ route('maladies.index') }}">
                        <i class="fa fa-virus me-1"></i> Maladies
                    </a>
                </li>
                @endcan

                {{-- Nomenclature des diagnostics --}}
                @can('parametres.maladies')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('diagnostics.*') ? 'active' : '' }}" href="{{ route('diagnostics.index') }}">
                        <i class="fa fa-list-ol me-1"></i> Diagnostics (CIM)
                    </a>
                </li>
                @endcan

                {{-- Médicaments (stock) --}}
                @can('stock.medicaments')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('medicaments.*') ? 'active' : '' }}"
                       href="{{ route('medicaments.index') }}">
                        <i class="fa fa-pills me-1"></i> Médicaments
                    </a>
                </li>
                @endcan

                {{-- Familles de médicaments (stock) --}}
                @can('stock.familles')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('familles.*') ? 'active' : '' }}"
                       href="{{ route('familles.index') }}">
                        <i class="fa fa-users-cog me-1"></i> Familles médicaments
                    </a>
                </li>
                @endcan

                {{-- Unités de mesure (stock) --}}
                @can('stock.unites')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('unites.*') ? 'active' : '' }}"
                       href="{{ route('unites.index') }}">
                        <i class="fa fa-layer-group me-1"></i> Unités
                    </a>
                </li>
                @endcan

                {{-- Rôles & Permissions (admin seulement) --}}
                {{-- Rôles et permissions : communs à tous les établissements, gérés dans l'Administration --}}

                {{-- Utilisateurs (admin seulement) --}}
                @can('users.view')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
                       href="{{ route('users.index') }}">
                        <i class="fa fa-users me-1"></i> Utilisateurs
                    </a>
                </li>
                @endcan

                {{-- Guichets de caisse --}}
                @can('parametres.etablissement')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('guichets.*') ? 'active' : '' }}" href="{{ route('guichets.index') }}">
                        <i class="fa fa-cash-register me-1"></i> Guichets de caisse
                    </a>
                </li>
                @endcan

                {{-- Modèles de certificats et lettres --}}
                @can('parametres.etablissement')
                <li class="nav-item mb-1">
                    <a class="nav-link {{ request()->routeIs('modeles.*') ? 'active' : '' }}" href="{{ route('modeles.index') }}">
                        <i class="fa fa-file-alt me-1"></i> Modèles de documents
                    </a>
                </li>
                @endcan

                {{-- Journal d'audit : toute la plateforme, dans l'Administration --}}
                {{-- Rapport technique des migrations : dans l'Administration --}}

            </ul>
        </div>
    </div>
</div>
