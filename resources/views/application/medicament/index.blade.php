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

                    <a href="{{ route('export.model', 'medicaments') }}" class="btn btn-sm btn-outline-primary" title="Exporter en Excel">
                        <i class="fa fa-file-excel me-1"></i> Exporter
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-open-import-modal" data-module="medicaments" data-label="Médicaments">
                        <i class="fa fa-file-import me-1"></i> Importer
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
                                <th>Famille</th>
                                <th>Stock</th>
                                <th>Stock Min</th>
                                <th>Unité</th>
                                <th class="text-end">P. Vente</th>
                                <th width="120" class="text-center">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        @include('layouts.partials.import_modal')
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
                        data: 'unite_select',
                        name: 'unite_select',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'prix_vente_display',
                        name: 'prix_vente_display',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
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
            // Mise à jour des prix quand on change l'unité dans le select
            $(document).on('change', '.unite-select', function() {
                const selected = $(this).find('option:selected');
                const row = $(this).closest('tr');
                row.find('.price-achat').text(selected.data('achat') || '-');
                row.find('.price-vente').text(selected.data('vente') || '-');
            });

        });
    </script>
@endsection
