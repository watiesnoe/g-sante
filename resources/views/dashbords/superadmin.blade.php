{{-- ===================================================================
     SUPERADMIN DASHBOARD — Vue d'ensemble système G-Santé
     =================================================================== --}}

@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $greetIcon = $hour < 12 ? '🌅' : ($hour < 18 ? '☀️' : '🌙');
@endphp

{{-- ── HERO BANNER ── --}}
<div class="gs-banner mb-4" style="background: var(--grad-dark);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="position:relative;z-index:2">
            <div class="gs-time-badge">
                <span>{{ $greetIcon }}</span>
                <span>{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
            </div>
            <div class="gs-banner-title">{{ $greeting }}, {{ Auth::user()->prenom }}</div>
            <div class="gs-banner-subtitle">
                Centre de contrôle &mdash; <span class="gs-role-pill">Super Admin</span>
            </div>
            <div class="mt-3 d-flex gap-3 flex-wrap">
                <div>
                    <div style="font-size:.75rem;opacity:.6;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Patients aujourd'hui</div>
                    <div style="font-size:1.5rem;font-weight:800">{{ $stats['new_patients_today'] ?? 0 }}</div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,.2);margin:0 .5rem"></div>
                <div>
                    <div style="font-size:.75rem;opacity:.6;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Consultations totales</div>
                    <div style="font-size:1.5rem;font-weight:800">{{ $stats['total_consultations'] ?? 0 }}</div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,.2);margin:0 .5rem"></div>
                <div>
                    <div style="font-size:.75rem;opacity:.6;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Utilisateurs</div>
                    <div style="font-size:1.5rem;font-weight:800">{{ $stats['total_users'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        {{-- Live Clock --}}
        <div class="text-end d-none d-lg-block" style="position:relative;z-index:2">
            <div id="gs-clock" style="font-size:2.5rem;font-weight:800;letter-spacing:.02em;opacity:.9">--:--:--</div>
            <div id="gs-date" style="font-size:.8rem;opacity:.65;margin-top:.1rem"></div>
            <div class="mt-2">
                <span class="pulse-dot success me-1"></span>
                <span style="font-size:.75rem;opacity:.7">Système opérationnel</span>
            </div>
        </div>
    </div>
    <i class="fas fa-hospital-user gs-banner-icon"></i>
</div>

{{-- ── KPI ROW 1 ── --}}
<div class="row g-3 mb-4">
    @php
    $kpis = [
        ['label'=>'Total Patients',    'value'=>$stats['total_patients']??0,    'sub'=>($stats['new_patients_today']??0).' nouveaux aujourd\'hui', 'icon'=>'fa-user-injured', 'bg'=>'var(--grad-teal)',   'bar'=>'#0891b2', 'route'=>route('patients.index'),       'trend'=>'+'.($stats['new_patients_today']??0),'tcolor'=>'success'],
        ['label'=>'Consultations',     'value'=>$stats['total_consultations']??0,'sub'=>'Total de l\'année',                                      'icon'=>'fa-stethoscope',  'bg'=>'var(--grad-green)',  'bar'=>'#10b981', 'route'=>route('consultations.index'),   'trend'=>'↗','tcolor'=>'success'],
        ['label'=>'Rendez-vous',       'value'=>$stats['total_rendezvou']??0,    'sub'=>'Planifiés cette année',                                   'icon'=>'fa-calendar-check','bg'=>'var(--grad-violet)', 'bar'=>'#7c3aed', 'route'=>route('rendezvous.index'),     'trend'=>null,'tcolor'=>null],
        ['label'=>'Ordonnances',       'value'=>$stats['total_ordonnance']??0,   'sub'=>'Prescriptions émises',                                    'icon'=>'fa-file-medical', 'bg'=>'var(--grad-amber)',  'bar'=>'#f59e0b', 'route'=>route('ordonnances.index'),    'trend'=>null,'tcolor'=>null],
    ];
    @endphp
    @foreach($kpis as $k)
    <div class="col-xl-3 col-md-6">
        <a href="{{ $k['route'] }}" class="gs-kpi h-100">
            @if($k['trend'])
            <span class="gs-kpi-trend bg-{{ $k['tcolor'] }}-subtle text-{{ $k['tcolor'] }}">{{ $k['trend'] }}</span>
            @endif
            <div class="gs-kpi-icon" style="background:{{ $k['bg'] }};color:#fff">
                <i class="fas {{ $k['icon'] }}"></i>
            </div>
            <div class="gs-kpi-label">{{ $k['label'] }}</div>
            <div class="gs-kpi-value">
                <span class="counter-val" data-target="{{ $k['value'] }}">{{ $k['value'] }}</span>
            </div>
            <div class="gs-kpi-sub">{{ $k['sub'] }}</div>
            <div class="gs-kpi-bar" style="background:{{ $k['bar'] }}"></div>
        </a>
    </div>
    @endforeach
</div>

{{-- ── KPI ROW 2 ── --}}
<div class="row g-3 mb-4">
    @php
    $kpis2 = [
        ['label'=>'Médecins',    'value'=>$stats['total_medecins']??0,    'icon'=>'fa-user-md',     'color'=>'#0891b2', 'bg'=>'var(--med-teal-light)',   'route'=>route('medecins.index')],
        ['label'=>'Secrétaires', 'value'=>$stats['total_secretaires']??0, 'icon'=>'fa-user-tie',    'color'=>'#7c3aed', 'bg'=>'var(--med-violet-light)', 'route'=>route('users.index')],
        ['label'=>'Médicaments', 'value'=>$stats['total_medicament']??0,  'icon'=>'fa-pills',       'color'=>'#10b981', 'bg'=>'var(--med-green-light)',  'route'=>route('medicaments.index')],
        ['label'=>'Examens',     'value'=>$stats['total_examens']??0,     'icon'=>'fa-vials',       'color'=>'#f59e0b', 'bg'=>'var(--med-amber-light)',  'route'=>route('examens.index')],
        ['label'=>'Lits Totaux', 'value'=>$stats['total_lits']??0,        'icon'=>'fa-bed',         'color'=>'#f43f5e', 'bg'=>'var(--med-rose-light)',   'route'=>route('lits.index')],
        ['label'=>'Tickets',     'value'=>$stats['total_ticket']??0,      'icon'=>'fa-ticket-alt',  'color'=>'#475569', 'bg'=>'#f1f5f9',                 'route'=>route('tickets.index')],
        ['label'=>'Fournisseurs','value'=>$stats['total_fournisseur']??0, 'icon'=>'fa-truck',       'color'=>'#0891b2', 'bg'=>'var(--med-teal-light)',   'route'=>route('fournisseurs.index')],
        ['label'=>'Admins',      'value'=>$stats['total_admins']??0,      'icon'=>'fa-user-shield', 'color'=>'#1e293b', 'bg'=>'#f1f5f9',                 'route'=>route('users.index')],
    ];
    @endphp
    @foreach($kpis2 as $k)
    <div class="col-xl-3 col-md-3 col-6">
        <a href="{{ $k['route'] }}" class="gs-kpi py-3 px-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $k['bg'] }};color:{{ $k['color'] }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
                    <i class="fas {{ $k['icon'] }}"></i>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em">{{ $k['label'] }}</div>
                    <div style="font-size:1.35rem;font-weight:800;color:#0f172a;line-height:1.2">
                        <span class="counter-val" data-target="{{ $k['value'] }}">{{ $k['value'] }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endforeach
</div>

{{-- ── CHARTS ROW ── --}}
<div class="row g-3 mb-4">
    {{-- Activity Chart --}}
    <div class="col-lg-8">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-teal-light);color:var(--med-teal);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-chart-line" style="font-size:.85rem"></i>
                    </span>
                    Activité des 7 derniers jours
                </h6>
                <span class="badge" style="background:var(--med-teal-light);color:var(--med-teal);font-size:.72rem;padding:.35rem .7rem;border-radius:50px">
                    <span class="pulse-dot success me-1" style="width:7px;height:7px"></span> Temps réel
                </span>
            </div>
            <div class="gs-card-body">
                <canvas id="activityChart" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- Donut + user distribution --}}
    <div class="col-lg-4">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-users" style="font-size:.85rem"></i>
                    </span>
                    Répartition Personnel
                </h6>
            </div>
            <div class="gs-card-body">
                <div style="max-width:180px;margin:0 auto">
                    <canvas id="usersChart"></canvas>
                </div>
                <div class="mt-3">
                    @php
                    $dist = [
                        ['role'=>'Médecins',    'count'=>$stats['total_medecins']??0,    'color'=>'#0891b2'],
                        ['role'=>'Patients',    'count'=>$stats['total_patients']??0,    'color'=>'#10b981'],
                        ['role'=>'Secrétaires', 'count'=>$stats['total_secretaires']??0, 'color'=>'#7c3aed'],
                        ['role'=>'Admins',      'count'=>$stats['total_admins']??0,      'color'=>'#f43f5e'],
                    ];
                    $total = array_sum(array_column($dist,'count'));
                    @endphp
                    @foreach($dist as $d)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $d['color'] }};display:inline-block;flex-shrink:0"></span>
                            <span style="font-size:.8rem;color:#475569;font-weight:500">{{ $d['role'] }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:60px;height:5px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                                <div style="height:100%;width:{{ $total > 0 ? round(($d['count']/$total)*100) : 0 }}%;background:{{ $d['color'] }};border-radius:3px"></div>
                            </div>
                            <span style="font-size:.82rem;font-weight:700;color:#0f172a;min-width:24px;text-align:right">{{ $d['count'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── MODULES SHORTCUTS ── --}}
<div class="gs-card mb-4">
    <div class="gs-card-header">
        <h6 class="gs-card-title">
            <span style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-th-large" style="font-size:.85rem"></i>
            </span>
            Accès Rapide aux Modules
        </h6>
    </div>
    <div class="gs-card-body pb-2">

        {{-- GROUPE : Activité Clinique --}}
        <div class="gs-section-label">🏥 Activité Clinique</div>
        <div class="row g-2 mb-4">
            @php $clinical = [
                ['icon'=>'fa-stethoscope',   'label'=>'Consultations','desc'=>'Dossiers cliniques',    'route'=>route('consultations.index'),      'bg'=>'var(--grad-teal)',   'badge'=>$stats['total_consultations']??0],
                ['icon'=>'fa-clock',         'label'=>"File d'attente",'desc'=>'Patients en attente',  'route'=>route('liste.attente'),            'bg'=>'var(--grad-violet)', 'badge'=>null],
                ['icon'=>'fa-calendar-check','label'=>'Rendez-vous',  'desc'=>'Planning médical',      'route'=>route('rendezvous.index'),         'bg'=>'var(--grad-green)',  'badge'=>$stats['total_rendezvou']??0],
                ['icon'=>'fa-file-medical',  'label'=>'Ordonnances',  'desc'=>'Prescriptions émises',  'route'=>route('ordonnances.index'),        'bg'=>'var(--grad-amber)',  'badge'=>$stats['total_ordonnance']??0],
                ['icon'=>'fa-vials',         'label'=>'Examens',      'desc'=>'Analyses biologiques',  'route'=>route('examens.index'),            'bg'=>'var(--grad-rose)',   'badge'=>$stats['total_examens']??0],
                ['icon'=>'fa-heartbeat',     'label'=>'Suivis',       'desc'=>'Évolution clinique',    'route'=>route('suivis.index'),             'bg'=>'var(--grad-dark)',   'badge'=>null],
            ]; @endphp
            @foreach($clinical as $m)
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ $m['route'] }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
                   style="border:1.5px solid #e2e8f0;background:#fff;transition:all .2s;min-height:68px"
                   onmouseover="this.style.borderColor='#0891b2';this.style.background='#f0f9ff';this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.style.transform='none'">
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $m['bg'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
                        <i class="fas {{ $m['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div style="font-size:.83rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $m['label'] }}</div>
                        <div style="font-size:.72rem;color:#94a3b8">{{ $m['desc'] }}</div>
                    </div>
                    @if($m['badge'] !== null)
                    <span style="background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700;padding:.15rem .5rem;border-radius:50px;flex-shrink:0">{{ $m['badge'] }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>

        {{-- GROUPE : Patients & Hospitalisation --}}
        <div class="gs-section-label">👤 Patients & Hospitalisation</div>
        <div class="row g-2 mb-4">
            @php $patients = [
                ['icon'=>'fa-user-injured',  'label'=>'Patients',       'desc'=>'Dossiers médicaux',       'route'=>route('patients.index'),          'bg'=>'var(--grad-green)',  'badge'=>$stats['total_patients']??0],
                ['icon'=>'fa-procedures',    'label'=>'Hospitalisations','desc'=>'Admissions en cours',     'route'=>route('hospitalisations.index'),  'bg'=>'var(--grad-amber)',  'badge'=>null],
                ['icon'=>'fa-bed',           'label'=>'Lits',            'desc'=>'Gestion des lits',        'route'=>route('lits.index'),              'bg'=>'var(--grad-rose)',   'badge'=>$stats['total_lits']??0],
                ['icon'=>'fa-door-open',     'label'=>'Salles',          'desc'=>'Salles d\'hospitalisation','route'=>route('salles.index'),            'bg'=>'var(--grad-violet)', 'badge'=>null],
                ['icon'=>'fa-ticket-alt',    'label'=>'Tickets',         'desc'=>'Salle d\'attente',        'route'=>route('tickets.index'),           'bg'=>'var(--grad-teal)',   'badge'=>$stats['total_ticket']??0],
                ['icon'=>'fa-baby',          'label'=>'Maternité',       'desc'=>'Grossesses & CPN',        'route'=>route('maternity.index'),         'bg'=>'var(--grad-dark)',   'badge'=>null],
            ]; @endphp
            @foreach($patients as $m)
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ $m['route'] }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
                   style="border:1.5px solid #e2e8f0;background:#fff;transition:all .2s;min-height:68px"
                   onmouseover="this.style.borderColor='#10b981';this.style.background='#f0fdf4';this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.style.transform='none'">
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $m['bg'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
                        <i class="fas {{ $m['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div style="font-size:.83rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $m['label'] }}</div>
                        <div style="font-size:.72rem;color:#94a3b8">{{ $m['desc'] }}</div>
                    </div>
                    @if($m['badge'] !== null)
                    <span style="background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700;padding:.15rem .5rem;border-radius:50px;flex-shrink:0">{{ $m['badge'] }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>

        {{-- GROUPE : Pharmacie & Stock --}}
        <div class="gs-section-label">💊 Pharmacie & Stock</div>
        <div class="row g-2 mb-4">
            @php $pharma = [
                ['icon'=>'fa-pills',         'label'=>'Médicaments', 'desc'=>'Gestion du stock',    'route'=>route('medicaments.index'), 'bg'=>'var(--grad-green)',  'badge'=>$stats['total_medicament']??0],
                ['icon'=>'fa-shopping-cart', 'label'=>'Commandes',   'desc'=>'Approvisionnement',   'route'=>route('commandes.index'),   'bg'=>'var(--grad-teal)',   'badge'=>null],
                ['icon'=>'fa-truck',         'label'=>'Réceptions',  'desc'=>'Livraisons reçues',   'route'=>route('receptions.index'),  'bg'=>'var(--grad-amber)',  'badge'=>null],
                ['icon'=>'fa-warehouse',     'label'=>'Fournisseurs','desc'=>'Partenaires supply',  'route'=>route('fournisseurs.index'),'bg'=>'var(--grad-violet)', 'badge'=>$stats['total_fournisseur']??0],
            ]; @endphp
            @foreach($pharma as $m)
            <div class="col-xl-3 col-md-6 col-6">
                <a href="{{ $m['route'] }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
                   style="border:1.5px solid #e2e8f0;background:#fff;transition:all .2s;min-height:68px"
                   onmouseover="this.style.borderColor='#f59e0b';this.style.background='#fffbeb';this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.style.transform='none'">
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $m['bg'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
                        <i class="fas {{ $m['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div style="font-size:.83rem;font-weight:700;color:#0f172a">{{ $m['label'] }}</div>
                        <div style="font-size:.72rem;color:#94a3b8">{{ $m['desc'] }}</div>
                    </div>
                    @if($m['badge'] !== null)
                    <span style="background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700;padding:.15rem .5rem;border-radius:50px;flex-shrink:0">{{ $m['badge'] }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>

        {{-- GROUPE : Administration --}}
        <div class="gs-section-label">⚙️ Administration</div>
        <div class="row g-2">
            @php $admin = [
                ['icon'=>'fa-users',         'label'=>'Utilisateurs',  'desc'=>'Personnel & accès',      'route'=>route('users.index'),        'bg'=>'var(--grad-dark)',   'badge'=>$stats['total_users']??0],
                ['icon'=>'fa-user-tag',      'label'=>'Rôles',         'desc'=>'Permissions système',    'route'=>route('admin.roles.index'),  'bg'=>'var(--grad-violet)', 'badge'=>null],
                ['icon'=>'fa-cash-register', 'label'=>'Caisse',        'desc'=>'Sessions financières',   'route'=>route('caisse.index'),       'bg'=>'var(--grad-green)',  'badge'=>null],
                ['icon'=>'fa-hand-holding-usd','label'=>'Assurances',  'desc'=>'Couvertures médicales',  'route'=>route('assurances.index'),   'bg'=>'var(--grad-teal)',   'badge'=>null],
                ['icon'=>'fa-cog',           'label'=>'Paramètres',    'desc'=>'Config. clinique',       'route'=>route('configuration'),      'bg'=>'var(--grad-dark)',   'badge'=>null],
                ['icon'=>'fa-briefcase-medical','label'=>'Services',   'desc'=>'Unités médicales',       'route'=>route('services.index'),     'bg'=>'var(--grad-rose)',   'badge'=>null],
            ]; @endphp
            @foreach($admin as $m)
            <div class="col-xl-2 col-md-4 col-6">
                <a href="{{ $m['route'] }}" class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
                   style="border:1.5px solid #e2e8f0;background:#fff;transition:all .2s;min-height:68px"
                   onmouseover="this.style.borderColor='#7c3aed';this.style.background='#faf5ff';this.style.transform='translateY(-2px)'"
                   onmouseout="this.style.borderColor='#e2e8f0';this.style.background='#fff';this.style.transform='none'">
                    <div style="width:42px;height:42px;border-radius:12px;background:{{ $m['bg'] }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
                        <i class="fas {{ $m['icon'] }}"></i>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div style="font-size:.83rem;font-weight:700;color:#0f172a">{{ $m['label'] }}</div>
                        <div style="font-size:.72rem;color:#94a3b8">{{ $m['desc'] }}</div>
                    </div>
                    @if($m['badge'] !== null)
                    <span style="background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:700;padding:.15rem .5rem;border-radius:50px;flex-shrink:0">{{ $m['badge'] }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>

    </div>
</div>

{{-- ── ADMIN QUICK ACTIONS ── --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-rose-light);color:var(--med-rose);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-exclamation-triangle" style="font-size:.85rem"></i>
                    </span>
                    Alertes Système
                </h6>
            </div>
            <div class="gs-card-body">
                <div class="gs-mini-stat mb-2">
                    <div class="gs-mini-stat-icon" style="background:var(--med-rose-light);color:var(--med-rose)">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.82rem;font-weight:600;color:#374151">Ruptures de stock</div>
                        <div style="font-size:.75rem;color:#94a3b8">Médicaments à 0 unité</div>
                    </div>
                    <span class="badge bg-danger rounded-pill" style="font-size:.8rem">0</span>
                </div>
                <div class="gs-mini-stat mb-2">
                    <div class="gs-mini-stat-icon" style="background:var(--med-amber-light);color:var(--med-amber)">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.82rem;font-weight:600;color:#374151">Lits disponibles</div>
                        <div style="font-size:.75rem;color:#94a3b8">Capacité d'hospitalisation</div>
                    </div>
                    <span class="badge bg-warning text-dark rounded-pill" style="font-size:.8rem">{{ ($stats['total_lits']??0) }}</span>
                </div>
                <div class="gs-mini-stat">
                    <div class="gs-mini-stat-icon" style="background:var(--med-green-light);color:var(--med-green)">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.82rem;font-weight:600;color:#374151">Personnel actif</div>
                        <div style="font-size:.75rem;color:#94a3b8">Médecins + secrétaires</div>
                    </div>
                    <span class="badge bg-success rounded-pill" style="font-size:.8rem">{{ ($stats['total_medecins']??0) + ($stats['total_secretaires']??0) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-tools" style="font-size:.85rem"></i>
                    </span>
                    Administration
                </h6>
            </div>
            <div class="gs-card-body">
                <a href="{{ route('users.create') }}" class="gs-action-btn" style="background:var(--med-teal-light);color:var(--med-teal-dark)">
                    <div class="icon-box" style="background:var(--med-teal);color:#fff"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600">Créer un utilisateur</div>
                        <div style="font-size:.73rem;opacity:.7">Médecin, secrétaire, admin…</div>
                    </div>
                </a>
                <a href="{{ route('admin.roles.index') }}" class="gs-action-btn" style="background:var(--med-violet-light);color:#6d28d9">
                    <div class="icon-box" style="background:#7c3aed;color:#fff"><i class="fas fa-user-tag"></i></div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600">Gérer les rôles</div>
                        <div style="font-size:.73rem;opacity:.7">Permissions et accès</div>
                    </div>
                </a>
                <a href="{{ route('configuration') }}" class="gs-action-btn" style="background:#f1f5f9;color:#374151">
                    <div class="icon-box" style="background:#475569;color:#fff"><i class="fas fa-cog"></i></div>
                    <div>
                        <div style="font-size:.85rem;font-weight:600">Paramètres système</div>
                        <div style="font-size:.73rem;opacity:.7">Configuration clinique</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
