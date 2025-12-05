<!-- Utilisez votre dashboard médical existant ici -->
<!-- Welcome Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="block block-rounded bg-primary text-white">
            <div class="block-content block-content-full">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="h4 mb-2">Bonjour, Dr. {{ Auth::user()->prenom ?? Auth::user()->name }} !</h3>
                        <p class="fs-sm mb-3 opacity-75">
                            Voici un aperçu de vos activités médicales aujourd'hui.
                            Vous avez {{ $todayAppointments->count() }} rendez-vous programmés.
                        </p>
                        <div class="h2 mb-0">{{ $stats['consultations_today'] }} consultations aujourd'hui</div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-stethoscope fa-4x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Le reste de votre dashboard médical existant -->
<!-- ... (votre contenu médical actuel) ... -->
