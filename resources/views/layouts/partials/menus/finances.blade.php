<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-file-invoice-dollar"></i>
        <span class="nav-main-link-name">Finances</span>
    </a>
    <ul class="nav-main-submenu">
        <li class="nav-main-item"><a class="nav-main-link" href="{{ route('depenses.index') }}"><span class="nav-main-link-name">Dépenses</span></a></li>
        @can('depenses.create')
            <li class="nav-main-item"><a class="nav-main-link" href="{{ route('depenses.create') }}"><span class="nav-main-link-name">Nouvelle dépense</span></a></li>
        @endcan
        @can('budget.view')
            <li class="nav-main-item"><a class="nav-main-link" href="{{ route('budget.index') }}"><span class="nav-main-link-name">Budget</span></a></li>
        @endcan
        @can('gratuites.gerer')
            <li class="nav-main-item"><a class="nav-main-link" href="{{ route('gratuites.index') }}"><span class="nav-main-link-name">Gratuités</span></a></li>
        @endcan
    </ul>
</li>
