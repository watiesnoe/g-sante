@if($lots->isEmpty())
    <p class="text-muted">Aucun.</p>
@else
    <table class="table table-sm table-striped fs-sm">
        <thead><tr><th>Médicament</th><th>Lot</th><th>Péremption</th><th class="text-end">Quantité</th></tr></thead>
        <tbody>
            @foreach($lots as $lot)
                <tr>
                    <td>{{ $lot->medicament->nom ?? '-' }}</td>
                    <td>{{ $lot->numero }}</td>
                    <td>{{ $lot->date_peremption?->format('d/m/Y') }}</td>
                    <td class="text-end">{{ $lot->quantite }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
