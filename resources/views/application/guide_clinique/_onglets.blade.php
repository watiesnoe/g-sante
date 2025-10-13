{{-- Onglets du guide clinique : maladies (MSF) / fiches Berrebi --}}
<ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ request('vue') !== 'berrebi' ? 'active' : '' }}" href="{{ route('guide-clinique.index', array_filter(['q' => request('q')])) }}"><i class="fa fa-notes-medical me-1"></i>Maladies (guide MSF)</a></li>
    <li class="nav-item"><a class="nav-link {{ request('vue') === 'berrebi' ? 'active' : '' }}" href="{{ route('guide-clinique.index', array_filter(['vue' => 'berrebi', 'q' => request('q')])) }}"><i class="fa fa-book me-1"></i>Fiches Berrebi (2005)@isset($nbBerrebi) <span class="badge bg-secondary">{{ $nbBerrebi }}</span>@endisset</a></li>
</ul>
