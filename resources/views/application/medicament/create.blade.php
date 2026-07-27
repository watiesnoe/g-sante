@extends('layouts.app')

@section('titre', isset($medicament) ? 'Modifier Médicament' : 'Ajouter Médicament')

@section('content')
    <div class="container mt-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold">
                    {{ isset($medicament) ? '✏️ Modifier Médicament' : '➕ Ajouter Médicament' }}
                </h5>
                <a href="{{ route('medicaments.index') }}" class="btn btn-light btn-sm text-primary fw-bold">↩️ Retour à la liste</a>
            </div>
            <div class="card-body p-4">
                <form action="{{ isset($medicament) ? route('medicaments.update', $medicament) : route('medicaments.store') }}" method="POST">
                    @csrf
                    @if(isset($medicament)) @method('PUT') @endif

                    <div class="row g-4 mb-4">
                        <div class="col-md-12">
                            <!-- Nom & Code Barre -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                                    <input type="text" name="nom" class="form-control" value="{{ $medicament->nom ?? old('nom') }}" required placeholder="Ex: Paracétamol 500mg">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Code Barre</label>
                                    <div class="input-group">
                                        <input type="text" name="code_barre" id="code_barre" class="form-control" value="{{ $medicament->code_barre ?? old('code_barre') }}" placeholder="EAN13" maxlength="50">
                                        <button type="button" class="btn btn-outline-secondary" id="btnGenMainBarcode" title="Générer un code aléatoire" tabindex="-1">
                                            <i class="fa fa-barcode"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Famille Médicament -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Famille Médicament <span class="text-danger">*</span></label>
                                <select name="famille_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($familles as $famille)
                                        <option value="{{ $famille->id }}" {{ (isset($medicament) && $medicament->famille_id == $famille->id) ? 'selected' : '' }}>
                                            {{ $famille->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Stock -->
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Stock actuel <span class="text-danger">*</span></label>
                                    <input type="number" name="stock" class="form-control" value="{{ $medicament->stock ?? 0 }}" min="0" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label fw-bold">Stock minimum <span class="text-danger">*</span></label>
                                    <input type="number" name="stock_min" class="form-control" value="{{ $medicament->stock_min ?? 0 }}" min="0" required>
                                </div>
                            </div>

                            <!-- Description --> 
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Description ou remarques importantes...">{{ $medicament->description ?? old('description') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <!-- Conditionnements / Unités -->
                            <div class="card border border-light-dark shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <span class="fw-bold text-primary"><i class="fa fa-boxes me-1"></i>Conditionnements / Unités</span>
                                    <button type="button" id="btnAddUnitRow" class="btn btn-xs btn-primary">
                                        <i class="fa fa-plus me-1"></i> Ajouter une unité
                                    </button>
                                </div>
                                <div class="card-body p-0 table-responsive" style="max-height: 400px; overflow: auto;">
                                    <table class="table table-sm table-striped align-middle mb-0" id="createUnitsTable">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th style="min-width: 150px;">Nom <span class="text-danger">*</span></th>
                                                <th style="min-width: 80px;">Symbole <span class="text-danger">*</span></th>
                                                <th style="min-width: 110px;">Facteur <span class="text-danger">*</span></th>
                                                <th style="min-width: 140px;">P. Achat <span class="text-danger">*</span></th>
                                                <th style="min-width: 140px;">P. Vente <span class="text-danger">*</span></th>
                                                <th class="text-center" style="min-width: 70px;">Défaut</th>
                                                <th class="text-center" style="min-width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="createUnitsBody">
                                            @if(isset($medicament) && $medicament->unites->count() > 0)
                                                @foreach($medicament->unites as $index => $unite)
                                                    <tr class="unit-row" data-index="{{ $index }}">
                                                        <input type="hidden" name="unites[{{ $index }}][id]" value="{{ $unite->id }}">
                                                        <td>
                                                            <input type="text" name="unites[{{ $index }}][nom]" class="form-control form-control-sm" value="{{ $unite->nom }}" required placeholder="Nom">
                                                        </td>
                                                        <td>
                                                            <input type="text" name="unites[{{ $index }}][symbole]" class="form-control form-control-sm" value="{{ $unite->symbole }}" required placeholder="Symb">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="unites[{{ $index }}][facteur]" class="form-control form-control-sm factor-input" value="{{ $unite->facteur }}" min="0.01" step="any" required placeholder="1">
                                                        </td>
                                                        <td>
                                                            <input type="text" inputmode="numeric" name="unites[{{ $index }}][prix_achat]" class="form-control form-control-sm price-input prix-achat-input w-100" value="{{ $unite->prix_achat }}" required>
                                                        </td>
                                                        <td>
                                                            <input type="text" inputmode="numeric" name="unites[{{ $index }}][prix_vente]" class="form-control form-control-sm price-input prix-vente-input w-100" value="{{ $unite->prix_vente }}" required>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="radio" name="default_unit_idx" value="{{ $index }}" class="form-check-input default-unit-radio" {{ $unite->is_default ? 'checked' : '' }}>
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-unit-row">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="unit-row" data-index="0">
                                                    <td>
                                                        <input type="text" name="unites[0][nom]" class="form-control form-control-sm" value="" required placeholder="Nom">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="unites[0][symbole]" class="form-control form-control-sm" value="" required placeholder="Symb">
                                                    </td>
                                                    <td>
                                                        <input type="number" name="unites[0][facteur]" class="form-control form-control-sm factor-input" value="1" min="0.01" step="any" required placeholder="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" inputmode="numeric" name="unites[0][prix_achat]" class="form-control form-control-sm price-input prix-achat-input w-100" value="0" required>
                                                    </td>
                                                    <td>
                                                        <input type="text" inputmode="numeric" name="unites[0][prix_vente]" class="form-control form-control-sm price-input prix-vente-input w-100" value="0" required>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="radio" name="default_unit_idx" value="0" class="form-check-input default-unit-radio" checked>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-unit-row">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between border-top pt-3">
                        <a href="{{ route('medicaments.index') }}" class="btn btn-secondary">↩️ Annuler</a>
                        <button type="submit" class="btn btn-success fw-bold">
                            {{ isset($medicament) ? '💾 Mettre à jour' : '✅ Enregistrer' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            let unitRowIndex = {{ isset($medicament) ? $medicament->unites->count() : 1 }};

            function toggleDeleteButtons() {
                const rows = $('#createUnitsBody .unit-row');
                if (rows.length <= 1) {
                    rows.find('.btn-delete-unit-row').hide();
                } else {
                    rows.find('.btn-delete-unit-row').show();
                }
            }

            function addUnitRow() {
                const body = $('#createUnitsBody');
                const idx = unitRowIndex++;
                
                const rowHtml = `
                    <tr class="unit-row" data-index="${idx}">
                        <td>
                            <input type="text" name="unites[${idx}][nom]" class="form-control form-control-sm" required placeholder="Nom">
                        </td>
                        <td>
                            <input type="text" name="unites[${idx}][symbole]" class="form-control form-control-sm" required placeholder="Symb">
                        </td>
                        <td>
                            <input type="number" name="unites[${idx}][facteur]" class="form-control form-control-sm factor-input" value="1" min="0.01" step="any" required placeholder="1">
                        </td>
                        <td>
                            <input type="text" inputmode="numeric" name="unites[${idx}][prix_achat]" class="form-control form-control-sm price-input prix-achat-input w-100" value="0" required>
                        </td>
                        <td>
                            <input type="text" inputmode="numeric" name="unites[${idx}][prix_vente]" class="form-control form-control-sm price-input prix-vente-input w-100" value="0" required>
                        </td>
                        <td class="text-center">
                            <input type="radio" name="default_unit_idx" value="${idx}" class="form-check-input default-unit-radio">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-unit-row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                
                body.append(rowHtml);
                toggleDeleteButtons();
            }

            $(document).on('click', '#btnAddUnitRow', function() {
                addUnitRow();
            });

            $(document).on('click', '.btn-delete-unit-row', function() {
                const row = $(this).closest('.unit-row');
                const wasChecked = row.find('.default-unit-radio').is(':checked');
                row.remove();
                toggleDeleteButtons();
                if (wasChecked) {
                    $('#createUnitsBody .default-unit-radio').first().prop('checked', true);
                }
            });

            // ====== Auto-Calcul des prix selon facteur ======
            const BODY = '#createUnitsBody';

            function parsePrice(val) {
                if (!val) return 0;
                // Retire les espaces (ex: "20 000" -> "20000")
                let str = val.toString().replace(/[\s\xA0]/g, '');
                // Si la chaîne contient une virgule mais pas de point (ex: "20000,50")
                if (str.indexOf(',') !== -1 && str.indexOf('.') === -1) {
                    str = str.replace(',', '.');
                }
                return parseFloat(str) || 0;
            }

            function formatPriceForDisplay(val) {
                let num = parsePrice(val);
                if (isNaN(num)) return '';
                // Formate avec un espace pour les milliers (ex: 200000 -> 200 000)
                let parts = num.toString().split(".");
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
                return parts.join(",");
            }

            function getBaseUnitPrices() {
                const defaultRow = $(BODY + ' .default-unit-radio:checked').closest('.unit-row');
                if (!defaultRow.length) return null;
                const baseAchat = parsePrice(defaultRow.find('.prix-achat-input').val());
                const baseVente = parsePrice(defaultRow.find('.prix-vente-input').val());
                return { achat: baseAchat, vente: baseVente };
            }

            function recalcAllNonBasePrices() {
                const base = getBaseUnitPrices();
                if (!base) return;
                const defaultRow = $(BODY + ' .default-unit-radio:checked').closest('.unit-row');
                const baseFacteur = parsePrice(defaultRow.find('.factor-input').val()) || 1;

                $(BODY + ' .unit-row').each(function() {
                    if ($(this).find('.default-unit-radio').is(':checked')) return;
                    const facteur = parsePrice($(this).find('.factor-input').val()) || 1;
                    const ratio = facteur / baseFacteur;
                    $(this).find('.prix-achat-input').val(formatPriceForDisplay(Math.round(base.achat * ratio)));
                    $(this).find('.prix-vente-input').val(formatPriceForDisplay(Math.round(base.vente * ratio)));
                });
            }

            $(document).on('change input', BODY + ' .factor-input', function() {
                const row = $(this).closest('.unit-row');
                if (row.find('.default-unit-radio').is(':checked')) {
                    recalcAllNonBasePrices();
                    return;
                }
                const base = getBaseUnitPrices();
                if (!base) return;
                const defaultRow = $(BODY + ' .default-unit-radio:checked').closest('.unit-row');
                const baseFacteur = parsePrice(defaultRow.find('.factor-input').val()) || 1;
                const facteur = parsePrice($(this).val()) || 1;
                const ratio = facteur / baseFacteur;
                row.find('.prix-achat-input').val(formatPriceForDisplay(Math.round(base.achat * ratio)));
                row.find('.prix-vente-input').val(formatPriceForDisplay(Math.round(base.vente * ratio)));
            });

            $(document).on('change input', BODY + ' .prix-achat-input, ' + BODY + ' .prix-vente-input', function() {
                const row = $(this).closest('.unit-row');
                if (row.find('.default-unit-radio').is(':checked')) {
                    recalcAllNonBasePrices();
                }
            });

            $(document).on('change', BODY + ' .default-unit-radio', function() {
                recalcAllNonBasePrices();
            });

            // Format on blur
            $(document).on('blur', BODY + ' .price-input', function() {
                $(this).val(formatPriceForDisplay($(this).val()));
            });

            // Format on load
            $(BODY + ' .price-input').each(function() {
                $(this).val(formatPriceForDisplay($(this).val()));
            });

            // Clean prices before submit
            $('form').on('submit', function() {
                $(BODY + ' .price-input').each(function() {
                    $(this).val(parsePrice($(this).val()));
                });
            });

            // Génération code barre aléatoire (EAN-like 13 chiffres) pour le médicament principal
            $(document).on('click', '#btnGenMainBarcode', function() {
                const randomCode = '200' + Math.floor(Math.random() * 9999999999).toString().padStart(10, '0');
                $('#code_barre').val(randomCode.substring(0, 13));
            });

            // Initialize delete button visibility
            toggleDeleteButtons();
        });
    </script>
@endsection
