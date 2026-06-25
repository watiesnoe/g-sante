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

                    <button type="button" id="btnAdd" class="btn btn-sm btn-primary">
                        <i class="fa fa-plus me-1"></i> Nouveau
                    </button>
                </div>
            </div>
            <div class="block-content block-content-full">
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

    {{-- MODAL — Ajouter / Modifier Médicament --}}
    <div class="modal fade" id="crudModal" tabindex="-1" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Ajouter un Médicament</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="crudForm">
                        @csrf
                        <input type="hidden" name="id" id="id">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Stock actuel <span class="text-danger">*</span></label>
                                <input type="number" name="stock" id="stock" class="form-control" value="0" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock minimum <span class="text-danger">*</span></label>
                                <input type="number" name="stock_min" id="stock_min" class="form-control" value="0" min="0" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Prix Achat <span class="text-danger">*</span></label>
                                <input type="text" name="prix_achat" id="prix_achat" class="form-control price-input" value="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix Vente <span class="text-danger">*</span></label>
                                <input type="text" name="prix_vente" id="prix_vente" class="form-control price-input" value="0" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unité de vente <span class="text-danger">*</span></label>
                                <select name="unite_id" id="unite_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($unites as $u)
                                        <option value="{{ $u->id }}">{{ $u->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Famille <span class="text-danger">*</span></label>
                                <select name="famille_id" id="famille_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach ($familles as $f)
                                        <option value="{{ $f->id }}">{{ $f->nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="text-end border-top pt-3 mt-3">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                <i class="fa fa-times me-1"></i> Fermer
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" id="btnSave">
                                <i class="fa fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            let filterStockCritique = false;

            const ROUTES = {
                index: '{{ route('medicaments.index') }}',
                store: '{{ route('medicaments.store') }}',
                show: '{{ rtrim(url('medicaments'), '/') }}/:id',
                update: '{{ rtrim(url('medicaments'), '/') }}/:id',
                destroy: '{{ rtrim(url('medicaments'), '/') }}/:id',
            };

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

                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nom', name: 'nom' },
                    { data: 'unite', name: 'unite' },
                    { data: 'famille', name: 'famille' },
                    { data: 'stock', name: 'stock' },
                    { data: 'stock_min', name: 'stock_min' },
                    { data: 'prix_achat', name: 'prix_achat' },
                    { data: 'prix_vente', name: 'prix_vente' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                ],

                ajaxData: function(d) {
                    d.famille_id = $('#filterFamille').val();
                    d.stock_critique = filterStockCritique ? 1 : 0;
                },

                mapData: function(data) {
                    $('#nom').val(data.nom);
                    $('#description').val(data.description);
                    $('#stock').val(data.stock);
                    $('#stock_min').val(data.stock_min);
                    $('#prix_achat').val(data.prix_achat);
                    $('#prix_vente').val(data.prix_vente);
                    $('#unite_id').val(data.unite_id);
                    $('#famille_id').val(data.famille_id);
                },
            });

            $('#filterFamille').on('change', function() {
                table.draw();
            });

            // Bouton Stock Critique optimisé
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
                table.draw(); // Relance la requête DataTable vers le serveur avec d.stock_critique mis à jour
            });

            $('#medicamentsTable').on('draw.dt', function() {
                table.rows().every(function() {
                    var d = this.data();
                    // Ajout d'une sécurité au cas où les variables arriveraient en String depuis l'API en ligne
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
                        container.append('<input type="hidden" name="medicament_ids[]" value="' + id + '">');
                    });
                    $('#bulkCommandForm').submit();
                }
            });
        });
    </script>
@endsection