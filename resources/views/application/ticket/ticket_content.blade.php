@php
    $statut = $ticket->statut ?? 'en_attente';
    $statutLabels = [
        'en_attente' => 'En attente',
        'valide'     => 'Validé',
        'paye'       => 'Payé',
        'expire'     => 'Expiré',
    ];
    $statutLabel = $statutLabels[$statut] ?? ucfirst($statut);
    $logoPath    = public_path('image/logo/logo.png');
    $initiale    = strtoupper(substr($ticket->patient->nom ?? 'P', 0, 1));
@endphp

{{-- ── HEADER ── --}}
<div class="ticket-header">
    <div class="ticket-header-left">
        @if(file_exists($logoPath))
            <img src="{{ $logoPath }}" style="height:28px; vertical-align:middle; margin-right:8px;">
        @endif
        <span class="clinic-name">G-SANT&Eacute;</span>
        <span class="clinic-tagline">Clinique M&eacute;dicale &amp; Centre d'Excellence</span>
    </div>
    <div class="ticket-header-right">
        <div class="ticket-badge">
            <div class="ticket-badge-label">Ticket N&deg;</div>
            <div class="ticket-badge-number">#{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>
</div>

{{-- ── STATUS BAR ── --}}
<div class="status-bar">
    <div class="status-cell" style="width:28%;">
        <div class="status-label">Date d'&eacute;mission</div>
        <div class="status-value">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y') }}</div>
    </div>
    <div class="status-cell" style="width:18%; padding-left:10px;">
        <div class="status-label">Heure</div>
        <div class="status-value">{{ \Carbon\Carbon::parse($ticket->created_at)->format('H:i') }}</div>
    </div>
    <div class="status-cell" style="width:26%; padding-left:10px;">
        <div class="status-label">Statut</div>
        <div class="status-value">
            <span class="badge-statut badge-{{ $statut }}">{{ $statutLabel }}</span>
        </div>
    </div>
    <div class="status-cell" style="width:28%; padding-left:10px; border-right:none;">
        <div class="status-label">Emis par</div>
        <div class="status-value">{{ $ticket->user->name ?? '—' }}</div>
    </div>
</div>

{{-- ── BODY ── --}}
<div class="ticket-body">

    {{-- Patient --}}
    <div class="patient-block">
        <div class="patient-icon-cell">
            <div class="patient-icon">{{ $initiale }}</div>
        </div>
        <div class="patient-info-cell">
            <div class="patient-name">
                {{ strtoupper($ticket->patient->nom ?? '') }} {{ $ticket->patient->prenom ?? '' }}
            </div>
            <div class="patient-sub">
                @if($ticket->patient->telephone ?? false) &nbsp;Tél : {{ $ticket->patient->telephone }} @endif
                @if($ticket->patient->age ?? false) &nbsp;&bull;&nbsp;{{ $ticket->patient->age }} ans @endif
                @if($ticket->medecin ?? false) &nbsp;&bull;&nbsp;Dr. {{ $ticket->medecin->name }} @endif
            </div>
        </div>
    </div>

    {{-- Prestations --}}
    <div class="section-title">D&eacute;tail des Prestations</div>
    <table class="prestations-table">
        <thead>
            <tr>
                <th style="width:22px; text-align:center;">#</th>
                <th>D&eacute;signation</th>
                <th style="text-align:center; width:40px;">Qt&eacute;</th>
                <th style="text-align:right; width:75px;">P.U (FCFA)</th>
                <th style="text-align:right; width:35px;">Rem.</th>
                <th style="text-align:right; width:80px;">Sous-total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ticket->items as $index => $item)
            <tr>
                <td style="text-align:center;">
                    <span class="row-number">{{ $index + 1 }}</span>
                </td>
                <td>
                    <div class="prestation-name">{{ $item->prestation->nom ?? $item->service ?? '—' }}</div>
                    @if($item->prestation->serviceMedical->nom ?? null)
                        <div class="prestation-detail">{{ $item->prestation->serviceMedical->nom }}</div>
                    @endif
                </td>
                <td style="text-align:center;">{{ $item->quantite }}</td>
                <td style="text-align:right;">{{ number_format($item->prix_unitaire, 0, ',', ' ') }}</td>
                <td style="text-align:right;">{{ $item->remise > 0 ? $item->remise.'%' : '-' }}</td>
                <td>{{ number_format($item->sous_total, 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#a0aec0; padding:8px;">
                    Aucune prestation enregistr&eacute;e.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Assurance --}}
    @if($ticket->assurance)
    <div class="assurance-block">
        <div class="assurance-left">
            <span class="assurance-tag">Assurance</span>
            <div class="assurance-label">
                <strong>{{ $ticket->assurance->nom }}</strong>
                &mdash; Couverture {{ $ticket->taux_couverture }}%
            </div>
        </div>
        <div class="assurance-right">
            <div style="font-size:7.5px; color:#166534;">Prise en charge</div>
            <div style="font-size:11px; font-weight:bold; color:#15803d;">
                {{ number_format($ticket->part_assurance, 0, ',', ' ') }} FCFA
            </div>
        </div>
    </div>
    @endif

    {{-- Totaux --}}
    <div class="totals-block">
        <div class="totals-spacer"></div>
        <div class="totals-table-cell" style="padding-left:12px;">
            <div class="totals-inner">
                <div class="total-row">
                    <div class="total-row-label">Sous-total</div>
                    <div class="total-row-value">{{ number_format($ticket->total, 0, ',', ' ') }} FCFA</div>
                </div>
                @if($ticket->assurance)
                <div class="total-row">
                    <div class="total-row-label">Part assurance</div>
                    <div class="total-row-value" style="color:#15803d;">
                        - {{ number_format($ticket->part_assurance, 0, ',', ' ') }} FCFA
                    </div>
                </div>
                @endif
                <div class="grand-total-row">
                    <div class="grand-total-label">NET &Agrave; PAYER</div>
                    <div class="grand-total-value">
                        {{ number_format($ticket->part_patient ?? $ticket->total, 0, ',', ' ') }} FCFA
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── FOOTER ── --}}
<div class="ticket-footer">
    <div class="footer-left">
        Merci de votre confiance &mdash; G-Sant&eacute;, votre sant&eacute; est notre priorit&eacute;.
    </div>
    <div class="footer-right">
        <span class="validity-badge">
            Valide jusqu'au {{ \Carbon\Carbon::parse($ticket->date_validite)->format('d/m/Y') }}
        </span>
    </div>
</div>
