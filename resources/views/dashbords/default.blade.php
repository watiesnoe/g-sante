@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
@endphp

{{-- HERO BANNER --}}
<div class="gs-banner mb-4" style="background: linear-gradient(135deg, #475569 0%, #1e293b 100%);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="position:relative;z-index:2">
            <div class="gs-time-badge">
                <i class="fas fa-desktop"></i>
                <span>Portail G-Santé</span>
            </div>
            <div class="gs-banner-title">{{ $greeting }}, {{ Auth::user()->prenom }}</div>
            <div class="gs-banner-subtitle">
                Accès au système sécurisé de gestion hospitalière.
            </div>
            <div class="mt-3 d-flex gap-3 flex-wrap">
                @if(isset($stats['ordonnances_today']))
                <div>
                    <div style="font-size:.7rem;opacity:.7;font-weight:600;text-transform:uppercase">Ord. aujourd'hui</div>
                    <div style="font-size:1.75rem;font-weight:800">{{ $stats['ordonnances_today'] }}</div>
                </div>
                @endif
                <div style="border-left:1px solid rgba(255,255,255,.2);margin:0 .5rem"></div>
                @if(isset($stats['patients_today']))
                <div>
                    <div style="font-size:.7rem;opacity:.7;font-weight:600;text-transform:uppercase">Nouv. Patients</div>
                    <div style="font-size:1.75rem;font-weight:800">{{ $stats['patients_today'] }}</div>
                </div>
                @endif
            </div>
        </div>
        <div class="text-end d-none d-md-block" style="position:relative;z-index:2">
            <div id="gs-clock"></div>
            <div id="gs-date"></div>
        </div>
    </div>
    <i class="fas fa-clinic-medical gs-banner-icon"></i>
</div>

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    @php
    $defKPIs = [
        ['label'=>'Ordonnances',   'value'=>$stats['total_ordonnances']??0, 'sub'=>'Enregistrées',       'icon'=>'fa-prescription-bottle', 'bg'=>'var(--grad-teal)',   'bar'=>'#0891b2', 'route'=>route('ordonnances.index')],
        ['label'=>'Médicaments',   'value'=>$stats['total_medicaments']??0, 'sub'=>'En stock',            'icon'=>'fa-pills',              'bg'=>'var(--grad-green)',  'bar'=>'#10b981', 'route'=>route('medicaments.index')],
        ['label'=>'Patients',      'value'=>$stats['total_patients']??0,    'sub'=>'Dossiers enregistrés','icon'=>'fa-user-injured',       'bg'=>'var(--grad-violet)', 'bar'=>'#7c3aed', 'route'=>route('patients.index')],
        ['label'=>'Tickets',       'value'=>$stats['total_tickets']??0,     'sub'=>'Salle d\'attente',    'icon'=>'fa-ticket-alt',         'bg'=>'var(--grad-amber)',  'bar'=>'#f59e0b', 'route'=>route('tickets.index')],
    ];
    @endphp
    @foreach($defKPIs as $k)
    <div class="col-xl-3 col-md-6">
        <a href="{{ $k['route'] }}" class="gs-kpi h-100">
            <div class="gs-kpi-icon" style="background:{{ $k['bg'] }};color:#fff">
                <i class="fas {{ $k['icon'] }}"></i>
            </div>
            <div class="gs-kpi-label">{{ $k['label'] }}</div>
            <div class="gs-kpi-value"><span class="counter-val" data-target="{{ $k['value'] }}">{{ $k['value'] }}</span></div>
            <div class="gs-kpi-sub">{{ $k['sub'] }}</div>
            <div class="gs-kpi-bar" style="background:{{ $k['bar'] }}"></div>
        </a>
    </div>
    @endforeach
</div>

{{-- SECONDARY STATS --}}
<div class="row g-3 mb-4">
    {{-- Occupation Lits --}}
    <div class="col-xl-4 col-md-6">
        <div class="gs-card h-100">
            <div class="gs-card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--med-rose-light);color:var(--med-rose);display:flex;align-items:center;justify-content:center;font-size:1rem">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div class="ms-3">
                        <div style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Occupation Lits</div>
                        <div style="font-size:1.5rem;font-weight:800;color:#0f172a">{{ $stats['lits_occupes']??0 }} / {{ $stats['total_lits']??0 }}</div>
                    </div>
                </div>
                @php $occPct = ($stats['total_lits']??0) > 0 ? round(($stats['lits_occupes']/$stats['total_lits'])*100) : 0; @endphp
                <div class="progress" style="height:8px;border-radius:4px">
                    <div class="progress-bar bg-danger" style="width:{{ $occPct }}%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2" style="font-size:.75rem;color:#94a3b8">
                    <span>{{ $occPct }}% occupé</span>
                    <span>{{ ($stats['total_lits']??0) - ($stats['lits_occupes']??0) }} libres</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertes Stock --}}
    <div class="col-xl-4 col-md-6">
        <div class="gs-card h-100">
            <div class="gs-card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--med-amber-light);color:var(--med-amber);display:flex;align-items:center;justify-content:center;font-size:1rem">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="ms-3">
                        <div style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Alertes Stock</div>
                        <div style="font-size:1.5rem;font-weight:800;color:var(--med-rose)">{{ ($stats['medicaments_low_stock']??0) + ($stats['medicaments_out_of_stock']??0) }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-danger rounded-pill" style="font-size:.7rem">Rupture: {{ $stats['medicaments_out_of_stock']??0 }}</span>
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size:.7rem">Faible: {{ $stats['medicaments_low_stock']??0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Fournisseurs --}}
    <div class="col-xl-4 col-md-12">
        <div class="gs-card h-100">
            <div class="gs-card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:var(--med-teal-light);color:var(--med-teal);display:flex;align-items:center;justify-content:center;font-size:1rem">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="ms-3">
                        <div style="font-size:.72rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em">Fournisseurs</div>
                        <div style="font-size:1.5rem;font-weight:800;color:#0f172a">{{ $stats['total_fournisseurs']??0 }}</div>
                    </div>
                </div>
                <div style="font-size:.8rem;color:#94a3b8">Partenaires logistiques actifs</div>
            </div>
        </div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="gs-card">
    <div class="gs-card-header">
        <h6 class="gs-card-title">
            <span style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-bolt" style="font-size:.85rem"></i>
            </span>
            Accès Rapide
        </h6>
    </div>
    <div class="gs-card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <a href="{{ route('ordonnances.index') }}" class="gs-module-btn py-4">
                    <div class="gs-module-icon bg-primary-subtle text-primary"><i class="fas fa-prescription-bottle"></i></div>
                    <span style="font-size:.9rem;font-weight:700">Gestion Ordonnances</span>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('medicaments.index') }}" class="gs-module-btn py-4">
                    <div class="gs-module-icon bg-success-subtle text-success"><i class="fas fa-pills"></i></div>
                    <span style="font-size:.9rem;font-weight:700">Pharmacie & Stock</span>
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('fournisseurs.index') }}" class="gs-module-btn py-4">
                    <div class="gs-module-icon bg-info-subtle text-info"><i class="fas fa-truck"></i></div>
                    <span style="font-size:.9rem;font-weight:700">Fournisseurs</span>
                </a>
            </div>
        </div>
    </div>
</div>
