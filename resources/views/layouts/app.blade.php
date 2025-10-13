<!doctype html>
<html lang="en" class="remember-theme">
<head>
    @include('layouts.partials.head')
</head>

<body>

<div id="page-container"
     class="sidebar-o sidebar-light enable-page-overlay side-scroll page-header-fixed main-content-narrow">

    @include('layouts.partials.sidebare')
    <!-- END Sidebar -->

    @include('layouts.partials.navbare')
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">
        <!-- Hero -->


        @if(!\App\Models\Etablissement::courant()->estConfigure() && !request()->routeIs('etablissement.*') && auth()->user()?->can('parametres.etablissement'))
            <div class="alert alert-info d-flex justify-content-between align-items-center rounded-0 mb-0">
                <span><i class="fa fa-hospital me-2"></i>Complétez les informations de votre établissement : nom, coordonnées, logo.</span>
                <a href="{{ route('etablissement.edit') }}" class="btn btn-sm btn-primary">Configurer</a>
            </div>
        @endif

        {{-- Abonnement de l'établissement : échéance proche, période de grâce, modules coupés --}}
        @auth
            @php $abonnementCourant = \App\Models\Etablissement::courant()->abonnement; @endphp
            @if($abonnementCourant && $abonnementCourant->statut !== 'termine')
                @php
                    $joursAb = $abonnementCourant->joursRestants();
                    $gereAb = auth()->user()->can('parametres.etablissement');
                @endphp
                @if($abonnementCourant->statut === 'restreint')
                    <div class="alert alert-danger d-flex align-items-center m-3 mb-0">
                        <i class="fa fa-lock me-2"></i>
                        <span>Abonnement échu : certains modules sont temporairement coupés
                            ({{ collect(\App\Models\Etablissement::courant()->modules_suspendus)->map(fn($m) => \App\Models\Etablissement::MODULES[$m]['label'] ?? $m)->implode(', ') }}).
                            Les données sont conservées et tout revient dès le renouvellement.{{ $gereAb ? ' Contactez l\'administrateur de la plateforme.' : ' Prévenez le responsable de l\'établissement.' }}</span>
                    </div>
                @elseif($gereAb && $abonnementCourant->statut === 'grace')
                    <div class="alert alert-warning m-3 mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Votre abonnement « {{ $abonnementCourant->formule->nom }} » a expiré le {{ $abonnementCourant->fin->format('d/m/Y') }}. Sans renouvellement avant le {{ $abonnementCourant->finGrace()->format('d/m/Y') }}, certains modules seront coupés.</div>
                @elseif($gereAb && $joursAb >= 0 && $joursAb <= 15)
                    <div class="alert alert-info m-3 mb-0"><i class="fa fa-calendar-alt me-2"></i>Votre abonnement « {{ $abonnementCourant->formule->nom }} » arrive à échéance le {{ $abonnementCourant->fin->format('d/m/Y') }} ({{ $joursAb }} jour(s)).</div>
                @endif
            @endif
        @endauth

        <!-- Page Content -->
        @yield('content')
        <!-- END Page Content -->
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    @include('layouts.partials.footer')
    <!-- END Footer -->
</div>
<!-- END Page Container -->

<!--
  Dashmix JS

  Core libraries and functionality
  webpack is putting everything together at asset/_js/main/app.js
-->
@include('layouts.partials.js')

<script src="{{ asset('admin/js/crud-helper.js') }}"></script>
@yield('scripts')

</body>
</html>
