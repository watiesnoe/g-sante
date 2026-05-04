<div style="text-align: center; margin-bottom: 10px;">
    @php $logoPath = public_path('image/logo/logo.png'); @endphp
    @if(file_exists($logoPath))
        <img src="{{ $logoPath }}" style="height: 50px; margin-bottom: 5px;">
    @endif
    <h2 style="margin: 0; font-size: 18px; color: #0665d0;">G-SANTÉ</h2>
    <p style="margin: 0; font-size: 9px; color: #666;">Clinique Médicale & Centre d'Excellence</p>
</div>

<div style="border-top: 1px solid #eee; border-bottom: 1px solid #eee; padding: 10px 0; margin-bottom: 10px;">
    <table style="width: 100%; border: none !important;">
        <tr>
            <td style="border: none !important; padding: 0;">
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Ticket N°</div>
                <div style="font-size: 14px; font-weight: bold; color: #0665d0;">#{{ $ticket->id }}</div>
            </td>
            <td style="border: none !important; padding: 0; text-align: right;">
                <div style="font-size: 10px; color: #666; text-transform: uppercase;">Date</div>
                <div style="font-size: 11px;">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>
</div>

<div style="margin-bottom: 15px;">
    <div style="font-size: 10px; color: #666; text-transform: uppercase;">Patient</div>
    <div style="font-size: 13px; font-weight: bold;">{{ $ticket->patient->nom ?? '' }} {{ $ticket->patient->prenom ?? '' }}</div>
</div>

<table class="items-table" style="width: 100%; border-collapse: collapse; font-size: 11px;">
    <thead>
        <tr style="background: #f8f9fa;">
            <th style="padding: 5px; text-align: left; border-bottom: 1px solid #ddd;">Prestation</th>
            <th style="padding: 5px; text-align: right; border-bottom: 1px solid #ddd;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ticket->items as $item)
            <tr>
                <td style="padding: 8px 5px; border-bottom: 1px solid #eee;">
                    {{ $item->service ?? '-' }}
                    <div style="font-size: 9px; color: #888;">{{ $item->quantite }} x {{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</div>
                </td>
                <td style="padding: 8px 5px; text-align: right; border-bottom: 1px solid #eee; font-weight: bold;">
                    {{ number_format($item->sous_total, 0, ',', ' ') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 15px; text-align: right;">
    <div style="font-size: 12px; color: #666;">Montant Total Payé</div>
    <div style="font-size: 18px; font-weight: bold; color: #0665d0;">{{ number_format($ticket->total, 0, ',', ' ') }} FCFA</div>
</div>

<div style="margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px; text-align: center; font-size: 9px; color: #888;">
    Merci de votre confiance.<br>
    Ce ticket est valable jusqu'au {{ \Carbon\Carbon::parse($ticket->date_validite)->format('d/m/Y') }}
</div>
