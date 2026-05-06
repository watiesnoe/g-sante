@extends('layouts.app')

@section('titre', $pageTitle ?? '🩺 Gestion des Médicaments')

@section('content')
    <div class="container-fluid mt-4">
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
                <div class="card border-0 shadow-sm bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small mb-1 opacity-75">Stock Critique</div>
                                <h3 class="fw-bold mb-0 text-white">{{ $stockCritique }}</h3>
                            </div>
                            <i class="fas fa-exclamation-triangle fa-2x opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Contenu principal -->
            <div class="col-12">
                <div class="block block-rounded shadow-sm">
                    <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                        <h3 class="block-title">Gestion de l'Inventaire</h3>
                        <div class="d-flex gap-2 align-items-center">
                            <select id="filterFamille" class="form-select form-select-sm" style="width: 200px;">
                                <option value="">Toutes les Familles</option>
                                @foreach($familles as $f)
                                    <option value="{{ $f->id }}" {{ request('famille_id') == $f->id ? 'selected' : '' }}>{{ $f->nom }}</option>
                                @endforeach
                            </select>
                            
                            <form id="bulkCommandForm" action="{{ route('commandes.panier.bulk-ajouter') }}" method="POST" class="d-none">
                                @csrf
                                <div id="selectedMedicamentsInputs"></div>
                            </form>

                            <button type="button" id="btnBulkCommand" class="btn btn-sm btn-alt-success d-none">
                                <i class="fa fa-shopping-cart me-1"></i> Commander (<span id="selectedCount">0</span>)
                            </button>

                            <a href="{{ route('medicaments.create') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus me-1"></i> Nouveau
                            </a>
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
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            var table = $('#medicamentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('medicaments.index') }}',
                    data: function (d) {
                        d.famille_id = $('#filterFamille').val();
                    }
                },
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'nom', name: 'nom' },
                    { data: 'unite', name: 'unite' },
                    { data: 'famille', name: 'famille' },
                    { data: 'stock', name: 'stock' },
                    { data: 'stock_min', name: 'stock_min' },
                    { data: 'prix_achat', name: 'prix_achat' },
                    { data: 'prix_vente', name: 'prix_vente' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json',
                    paginate: {
                        previous: '<i class="fa fa-chevron-left"></i>',
                        next: '<i class="fa fa-chevron-right"></i>'
                    }
                },
                dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-12 text-center'i><'col-sm-12 text-center'p>>",
                pagingType: 'simple_numbers',
                rowCallback: function(row, data) {
                    if (data.stock <= data.stock_min) {
                        $(row).addClass('table-danger');
                    }
                }
            });

            // Gérer les cases à cocher
            $('#checkAll').on('click', function() {
                $('.medicament-checkbox').prop('checked', this.checked);
                updateBulkButton();
            });

            $(document).on('change', '.medicament-checkbox', function() {
                updateBulkButton();
            });

            function updateBulkButton() {
                let selectedCount = $('.medicament-checkbox:checked').length;
                $('#selectedCount').text(selectedCount);
                if (selectedCount > 0) {
                    $('#btnBulkCommand').removeClass('d-none');
                } else {
                    $('#btnBulkCommand').addClass('d-none');
                }
            }

            $('#btnBulkCommand').on('click', function() {
                let selectedIds = [];
                $('.medicament-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length > 0) {
                    let inputsContainer = $('#selectedMedicamentsInputs');
                    inputsContainer.empty();
                    selectedIds.forEach(id => {
                        inputsContainer.append(`<input type="hidden" name="medicament_ids[]" value="${id}">`);
                    });
                    $('#bulkCommandForm').submit();
                }
            });

            $('#filterFamille').on('change', function() {
                table.draw();
            });

            // Confirmation avant suppression
            $('body').on('submit', 'form.d-inline', function(e) {
                if (!confirm('Voulez-vous vraiment supprimer ce médicament ?')) e.preventDefault();
            });
        });
    </script>
@endsection
