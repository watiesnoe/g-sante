@extends('layouts.app')

@section('title_page', isset($commande) ? 'Modifier Commande' : 'Commande de Médicaments')
@section('page_link')
    <a href="{{ route('commandes.index') }}">Commande</a>
@endsection
@section('page_name', isset($commande) ? 'Modifier Commande' : 'Nouvelle Commande')

@section('content')
    <div class="container-fluid">
        <form method="POST" action="{{ isset($commande) ? route('commandes.update', $commande->id) : route('commandes.store') }}">
            @csrf
            @if(isset($commande))
                @method('PUT')
            @endif

            <div class="row">
                {{-- Informations Commande --}}
                <div class="col-md-12">
                    <div class="block block-rounded">
                        <div class="block-header bg-primary text-white">
                            <h4 class="block-title">{{ isset($commande) ? 'Commande #' . $commande->reference : 'Nouvelle Commande' }}</h4>
                        </div>
                        <div class="block-content">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label>Référence</label>
                                    <input type="text" name="reference" value="{{ isset($commande) ? $commande->reference : 'CMD-' . str_pad(App\Models\Commande::count() + 1, 4, '0', STR_PAD_LEFT) }}" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label>Date</label>
                                    <input type="date" name="date_commande" value="{{ isset($commande) ? $commande->date_commande->format('Y-m-d') : date('Y-m-d') }}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label>Fournisseur</label>
                                    <select name="fournisseur_id" class="form-control js-select2" required>
                                        <option value="">-- Choisir un fournisseur --</option>
                                        @foreach($fournisseurs as $f)
                                            <option value="{{ $f->id }}" {{ isset($commande) && $f->id == $commande->fournisseur_id ? 'selected' : '' }}>
                                                {{ $f->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Médicaments --}}
                <div class="col-md-12">
                    <div class="block block-rounded mt-3">
                        <div class="block-content">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <select id="selectMedicament" class="form-control js-select2">
                                        <option value="">-- Choisir un médicament --</option>
                                        @foreach($medicaments as $m)
                                            <option value="{{ $m->id }}" data-prix="{{ $m->prix_achat }}">{{ $m->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table class="table table-bordered" id="table-panier">
                                <thead class="table-light">
                                <tr>
                                    <th>Médicament</th>
                                    <th width="20%">Quantité</th>
                                    <th width="20%">Prix Unitaire</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Montant total :</th>
                                    <th id="total_general">0</th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>

                            <button type="submit" class="btn btn-primary mb-3">
                                {{ isset($commande) ? 'Mettre à jour la commande' : 'Valider la commande' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            $('.js-select2').select2({ placeholder: "Sélectionner", allowClear: true, width: '100%' });

            // Charger le panier depuis session ou la commande existante
            let panier = {};

            @if(isset($commande))
                @foreach($commande->lignes as $ligne)
                panier[{{ $ligne->medicament_id }}] = {
                id: {{ $ligne->medicament_id }},
                nom: "{{ $ligne->medicament->nom }}",
                quantite: {{ $ligne->quantite }},
                prix_unitaire: {{ $ligne->prix_unitaire }}
            };
            @endforeach
                @else
                panier = {!! json_encode(session('panier', [])) !!};
            @endif

            updateTable(panier);

            // Ajouter médicament
            $('#selectMedicament').change(function(){
                let id = $(this).val();
                if(!id) return;
                if(panier[id]){
                    panier[id].quantite += 1;
                } else {
                    let nom = $(this).find('option:selected').text();
                    let prix = parseFloat($(this).find('option:selected').data('prix'));
                    panier[id] = {id:id, nom:nom, quantite:1, prix_unitaire:prix};
                }
                updateTable(panier);
                $(this).val(null).trigger('change');

                @if(!isset($commande))
                // Async pour session si création
                $.post("{{ route('commandes.panier.ajouter') }}", {_token:"{{ csrf_token() }}", medicament_id:id});
                @endif
            });

            // Supprimer médicament
            $('#table-panier').on('click', '.remove', function(){
                let id = $(this).data('id');
                delete panier[id];
                updateTable(panier);

                @if(!isset($commande))
                $.post("{{ route('commandes.panier.supprimer') }}",{_token:"{{ csrf_token() }}", medicament_id:id});
                @endif
            });

            // Modifier quantité/prix localement
            $('#table-panier').on('input', '.quantite, .prix', function(){
                let tr = $(this).closest('tr');
                let id = tr.data('id');
                panier[id].quantite = parseInt(tr.find('.quantite').val());
                panier[id].prix_unitaire = parseFloat(tr.find('.prix').val());
                updateTable(panier);
            });

            function updateTable(p){
                let tbody = $('#table-panier tbody');
                tbody.empty();
                let total = 0;
                Object.values(p).forEach(item => {
                    total += item.quantite * item.prix_unitaire;
                    tbody.append(`
                <tr data-id="${item.id}">
                    <td><input type="hidden" name="medicament_id[]" value="${item.id}">${item.nom}</td>
                    <td><input type="number" name="quantite[]" class="form-control quantite" value="${item.quantite}" min="1"></td>
                    <td><input type="number" name="prix_unitaire[]" class="form-control prix" step="0.01" value="${item.prix_unitaire}"></td>
                    <td class="total">${(item.quantite*item.prix_unitaire).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove" data-id="${item.id}">X</button></td>
                </tr>
            `);
                });
                $('#total_general').text(total.toFixed(2));
            }
        });
    </script>
@endsection
