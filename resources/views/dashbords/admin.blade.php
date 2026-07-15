@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
@endphp

{{-- HERO BANNER --}}
<div class="gs-banner mb-4" style="background: var(--grad-dark);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="position:relative;z-index:2">
            <div class="gs-time-badge">
                <i class="fas fa-chart-line text-warning"></i>
                <span>Gestion Administrative & Financière</span>
            </div>
            <div class="gs-banner-title">{{ $greeting }}, {{ Auth::user()->prenom }}</div>
            <div class="gs-banner-subtitle">
                Revenus ce mois : <strong style="font-size:1.2rem;color:var(--med-amber)">{{ number_format($stats['revenus_mois'] ?? 0, 0, ',', ' ') }} F CFA</strong>
            </div>
            <div class="mt-3 d-flex gap-3 flex-wrap">
                <div>
                    <div style="font-size:.7rem;opacity:.7;font-weight:600;text-transform:uppercase">Personnel</div>
                    <div style="font-size:1.75rem;font-weight:800">{{ $stats['total_personnel'] ?? 0 }}</div>
                </div>
                <div style="border-left:1px solid rgba(255,255,255,.2);margin:0 .5rem"></div>
                <div>
                    <div style="font-size:.7rem;opacity:.7;font-weight:600;text-transform:uppercase">Cons. / mois</div>
                    <div style="font-size:1.75rem;font-weight:800">{{ $stats['consultations_mois'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="text-end d-none d-md-block" style="position:relative;z-index:2">
            <div id="gs-clock"></div>
            <div id="gs-date"></div>
            <div class="mt-2">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                    <i class="fas fa-shield-alt me-1"></i> Mode Admin
                </span>
            </div>
        </div>
    </div>
    <i class="fas fa-coins gs-banner-icon"></i>
</div>

{{-- KPI CARDS --}}
<div class="row g-3 mb-4">
    @php
    $adminKPIs = [
        ['label'=>'Personnel Actif',   'value'=>$stats['total_personnel']??0, 'sub'=>'Staff enregistré',      'icon'=>'fa-user-tie',    'bg'=>'var(--grad-teal)',   'bar'=>'#0891b2', 'route'=>route('users.index'), 'module'=>'users'],
        ['label'=>'Consultations/mois','value'=>$stats['consultations_mois']??0,'sub'=>'Activité mensuelle',   'icon'=>'fa-stethoscope', 'bg'=>'var(--grad-green)',  'bar'=>'#10b981', 'route'=>route('consultations.index'), 'module'=>'consultation'],
        ['label'=>'Alertes Stock',     'value'=>$stats['alertes_stock']??0,    'sub'=>'Articles critiques',   'icon'=>'fa-exclamation-triangle','bg'=>'var(--grad-rose)',   'bar'=>'#f43f5e', 'route'=>route('medicaments.index'), 'module'=>'stock'],
        ['label'=>'Total Patients',    'value'=>$stats['total_patients']??0,   'sub'=>'Base patients',       'icon'=>'fa-users',         'bg'=>'var(--grad-violet)', 'bar'=>'#7c3aed', 'route'=>route('patients.index'), 'module'=>'patient'],
    ];
    $adminKPIs = array_filter($adminKPIs, function($k) {
        return auth()->user()->hasModuleAccess($k['module']);
    });
    $kpiCount = count($adminKPIs);
    $kpiColClass = match($kpiCount) {
        4 => 'col-xl-3 col-md-6',
        3 => 'col-xl-4 col-md-6',
        2 => 'col-xl-6 col-md-6',
        1 => 'col-xl-12 col-md-12',
        default => 'd-none',
    };
    @endphp
    @foreach($adminKPIs as $k)
    <div class="{{ $kpiColClass }}">
        <a href="{{ $k['route'] }}" class="gs-kpi h-100">
            @if($k['label'] == 'Alertes Stock' && $k['value'] > 0)
                <span class="gs-kpi-trend bg-danger-subtle text-danger"><span class="pulse-dot danger" style="width:6px;height:6px"></span> CRITIQUE</span>
            @endif
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

{{-- SECONDARY CONTENT --}}
@php
    $hasStock = auth()->user()->hasModuleAccess('stock');
    $hasUsers = auth()->user()->hasModuleAccess('users');
    $hasCaisse = auth()->user()->hasModuleAccess('caisse');
    $hasParametre = auth()->user()->hasModuleAccess('parametre');
    $hasPaiements = auth()->user()->hasModuleAccess('paiements');
    $hasSystem = $hasUsers || $hasCaisse || $hasStock || $hasParametre;
    $hasFinance = $hasCaisse || $hasPaiements;
    $hasRightCol = $hasSystem || $hasFinance;
@endphp
@if($hasStock || $hasRightCol)
<div class="row g-3">
    {{-- Stock Alerts --}}
    @if($hasStock)
    <div class="{{ $hasRightCol ? 'col-lg-7' : 'col-lg-12' }}">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-rose-light);color:var(--med-rose);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-pills" style="font-size:.85rem"></i>
                    </span>
                    Alertes de Stock Prioritaires
                </h6>
                @if(isset($lowStockMedicaments) && $lowStockMedicaments->count() > 0)
                    <span class="badge bg-danger rounded-pill">{{ $lowStockMedicaments->count() }} articles</span>
                @endif
            </div>
            <div class="gs-scroll-list" style="max-height:350px">
                @if(isset($lowStockMedicaments) && $lowStockMedicaments->count() > 0)
                    @foreach($lowStockMedicaments as $med)
                        @php $isCrit = $med->stock <= 5; @endphp
                        <div class="gs-stock-item px-4 py-3 {{ $isCrit ? 'critical' : 'low' }}">
                            <div class="flex-grow-1">
                                <div style="font-size:.9rem;font-weight:700;color:#0f172a">{{ $med->nom }}</div>
                                <div style="font-size:.75rem;color:#64748b;margin-top:.2rem">
                                    Seuil d'alerte : {{ $med->stock_min }} unités
                                </div>
                                <div class="gs-stock-progress" style="width:150px;margin-top:.5rem">
                                    @php $pct = $med->stock_min > 0 ? min(100, ($med->stock / $med->stock_min) * 100) : 0; @endphp
                                    <div class="gs-stock-progress-bar" style="width:{{ $pct }}%;background:{{ $isCrit ? 'var(--med-rose)' : 'var(--med-amber)' }}"></div>
                                </div>
                            </div>
                            <div class="text-end">
                                <div style="font-size:1.1rem;font-weight:800;color:{{ $isCrit ? 'var(--med-rose)' : 'var(--med-amber)' }}">{{ $med->stock }}</div>
                                <div style="font-size:.7rem;text-transform:uppercase;font-weight:700;color:#94a3b8">Unités</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <div style="font-size:3rem">✅</div>
                        <div style="font-size:.9rem;color:var(--med-green);font-weight:600;margin-top:1rem">Tout est sous contrôle. Aucun article en rupture.</div>
                    </div>
                @endif
            </div>
            @if(isset($lowStockMedicaments) && $lowStockMedicaments->count() > 0)
            <div class="gs-card-body pt-0 text-center">
                <a href="{{ route('commandes.index') }}" class="btn btn-sm btn-primary rounded-pill px-4 mt-3">Passer une commande</a>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Admin Actions --}}
    @if($hasRightCol)
    <div class="{{ $hasStock ? 'col-lg-5' : 'col-lg-12' }}">
        @if($hasSystem)
        <div class="gs-card mb-3">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-teal-light);color:var(--med-teal);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-tools" style="font-size:.85rem"></i>
                    </span>
                    Gestion Système
                </h6>
            </div>
            <div class="gs-card-body">
                <div class="row g-2">
                    @if($hasUsers)
                    <div class="col-6">
                        <a href="{{ route('users.index') }}" class="gs-module-btn p-3">
                            <div class="gs-module-icon bg-primary-subtle text-primary"><i class="fas fa-users-cog"></i></div>
                            <span>Utilisateurs</span>
                        </a>
                    </div>
                    @endif
                    @if($hasCaisse)
                    <div class="col-6">
                        <a href="{{ route('caisse.index') }}" class="gs-module-btn p-3">
                            <div class="gs-module-icon bg-success-subtle text-success"><i class="fas fa-cash-register"></i></div>
                            <span>Caisse</span>
                        </a>
                    </div>
                    @endif
                    @if($hasStock)
                    <div class="col-6">
                        <a href="{{ route('medicaments.index') }}" class="gs-module-btn p-3">
                            <div class="gs-module-icon bg-info-subtle text-info"><i class="fas fa-pills"></i></div>
                            <span>Pharmacie</span>
                        </a>
                    </div>
                    @endif
                    @if($hasParametre)
                    <div class="col-6">
                        <a href="{{ route('configuration') }}" class="gs-module-btn p-3">
                            <div class="gs-module-icon bg-secondary-subtle text-secondary"><i class="fas fa-cogs"></i></div>
                            <span>Config</span>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($hasFinance)
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-amber-light);color:var(--med-amber);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-hand-holding-usd" style="font-size:.85rem"></i>
                    </span>
                    Performance Financière
                </h6>
            </div>
            <div class="gs-card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.85rem;color:#64748b">Objectif mensuel</span>
                    <span style="font-size:.85rem;font-weight:700">75% atteint</span>
                </div>
                <div class="progress mb-3" style="height:10px;border-radius:5px">
                    <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" style="width: 75%"></div>
                </div>
                <p style="font-size:.78rem;color:#94a3b8;line-height:1.4">
                    Les revenus de ce mois sont en hausse de 12% par rapport au mois dernier. Continuez à surveiller les impayés d'ordonnances.
                </p>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
@endif
