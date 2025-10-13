@extends('layouts.app')

@section('titre', isset($commande) ? 'Éditer la commande' : 'Créer une commande')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4>📝 {{ isset($commande) ? 'Modifier la commande' : 'Nouvelle commande' }}</h4>
            </div>
            <div class="card-body">
                <form action="{{ isset($commande) ? route('commandes.update', $commande->id) : route('commandes.store') }}"
                      method="POST" id="commandeForm">
                    @csrf
                    @if(isset($commande))
                        @method('PUT')
                    @endif

                    <!-- Fournisseur -->
                    <div class="mb-3">
                        <label for="fournisseur_id" class="form-label">Fournisseur</label>
                        <select name="fournisseur_id" id="fournisseur_id" class="form-control" required>
                            <option value="">-- Sélectionnez un fournisseur --</option>
                            @foreach($fournisseurs as $f)
                                <option value="{{ $f->id }}" {{ isset($commande) && $commande->fournisseur_id == $f->id ? 'selected' : '' }}>
                                    {{ $f->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date de commande -->
                    <div class="mb-3">
                        <label for="date_commande" class="form-label">Date de commande</label>
                        <input type="date" name="date_commande" id="date_commande" class="form-control" required
                               value="{{ isset($commande) ? \Carbon\Carbon::parse($commande->date_commande)->format('Y-m-d') : now()->format('Y-m-d') }}">
                    </div>

                    <!-- Tableau des médicaments -->
                    <h5 class="mt-4">📦 Médicaments</h5>
                    <table class="table table-bordered" id="medicamentsTable">
                        <thead class="table-light">
                        <tr>
                            <th>Médicament</th>
                            <th>Quantité</th>
                            <th>Prix Unitaire</th>
                            <th>Sous-total</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $rowIndex = 0; @endphp
                        @if(isset($commande) && $commande->medicaments)
                            @foreach($commande->medicaments as $i => $med)
                                <tr>
                                    <td>
                                        <select name="medicaments[{{ $i }}][medicament_id]" class="form-control selectMed" required>
                                            <option value="">-- Choisir --</option>
                                            @foreach($medicaments as $m)
                                                <option value="{{ $m->id }}" {{ $med->id == $m->id ? 'selected' : '' }}>
                                                    {{ $m->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="medicaments[{{ $i }}][quantite]"
                                               class="form-control quantite" value="{{ $med->pivot->quantite }}" min="1" required>
                                    </td>
                                    <td>
                                        <input type="number" name="medicaments[{{ $i }}][prix_unitaire]"
                                               class="form-control prix" value="{{ $med->pivot->prix_unitaire }}" min="0" step="0.01" required>
                                    </td>
                                    <td class="sous-total">{{ number_format($med->pivot->quantite * $med->pivot->prix_unitaire, 2) }}</td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
                                </tr>
                                @php $rowIndex++; @endphp
                            @endforeach
                        @else
                            <tr>
                                <td>
                                    <select name="medicaments[0][medicament_id]" class="form-control selectMed" required>
                                        <option value="">-- Choisir --</option>
                                        @foreach($medicaments as $m)
                                            <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="medicaments[0][quantite]" class="form-control quantite" value="1" min="1" required></td>
                                <td><input type="number" name="medicaments[0][prix_unitaire]" class="form-control prix" value="0" min="0" step="0.01" required></td>
                                <td class="sous-total">0.00</td>
                                <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
                            </tr>
                            @php $rowIndex = 1; @endphp
                        @endif
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-secondary" id="addRow">➕ Ajouter un médicament</button>

                    <div class="mt-3">
                        <h5>Total : <span id="total">0.00</span> CFA</h5>
                        <input type="hidden" name="total" id="totalInput" value="{{ isset($commande) ? $commande->total : 0 }}">
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">✅ {{ isset($commande) ? 'Mettre à jour' : 'Enregistrer' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            let rowIndex = {{ $rowIndex ?? 1 }};
            let medicamentIds = [];

            function calculerTotal() {
                let total = 0;
                $("#medicamentsTable tbody tr").each(function() {
                    let q = parseFloat($(this).find(".quantite").val()) || 0;
                    let p = parseFloat($(this).find(".prix").val()) || 0;
                    let st = q * p;
                    $(this).find(".sous-total").text(st.toFixed(2));
                    total += st;
                });
                $("#total").text(total.toFixed(2));
                $("#totalInput").val(total.toFixed(2));
            }

            function updateMedicamentIds() {
                medicamentIds = $(".selectMed").map(function() {
                    return $(this).val();
                }).get().filter(v => v);
            }

            // Ajouter une ligne
            $("#addRow").click(function() {
                let options = `@foreach($medicaments as $m)<option value="{{ $m->id }}">{{ $m->nom }}</option>@endforeach`;
                let newRow = `<tr>
            <td>
                <select name="medicaments[${rowIndex}][medicament_id]" class="form-control selectMed" required>
                    <option value="">-- Choisir --</option>
                    ${options}
                </select>
            </td>
            <td><input type="number" name="medicaments[${rowIndex}][quantite]" class="form-control quantite" value="1" min="1" required></td>
            <td><input type="number" name="medicaments[${rowIndex}][prix_unitaire]" class="form-control prix" value="0" min="0" step="0.01" required></td>
            <td class="sous-total">0.00</td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
        </tr>`;
                $("#medicamentsTable tbody").append(newRow);
                rowIndex++;
                updateMedicamentIds();
            });

            // Supprimer une ligne
            $(document).on("click", ".removeRow", function() {
                $(this).closest("tr").remove();
                calculerTotal();
                updateMedicamentIds();
            });

            // Vérifier la duplication
            $(document).on("change", ".selectMed", function() {
                let val = $(this).val();
                if(medicamentIds.includes(val)) {
                    alert("Ce médicament est déjà dans le panier !");
                    $(this).val("");
                } else {
                    updateMedicamentIds();
                }
            });

            // Recalcul automatique
            $(document).on("input", ".quantite, .prix", function() {
                calculerTotal();
            });

            calculerTotal();
            updateMedicamentIds();
        });
    </script>
@endsection
