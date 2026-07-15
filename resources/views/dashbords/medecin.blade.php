@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $consToday = $stats['consultations_today'] ?? 0;
@endphp

{{-- HERO BANNER --}}
<div class="gs-banner mb-4" style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 60%, #164e63 100%);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="position:relative;z-index:2">
            <div class="gs-time-badge">
                <i class="fas fa-heartbeat" style="color:#f43f5e"></i>
                <span>Espace Clinique</span>
                <span class="pulse-dot success" style="width:7px;height:7px;margin-left:.25rem"></span>
            </div>
            <div class="gs-banner-title">{{ $greeting }}, Dr. {{ Auth::user()->prenom }}</div>
            <div class="gs-banner-subtitle">
                @if($consToday > 0)
                    <strong>{{ $consToday }} consultation{{ $consToday > 1 ? 's' : '' }}</strong> planifiée{{ $consToday > 1 ? 's' : '' }} aujourd'hui
                @else
                    Aucune consultation planifiée aujourd'hui
                @endif
            </div>
            <div class="mt-3 d-flex gap-3 flex-wrap">
                <div><div style="font-size:.7rem;opacity:.65;font-weight:600;text-transform:uppercase">Aujourd'hui</div><div style="font-size:1.75rem;font-weight:800">{{ $consToday }}</div></div>
                <div style="border-left:1px solid rgba(255,255,255,.25);margin:0 .5rem"></div>
                <div><div style="font-size:.7rem;opacity:.65;font-weight:600;text-transform:uppercase">Total consultations</div><div style="font-size:1.75rem;font-weight:800">{{ $stats['total_consultations'] ?? 0 }}</div></div>
                <div style="border-left:1px solid rgba(255,255,255,.25);margin:0 .5rem"></div>
                <div><div style="font-size:.7rem;opacity:.65;font-weight:600;text-transform:uppercase">Hospitalisés</div><div style="font-size:1.75rem;font-weight:800">{{ $stats['active_hospitalisations'] ?? 0 }}</div></div>
            </div>
        </div>
        <div class="text-end d-none d-md-block" style="position:relative;z-index:2">
            <div id="gs-clock"></div><div id="gs-date"></div>
        </div>
    </div>
    <i class="fas fa-user-md gs-banner-icon"></i>
</div>

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    @php
    $kpis = [
        ['label'=>"Consultations Auj.", 'value'=>$stats['consultations_today']??0, 'sub'=>'Patients reçus',        'icon'=>'fa-stethoscope',        'bg'=>'var(--grad-teal)',   'bar'=>'#0891b2', 'route'=>route('consultations.index'),    'alert'=>false, 'module'=>'consultation'],
        ['label'=>'Patients Totaux',    'value'=>$stats['total_patients']??0,      'sub'=>'Dossiers actifs',       'icon'=>'fa-user-injured',       'bg'=>'var(--grad-green)',  'bar'=>'#10b981', 'route'=>route('patients.index'),          'alert'=>false, 'module'=>'patient'],
        ['label'=>'Hospitalisés',       'value'=>$stats['active_hospitalisations']??0,'sub'=>'En cours',           'icon'=>'fa-procedures',         'bg'=>'var(--grad-amber)',  'bar'=>'#f59e0b', 'route'=>route('hospitalisations.index'), 'alert'=>($stats['active_hospitalisations']??0) > 0, 'module'=>'hospitalisation'],
    ];
    $kpis = array_filter($kpis, function($k) {
        return auth()->user()->hasModuleAccess($k['module']);
    });
    $kpiCount = count($kpis);
    $kpiColClass = match($kpiCount) {
        3 => 'col-xl-4 col-md-4',
        2 => 'col-xl-6 col-md-6',
        1 => 'col-xl-12 col-md-12',
        default => 'd-none',
    };
    @endphp
    @foreach($kpis as $k)
    <div class="{{ $kpiColClass }}">
        <a href="{{ $k['route'] }}" class="gs-kpi h-100">
            @if($k['alert'])<span class="gs-kpi-trend" style="background:var(--med-rose-light);color:var(--med-rose)"><span class="pulse-dot danger" style="width:7px;height:7px;margin-right:.3rem"></span>Alerte</span>@endif
            <div class="gs-kpi-icon" style="background:{{ $k['bg'] }};color:#fff"><i class="fas {{ $k['icon'] }}"></i></div>
            <div class="gs-kpi-label">{{ $k['label'] }}</div>
            <div class="gs-kpi-value"><span class="counter-val" data-target="{{ $k['value'] }}">{{ $k['value'] }}</span></div>
            <div class="gs-kpi-sub">{{ $k['sub'] }}</div>
            <div class="gs-kpi-bar" style="background:{{ $k['bar'] }}"></div>
        </a>
    </div>
    @endforeach
</div>

{{-- CHART + RDV --}}
@php
    $hasConsultation = auth()->user()->hasModuleAccess('consultation');
    $hasRendezvous = auth()->user()->hasModuleAccess('rendezvous');
@endphp
@if($hasConsultation || $hasRendezvous)
<div class="row g-3 mb-4">
    @if($hasConsultation)
    <div class="{{ $hasRendezvous ? 'col-lg-8' : 'col-lg-12' }}">
        <div class="gs-card h-100">
            <div class="gs-card-header">
                <h6 class="gs-card-title"><span style="width:32px;height:32px;border-radius:8px;background:var(--med-teal-light);color:var(--med-teal);display:flex;align-items:center;justify-content:center"><i class="fas fa-chart-bar" style="font-size:.85rem"></i></span>Mes consultations — 6 derniers mois</h6>
                <a href="{{ route('consultations.index') }}" class="btn btn-sm btn-outline-primary rounded-pill" style="font-size:.78rem">Voir tout</a>
            </div>
            <div class="gs-card-body"><canvas id="consultations-chart" height="220"></canvas></div>
        </div>
    </div>
    @endif
    @if($hasRendezvous)
    <div class="{{ $hasConsultation ? 'col-lg-4' : 'col-lg-12' }}">
        <div class="gs-card h-100">
            <div class="gs-card-header">
                <h6 class="gs-card-title"><span style="width:32px;height:32px;border-radius:8px;background:var(--med-violet-light);color:#7c3aed;display:flex;align-items:center;justify-content:center"><i class="fas fa-calendar-day" style="font-size:.85rem"></i></span>RDV d'aujourd'hui</h6>
                <span class="badge rounded-pill" style="background:var(--med-violet-light);color:#7c3aed">{{ $todayAppointments->count() }}</span>
            </div>
            <div class="gs-scroll-list" style="max-height:280px">
                @forelse($todayAppointments as $appt)
                @php $initials = strtoupper(substr($appt->patient->prenom??'P',0,1).substr($appt->patient->nom??'',0,1)); $colors=['#0891b2','#10b981','#7c3aed','#f59e0b','#f43f5e']; $color=$colors[$loop->index%5]; @endphp
                <div class="gs-appt-item">
                    <div class="gs-appt-avatar" style="background:{{ $color }}20;color:{{ $color }}">{{ $initials }}</div>
                    <div class="flex-grow-1">
                        <div style="font-size:.85rem;font-weight:600;color:#0f172a">{{ $appt->patient->prenom??'' }} {{ $appt->patient->nom??'Patient' }}</div>
                        <div class="gs-appt-time"><i class="fas fa-clock me-1" style="font-size:.65rem"></i>{{ \Carbon\Carbon::parse($appt->date_heure)->format('H:i') }} <span style="color:#94a3b8;font-weight:400">· {{ Str::limit($appt->motif??'',18) }}</span></div>
                    </div>
                    @php $sbg=$appt->statut==='realise'?'#d1fae5':($appt->statut==='annule'?'#ffe4e6':'#e0f2fe'); $sc=$appt->statut==='realise'?'#059669':($appt->statut==='annule'?'#e11d48':'#0891b2'); @endphp
                    <span style="background:{{ $sbg }};color:{{ $sc }};font-size:.7rem;font-weight:700;padding:.2rem .6rem;border-radius:50px">{{ ucfirst($appt->statut) }}</span>
                </div>
                @empty
                <div class="text-center py-5"><div style="font-size:2.5rem">📅</div><div style="font-size:.85rem;color:#94a3b8;font-weight:500">Aucun rendez-vous aujourd'hui</div></div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- STOCK + HOSPITALISATIONS --}}
@if(auth()->user()->hasModuleAccess('hospitalisation'))
<div class="row g-3 mb-4">
    <div class="col-lg-12">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title"><span style="width:32px;height:32px;border-radius:8px;background:var(--med-amber-light);color:var(--med-amber);display:flex;align-items:center;justify-content:center"><i class="fas fa-procedures" style="font-size:.85rem"></i></span>Hospitalisations Actives</h6>
                <span class="badge rounded-pill" style="background:var(--med-amber-light);color:var(--med-amber)">{{ $activeHospitalisations->count() }}</span>
            </div>
            <div class="gs-scroll-list" style="max-height:260px">
                @forelse($activeHospitalisations->take(6) as $hosp)
                @php $patient=$hosp->consultation->patient??null; $nom=$patient?($patient->prenom.' '.$patient->nom):'N/A'; $initials=$patient?strtoupper(substr($patient->prenom??'H',0,1).substr($patient->nom??'',0,1)):'HO'; @endphp
                <div class="gs-appt-item">
                    <div class="gs-appt-avatar" style="background:var(--med-amber-light);color:var(--med-amber)">{{ $initials }}</div>
                    <div class="flex-grow-1">
                        <div style="font-size:.85rem;font-weight:600;color:#0f172a">{{ $nom }}</div>
                        <div style="font-size:.75rem;color:#64748b">{{ $hosp->salle->nom??'N/A' }} · {{ $hosp->service->nom??'N/A' }}</div>
                    </div>
                    <a href="{{ route('hospitalisations.show', $hosp->uuid) }}" style="background:var(--med-amber-light);color:var(--med-amber);font-size:.72rem;font-weight:600;padding:.3rem .75rem;border-radius:50px;text-decoration:none">Gérer</a>
                </div>
                @empty
                <div class="text-center py-4"><div style="font-size:2rem">🛏️</div><div style="font-size:.82rem;color:#94a3b8;margin-top:.5rem">Aucune hospitalisation en cours</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endif

{{-- QUICK ACTIONS --}}
@php
    $actions = [
        ['icon'=>'fa-stethoscope','label'=>'Nouvelle Consultation','desc'=>'Ouvrir un dossier','route'=>route('consultations.create'),'bg'=>'var(--grad-teal)', 'module'=>'consultation'],
        ['icon'=>'fa-file-medical','label'=>'Ordonnance','desc'=>'Prescrire un traitement','route'=>route('ordonnances.create'),'bg'=>'var(--grad-green)', 'module'=>'ordonnance'],
        ['icon'=>'fa-calendar-plus','label'=>'Rendez-vous','desc'=>'Planifier un suivi','route'=>route('rendezvous.index'),'bg'=>'var(--grad-violet)', 'module'=>'rendezvous'],
        ['icon'=>'fa-procedures','label'=>'Hospitalisation','desc'=>'Admettre','route'=>route('hospitalisations.create'),'bg'=>'var(--grad-rose)', 'module'=>'hospitalisation'],
        ['icon'=>'fa-clock','label'=>"File d'attente",'desc'=>'Patients en attente','route'=>route('liste.attente'),'bg'=>'var(--grad-dark)', 'module'=>'consultation']
    ];
    $actions = array_filter($actions, function($a) {
        return auth()->user()->hasModuleAccess($a['module']);
    });
@endphp
@if(count($actions) > 0)
<div class="gs-card">
    <div class="gs-card-header"><h6 class="gs-card-title"><span style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center"><i class="fas fa-bolt" style="font-size:.85rem"></i></span>Actions Rapides</h6></div>
    <div class="gs-card-body">
        <div class="row g-2 justify-content-center">
            @foreach($actions as $a)
            <div class="col-xl col-lg-4 col-md-4 col-6">
                <a href="{{ $a['route'] }}" class="gs-module-btn">
                    <div class="gs-module-icon" style="background:{{ $a['bg'] }};color:#fff;width:52px;height:52px;border-radius:16px"><i class="fas {{ $a['icon'] }}" style="font-size:1.1rem"></i></div>
                    <span style="font-size:.78rem;font-weight:700;color:#374151">{{ $a['label'] }}</span>
                    <span style="font-size:.68rem;color:#94a3b8">{{ $a['desc'] }}</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
