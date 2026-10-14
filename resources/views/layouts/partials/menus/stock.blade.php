<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-boxes"></i>
        <span class="nav-main-link-name">Stock</span>
    </a>
    <ul class="nav-main-submenu">
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('fournisseurs.index')}}"><span class="nav-main-link-name">Fournisseurs</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('medicaments.index')}}"><span class="nav-main-link-name">Médicaments</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('stock.alertes')}}"><span class="nav-main-link-name">Alertes de stock</span></a></li>
        @if(\App\Models\Etablissement::courant()->parametre('stock.multi_magasins'))
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('magasins.index')}}"><span class="nav-main-link-name">Magasins et transferts</span></a></li>
        @endif
        @if(auth()->user()->can('achats.demander') || auth()->user()->can('achats.approuver'))
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('achats.index')}}"><span class="nav-main-link-name">Demandes d'achat</span></a></li>
        @endif
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('commandes.index')}}"><span class="nav-main-link-name">Commandes</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('receptions.index')}}"><span class="nav-main-link-name">Réceptions</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('familles.index')}}"><span class="nav-main-link-name">Familles</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('unites.index')}}"><span class="nav-main-link-name">Unités</span></a></li>
        @if(Auth::user()->hasRole(['super_admin', 'admin']) || Auth::user()->can('stock.inventaire'))
        <li class="nav-main-item"><a class="nav-main-link {{ request()->routeIs('inventaires.*') ? 'active' : '' }}" href="{{route('inventaires.index')}}"><span class="nav-main-link-name">📋 Inventaire</span></a></li>
        @endif
    </ul>
</li>
