@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $apptCount = $todayAppointments->count();
@endphp

{{-- HERO BANNER --}}
<div class="gs-banner mb-4" style="background: var(--grad-teal);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="position:relative;z-index:2">
            <div class="gs-time-badge">
                <i class="fas fa-headset"></i>
                <span>Accueil & Secrétariat</span>
            </div>
            <div class="gs-banner-title">{{ $greeting }}, {{ Auth::user()->prenom }}</div>
            <div class="gs-banner-subtitle">
                @if($apptCount > 0)
                    Vous avez <strong>{{ $apptCount }} rendez-vous</strong> à gérer aujourd'hui.
                @else
                    Aucun rendez-vous planifié pour le moment.
                @endif
            </div>
            <div class="mt-3 d-flex gap-3 flex-wrap">
                <div>
                    <div style="font-size:.7rem;opacity:.8;font-weight:600;text-transform:uppercase">Nouv. Patients</div>
                    <div style="font-size:1.75rem;font-weight:800">{{ $stats['new_patients_today'] ?? 0 }}</div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,.3);margin:0 .5rem"></div>
                <div>
                    <div style="font-size:.7rem;opacity:.8;font-weight:600;text-transform:uppercase">RDV Réalisés</div>
                    <div style="font-size:1.75rem;font-weight:800">{{ $stats['rdv_realises'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="text-end d-none d-md-block" style="position:relative;z-index:2">
            <div id="gs-clock"></div>
            <div id="gs-date"></div>
        </div>
    </div>
    <i class="fas fa-calendar-alt gs-banner-icon"></i>
</div>

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    @php
    $secStats = [
        ['label'=>'Nouveaux Patients', 'value'=>$stats['new_patients_today']??0, 'sub'=>'Inscrits aujourd\'hui', 'icon'=>'fa-user-plus',    'bg'=>'var(--grad-teal)',   'bar'=>'#0891b2', 'route'=>route('patients.index')],
        ['label'=>'RDV en Attente',    'value'=>$stats['rdv_attente']??0,    'sub'=>'À recevoir',         'icon'=>'fa-clock',        'bg'=>'var(--grad-amber)',  'bar'=>'#f59e0b', 'route'=>route('rendezvous.index')],
        ['label'=>'RDV Réalisés',      'value'=>$stats['rdv_realises']??0,   'sub'=>'Traités ce jour',    'icon'=>'fa-calendar-check','bg'=>'var(--grad-green)',  'bar'=>'#10b981', 'route'=>route('rendezvous.index')],
        ['label'=>'Total Patients',    'value'=>$stats['total_patients']??0,  'sub'=>'Base de données',    'icon'=>'fa-users',         'bg'=>'var(--grad-violet)', 'bar'=>'#7c3aed', 'route'=>route('patients.index')],
    ];
    @endphp
    @foreach($secStats as $s)
    <div class="col-xl-3 col-md-6">
        <a href="{{ $s['route'] }}" class="gs-kpi h-100">
            <div class="gs-kpi-icon" style="background:{{ $s['bg'] }};color:#fff">
                <i class="fas {{ $s['icon'] }}"></i>
            </div>
            <div class="gs-kpi-label">{{ $s['label'] }}</div>
            <div class="gs-kpi-value"><span class="counter-val" data-target="{{ $s['value'] }}">{{ $s['value'] }}</span></div>
            <div class="gs-kpi-sub">{{ $s['sub'] }}</div>
            <div class="gs-kpi-bar" style="background:{{ $s['bar'] }}"></div>
        </a>
    </div>
    @endforeach
</div>

{{-- MAIN CONTENT --}}
<div class="row g-3">
    {{-- Appointments List --}}
    <div class="col-lg-8">
        <div class="gs-card h-100">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-teal-light);color:var(--med-teal);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-calendar-day" style="font-size:.85rem"></i>
                    </span>
                    Planning du jour
                </h6>
                <span class="badge rounded-pill" style="background:var(--med-teal-light);color:var(--med-teal)">{{ $apptCount }} RDV</span>
            </div>
            <div class="gs-scroll-list" style="max-height:450px">
                @forelse($todayAppointments as $appt)
                <div class="gs-appt-item px-4 py-3">
                    <div style="width:45px;height:45px;border-radius:12px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-weight:700;color:#64748b;flex-shrink:0">
                        {{ strtoupper(substr($appt->patient->prenom??'P',0,1)) }}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <span style="font-size:.9rem;font-weight:700;color:#0f172a">{{ $appt->patient->prenom }} {{ $appt->patient->nom }}</span>
                            <span class="gs-appt-time"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($appt->date_heure)->format('H:i') }}</span>
                        </div>
                        <div style="font-size:.78rem;color:#64748b;margin-top:.1rem">
                            <i class="fas fa-user-md me-1 opacity-50"></i> Dr. {{ $appt->medecin->prenom ?? 'N/A' }} {{ $appt->medecin->name ?? '' }}
                            <span class="mx-2 opacity-25">|</span>
                            <i class="fas fa-tag me-1 opacity-50"></i> {{ $appt->motif }}
                        </div>
                    </div>
                    <div class="ms-3">
                        @php
                            $status_class = match($appt->statut) {
                                'prevu' => 'primary',
                                'realise' => 'success',
                                'annule' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $status_class }}-subtle text-{{ $status_class }} rounded-pill" style="font-size:.7rem;padding:.3rem .7rem">
                            {{ strtoupper($appt->statut) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div style="font-size:3rem;margin-bottom:1rem">📅</div>
                    <div style="font-size:.9rem;color:#94a3b8;font-weight:500">Aucun rendez-vous enregistré pour aujourd'hui.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-lg-4">
        <div class="gs-card mb-3">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-rose-light);color:var(--med-rose);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-bolt" style="font-size:.85rem"></i>
                    </span>
                    Actions Rapides
                </h6>
            </div>
            <div class="gs-card-body">
                <a href="{{ route('rendezvous.index') }}" class="gs-action-btn" style="background:var(--med-teal-light);color:var(--med-teal-dark)">
                    <div class="icon-box" style="background:var(--med-teal);color:#fff"><i class="fas fa-calendar-plus"></i></div>
                    <div>
                        <div style="font-size:.85rem;font-weight:700">Nouveau RDV</div>
                        <div style="font-size:.72rem;opacity:.7">Planifier une visite</div>
                    </div>
                </a>
                <a href="{{ route('patients.create') }}" class="gs-action-btn" style="background:var(--med-green-light);color:#065f46">
                    <div class="icon-box" style="background:var(--med-green);color:#fff"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <div style="font-size:.85rem;font-weight:700">Nouveau Patient</div>
                        <div style="font-size:.72rem;opacity:.7">Enregistrer un dossier</div>
                    </div>
                </a>
                <a href="{{ route('tickets.index') }}" class="gs-action-btn" style="background:var(--med-amber-light);color:#92400e">
                    <div class="icon-box" style="background:var(--med-amber);color:#fff"><i class="fas fa-ticket-alt"></i></div>
                    <div>
                        <div style="font-size:.85rem;font-weight:700">Tickets / Attente</div>
                        <div style="font-size:.72rem;opacity:.7">Gérer la file d'attente</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-violet-light);color:#7c3aed;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-info-circle" style="font-size:.85rem"></i>
                    </span>
                    Aide mémoire
                </h6>
            </div>
            <div class="gs-card-body">
                <div style="font-size:.78rem;color:#64748b;line-height:1.6">
                    <div class="d-flex gap-2 mb-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <span>Vérifier systématiquement l'identité du patient.</span>
                    </div>
                    <div class="d-flex gap-2 mb-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <span>Confirmer le médecin traitant lors du RDV.</span>
                    </div>
                    <div class="d-flex gap-2">
                        <i class="fas fa-check-circle text-success mt-1"></i>
                        <span>S'assurer que la caisse est ouverte pour les paiements.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
