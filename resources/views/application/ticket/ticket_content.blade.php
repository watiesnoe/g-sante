<h2>Clinique Santé Plus</h2>
<h3>Ticket N° {{ $ticket->id }}</h3>
<p><strong>Patient :</strong> {{ $ticket->patient->nom ?? '' }} {{ $ticket->patient->prenom ?? '' }}</p>
<p><strong>Date :</strong> {{ \Carbon\Carbon::parse($ticket->created_at)->format('d/m/Y H:i') }}</p>

<table>
    <thead>
    <tr>
        <th>Prestation</th>
        <th>Quantité</th>
        <th>Prix (F CFA)</th>
        <th>Total</th>
    </tr>
    </thead>
    <tbody>
    @foreach($ticket->items as $item)
        <tr>
            <td>{{ $item->service ?? '-' }}</td>
            <td>{{ $item->quantite }}</td>
            <td>{{ number_format($item->prix_unitaire, 0, ',', ' ') }}</td>
            <td>{{ number_format($item->sous_total, 0, ',', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    <strong>Total : {{ number_format($ticket->total, 0, ',', ' ') }} F CFA</strong><br>
    <small>Validité : {{ \Carbon\Carbon::parse($ticket->date_validite)->format('d/m/Y') }}</small>
</div>
