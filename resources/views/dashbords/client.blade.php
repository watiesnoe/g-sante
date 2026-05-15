@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
@endphp

{{-- HERO BANNER --}}
<div class="gs-banner mb-4" style="background: linear-gradient(135deg, #0891b2 0%, #0d9488 100%);">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div style="position:relative;z-index:2">
            <div class="gs-time-badge">
                <i class="fas fa-user-circle"></i>
                <span>Mon Espace Santé</span>
            </div>
            <div class="gs-banner-title">{{ $greeting }}, {{ Auth::user()->prenom }}</div>
            <div class="gs-banner-subtitle">
                Bienvenue dans votre espace personnel sécurisé.
            </div>
            <div class="mt-3">
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(255,255,255,.2);backdrop-filter:blur(5px);border:1px solid rgba(255,255,255,.3)">
                    <i class="fas fa-calendar-check me-1"></i> {{ $mesRendezVous->count() }} RDV à venir
                </span>
            </div>
        </div>
        <div class="text-end d-none d-md-block" style="position:relative;z-index:2">
            <div id="gs-clock"></div>
            <div id="gs-date"></div>
        </div>
    </div>
    <i class="fas fa-heartbeat gs-banner-icon"></i>
</div>

<div class="row g-3">
    {{-- Appointments --}}
    <div class="col-lg-6">
        <div class="gs-card h-100">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-teal-light);color:var(--med-teal);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-calendar-alt" style="font-size:.85rem"></i>
                    </span>
                    Mes Prochains Rendez-vous
                </h6>
            </div>
            <div class="gs-scroll-list" style="max-height:300px">
                @forelse($mesRendezVous as $rdv)
                <div class="gs-appt-item px-4 py-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:#f0f9ff;color:var(--med-teal);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                        <i class="far fa-calendar-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.9rem;font-weight:700;color:#0f172a">{{ \Carbon\Carbon::parse($rdv->date_heure)->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
                        <div style="font-size:.78rem;color:#64748b;margin-top:.1rem">
                            <i class="far fa-clock me-1"></i> {{ \Carbon\Carbon::parse($rdv->date_heure)->format('H:i') }}
                            <span class="mx-2 opacity-25">|</span>
                            <i class="fas fa-user-md me-1"></i> Dr. {{ $rdv->medecin->prenom ?? 'N/A' }} {{ $rdv->medecin->name ?? '' }}
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size:.7rem">{{ ucfirst($rdv->statut) }}</span>
                </div>
                @empty
                <div class="text-center py-5">
                    <div style="font-size:2.5rem;opacity:.3">📅</div>
                    <p style="font-size:.85rem;color:#94a3b8;margin-top:1rem">Vous n'avez aucun rendez-vous prévu.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Ordonnances --}}
    <div class="col-lg-6">
        <div class="gs-card h-100">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-green-light);color:var(--med-green);display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-file-medical" style="font-size:.85rem"></i>
                    </span>
                    Mes Ordonnances Actives
                </h6>
            </div>
            <div class="gs-scroll-list" style="max-height:300px">
                @forelse($ordonnancesActives as $ordo)
                <div class="gs-appt-item px-4 py-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:#f0fdf4;color:var(--med-green);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                        <i class="fas fa-prescription"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.9rem;font-weight:700;color:#0f172a">Ordonnance #{{ $ordo->id }}</div>
                        <div style="font-size:.78rem;color:#64748b;margin-top:.1rem">
                            Émise le {{ \Carbon\Carbon::parse($ordo->created_at)->format('d/m/Y') }}
                        </div>
                    </div>
                    <a href="{{ route('ordonnances.show', $ordo->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3" style="font-size:.75rem">Voir</a>
                </div>
                @empty
                <div class="text-center py-5">
                    <div style="font-size:2.5rem;opacity:.3">💊</div>
                    <p style="font-size:.85rem;color:#94a3b8;margin-top:1rem">Aucune ordonnance active.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    {{-- Last Consultation --}}
    <div class="col-12">
        <div class="gs-card">
            <div class="gs-card-header">
                <h6 class="gs-card-title">
                    <span style="width:32px;height:32px;border-radius:8px;background:var(--med-violet-light);color:#7c3aed;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-stethoscope" style="font-size:.85rem"></i>
                    </span>
                    Ma Dernière Consultation
                </h6>
            </div>
            <div class="gs-card-body p-4">
                @if($derniereConsultation)
                <div class="d-flex align-items-center gap-4">
                    <div style="font-size:3rem;color:#cbd5e1">🏥</div>
                    <div>
                        <div style="font-size:1.1rem;font-weight:800;color:#0f172a">Consultation du {{ \Carbon\Carbon::parse($derniereConsultation->created_at)->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
                        <p class="text-muted mb-0 mt-1">
                            Motif : {{ $derniereConsultation->motif ?? 'N/A' }} <br>
                            Médecin : Dr. {{ $derniereConsultation->medecin->prenom ?? '' }} {{ $derniereConsultation->medecin->name ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('consultations.show', $derniereConsultation->id) }}" class="btn btn-primary rounded-pill px-4">Détails du dossier</a>
                    </div>
                </div>
                @else
                <div class="text-center py-4">
                    <p style="font-size:.85rem;color:#94a3b8">Historique de consultation indisponible.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
