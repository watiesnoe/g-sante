<li class="nav-main-item">
    <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" href="#">
        <i class="nav-main-link-icon fa fa-calendar-alt"></i>
        <span class="nav-main-link-name">Rendez-vous</span>
    </a>
    <ul class="nav-main-submenu">
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('rendezvous.index')}}"><span class="nav-main-link-name">Liste des Rendez-vous</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('rendezvous.disponible')}}"><span class="nav-main-link-name">Rendez-vous realisés</span></a></li>
        <li class="nav-main-item"><a class="nav-main-link" href="{{route('rendezvous.annuler')}}"><span class="nav-main-link-name">Rendez-vous annulés</span></a></li>
    </ul>
</li>
