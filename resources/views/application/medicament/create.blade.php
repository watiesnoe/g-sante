@extends('layouts.app')

@section('titre', isset($medicament) ? 'Modifier Médicament' : 'Ajouter Médicament')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1">
                    <i class="fa fa-pills me-2 text-primary"></i>{{ isset($medicament) ? 'Modifier Médicament : ' . $medicament->nom : 'Nouveau Médicament' }}
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('medicaments.index') }}">Médicaments</a></li>
                        <li class="breadcrumb-item active">{{ isset($medicament) ? 'Modifier' : 'Créer' }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                @if(isset($medicament))
                    <a href="{{ route('medicaments.show', $medicament->uuid ?? $medicament->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-eye me-1"></i> Voir Détails
                    </a>
                @endif
                <a href="{{ route('medicaments.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
        </div>

        <form action="{{ isset($medicament) ? route('medicaments.update', $medicament) : route('medicaments.store') }}" method="POST">
            @csrf
            @if(isset($medicament)) @method('PUT') @endif

            <div class="row g-4">
                {{-- Informations générales --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0"><i class="fa fa-info-circle text-primary me-2"></i>Informations générales</h5>
                        </div>
                        <div class="card-body p-4">
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
                    </div>
                </div>

                {{-- Conditionnements / Unités --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0"><i class="fa fa-layer-group text-warning me-2"></i>Conditionnements & Unités</h5>
                            <button type="button" id="btnAddUnitRow" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus me-1"></i> Ajouter une unité
                            </button>
                        </div>
                        <div class="card-body p-0 table-responsive" style="max-height: 450px; overflow: auto;">
                            <table class="table table-sm table-striped align-middle mb-0" id="createUnitsTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="min-width: 130px;">Nom <span class="text-danger">*</span></th>
                                        <th style="min-width: 70px;">Symb. <span class="text-danger">*</span></th>
                                        <th style="min-width: 80px;">Facteur <span class="text-danger">*</span></th>
                                        <th style="min-width: 110px;">P. Achat <span class="text-danger">*</span></th>
                                        <th style="min-width: 110px;">P. Vente <span class="text-danger">*</span></th>
                                        <th class="text-center" style="min-width: 60px;">Défaut</th>
                                        <th class="text-center" style="min-width: 40px;"></th>
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

            <!-- Barre d'actions bas de page -->
            <div class="card border-0 shadow-sm rounded-3 p-3 d-flex flex-row justify-content-between align-items-center mb-4">
                <a href="{{ route('medicaments.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i> Annuler
                </a>
                <button type="submit" class="btn btn-success fw-bold px-4">
                    <i class="fa fa-check me-1"></i> {{ isset($medicament) ? 'Mettre à jour' : 'Enregistrer' }}
                </button>
            </div>
        </form>
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
