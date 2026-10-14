@if($medicaments->isEmpty())
    <p class="text-muted">Aucun.</p>
@else
    <table class="table table-sm table-striped fs-sm">
        <thead><tr><th>Médicament</th><th class="text-end">Stock</th><th class="text-end">Seuil</th></tr></thead>
        <tbody>
            @foreach($medicaments as $m)
                <tr>
                    <td>{{ $m->nom }}</td>
                    <td class="text-end fw-semibold">{{ $m->stock }}</td>
                    <td class="text-end">{{ $m->stock_min }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
