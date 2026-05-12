@extends('layouts.app')
@section('title', 'Tableau de Bord — G-Santé')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* ============================
       G-SANTÉ DASHBOARD DESIGN SYSTEM
       ============================ */
    :root {
        --med-teal:        #0891b2;
        --med-teal-dark:   #0e7490;
        --med-teal-light:  #e0f2fe;
        --med-green:       #10b981;
        --med-green-light: #d1fae5;
        --med-amber:       #f59e0b;
        --med-amber-light: #fef3c7;
        --med-rose:        #f43f5e;
        --med-rose-light:  #ffe4e6;
        --med-violet:      #7c3aed;
        --med-violet-light:#ede9fe;
        --med-slate:       #475569;
        --med-slate-light: #f1f5f9;
        --grad-teal:       linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        --grad-green:      linear-gradient(135deg, #10b981 0%, #059669 100%);
        --grad-amber:      linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --grad-rose:       linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        --grad-violet:     linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
        --grad-dark:       linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        --shadow-sm:       0 1px 3px 0 rgba(0,0,0,.07), 0 1px 2px 0 rgba(0,0,0,.04);
        --shadow-md:       0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -1px rgba(0,0,0,.04);
        --shadow-lg:       0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -2px rgba(0,0,0,.04);
        --shadow-xl:       0 20px 25px -5px rgba(0,0,0,.1),  0 10px 10px -5px rgba(0,0,0,.04);
        --radius-lg:       16px;
        --radius-xl:       20px;
    }

    body { font-family: 'Inter', sans-serif; background-color: #f0f9ff; }

    /* ---- WELCOME BANNER ---- */
    .gs-banner {
        border-radius: var(--radius-xl);
        padding: 2rem 2.5rem;
        position: relative;
        overflow: hidden;
        color: #fff;
        box-shadow: var(--shadow-xl);
        margin-bottom: 1.5rem;
    }
    .gs-banner::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,.1);
        border-radius: 50%;
    }
    .gs-banner::after {
        content: '';
        position: absolute;
        bottom: -80px; right: 80px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,.07);
        border-radius: 50%;
    }
    .gs-banner-icon {
        font-size: 5rem;
        opacity: .15;
        position: absolute;
        right: 2.5rem;
        top: 50%;
        transform: translateY(-50%);
    }
    .gs-banner-title { font-size: 1.75rem; font-weight: 800; margin-bottom: .25rem; }
    .gs-banner-subtitle { font-size: 1rem; opacity: .85; font-weight: 400; }
    .gs-time-badge {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: rgba(255,255,255,.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.3);
        border-radius: 50px;
        padding: .3rem .85rem;
        font-size: .8rem;
        font-weight: 600;
        margin-bottom: .75rem;
    }

    /* ---- KPI CARDS ---- */
    .gs-kpi {
        background: #fff;
        border-radius: var(--radius-lg);
        padding: 1.4rem 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: var(--shadow-sm);
        transition: transform .25s ease, box-shadow .25s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .gs-kpi:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
        color: inherit;
        text-decoration: none;
    }
    .gs-kpi-icon {
        width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        margin-bottom: 1rem;
        flex-shrink: 0;
    }
    .gs-kpi-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        color: #0f172a;
        margin-bottom: .25rem;
    }
    .gs-kpi-label {
        font-size: .82rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: .15rem;
    }
    .gs-kpi-sub {
        font-size: .78rem;
        color: #94a3b8;
    }
    .gs-kpi-bar {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 4px;
        border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    }
    .gs-kpi-trend {
        position: absolute;
        top: 1.2rem; right: 1.2rem;
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .55rem;
        border-radius: 50px;
    }

    /* ---- PULSE DOT (for live indicators) ---- */
    .pulse-dot {
        display: inline-block;
        width: 10px; height: 10px;
        border-radius: 50%;
        position: relative;
    }
    .pulse-dot::after {
        content: '';
        position: absolute;
        top: -3px; left: -3px;
        width: 16px; height: 16px;
        border-radius: 50%;
        animation: pulse-ring 1.5s ease-out infinite;
    }
    .pulse-dot.danger { background: var(--med-rose); }
    .pulse-dot.danger::after { border: 2px solid var(--med-rose); }
    .pulse-dot.success { background: var(--med-green); }
    .pulse-dot.success::after { border: 2px solid var(--med-green); }
    .pulse-dot.warning { background: var(--med-amber); }
    .pulse-dot.warning::after { border: 2px solid var(--med-amber); }

    @keyframes pulse-ring {
        0%   { opacity: 1; transform: scale(1); }
        100% { opacity: 0; transform: scale(1.8); }
    }

    /* ---- CARDS ---- */
    .gs-card {
        background: #fff;
        border-radius: var(--radius-lg);
        border: 1px solid #e2e8f0;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .gs-card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
    }
    .gs-card-title {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .gs-card-body { padding: 1.25rem 1.5rem; }

    /* ---- MODULE SHORTCUTS ---- */
    .gs-module-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.25rem .75rem;
        border-radius: var(--radius-lg);
        border: 2px solid #e2e8f0;
        text-decoration: none;
        color: #374151;
        transition: all .2s ease;
        background: #fff;
        gap: .5rem;
        text-align: center;
    }
    .gs-module-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        border-color: var(--med-teal);
        color: var(--med-teal);
        text-decoration: none;
    }
    .gs-module-btn .gs-module-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        margin-bottom: .25rem;
        transition: transform .2s;
    }
    .gs-module-btn:hover .gs-module-icon { transform: scale(1.1); }
    .gs-module-btn span { font-size: .78rem; font-weight: 600; }

    /* ---- APPOINTMENT LIST ---- */
    .gs-appt-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: .85rem 1.25rem;
        border-bottom: 1px solid #f8fafc;
        transition: background .15s;
    }
    .gs-appt-item:last-child { border-bottom: none; }
    .gs-appt-item:hover { background: #f8fafc; }
    .gs-appt-avatar {
        width: 40px; height: 40px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: .9rem;
        flex-shrink: 0;
    }
    .gs-appt-time {
        font-size: .72rem;
        font-weight: 700;
        color: var(--med-teal);
        letter-spacing: .05em;
    }

    /* ---- STOCK ALERT ITEM ---- */
    .gs-stock-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .75rem 1.25rem;
        border-left: 3px solid transparent;
        border-bottom: 1px solid #f8fafc;
        transition: background .15s;
    }
    .gs-stock-item:last-child { border-bottom: none; }
    .gs-stock-item.critical { border-left-color: var(--med-rose); background: #fff5f5; }
    .gs-stock-item.low { border-left-color: var(--med-amber); background: #fffbeb; }
    .gs-stock-progress {
        height: 6px;
        border-radius: 3px;
        background: #e2e8f0;
        margin-top: .35rem;
        overflow: hidden;
    }
    .gs-stock-progress-bar {
        height: 100%;
        border-radius: 3px;
        transition: width .5s ease;
    }

    /* ---- QUICK ACTION BUTTONS ---- */
    .gs-action-btn {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .9rem 1.25rem;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        font-size: .88rem;
        transition: all .2s ease;
        border: none;
        width: 100%;
        margin-bottom: .5rem;
    }
    .gs-action-btn:last-child { margin-bottom: 0; }
    .gs-action-btn:hover { transform: translateX(4px); text-decoration: none; }
    .gs-action-btn .icon-box {
        width: 36px; height: 36px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        font-size: .9rem;
    }

    /* ---- ROLE BADGE IN HEADER ---- */
    .gs-role-pill {
        background: rgba(255,255,255,.25);
        border: 1px solid rgba(255,255,255,.4);
        padding: .25rem .8rem;
        border-radius: 50px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    /* ---- CLOCK ---- */
    #gs-clock {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: .02em;
        opacity: .9;
    }
    #gs-date {
        font-size: .8rem;
        opacity: .75;
        margin-top: .1rem;
    }

    /* ---- CHART CONTAINERS ---- */
    .gs-chart-wrap { position: relative; padding: 1rem 0 .5rem; }

    /* ---- ANIMATE COUNTERS ---- */
    .counter-val { display: inline-block; }

    /* ---- SECTION LABEL ---- */
    .gs-section-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #94a3b8;
        margin-bottom: .75rem;
    }

    /* ---- STAT MINI BAR ---- */
    .gs-mini-stat {
        background: #f8fafc;
        border-radius: 12px;
        padding: .75rem 1rem;
        display: flex;
        align-items: center;
        gap: .75rem;
    }
    .gs-mini-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: .95rem;
    }

    /* ---- SCROLLABLE LIST ---- */
    .gs-scroll-list { max-height: 340px; overflow-y: auto; }
    .gs-scroll-list::-webkit-scrollbar { width: 4px; }
    .gs-scroll-list::-webkit-scrollbar-track { background: #f1f5f9; }
    .gs-scroll-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    /* Responsive tweaks */
    @media (max-width: 768px) {
        .gs-banner { padding: 1.5rem; }
        .gs-banner-icon { display: none; }
        .gs-banner-title { font-size: 1.35rem; }
        .gs-kpi-value { font-size: 1.6rem; }
    }
</style>
@endsection

@section('content')
<div class="content">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Accueil</a></li>
                    <li class="breadcrumb-item active">Tableau de Bord</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted small d-none d-md-inline">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
            </span>
            <button id="refresh-dashboard" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                <i class="fas fa-sync-alt me-1"></i> Actualiser
            </button>
        </div>
    </div>

    {{-- ===== ROLE-BASED DASHBOARD INCLUDE ===== --}}
    @hasrole('super_admin')
        @include('dashbords.superadmin')
    @elsehasrole('admin')
        @include('dashbords.admin')
    @elsehasrole('secretaire')
        @include('dashbords.secretaire')
    @elsehasrole('medecin')
        @include('dashbords.medecin')
    @elsehasrole('client')
        @include('dashbords.client')
    @else
        @include('dashbords.default')
    @endhasrole

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---- Refresh button ---- */
    const refreshBtn = document.getElementById('refresh-dashboard');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function () {
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Chargement…';
            location.reload();
        });
    }

    /* ---- Animated counter ---- */
    function animateCounter(el, target, duration = 1200) {
        const start = 0;
        const step  = target / (duration / 16);
        let current = start;
        const timer = setInterval(() => {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = Math.floor(current).toLocaleString('fr-FR');
        }, 16);
    }
    document.querySelectorAll('.counter-val[data-target]').forEach(el => {
        animateCounter(el, parseInt(el.dataset.target, 10));
    });

    /* ---- Live clock ---- */
    const clockEl = document.getElementById('gs-clock');
    const dateEl  = document.getElementById('gs-date');
    function updateClock() {
        const now   = new Date();
        const hh    = String(now.getHours()).padStart(2, '0');
        const mm    = String(now.getMinutes()).padStart(2, '0');
        const ss    = String(now.getSeconds()).padStart(2, '0');
        if (clockEl) clockEl.textContent = `${hh}:${mm}:${ss}`;
        const days  = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        const months= ['jan','fév','mar','avr','mai','jun','jul','aoû','sep','oct','nov','déc'];
        if (dateEl) dateEl.textContent = `${days[now.getDay()]} ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    /* ============================================
       SUPERADMIN CHARTS
       ============================================ */
    @hasrole('super_admin')
    const actCtx = document.getElementById('activityChart');
    if (actCtx) {
        new Chart(actCtx, {
            type: 'line',
            data: {
                labels: @json($last7Days->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->locale('fr')->isoFormat('D MMM'))),
                datasets: [
                    {
                        label: 'Consultations',
                        data: @json($last7Days->pluck('consultations')),
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(8,145,178,.08)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#0891b2',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Rendez-vous',
                        data: @json($last7Days->pluck('rendezvous')),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,.08)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Nouveaux patients',
                        data: @json($last7Days->pluck('patients')),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124,58,237,.06)',
                        borderWidth: 2.5,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#7c3aed',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { usePointStyle: true, padding: 20, font: { size: 12, weight: '600' } } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    const donutCtx = document.getElementById('usersChart');
    if (donutCtx) {
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: ['Médecins', 'Patients', 'Secrétaires', 'Admins'],
                datasets: [{
                    data: [
                        {{ $stats['total_medecins'] ?? 0 }},
                        {{ $stats['total_patients'] ?? 0 }},
                        {{ $stats['total_secretaires'] ?? 0 }},
                        {{ $stats['total_admins'] ?? 0 }}
                    ],
                    backgroundColor: ['#0891b2','#10b981','#7c3aed','#f43f5e'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 4,
                }]
            },
            options: {
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                }
            }
        });
    }
    @endhasrole

    /* ============================================
       MEDECIN CHARTS
       ============================================ */
    @hasrole('medecin')
    const consCtx = document.getElementById('consultations-chart');
    if (consCtx) {
        new Chart(consCtx, {
            type: 'bar',
            data: {
                labels: @json($consultationStats['months'] ?? []),
                datasets: [{
                    label: 'Consultations',
                    data: @json($consultationStats['counts'] ?? []),
                    backgroundColor: function(ctx) {
                        const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                        g.addColorStop(0, 'rgba(8,145,178,.85)');
                        g.addColorStop(1, 'rgba(8,145,178,.2)');
                        return g;
                    },
                    borderColor: '#0891b2',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} consultations` } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }
    @endhasrole

});
</script>
@endsection
