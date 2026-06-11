<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ticket de Consultation</title>
<style>
    * { margin: 0; padding: 0; }

    @page { size: A4 landscape; margin: 0; }

    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        font-size: 9px;
        color: #1a1a2e;
        background: #fff;
        margin: 0;
        padding: 0;
    }

    table { border-collapse: collapse; }

    /* ── Divider ── */
    .sep-blue { background: #1565c0; }
    .sep-blue-thin { background: #1565c0; opacity: 0.3; }

    /* ── Section badges ── */
    .section-header {
        background: #1565c0;
        color: #fff;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 3px 6px;
        margin-bottom: 3px;
    }

    /* ── Meta labels ── */
    .meta-label { font-size: 7px; color: #777; text-transform: uppercase; }
    .meta-value { font-size: 9px; font-weight: bold; color: #1565c0; }
    .meta-normal { color: #1a1a2e; }
    .meta-green  { color: #2e7d32; }

    /* ── Facturation table ── */
    .factu th {
        font-size: 7.5px; color: #555; font-weight: normal;
        text-transform: uppercase; border-bottom: 1px solid #1565c0;
        padding: 2px 4px;
    }
    .factu td { font-size: 8.5px; padding: 2px 4px; border-bottom: 1px solid #eee; }
    .factu tfoot td {
        font-size: 10px; font-weight: bold; color: #1565c0;
        border-top: 2px solid #1565c0; border-bottom: 2px solid #1565c0;
        padding: 3px 4px;
    }

    /* ── Payment boxes ── */
    .payment-box {
        background: #e8f5e9; border: 1px solid #a5d6a7;
        text-align: center; padding: 3px 5px; vertical-align: middle;
    }
    .payment-label  { font-size: 7px; color: #2e7d32; }
    .payment-amount { font-size: 11px; font-weight: bold; color: #1b5e20; }

    /* ── Barcode ── */
    .barcode { font-family: monospace; font-size: 24px; letter-spacing: -1px; line-height: 1; }

    /* ── Coupon styles ── */
    .cp-title { font-size: 8.5px; font-weight: bold; color: #1565c0; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
    .cp-section { font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: #1a1a2e; padding: 2px 0; }
    .cp-label { font-size: 7px; font-weight: bold; color: #555; }
    .cp-val   { font-size: 7px; color: #1a1a2e; }
    .cp-total { font-size: 8px; font-weight: bold; color: #1565c0; }
    .cp-statut { font-size: 8px; font-weight: bold; color: #1565c0; text-transform: uppercase; }
    .cp-barcode { font-family: monospace; font-size: 19px; letter-spacing: -1px; line-height: 1; text-align: center; }
</style>
</head>
<body>
@php
    $tckNum     = 'TCK-' . \Carbon\Carbon::parse($ticket->created_at)->format('Y') . '-' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT);
    $date       = \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y');
    $heure      = \Carbon\Carbon::parse($ticket->created_at)->format('H:i');
    $statut     = $ticket->statut ?? 'en_attente';
    $statutLabels = ['en_attente'=>'En attente','valide'=>'Validé','paye'=>'Payé','expire'=>'Expiré'];
    $statutLabel  = $statutLabels[$statut] ?? ucfirst($statut);
    $logoPath   = public_path('image/logo/logo.png');
    $patient    = $ticket->patient;
    $patCode    = 'PAT-' . str_pad($patient->id ?? 0, 6, '0', STR_PAD_LEFT);
    $nomComplet = strtoupper($patient->nom ?? '') . ' ' . ($patient->prenom ?? '');
    $montantPaye  = $ticket->part_patient ?? $ticket->total;
    $resteAPayer  = 0;
    $bc = str_repeat('| ', 20) . '|';
    // First item service
    $firstItem    = $ticket->items->first();
    $serviceName  = $firstItem->prestation->serviceMedical->nom ?? null;
    $medecinName  = $ticket->medecin->name ?? null;
@endphp

{{-- ROOT TABLE: two columns --}}
<table style="width:297mm; border-collapse:collapse;">
<tr>

{{-- ══════════════════ MAIN TICKET ══════════════════ --}}
<td style="width:192mm; vertical-align:top; padding:7mm 8mm; border-right:2px dashed #bbb;">

    {{-- HEADER --}}
    <table style="width:100%; margin-bottom:3mm;">
        <tr>
            <td style="width:18mm; vertical-align:middle;">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height:15mm;">
                @else
                    <div style="width:15mm;height:15mm;background:#e8f4fd;border-radius:50%;text-align:center;line-height:15mm;font-size:8px;color:#1565c0;font-weight:bold;">G+</div>
                @endif
            </td>
            <td style="padding-left:4mm; vertical-align:middle;">
                <div style="font-size:15px;font-weight:bold;color:#1565c0;letter-spacing:0.5px;">G-SANTÉ</div>
                <div style="font-size:7.5px;color:#555;margin-top:1px;">Soins de qualité, notre priorité</div>
                <div style="font-size:7px;color:#555;margin-top:2px;">📍 Ségou, Mali &nbsp; 📞 (+225) 07 00 00 00 00 / 05 00 00 00 00</div>
            </td>
        </tr>
    </table>
    <div style="height:2px;background:#1565c0;margin-bottom:2px;"></div>
    <div style="height:1px;background:#9bb8e0;margin-bottom:4mm;"></div>

    {{-- TITLE --}}
    <div style="text-align:center;font-size:13px;font-weight:bold;color:#1565c0;letter-spacing:2px;text-transform:uppercase;border-bottom:2px solid #1565c0;padding-bottom:2mm;margin-bottom:3mm;">
        Ticket de Consultation
    </div>

    {{-- META ROW --}}
    <table style="width:100%; margin-bottom:3mm;">
        <tr>
            <td style="padding-right:5mm;">
                <div class="meta-label">N° Ticket</div>
                <div class="meta-value">{{ $tckNum }}</div>
            </td>
            <td style="padding-right:5mm;">
                <div class="meta-label">Date</div>
                <div class="meta-value meta-normal">{{ $date }}</div>
            </td>
            <td style="padding-right:5mm;">
                <div class="meta-label">Heure</div>
                <div class="meta-value meta-normal">{{ $heure }}</div>
            </td>
            <td style="padding-right:5mm;">
                <div class="meta-label">Statut</div>
                <div class="meta-value meta-normal">{{ $statutLabel }}</div>
            </td>
            <td>
                <div class="meta-label">Émis par</div>
                <div class="meta-value meta-normal">{{ $ticket->user->name ?? '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- SECTION: PATIENT --}}
    <div class="section-header">Informations du Patient</div>
    <table style="width:100%; border:1px solid #d0ddf5; margin-bottom:3mm;">
        <tr>
            <td style="width:20mm; text-align:center; vertical-align:middle; padding:3mm; border-right:1px solid #d0ddf5;">
                <div style="width:14mm;height:17mm;background:#e8f4fd;border:1px solid #90caf9;border-radius:3px;text-align:center;line-height:17mm;font-size:18px;color:#1565c0;display:inline-block;">👤</div>
            </td>
            <td style="padding:2mm 3mm; vertical-align:top;">
                <table style="width:100%;">
                    <tr>
                        <td style="width:28mm;font-size:8.5px;color:#555;padding:1px 0;">Code Patient</td>
                        <td style="width:4mm;color:#555;">:</td>
                        <td style="font-size:8.5px;font-weight:bold;">{{ $patCode }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:8.5px;color:#555;padding:1px 0;">Nom et Prénom</td>
                        <td style="color:#555;">:</td>
                        <td style="font-size:8.5px;font-weight:bold;">{{ $nomComplet }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:8.5px;color:#555;padding:1px 0;">Sexe</td>
                        <td style="color:#555;">:</td>
                        <td style="font-size:8.5px;font-weight:bold;">{{ $patient->genre ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:8.5px;color:#555;padding:1px 0;">Âge</td>
                        <td style="color:#555;">:</td>
                        <td style="font-size:8.5px;font-weight:bold;">{{ $patient->age ? $patient->age . ' ans' : '—' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size:8.5px;color:#555;padding:1px 0;">Téléphone</td>
                        <td style="color:#555;">:</td>
                        <td style="font-size:8.5px;font-weight:bold;">{{ $patient->telephone ?? '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- SECTION: CONSULTATION --}}
    <div class="section-header">Consultation</div>
    <table style="width:100%; border:1px solid #d0ddf5; margin-bottom:3mm;">
        @if($serviceName)
        <tr>
            <td style="width:6mm;text-align:center;padding:2px 3px;border-bottom:1px solid #eee;color:#1565c0;font-size:10px;">🏥</td>
            <td style="width:28mm;font-size:8.5px;color:#555;padding:2px 3px;border-bottom:1px solid #eee;">Service</td>
            <td style="font-size:8.5px;font-weight:bold;padding:2px 3px;border-bottom:1px solid #eee;">{{ $serviceName }}</td>
        </tr>
        @endif
        @if($medecinName)
        <tr>
            <td style="width:6mm;text-align:center;padding:2px 3px;border-bottom:1px solid #eee;color:#1565c0;font-size:10px;">👨‍⚕️</td>
            <td style="font-size:8.5px;color:#555;padding:2px 3px;border-bottom:1px solid #eee;">Médecin</td>
            <td style="font-size:8.5px;font-weight:bold;padding:2px 3px;border-bottom:1px solid #eee;">Dr. {{ $medecinName }}</td>
        </tr>
        @endif
        @if($ticket->description)
        <tr>
            <td style="width:6mm;text-align:center;padding:2px 3px;color:#1565c0;font-size:10px;">📋</td>
            <td style="font-size:8.5px;color:#555;padding:2px 3px;">Motif</td>
            <td style="font-size:8.5px;padding:2px 3px;">{{ $ticket->description }}</td>
        </tr>
        @endif
        @if(!$serviceName && !$medecinName && !$ticket->description)
        <tr><td colspan="3" style="padding:4px 6px; color:#aaa; font-size:8px; text-align:center;">—</td></tr>
        @endif
    </table>

    {{-- SECTION: FACTURATION --}}
    <div class="section-header">Facturation</div>
    <table class="factu" style="width:100%; margin-bottom:2mm;">
        <thead>
            <tr>
                <th style="text-align:left;">Désignation</th>
                <th style="text-align:right; width:35mm;">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ticket->items as $item)
            <tr>
                <td>{{ $item->prestation->nom ?? $item->service ?? '—' }}</td>
                <td style="text-align:right; font-weight:bold;">{{ number_format($item->sous_total, 0, ',', ' ') }}</td>
            </tr>
            @empty
            <tr><td colspan="2" style="text-align:center;color:#aaa;">Aucune prestation</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td style="text-align:right;">{{ number_format($ticket->total, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- PAYMENT --}}
    <table style="width:100%; margin-bottom:3mm;">
        <tr>
            <td class="payment-box" style="width:50%; border-right:1px solid #a5d6a7;">
                <div class="payment-label">Montant payé</div>
                <div class="payment-amount">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</div>
            </td>
            <td class="payment-box" style="width:50%;">
                <div class="payment-label">Reste à payer</div>
                <div class="payment-amount">{{ number_format($resteAPayer, 0, ',', ' ') }} FCFA</div>
            </td>
        </tr>
    </table>

    {{-- ASSURANCE --}}
    @if($ticket->assurance)
    <table style="width:100%; border:1px solid #e0e0e0; background:#fafafa; margin-bottom:3mm;">
        <tr>
            <td style="padding:2px 4mm; border-right:1px solid #e0e0e0;">
                <div style="font-size:7px;color:#888;">Assurance</div>
                <div style="font-weight:bold;font-size:8.5px;">{{ $ticket->assurance->nom }}</div>
            </td>
            <td style="padding:2px 4mm; border-right:1px solid #e0e0e0;">
                <div style="font-size:7px;color:#888;">Couverture</div>
                <div style="font-weight:bold;font-size:8.5px;">{{ $ticket->taux_couverture }}%</div>
            </td>
            <td style="padding:2px 4mm;">
                <div style="font-size:7px;color:#888;">Part assurance</div>
                <div style="font-weight:bold;font-size:8.5px;color:#2e7d32;">{{ number_format($ticket->part_assurance, 0, ',', ' ') }} FCFA</div>
            </td>
        </tr>
    </table>
    @endif

    {{-- BARCODE --}}
    <table style="width:100%; margin-top:2mm;">
        <tr>
            <td style="width:75%; vertical-align:middle;">
                <div class="barcode">{{ $bc }}</div>
                <div style="font-size:7px;color:#555;margin-top:1mm;">{{ $tckNum }}</div>
            </td>
            <td style="width:25%; text-align:right; vertical-align:middle;">
                <div style="width:18mm;height:18mm;background:repeating-linear-gradient(45deg,#f0f0f0,#f0f0f0 2px,#fff 2px,#fff 4px);border:1px solid #ddd;display:inline-block;"></div>
            </td>
        </tr>
    </table>
    <div style="text-align:center;font-size:7.5px;font-weight:bold;color:#1565c0;text-transform:uppercase;letter-spacing:0.5px;margin-top:2mm;">
        Merci de conserver ce ticket jusqu'à la fin de votre prise en charge.
    </div>

</td>

{{-- ══════════════════ COUPON ══════════════════ --}}
<td style="width:105mm; vertical-align:top; padding:6mm 7mm;">

    {{-- Coupon Header --}}
    <table style="width:100%; margin-bottom:2mm;">
        <tr>
            <td style="width:11mm; vertical-align:middle;">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height:9mm;">
                @else
                    <div style="width:9mm;height:9mm;background:#e8f4fd;border-radius:50%;text-align:center;line-height:9mm;font-size:6px;color:#1565c0;font-weight:bold;">G+</div>
                @endif
            </td>
            <td style="padding-left:2mm; vertical-align:middle;">
                <div class="coupon-clinic-name" style="font-size:10px;font-weight:bold;color:#1565c0;">G-SANTÉ</div>
                <div style="font-size:6.5px;color:#555;">Ségou – Mali</div>
                <div style="font-size:6.5px;color:#555;">Tél : (+225) 07 00 00 00 00</div>
            </td>
        </tr>
    </table>

    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>
    <div class="cp-title">Ticket de Consultation</div>
    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>

    <table style="width:100%; margin-bottom:2mm;">
        <tr>
            <td class="cp-label" style="width:17mm;">TICKET</td>
            <td style="width:4mm;font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $tckNum }}</td>
        </tr>
        <tr>
            <td class="cp-label">DATE</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $date }}</td>
        </tr>
        <tr>
            <td class="cp-label">HEURE</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $heure }}</td>
        </tr>
        <tr>
            <td class="cp-label">ÉMIS PAR</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $ticket->user->name ?? '—' }}</td>
        </tr>
    </table>

    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>
    <div class="cp-section">Patient</div>
    <table style="width:100%; margin-bottom:2mm;">
        <tr>
            <td class="cp-label" style="width:12mm;">Code</td>
            <td style="width:3mm;font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $patCode }}</td>
        </tr>
        <tr>
            <td class="cp-label">Nom</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $nomComplet }}</td>
        </tr>
        <tr>
            <td class="cp-label">Sexe</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $patient->genre ?? '—' }}</td>
        </tr>
        <tr>
            <td class="cp-label">Âge</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $patient->age ? $patient->age . ' ans' : '—' }}</td>
        </tr>
        <tr>
            <td class="cp-label">Tél</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $patient->telephone ?? '—' }}</td>
        </tr>
    </table>

    @if($medecinName || $serviceName)
    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>
    <div class="cp-section">Consultation</div>
    <table style="width:100%; margin-bottom:2mm;">
        @if($serviceName)
        <tr>
            <td class="cp-label" style="width:12mm;">Service</td>
            <td style="width:3mm;font-size:7px;color:#555;">:</td>
            <td class="cp-val">{{ $serviceName }}</td>
        </tr>
        @endif
        @if($medecinName)
        <tr>
            <td class="cp-label">Médecin</td>
            <td style="font-size:7px;color:#555;">:</td>
            <td class="cp-val">Dr. {{ $medecinName }}</td>
        </tr>
        @endif
    </table>
    @endif

    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>
    <div class="cp-section">Facturation</div>
    <table style="width:100%; margin-bottom:1mm;">
        @foreach($ticket->items as $item)
        <tr>
            <td class="cp-val" style="padding:0.5mm 0;">{{ $item->prestation->nom ?? '—' }}</td>
            <td class="cp-val" style="text-align:right;font-weight:bold;padding:0.5mm 0;">{{ number_format($item->sous_total, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endforeach
    </table>
    <table style="width:100%; border-top:1px solid #aaa; padding-top:1mm;">
        <tr>
            <td class="cp-total">TOTAL</td>
            <td class="cp-total" style="text-align:right;">{{ number_format($ticket->total, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>
    <table style="width:100%; margin-bottom:1mm;">
        <tr>
            <td class="cp-val">Payé</td>
            <td class="cp-val" style="text-align:right;font-weight:bold;">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td class="cp-val">Reste à payer</td>
            <td class="cp-val" style="text-align:right;">{{ number_format($resteAPayer, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>
    <div class="cp-statut">Statut : {{ strtoupper($statutLabel) }}</div>
    <div style="border-top:1px dashed #aaa; margin:2mm 0;"></div>

    <div style="font-size:7px;color:#555;text-align:center;font-style:italic;margin-bottom:2mm;">Merci de votre confiance</div>

    <div class="cp-barcode">{{ $bc }}</div>
    <div style="font-size:6px;color:#555;text-align:center;margin-top:1mm;">{{ $tckNum }}</div>

</td>

</tr>
</table>

</body>
</html>