@extends('layouts.app')

@section('title', 'Modifier ' . $inventaire->reference)

@section('styles')
<style>
    /* ── Dropdown catégorie ─────────────────────────────────────── */
    .cat-selector-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        flex-wrap: wrap;
    }
    .cat-selector-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: #475569;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .cat-select-wrapper {
        position: relative;
        flex: 1;
        max-width: 340px;
        min-width: 200px;
    }
    .cat-select-wrapper .cat-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #f39c12;
        pointer-events: none;
        font-size: 0.85rem;
    }
    .cat-select-wrapper .cat-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 0.8rem;
    }
    #catDropdown {
        width: 100%;
        appearance: none;
        -webkit-appearance: none;
        padding: 9px 36px 9px 34px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-size: 0.85rem;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }
    #catDropdown:focus {
        border-color: #f39c12;
        box-shadow: 0 0 0 3px rgba(243,156,18,.12);
    }

    .cat-panel { display: none; }
    .cat-panel.active { display: block; }

    .cat-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        background: linear-gradient(90deg, #fff8ee 0%, #f8fafc 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .cat-panel-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.88rem;
        color: #e67e22;
    }
    .cat-pill-count {
        background: #f39c12;
        color: #fff;
        border-radius: 12px;
        padding: 2px 10px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .summary-sticky {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 2px solid #e2e8f0;
        padding: 12px 20px;
        z-index: 100;
        box-shadow: 0 -4px 12px rgba(0,0,0,.06);
    }
    .summary-stat { min-width: 90px; text-align: center; }
    .summary-stat .num { font-size: 1.5rem; font-weight: 700; line-height: 1; }
    .summary-stat .lbl { font-size: 0.72rem; color: #64748b; text-transform: uppercase; letter-spacing: .5px; }

    .stock-reel-input { min-width: 100px; }

    /* ── Recherche globale ──────────────────────────────────────── */
    .global-search-wrapper {
        position: relative;
        flex: 1;
        max-width: 300px;
        min-width: 180px;
    }
    .global-search-wrapper .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.82rem;
        pointer-events: none;
        transition: color .2s;
    }
    .global-search-wrapper:focus-within .search-icon { color: #f39c12; }
    .global-search-wrapper .clear-search {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.78rem;
        cursor: pointer;
        display: none;
        border: none;
        background: none;
        padding: 2px 4px;
        border-radius: 4px;
        transition: color .15s;
    }
    .global-search-wrapper .clear-search:hover { color: #ef4444; }
    #globalSearch {
        width: 100%;
        padding: 9px 32px 9px 34px;
        border: 1.5px solid #cbd5e1;
        border-radius: 10px;
        background: #fff;
        font-size: 0.85rem;
        color: #1e293b;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }
    #globalSearch:focus {
        border-color: #f39c12;
        box-shadow: 0 0 0 3px rgba(243,156,18,.12);
    }
    #globalSearch::placeholder { color: #b0bec5; }
    #searchResultBadge { font-size: 0.78rem; white-space: nowrap; }
</style>
@endsection

@section('content')
<main id="main-container">
<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 fw-bold mb-1">
                <i class="fa fa-pencil text-warning me-2"></i>Modifier — {{ $inventaire->reference }}
            </h2>
            <p class="text-muted mb-0">Correction des quantités comptées (statut : Brouillon)</p>
        </div>
        <a href="{{ route('inventaires.show', $inventaire->uuid) }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('inventaires.update', $inventaire->uuid) }}" method="POST" id="editForm">
        @csrf
        @method('PUT')

        {{-- Informations générales --}}
        <div class="block block-rounded mb-4">
            <div class="block-header block-header-default">
                <h3 class="block-title"><i class="fa fa-info-circle me-1"></i> Informations générales</h3>
            </div>
            <div class="block-content">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date de l'inventaire <span class="text-danger">*</span></label>
                        <input type="date" name="date_inventaire"
                               class="form-control @error('date_inventaire') is-invalid @enderror"
                               value="{{ old('date_inventaire', $inventaire->date_inventaire->format('Y-m-d')) }}" required>
                        @error('date_inventaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Observations</label>
                        <input type="text" name="observations"
                               class="form-control"
                               value="{{ old('observations', $inventaire->observations) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Bloc principal --}}
        @php
            $totalLignes = $inventaire->lignes->count();
            $isFirst     = true;
        @endphp
        <div class="block block-rounded">
            <div class="block-header block-header-default d-flex justify-content-between align-items-center">
                <h3 class="block-title">
                    <i class="fa fa-layer-group me-1"></i>
                    Correction par catégorie
                    <span class="badge bg-white text-warning ms-2" style="font-size:.75rem;">
                        {{ $lignesParFamille->count() }} catégorie(s) · {{ $totalLignes }} ligne(s)
                    </span>
                </h3>
                <button type="button" id="btnCopierStock" class="btn btn-sm btn-alt-info">
                    <i class="fa fa-copy me-1"></i> Copier théorique (tout)
                </button>
            </div>

            {{-- Barre de contrôles : recherche + dropdown catégorie --}}
            <div class="cat-selector-bar">

                {{-- 🔍 Recherche globale --}}
                <div class="global-search-wrapper">
                    <i class="fa fa-search search-icon"></i>
                    <input type="text" id="globalSearch" placeholder="Rechercher un médicament…" autocomplete="off">
                    <button type="button" class="clear-search" id="clearSearch" title="Effacer">
                        <i class="fa fa-times"></i>
                    </button>
                </div>

                <span id="searchResultBadge" class="badge bg-warning text-dark" style="display:none;"></span>

                {{-- Séparateur --}}
                <div style="width:1px;height:28px;background:#e2e8f0;"></div>

                {{-- 🏷 Dropdown catégorie --}}
                <span class="cat-selector-label">
                    <i class="fa fa-filter text-warning"></i>
                    Catégorie :
                </span>
                <div class="cat-select-wrapper">
                    <i class="fa fa-tag cat-icon"></i>
                    <select id="catDropdown">
                        @foreach($lignesParFamille as $famNom => $lignes)
                        @php
                            $label    = $famNom === '__sans_famille__' ? 'Sans catégorie' : $famNom;
                            $panelKey = Str::slug($famNom, '-');
                        @endphp
                        <option value="panel-{{ $panelKey }}"
                                data-label="{{ $label }}"
                                data-count="{{ $lignes->count() }}"
                                {{ $loop->first ? 'selected' : '' }}>
                            {{ $label }} ({{ $lignes->count() }})
                        </option>
                        @endforeach
                    </select>
                    <i class="fa fa-chevron-down cat-arrow"></i>
                </div>
                <button type="button" class="btn btn-sm btn-alt-secondary" id="btnCopierCat">
                    <i class="fa fa-copy me-1"></i> Copier théorique (catégorie)
                </button>
            </div>

            {{-- Panneaux --}}
            <div class="block-content block-content-full p-0">
                @foreach($lignesParFamille as $famNom => $lignes)
                @php
                    $label    = $famNom === '__sans_famille__' ? 'Sans catégorie' : $famNom;
                    $panelKey = Str::slug($famNom, '-');
                    $isFirst  = $loop->first;
                @endphp
                <div class="cat-panel {{ $isFirst ? 'active' : '' }}" id="panel-{{ $panelKey }}">

                    <div class="cat-panel-header">
                        <div class="cat-panel-title">
                            <i class="fa fa-tag"></i>
                            {{ $label }}
                            <span class="cat-pill-count">{{ $lignes->count() }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-vcenter mb-0 w-100 cat-datatable"
                               id="table-{{ $panelKey }}">
                            <thead class="table-light">
                                <tr>
                                    <th>Médicament</th>
                                    <th class="text-center">Stock théorique</th>
                                    <th class="text-center" style="min-width:130px;">Stock réel <span class="text-danger">*</span></th>
                                    <th class="text-center">Écart</th>
                                    <th style="min-width:170px;">Observations</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($lignes as $ligne)
                            <tr data-ligne-id="{{ $ligne->id }}"
                                data-theorique="{{ $ligne->stock_theorique }}"
                                data-stock-reel="{{ old('stock_reel.' . $ligne->id, $ligne->stock_reel) }}"
                                data-obs="{{ old('obs_ligne.' . $ligne->id, $ligne->observations ?? '') }}">
                                <td class="fw-semibold">{{ $ligne->medicament->nom }}</td>
                                <td class="text-center text-muted">{{ $ligne->stock_theorique }}</td>
                                <td class="text-center">
                                    <input type="number"
                                           class="form-control text-center stock-reel-input"
                                           min="0"
                                           value="{{ old('stock_reel.' . $ligne->id, $ligne->stock_reel) }}"
                                           data-ligne-id="{{ $ligne->id }}"
                                           data-theorique="{{ $ligne->stock_theorique }}"
                                           required>
                                </td>
                                <td class="text-center ecart-cell">
                                    @php $e = $ligne->ecart; @endphp
                                    <span class="badge {{ $e === 0 ? 'bg-success' : ($e > 0 ? 'bg-info' : 'bg-danger') }}">
                                        {{ $e >= 0 ? '+' : '' }}{{ $e }}
                                    </span>
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control obs-ligne-input"
                                           placeholder="Remarque..."
                                           data-ligne-id="{{ $ligne->id }}"
                                           value="{{ old('obs_ligne.' . $ligne->id, $ligne->observations) }}">
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Résumé sticky --}}
        <div class="summary-sticky">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <div class="summary-stat">
                        <div class="num text-success" id="summaryConformes">0</div>
                        <div class="lbl">Conformes</div>
                    </div>
                    <div class="summary-stat">
                        <div class="num text-info" id="summaryExcedents">0</div>
                        <div class="lbl">Excédents</div>
                    </div>
                    <div class="summary-stat">
                        <div class="num text-danger" id="summaryManquants">0</div>
                        <div class="lbl">Manquants</div>
                    </div>
                    <div class="summary-stat">
                        <div class="num text-primary">{{ $totalLignes }}</div>
                        <div class="lbl">Total</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('inventaires.show', $inventaire->uuid) }}" class="btn btn-secondary">
                        <i class="fa fa-times me-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Enregistrer les modifications
                    </button>
                </div>
            </div>
        </div>

        <div id="hiddenFields"></div>
    </form>
</div>
</main>
@endsection

@section('scripts')
<script>
$(document).ready(function () {

    var stockData   = {};
    var obsData     = {};
    var dtInstances = {};

    /* ── Init DataTables ─────────────────────────────────────────── */
    $('.cat-datatable').each(function () {
        var panelId = $(this).closest('.cat-panel').attr('id');

        var dt = $(this).DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            ordering  : true,
            order     : [[0, 'asc']],
            columnDefs: [{ orderable: false, searchable: false, targets: [2, 3, 4] }],
            language  : { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/fr-FR.json' },
            drawCallback: function () {
                restoreValues(this.api());
                updateSummary();
            }
        });

        dtInstances[panelId] = dt;

        dt.rows().nodes().each(function (row) {
            var $row    = $(row);
            var ligneId = $row.data('ligne-id');
            stockData[ligneId] = $row.data('stock-reel');
            obsData[ligneId]   = $row.data('obs') || '';
        });
    });

    /* ── Restaurer ────────────────────────────────────────────────── */
    function restoreValues(dt) {
        dt.rows().nodes().each(function (row) {
            var $row    = $(row);
            var ligneId = $row.data('ligne-id');
            if (stockData[ligneId] !== undefined) $row.find('.stock-reel-input').val(stockData[ligneId]);
            if (obsData[ligneId]   !== undefined) $row.find('.obs-ligne-input').val(obsData[ligneId]);
            updateEcartRow($row);
        });
    }

    /* ── Écart ────────────────────────────────────────────────────── */
    function updateEcartRow($row) {
        var $input    = $row.find('.stock-reel-input');
        var theorique = parseInt($input.data('theorique')) || 0;
        var reel      = parseInt($input.val()) || 0;
        var ecart     = reel - theorique;
        $row.find('.ecart-cell .badge')
            .removeClass('bg-success bg-info bg-danger')
            .addClass(ecart === 0 ? 'bg-success' : (ecart > 0 ? 'bg-info' : 'bg-danger'))
            .text((ecart >= 0 ? '+' : '') + ecart);
    }

    /* ── Résumé ───────────────────────────────────────────────────── */
    function updateSummary() {
        var conformes = 0, excedents = 0, manquants = 0;
        $.each(dtInstances, function (_, dt) {
            dt.rows().nodes().each(function (row) {
                var $row      = $(row);
                var ligneId   = $row.data('ligne-id');
                var theorique = parseInt($row.data('theorique')) || 0;
                var reel      = parseInt(stockData[ligneId] !== undefined ? stockData[ligneId] : theorique);
                var ecart     = reel - theorique;
                if (ecart === 0)     conformes++;
                else if (ecart > 0)  excedents++;
                else                 manquants++;
            });
        });
        $('#summaryConformes').text(conformes);
        $('#summaryExcedents').text(excedents);
        $('#summaryManquants').text(manquants);
    }

    /* ── Afficher un panneau ─────────────────────────────────────── */
    function showPanel(panelId) {
        $('.cat-panel').removeClass('active');
        $('#' + panelId).addClass('active');
        $('#catDropdown').val(panelId);
        if (dtInstances[panelId]) dtInstances[panelId].columns.adjust().draw(false);
    }

    $('#catDropdown').on('change', function () {
        if ($('#globalSearch').val() !== '') clearGlobalSearch();
        showPanel($(this).val());
    });

    /* ── Recherche globale ───────────────────────────────────────── */
    var searchTimer = null;

    function clearGlobalSearch() {
        $('#globalSearch').val('');
        $('#clearSearch').hide();
        $('#searchResultBadge').hide();
        $.each(dtInstances, function (_, dt) { dt.search('').draw(false); });
        showPanel($('#catDropdown').val());
    }

    $('#globalSearch').on('input', function () {
        clearTimeout(searchTimer);
        var term = $.trim($(this).val());
        if (term === '') { clearGlobalSearch(); return; }
        $('#clearSearch').show();

        searchTimer = setTimeout(function () {
            var totalFound = 0, firstPanel = null;

            $.each(dtInstances, function (panelId, dt) {
                dt.search(term).draw(false);
                var count = dt.rows({ search: 'applied' }).count();
                totalFound += count;
                if (count > 0 && firstPanel === null) firstPanel = panelId;
            });

            if (firstPanel) {
                $('.cat-panel').removeClass('active');
                $('#' + firstPanel).addClass('active');
                $('#catDropdown').val(firstPanel);
                if (dtInstances[firstPanel]) dtInstances[firstPanel].columns.adjust();
            }

            var $badge = $('#searchResultBadge');
            if (totalFound > 0) {
                $badge.text(totalFound + ' résultat' + (totalFound > 1 ? 's' : '')).show()
                      .removeClass('bg-danger text-white').addClass('bg-warning text-dark');
            } else {
                $badge.text('Aucun résultat').show()
                      .removeClass('bg-warning text-dark').addClass('bg-danger text-white');
            }
        }, 250);
    });

    $('#clearSearch').on('click', function () { clearGlobalSearch(); });


    /* ── Saisie ───────────────────────────────────────────────────── */
    $(document).on('input', '.stock-reel-input', function () {
        stockData[$(this).data('ligne-id')] = $(this).val();
        updateEcartRow($(this).closest('tr'));
        updateSummary();
    });

    $(document).on('input', '.obs-ligne-input', function () {
        obsData[$(this).data('ligne-id')] = $(this).val();
    });

    /* ── Copier tout ──────────────────────────────────────────────── */
    $('#btnCopierStock').on('click', function () {
        $.each(dtInstances, function (_, dt) {
            dt.rows().nodes().each(function (row) {
                var $row    = $(row);
                var ligneId = $row.data('ligne-id');
                var th      = $row.data('theorique');
                stockData[ligneId] = th;
                $row.find('.stock-reel-input').val(th);
                updateEcartRow($row);
            });
        });
        updateSummary();
    });

    /* ── Copier catégorie active ───────────────────────────────────── */
    $('#btnCopierCat').on('click', function () {
        var panelId = $('#catDropdown').val();
        var dt = dtInstances[panelId];
        if (!dt) return;
        dt.rows().nodes().each(function (row) {
            var $row    = $(row);
            var ligneId = $row.data('ligne-id');
            var th      = $row.data('theorique');
            stockData[ligneId] = th;
            $row.find('.stock-reel-input').val(th);
            updateEcartRow($row);
        });
        updateSummary();
    });

    /* ── Submit ───────────────────────────────────────────────────── */
    $('#editForm').on('submit', function () {
        var $hidden = $('#hiddenFields').empty();
        $.each(stockData, function (ligneId, val) {
            $('<input>').attr({ type:'hidden', name:'stock_reel['+ligneId+']', value: val !== '' ? val : 0 }).appendTo($hidden);
        });
        $.each(obsData, function (ligneId, obs) {
            if (obs && obs !== '') {
                $('<input>').attr({ type:'hidden', name:'obs_ligne['+ligneId+']', value: obs }).appendTo($hidden);
            }
        });
        return true;
    });

    updateSummary();
});
</script>
@endsection
