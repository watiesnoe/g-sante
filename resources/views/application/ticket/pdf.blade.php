<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $ticket->id }} — G-Santé</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        @page {
            size: A4 portrait;
            margin: 8mm 8mm 8mm 8mm;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            background: #f0f4f8;
            color: #1a202c;
            font-size: 10px;
            width: 100%;
        }

        .page {
            width: 100%;
            padding: 10px 14px;
            background: #f0f4f8;
        }

        /* ─── Ticket Card ─── */
        .ticket-card {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            position: relative;
            page-break-inside: avoid;
        }

        /* ─── Header Band ─── */
        .ticket-header {
            background: linear-gradient(135deg, #0652c5 0%, #0891b2 100%);
            padding: 8px 14px;
            display: table;
            width: 100%;
        }
        .ticket-header-left { display: table-cell; vertical-align: middle; }
        .ticket-header-right { display: table-cell; vertical-align: middle; text-align: right; }

        .clinic-name {
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 1px;
            display: inline;
        }
        .clinic-tagline {
            font-size: 7.5px;
            color: rgba(255,255,255,0.75);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: block;
            margin-top: 1px;
        }
        .ticket-badge {
            background: rgba(255,255,255,0.18);
            border: 1.5px solid rgba(255,255,255,0.45);
            border-radius: 6px;
            padding: 3px 10px;
            display: inline-block;
        }
        .ticket-badge-label { font-size: 7px; color: rgba(255,255,255,0.70); text-transform: uppercase; letter-spacing: 1px; }
        .ticket-badge-number { font-size: 13px; font-weight: bold; color: #ffffff; letter-spacing: 1px; }

        /* ─── Status Bar ─── */
        .status-bar {
            background: #f7fafc;
            border-bottom: 1px solid #e8edf3;
            padding: 4px 14px;
            display: table;
            width: 100%;
        }
        .status-cell {
            display: table-cell;
            padding: 0 10px 0 0;
            vertical-align: middle;
            border-right: 1px solid #e2e8f0;
        }
        .status-cell:last-child { border-right: none; padding-right: 0; }
        .status-label { font-size: 7px; color: #718096; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 1px; }
        .status-value { font-size: 9px; font-weight: bold; color: #2d3748; }

        .badge-statut {
            display: inline-block; padding: 1px 7px; border-radius: 20px;
            font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge-en_attente { background: #fef3c7; color: #92400e; border: 1px solid #f59e0b; }
        .badge-valide     { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .badge-paye       { background: #dbeafe; color: #1e40af; border: 1px solid #3b82f6; }
        .badge-expire     { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

        /* ─── Body ─── */
        .ticket-body { padding: 8px 14px; }

        /* Patient block */
        .patient-block {
            display: table; width: 100%;
            background: #eef6ff; border-radius: 6px; border: 1px solid #bfdbfe;
            padding: 5px 10px; margin-bottom: 8px;
        }
        .patient-icon-cell { display: table-cell; width: 26px; vertical-align: middle; }
        .patient-icon {
            width: 22px; height: 22px; background: #2563eb; border-radius: 50%;
            text-align: center; line-height: 22px; color: #fff; font-size: 11px; font-weight: bold;
        }
        .patient-info-cell { display: table-cell; vertical-align: middle; padding-left: 8px; }
        .patient-name { font-size: 11px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; }
        .patient-sub { font-size: 8px; color: #3b82f6; }

        /* ─── Section title ─── */
        .section-title {
            font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #718096;
            margin-bottom: 4px; padding-bottom: 3px; border-bottom: 1.5px solid #e8edf3; font-weight: bold;
        }

        /* ─── Table prestations ─── */
        .prestations-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .prestations-table thead tr { background: #1e3a8a; }
        .prestations-table thead th {
            padding: 4px 6px; font-size: 8px; color: #ffffff;
            text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold; border: none;
        }
        .prestations-table thead th:last-child { text-align: right; }
        .prestations-table tbody tr:nth-child(even) { background: #f7fafc; }
        .prestations-table tbody tr:nth-child(odd)  { background: #ffffff; }
        .prestations-table tbody td {
            padding: 4px 6px; border-bottom: 1px solid #e8edf3;
            font-size: 9px; color: #2d3748; vertical-align: middle;
        }
        .prestations-table tbody td:last-child { text-align: right; font-weight: bold; color: #1e3a8a; }
        .prestation-name { font-weight: 600; color: #1a202c; }
        .prestation-detail { font-size: 7.5px; color: #718096; margin-top: 1px; }
        .row-number {
            display: inline-block; width: 15px; height: 15px; background: #e0e7ff; color: #3730a3;
            border-radius: 50%; text-align: center; line-height: 15px; font-size: 7.5px; font-weight: bold;
        }

        /* ─── Totaux ─── */
        .totals-block { display: table; width: 100%; margin-top: 4px; }
        .totals-spacer { display: table-cell; width: 55%; }
        .totals-table-cell { display: table-cell; width: 45%; vertical-align: top; }
        .totals-inner { background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
        .total-row { display: table; width: 100%; padding: 4px 10px; border-bottom: 1px solid #e8edf3; }
        .total-row:last-child { border-bottom: none; }
        .total-row-label { display: table-cell; font-size: 8.5px; color: #718096; }
        .total-row-value { display: table-cell; text-align: right; font-size: 9px; font-weight: bold; color: #2d3748; }
        .grand-total-row { background: #1e3a8a; display: table; width: 100%; padding: 6px 10px; }
        .grand-total-label { display: table-cell; font-size: 9px; color: rgba(255,255,255,0.85); font-weight: bold; }
        .grand-total-value { display: table-cell; text-align: right; font-size: 12px; font-weight: bold; color: #ffffff; }

        /* ─── Assurance block ─── */
        .assurance-block {
            background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;
            padding: 5px 10px; margin-bottom: 6px; display: table; width: 100%;
        }
        .assurance-left { display: table-cell; vertical-align: middle; }
        .assurance-right { display: table-cell; vertical-align: middle; text-align: right; }
        .assurance-tag {
            display: inline-block; background: #15803d; color: #fff; font-size: 7px; font-weight: bold;
            padding: 1px 6px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;
        }
        .assurance-label { font-size: 8px; color: #166534; }

        /* ─── Tear Line ─── */
        .tear-line {
            text-align: center; font-size: 7.5px; color: #b0bec5;
            letter-spacing: 2px; margin: 5px 0; text-transform: uppercase;
        }

        /* ─── Footer ─── */
        .ticket-footer {
            background: #f7fafc; border-top: 1px solid #e8edf3;
            padding: 5px 14px; display: table; width: 100%;
        }
        .footer-left { display: table-cell; vertical-align: middle; font-size: 7.5px; color: #718096; }
        .footer-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 7px; color: #a0aec0; }
        .validity-badge {
            display: inline-block; background: #fef3c7; color: #92400e; border: 1px solid #fbbf24;
            border-radius: 4px; padding: 1px 6px; font-size: 7px; font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ═══════════ TICKET 1 ═══════════ --}}
    <div class="ticket-card" style="margin-bottom: 6px;">
        @include('application.ticket.ticket_content', ['ticket' => $ticket])
    </div>

    {{-- ─── Ligne de découpe ─── --}}
    <div class="tear-line" style="margin: 4px 0;">- - - - - - - - - - - Couper ici / Garder ce coupon - - - - - - - - - - -</div>

    {{-- ═══════════ TICKET 2 ═══════════ --}}
    <div class="ticket-card" style="border: 1.5px dashed #94a3b8; margin-top: 6px;">
        @include('application.ticket.ticket_content', ['ticket' => $ticket])
    </div>

</div>
</body>
</html>
