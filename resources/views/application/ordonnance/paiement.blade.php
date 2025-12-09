@extends('layouts.app')

@section('titre', 'Paiement de l\'ordonnance')

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">💳 Paiement de l'Ordonnance #{{ $ordonnance->id }}</h2>

        <form id="paiementForm" method="post" >
            @csrf
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Choisir</th>
                        <th>Médicament</th>
                        <th>Quantité Prescrite</th>
                        <th>Stock Disponible</th>
                        <th>Prix Unitaire</th>
                        <th>Quantité à payer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicaments as $med)
                    @php
                        $quantitePrescrite = $med->pivot->quantite ?? 1; // ou champ correct
                        $maxQty = min($quantitePrescrite, $med->stock);
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="med-checkbox" data-med-id="{{ $med->id }}">
                        </td>
                        <td>{{ $med->nom }}</td>
                        <td>{{ $quantitePrescrite }}</td>
                        <td>{{ $med->stock }}</td>
                        <td>{{ $med->prix_vente }} CFA</td>
                        <td>
                            <input type="number" name="medicaments[{{ $med->id }}]"
                                   max="{{ $maxQty }}" min="0" class="form-control qty-input"
                                   data-med-id="{{ $med->id }}" value="0" disabled>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <button class="btn btn-success" type="submit">💳 Payer</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){

            // Activer/désactiver le champ quantité selon checkbox
            $('.med-checkbox').change(function(){
                let medId = $(this).data('med-id');
                let qtyInput = $('.qty-input[data-med-id="'+medId+'"]');
                if($(this).is(':checked')){
                    qtyInput.prop('disabled', false);
                    qtyInput.val(qtyInput.attr('max')); // par défaut payer max disponible
                } else {
                    qtyInput.prop('disabled', true);
                    qtyInput.val(0);
                }
            });

            // Soumission AJAX
            $('#paiementForm').submit(function(e){
                e.preventDefault();

                $.ajax({
                    url: "{{ route('ordonnances.payer', $ordonnance->id) }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res){
                        if(res.success){
                            Swal.fire({
                                icon: 'success',
                                title: 'Paiement effectué !',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = "{{ route('ordonnances.index') }}";
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erreur',
                                text: res.message
                            });
                        }
                    },
                    error: function(){
                        Swal.fire({
                            icon: 'error',
                            title: 'Erreur',
                            text: 'Une erreur est survenue lors du paiement.'
                        });
                    }
                });
            });

        });
    </script>
@endsection
