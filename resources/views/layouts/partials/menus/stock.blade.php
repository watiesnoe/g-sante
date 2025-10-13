@php $u = auth()->user(); @endphp
<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-boxes"></i>
        <span class="nav-main-link-name">Stock</span>
    </a>
    <ul class="nav-main-submenu">
        @if($u->can('stock.requisition') || $u->can('stock.servir'))
        <li class="nav-main-item"><a class="nav-main-link" href="{{ route('requisitions.index') }}"><span class="nav-main-link-name">Réquisitions des services</span></a></li>
        @endif
        @can('stock.medicaments')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('medicaments.index')}}"><span class="nav-main-link-name">Médicaments</span></a></li>
        @endcan
        @can('stock.view')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('stock.alertes')}}"><span class="nav-main-link-name">Alertes de stock</span></a></li>
        @if(\App\Models\Etablissement::courant()->parametre('stock.multi_magasins'))
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('magasins.index')}}"><span class="nav-main-link-name">Magasins et transferts</span></a></li>
        @endif
        @endcan
        @can('stock.besoins')
        <li class="nav-main-item"><a class="nav-main-link" href="{{ route('besoins.index') }}"><span class="nav-main-link-name">État des besoins</span></a></li>
        @endcan
        @if($u->can('achats.demander') || $u->can('achats.approuver'))
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('achats.index')}}"><span class="nav-main-link-name">Propositions de commande</span></a></li>
        @endif
        @can('stock.commandes')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('commandes.index')}}"><span class="nav-main-link-name">Commandes</span></a></li>
        @endcan
        @can('stock.receptions')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('receptions.index')}}"><span class="nav-main-link-name">Réceptions</span></a></li>
        @endcan
        @can('stock.fournisseurs')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('fournisseurs.index')}}"><span class="nav-main-link-name">Fournisseurs</span></a></li>
        @endcan
        @can('stock.familles')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('familles.index')}}"><span class="nav-main-link-name">Familles</span></a></li>
        @endcan
        @can('stock.unites')
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('unites.index')}}"><span class="nav-main-link-name">Unités</span></a></li>
        @endcan
        @if($u->hasRole(['super_admin', 'admin']) || $u->can('stock.inventaire'))
        <li class="nav-main-item"><a class="nav-main-link {{ request()->routeIs('inventaires.*') ? 'active' : '' }}" href="{{route('inventaires.index')}}"><span class="nav-main-link-name">📋 Inventaire</span></a></li>
        @endif
    </ul>
</li>
