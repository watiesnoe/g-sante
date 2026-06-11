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
<div class="ticket-header-container" style="padding: 14px 18px 0 18px;">
    @include('layouts.pdf_header', [
        'docNumber' => 'TCK-' . \Carbon\Carbon::parse($ticket->created_at)->format('Ymd') . '-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT),
    ])
</div>

{{-- ── STATUS BAR ── --}}
<table class="status-bar" style="border-collapse: collapse; width: 100%;">
    <tr>
        <td class="status-cell status-cell-first" style="width:28%;">
            <div class="status-label">Date d'&eacute;mission</div>
            <div class="status-value">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y') }}</div>
        </td>
        <td class="status-cell" style="width:18%;">
            <div class="status-label">Heure</div>
            <div class="status-value">{{ \Carbon\Carbon::parse($ticket->created_at)->format('H:i') }}</div>
        </td>
        <td class="status-cell" style="width:26%;">
            <div class="status-label">Statut</div>
            <div class="status-value">
                <span class="badge-statut badge-{{ $statut }}">{{ $statutLabel }}</span>
            </div>
        </td>
        <td class="status-cell status-cell-last" style="width:28%;">
            <div class="status-label">Emis par</div>
            <div class="status-value">{{ $ticket->user->name ?? '—' }}</div>
        </td>
    </tr>
</table>

{{-- ── BODY ── --}}
<div class="ticket-body">

    {{-- Patient ── --}}
    <table class="patient-block" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td class="patient-icon-cell">
                <div class="patient-icon">{{ $initiale }}</div>
            </td>
            <td class="patient-info-cell">
                <div class="patient-name">
                    {{ strtoupper($ticket->patient->nom ?? '') }} {{ $ticket->patient->prenom ?? '' }}
                </div>
                <div class="patient-sub">
                    @if($ticket->patient->telephone ?? false) &nbsp;Tél : {{ $ticket->patient->telephone }} @endif
                    @if($ticket->patient->age ?? false) &nbsp;&bull;&nbsp;{{ $ticket->patient->age }} ans @endif
                    @if($ticket->medecin ?? false) &nbsp;&bull;&nbsp;Dr. {{ $ticket->medecin->name }} @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Prestations ── --}}
    <div class="section-title">D&eacute;tail des Prestations</div>
    <table class="prestations-table" style="border-collapse: collapse; width: 100%;">
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

    {{-- Assurance ── --}}
    @if($ticket->assurance)
    <table class="assurance-block" style="border-collapse: collapse; width: 100%;">
        <tr>
            <td class="assurance-left">
                <span class="assurance-tag">Assurance</span>
                <div class="assurance-label">
                    <strong>{{ $ticket->assurance->nom }}</strong>
                    &mdash; Couverture {{ $ticket->taux_couverture }}%
                </div>
            </td>
            <td class="assurance-right">
                <div style="font-size:7.5px; color:#166534;">Prise en charge</div>
                <div style="font-size:11px; font-weight:bold; color:#15803d;">
                    {{ number_format($ticket->part_assurance, 0, ',', ' ') }} FCFA
                </div>
            </td>
        </tr>
    </table>
    @endif

    {{-- Totaux ── --}}
    <table style="width: 100%; margin-top: 6px; border-collapse: collapse;">
        <tr>
            <!-- Spacer cell -->
            <td style="width: 55%;"></td>
            <!-- Totals cell -->
            <td style="width: 45%; vertical-align: top; padding-left: 12px;">
                <table style="width: 100%; background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; border-collapse: collapse; overflow: hidden;">
                    <tr>
                        <td style="padding: 6px 12px; font-size: 9px; color: #718096; border-bottom: 1px solid #e8edf3;">Sous-total</td>
                        <td style="padding: 6px 12px; text-align: right; font-size: 9.5px; font-weight: bold; color: #2d3748; border-bottom: 1px solid #e8edf3;">
                            {{ number_format($ticket->total, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @if($ticket->assurance)
                    <tr>
                        <td style="padding: 6px 12px; font-size: 9px; color: #718096; border-bottom: 1px solid #e8edf3;">Part assurance</td>
                        <td style="padding: 6px 12px; text-align: right; font-size: 9.5px; font-weight: bold; color: #15803d; border-bottom: 1px solid #e8edf3;">
                            - {{ number_format($ticket->part_assurance, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                    @endif
                    <tr style="background: #1e3a8a;">
                        <td style="padding: 8px 12px; font-size: 10px; color: rgba(255,255,255,0.85); font-weight: bold; border-radius: 0 0 0 6px;">NET &Agrave; PAYER</td>
                        <td style="padding: 8px 12px; text-align: right; font-size: 14px; font-weight: bold; color: #ffffff; border-radius: 0 0 6px 0;">
                            {{ number_format($ticket->part_patient ?? $ticket->total, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</div>

{{-- ── FOOTER ── --}}
<table class="ticket-footer" style="border-collapse: collapse; width: 100%;">
    <tr>
        <td class="footer-left">
            Merci de votre confiance &mdash; G-Sant&eacute;, votre sant&eacute; est notre priorit&eacute;.
        </td>
        <td class="footer-right">
            <span class="validity-badge">
                Valide jusqu'au {{ \Carbon\Carbon::parse($ticket->date_validite)->format('d/m/Y') }}
            </span>
        </td>
    </tr>
</table>
