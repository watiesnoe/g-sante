@extends('layouts.app')

@section('titre', $pageTitle ?? '🩺 Gestion des Médicaments')

@section('content')
    <div class="container-fluid mt-4">

        {{-- ── Stats ────────────────────────────────────────────── --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small mb-1 opacity-75">Total Molécules</div>
                                <h3 class="fw-bold mb-0">{{ $totalMolecules }}</h3>
                            </div>
                            <i class="fas fa-capsules fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-danger text-white" id="btnStockCritique"
                    style="cursor: pointer; transition: all 0.2s;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small mb-1 opacity-75">Stock Critique</div>
                                <h3 class="fw-bold mb-0 text-white d-flex align-items-center">
                                    {{ $stockCritique }}
                                    <span id="badgeCritiqueActive"
                                        class="badge bg-white text-danger ms-2 font-size-sm d-none">Filtré</span>
                                </h3>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Table ────────────────────────────────────────────── --}}
        <div class="block block-rounded shadow-sm">
            <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                <h3 class="block-title">Gestion de l'Inventaire</h3>
                <div class="d-flex gap-2 align-items-center">

                    <select id="filterFamille" class="form-select form-select-sm" style="width:200px;">
                        <option value="">Toutes les Familles</option>
                        @foreach ($familles as $f)
                            <option value="{{ $f->id }}"
                                {{ (isset($selectedFamilleId) && $selectedFamilleId == $f->id) || request('famille_id') == $f->id ? 'selected' : '' }}>
                                {{ $f->nom }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Bulk commande --}}
                    <form id="bulkCommandForm" action="{{ route('commandes.panier.bulk-ajouter') }}" method="POST"
                        class="d-none">
                        @csrf
                        <div id="selectedMedicamentsInputs"></div>
                    </form>
                    <button type="button" id="btnBulkCommand" class="btn btn-sm btn-alt-success d-none">
                        <i class="fa fa-shopping-cart me-1"></i> Commander (<span id="selectedCount">0</span>)
                    </button>

                    <a href="{{ route('medicaments.create') }}" class="btn btn-sm btn-primary" id="btnAdd">
                        <i class="fa fa-plus me-1"></i> Nouveau
                    </a>
                </div>
            </div>
            <div class="block-content block-content-full ">
                <div class="table-responsive small">
                    <table class="table table-bordered table-sm table-hover" id="medicamentsTable">
                        <thead class="bg-light">
                            <tr>
                                <th width="30" class="text-center">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th>Nom</th>
                                <th>Unité</th>
                                <th>Famille</th>
                                <th>Stock</th>
                                <th>Stock Min</th>
                                <th>Prix Achat</th>
                                <th>Prix Vente</th>
                                <th width="120" class="text-center">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
    <script>
        $(function() {
            let filterStockCritique = false;
            let unitRowIndex = 0;

            const ROUTES = {
                index: '{{ route('medicaments.index') }}',
                store: '{{ route('medicaments.store') }}',
                show: '{{ rtrim(url('medicaments'), '/') }}/:id',
                update: '{{ rtrim(url('medicaments'), '/') }}/:id',
                destroy: '{{ rtrim(url('medicaments'), '/') }}/:id',
            };

            function toggleDeleteButtons() {
                const rows = $('#modalUnitsBody .unit-row');
                if (rows.length <= 1) {
                    rows.find('.btn-delete-unit-row').hide();
                } else {
                    rows.find('.btn-delete-unit-row').show();
                }
            }

            function addUnitRow(data = null) {
                const body = $('#modalUnitsBody');
                const idx = unitRowIndex++;

                const idVal = data ? data.id : '';
                const nomVal = data ? data.nom : '';
                const symboleVal = data ? data.symbole : '';
                const facteurVal = data ? data.facteur : (idx === 0 ? '1' : '');
                const prixAchatVal = data ? data.prix_achat : '0';
                const prixVenteVal = data ? data.prix_vente : '0';
                const isDefault = data ? data.is_default : (idx === 0);

                const rowHtml = `
                    <tr class="unit-row" data-index="${idx}">
                        <input type="hidden" name="unites[${idx}][id]" value="${idVal}">
                        <td>
                            <input type="text" name="unites[${idx}][nom]" class="form-control form-control-sm" value="${nomVal}" required placeholder="Nom">
                        </td>
                        <td>
                            <input type="text" name="unites[${idx}][symbole]" class="form-control form-control-sm" value="${symboleVal}" required placeholder="Symb">
                        </td>
                        <td>
                            <input type="number" name="unites[${idx}][facteur]" class="form-control form-control-sm factor-input" value="${facteurVal}" min="0.01" step="any" required placeholder="1">
                        </td>
                        <td>
                            <input type="number" name="unites[${idx}][prix_achat]" class="form-control form-control-sm price-input prix-achat-input" value="${prixAchatVal}" min="0" step="any" required>
                        </td>
                        <td>
                            <input type="number" name="unites[${idx}][prix_vente]" class="form-control form-control-sm price-input prix-vente-input" value="${prixVenteVal}" min="0" step="any" required>
                        </td>
                        <td class="text-center">
                            <input type="radio" name="default_unit_idx" value="${idx}" class="form-check-input default-unit-radio" ${isDefault ? 'checked' : ''}>
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

            function populateModalUnits(unites) {
                $('#modalUnitsBody').empty();
                unitRowIndex = 0;
                if (unites && unites.length > 0) {
                    unites.forEach(u => addUnitRow(u));
                } else {
                    addUnitRow();
                }
            }

            // ====== Auto-Calcul des prix selon facteur ======
            function getBaseUnitPrices() {
                // L'unité de base = celle dont le radio "Défaut" est coché
                const defaultRow = $('#modalUnitsBody .default-unit-radio:checked').closest('.unit-row');
                if (!defaultRow.length) return null;
                const baseAchat = parseFloat(defaultRow.find('.prix-achat-input').val()) || 0;
                const baseVente = parseFloat(defaultRow.find('.prix-vente-input').val()) || 0;
                return {
                    achat: baseAchat,
                    vente: baseVente
                };
            }

            function recalcAllNonBasePrices() {
                const base = getBaseUnitPrices();
                if (!base) return;
                const defaultRow = $('#modalUnitsBody .default-unit-radio:checked').closest('.unit-row');
                const baseFacteur = parseFloat(defaultRow.find('.factor-input').val()) || 1;

                $('#modalUnitsBody .unit-row').each(function() {
                    if ($(this).find('.default-unit-radio').is(':checked')) return; // skip base
                    const facteur = parseFloat($(this).find('.factor-input').val()) || 1;
                    const ratio = facteur / baseFacteur;
                    $(this).find('.prix-achat-input').val(Math.round(base.achat * ratio));
                    $(this).find('.prix-vente-input').val(Math.round(base.vente * ratio));
                });
            }

            // Quand le facteur d'une ligne non-défaut change → recalcul de ses prix
            $(document).on('change input', '#modalUnitsBody .factor-input', function() {
                const row = $(this).closest('.unit-row');
                if (row.find('.default-unit-radio').is(':checked')) {
                    // Si c'est la ligne de base, recalc tout
                    recalcAllNonBasePrices();
                    return;
                }
                const base = getBaseUnitPrices();
                if (!base) return;
                const defaultRow = $('#modalUnitsBody .default-unit-radio:checked').closest('.unit-row');
                const baseFacteur = parseFloat(defaultRow.find('.factor-input').val()) || 1;
                const facteur = parseFloat($(this).val()) || 1;
                const ratio = facteur / baseFacteur;
                row.find('.prix-achat-input').val(Math.round(base.achat * ratio));
                row.find('.prix-vente-input').val(Math.round(base.vente * ratio));
            });

            // Quand le prix de l'unité de base change → recalcul de toutes les autres
            $(document).on('change input', '#modalUnitsBody .prix-achat-input, #modalUnitsBody .prix-vente-input',
                function() {
                    const row = $(this).closest('.unit-row');
                    if (row.find('.default-unit-radio').is(':checked')) {
                        recalcAllNonBasePrices();
                    }
                });

            // Quand on change l'unité par défaut
            $(document).on('change', '#modalUnitsBody .default-unit-radio', function() {
                recalcAllNonBasePrices();
            });

            // Génération code barre aléatoire (EAN-like 13 chiffres) pour le médicament principal
            $(document).on('click', '#btnGenMainBarcode', function() {
                const randomCode = '200' + Math.floor(Math.random() * 9999999999).toString().padStart(10,
                    '0');
                $('#code_barre').val(randomCode.substring(0, 13));
            });

            // Bind dynamic row actions
            $(document).on('click', '#btnAddUnitRow', function() {
                addUnitRow();
            });

            $(document).on('click', '.btn-delete-unit-row', function() {
                const row = $(this).closest('.unit-row');
                const wasChecked = row.find('.default-unit-radio').is(':checked');
                row.remove();
                toggleDeleteButtons();
                if (wasChecked) {
                    $('#modalUnitsBody .default-unit-radio').first().prop('checked', true);
                }
            });

            // Monitor view/edit state based on modal setup
            $('#crudModal').on('show.bs.modal', function() {
                const isView = $('#modalTitle').text().includes('Détails') || $('#modalTitle').text()
                    .includes('🔍');
                if (isView) {
                    $('#btnAddUnitRow').hide();
                    $('.btn-delete-unit-row').hide();
                    $('#modalUnitsBody input').prop('disabled', true);
                } else {
                    $('#btnAddUnitRow').show();
                    $('.btn-delete-unit-row').show();
                    // Let CrudHelper handle standard fields, but make sure units inputs are enabled if not view mode
                    $('#modalUnitsBody input').prop('disabled', false);
                }
            });

            var table = CrudHelper.init({
                tableId: '#medicamentsTable',
                formId: '#crudForm',
                modalId: '#crudModal',
                modalLabel: '#modalTitle',
                btnAddId: '#btnAdd',
                btnSaveId: '#btnSave',
                hiddenId: '#id',
                editClass: '.edit',
                deleteClass: '.delete',
                viewClass: '.view',
                addTitle: '➕ Ajouter un Médicament',
                editTitle: '✏️ Modifier le Médicament',
                viewTitle: '🔍 Détails du Médicament',

                ajaxUrl: ROUTES.index,
                storeUrl: ROUTES.store,
                showUrl: ROUTES.show,
                updateUrl: ROUTES.update,
                deleteUrl: ROUTES.destroy,

                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nom',
                        name: 'nom'
                    },
                    {
                        data: 'unite',
                        name: 'unite',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'famille',
                        name: 'famille',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'stock_min',
                        name: 'stock_min'
                    },
                    {
                        data: 'prix_achat',
                        name: 'prix_achat',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'prix_vente',
                        name: 'prix_vente',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ],

                ajaxData: function(d) {
                    d.famille_id = $('#filterFamille').val();
                    d.stock_critique = filterStockCritique ? 1 : 0;
                },

                onAdd: function() {
                    populateModalUnits([]);
                },

                mapData: function(data) {
                    $('#nom').val(data.nom);
                    $('#code_barre').val(data.code_barre || '');
                    $('#description').val(data.description);
                    $('#stock').val(data.stock);
                    $('#stock_min').val(data.stock_min);
                    $('#famille_id').val(data.famille_id);
                    populateModalUnits(data.unites);
                },
            });

            $('#filterFamille').on('change', function() {
                table.draw();
            });

            // Bouton Stock Critique
            $('#btnStockCritique').on('click', function() {
                filterStockCritique = !filterStockCritique;
                if (filterStockCritique) {
                    $(this).css({
                        'transform': 'scale(0.98)',
                        'box-shadow': '0 0 10px rgba(220, 53, 69, 0.5)'
                    }).addClass('border border-light');
                    $('#badgeCritiqueActive').removeClass('d-none');
                } else {
                    $(this).css({
                        'transform': '',
                        'box-shadow': ''
                    }).removeClass('border border-light');
                    $('#badgeCritiqueActive').addClass('d-none');
                }
                table.draw();
            });

            $('#medicamentsTable').on('draw.dt', function() {
                table.rows().every(function() {
                    var d = this.data();
                    if (parseInt(d.stock) <= parseInt(d.stock_min)) {
                        $(this.node()).addClass('table-danger');
                    }
                });
            });

            $('#checkAll').on('click', function() {
                $('.medicament-checkbox').prop('checked', this.checked);
                updateBulkButton();
            });

            $(document).on('change', '.medicament-checkbox', function() {
                updateBulkButton();
            });

            function updateBulkButton() {
                var count = $('.medicament-checkbox:checked').length;
                $('#selectedCount').text(count);
                $('#btnBulkCommand').toggleClass('d-none', count === 0);
            }

            $('#btnBulkCommand').on('click', function() {
                var ids = $('.medicament-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (ids.length) {
                    var container = $('#selectedMedicamentsInputs').empty();
                    ids.forEach(function(id) {
                        container.append('<input type="hidden" name="medicament_ids[]" value="' +
                            id + '">');
                    });
                    $('#bulkCommandForm').submit();
                }
            });
        });
    </script>
@endsection
